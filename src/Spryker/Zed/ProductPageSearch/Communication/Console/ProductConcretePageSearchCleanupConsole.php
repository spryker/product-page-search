<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\ProductPageSearch\Communication\Console;

use Propel\Runtime\Propel;
use Spryker\Zed\Kernel\Communication\Console\Console;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Migration command: removes stale concrete product OpenSearch index entries after
 * enabling PRODUCT_CONCRETE_SEARCH_IN_STORAGE_ENABLED. Reads concrete product IDs
 * from the database in batches and directly unpublishes the corresponding search index entries.
 *
 * @method \Spryker\Zed\ProductPageSearch\Business\ProductPageSearchFacadeInterface getFacade()
 * @method \Spryker\Zed\ProductPageSearch\Persistence\ProductPageSearchQueryContainerInterface getQueryContainer()
 * @method \Spryker\Zed\ProductPageSearch\Communication\ProductPageSearchCommunicationFactory getFactory()
 * @method \Spryker\Zed\ProductPageSearch\Persistence\ProductPageSearchRepositoryInterface getRepository()
 */
class ProductConcretePageSearchCleanupConsole extends Console
{
    protected const string COMMAND_NAME = 'product-concrete-page-search:cleanup';

    protected const string DESCRIPTION = 'Migration: unpublishes all concrete products to remove stale ElasticSearch/OpenSearch index entries. Run after enabling PRODUCT_CONCRETE_SEARCH_IN_STORAGE_ENABLED.';

    protected const int BATCH_SIZE = 200;

    protected const string OPTION_LIMIT = 'limit';

    protected const string OPTION_OFFSET = 'offset';

    protected function configure(): void
    {
        $this->setName(static::COMMAND_NAME);
        $this->setDescription(static::DESCRIPTION);
        $this->addOption(static::OPTION_LIMIT, null, InputOption::VALUE_OPTIONAL, 'Maximum number of concrete products to process.');
        $this->addOption(static::OPTION_OFFSET, null, InputOption::VALUE_OPTIONAL, 'Starting offset for processing concrete products.', 0);

        parent::configure();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        Propel::disableInstancePooling();

        $offset = (int)$input->getOption(static::OPTION_OFFSET);
        $limit = $input->getOption(static::OPTION_LIMIT) !== null ? (int)$input->getOption(static::OPTION_LIMIT) : null;

        $total = $this->resolveTotalCount($limit, $offset);

        $progressBar = new ProgressBar($output, $total);
        $progressBar->setFormat(' %current%/%max% [%bar%] %percent:3s%% %elapsed:6s%/%estimated:-6s% mem:%memory:6s%');
        $progressBar->start();

        $processed = 0;
        $currentOffset = $offset;

        do {
            $batchLimit = $this->resolveBatchLimit($processed, $limit);
            $productConcreteIds = $this->getRepository()->getProductConcreteIdsByOffsetAndLimit($currentOffset, $batchLimit);

            if (!$productConcreteIds) {
                break;
            }

            $this->getFacade()->unpublishProductConcretes($productConcreteIds);

            $batchCount = count($productConcreteIds);
            $processed += $batchCount;
            $currentOffset += $batchCount;

            $progressBar->advance($batchCount);
        } while ($batchCount === $batchLimit && ($limit === null || $processed < $limit));

        $progressBar->finish();
        $output->writeln('');
        $output->writeln(sprintf('Done. Unpublished %d concrete products. Peak memory: %s MB.', $processed, round(memory_get_peak_usage(true) / 1024 / 1024, 1)));

        return static::CODE_SUCCESS;
    }

    protected function resolveTotalCount(?int $limit, int $offset): int
    {
        $totalInDatabase = $this->getRepository()->countProductConcretes();
        $available = max(0, $totalInDatabase - $offset);

        if ($limit !== null) {
            return min($limit, $available);
        }

        return $available;
    }

    protected function resolveBatchLimit(int $processed, ?int $limit): int
    {
        if ($limit === null) {
            return static::BATCH_SIZE;
        }

        return min(static::BATCH_SIZE, $limit - $processed);
    }
}
