<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\ProductPageSearch\Business\Provider;

use ArrayObject;
use DateTime;
use Generated\Shared\Transfer\ElasticsearchSearchContextTransfer;
use Generated\Shared\Transfer\ProductAbstractReadinessRequestTransfer;
use Generated\Shared\Transfer\ProductReadinessTransfer;
use Generated\Shared\Transfer\SearchContextTransfer;
use Generated\Shared\Transfer\SearchDocumentTransfer;
use Generated\Shared\Transfer\SynchronizationDataTransfer;
use RuntimeException;
use Spryker\Client\Search\SearchClientInterface;
use Spryker\Service\Synchronization\SynchronizationServiceInterface;
use Spryker\Zed\ProductPageSearch\Persistence\ProductPageSearchRepositoryInterface;
use Spryker\Zed\ProductPageSearch\ProductPageSearchConfig;

class PageSearchProductAbstractReadinessProvider implements ProductAbstractReadinessProviderInterface
{
    protected const string TITLE_IN_PAGE_SEARCH = 'In Page Search for stores/locale';

    protected const string FALLBACK_VALUE = '-';

    protected const string DEFAULT_PRODUCT_ABSTRACT_INDEX_TYPE = 'page';

    protected const string DEFAULT_SOURCE_IDENTIFIER = 'page';

    protected const string FORMAT_DATE_OUTPUT = 'Y-m-d H:i:s';

    protected const string FORMAT_DATE_WITH_UTC = '%s UTC';

    protected const string FORMAT_ROW = '%s: %s, document: %s &mdash; Last updated. DB: <strong>%s</strong>. Status: %s';

    protected const string FORMAT_DOCUMENT_KEY_LINK = '<a href="/search-elasticsearch-gui/maintenance/document-info?documentId=%s&index=%s" target="_blank">%s</a>';

    protected const string STATUS_HTML_SYNCED = '<span style="color:green;font-weight:bold">Synced</span>';

    protected const string STATUS_HTML_UNSYNCED = '<span style="color:red;font-weight:bold">Unsynced</span>';

    protected const string KEY_STORE = 'store';

    protected const string KEY_LOCALE = 'locale';

    protected const string KEY_UPDATED_AT = 'updated_at';

    protected const string KEY_DATA = 'data';

    public function __construct(
        protected SearchClientInterface $searchClient,
        protected SynchronizationServiceInterface $synchronizationService,
        protected ProductPageSearchRepositoryInterface $productPageSearchRepository,
        protected ProductPageSearchConfig $config,
    ) {
    }

    /**
     * @param \Generated\Shared\Transfer\ProductAbstractReadinessRequestTransfer $productAbstractReadinessRequestTransfer
     * @param \ArrayObject<int, \Generated\Shared\Transfer\ProductReadinessTransfer> $productReadinessTransfers
     *
     * @return \ArrayObject<int, \Generated\Shared\Transfer\ProductReadinessTransfer>
     */
    public function provide(
        ProductAbstractReadinessRequestTransfer $productAbstractReadinessRequestTransfer,
        ArrayObject $productReadinessTransfers
    ): ArrayObject {
        $idProductAbstract = $productAbstractReadinessRequestTransfer->getProductAbstract()->getIdProductAbstract();
        $entries = $this->productPageSearchRepository->getProductAbstractPageSearchEntriesByIdProductAbstract($idProductAbstract);

        $productReadinessTransfers->append(
            (new ProductReadinessTransfer())
                ->setTitle(static::TITLE_IN_PAGE_SEARCH)
                ->setValues($this->buildRowValues($idProductAbstract, $entries)),
        );

        return $productReadinessTransfers;
    }

    /**
     * @param array<int, array<string, mixed>> $entries One entry per store+locale combination
     *
     * @return array<string>
     */
    protected function buildRowValues(int $idProductAbstract, array $entries): array
    {
        if (!$entries) {
            return [static::FALLBACK_VALUE];
        }

        $values = [];

        foreach ($entries as $entry) {
            $values[] = $this->formatRow($idProductAbstract, $entry);
        }

        return $values;
    }

    /**
     * @param array<string, mixed> $entry
     */
    protected function formatRow(int $idProductAbstract, array $entry): string
    {
        $storeName = $entry[static::KEY_STORE];
        $locale = $entry[static::KEY_LOCALE];
        $dbData = $entry[static::KEY_DATA] ?? null;
        $dbUpdatedAt = $entry[static::KEY_UPDATED_AT] ?? null;

        $dbFormatted = $dbUpdatedAt !== null
            ? sprintf(static::FORMAT_DATE_WITH_UTC, $this->formatUpdatedAt($dbUpdatedAt))
            : static::FALLBACK_VALUE;

        $documentKey = $this->buildProductAbstractDocumentKey($idProductAbstract, $storeName, $locale);
        $esData = $this->readDocumentData($documentKey, $storeName);

        $statusHtml = ($esData !== null && $dbData !== null && $esData === $dbData)
            ? static::STATUS_HTML_SYNCED
            : static::STATUS_HTML_UNSYNCED;

        $indexName = $this->buildIndexName($storeName);
        $documentKeyLink = sprintf(static::FORMAT_DOCUMENT_KEY_LINK, $documentKey, $indexName, $documentKey);

        return sprintf(static::FORMAT_ROW, $storeName, $locale, $documentKeyLink, $dbFormatted, $statusHtml);
    }

    protected function buildIndexName(string $storeName): string
    {
        $parts = array_filter([
            $this->config->getSearchIndexPrefix(),
            strtolower($storeName),
            static::DEFAULT_SOURCE_IDENTIFIER,
        ]);

        return implode('_', $parts);
    }

    protected function buildProductAbstractDocumentKey(int $idProductAbstract, string $storeName, string $localeName): string
    {
        $synchronizationDataTransfer = new SynchronizationDataTransfer();
        $synchronizationDataTransfer->setStore(strtolower($storeName));
        $synchronizationDataTransfer->setLocale($localeName);
        $synchronizationDataTransfer->setReference((string)$idProductAbstract);

        $storageKeyBuilder = $this->synchronizationService
            ->getStorageKeyBuilder(ProductPageSearchConfig::PRODUCT_ABSTRACT_RESOURCE_NAME);

        return $storageKeyBuilder->generateKey($synchronizationDataTransfer);
    }

    /**
     * @return array<string, mixed>|null
     */
    protected function readDocumentData(string $documentKey, ?string $storeName = null): ?array
    {
        try {
            $searchDocumentTransfer = new SearchDocumentTransfer();
            $searchDocumentTransfer->setId($documentKey);
            $searchDocumentTransfer->setType(static::DEFAULT_PRODUCT_ABSTRACT_INDEX_TYPE);

            $searchContextTransfer = new SearchContextTransfer();
            $searchContextTransfer->setSourceIdentifier(static::DEFAULT_SOURCE_IDENTIFIER);

            $elasticsearchContext = new ElasticsearchSearchContextTransfer();
            $elasticsearchContext->setTypeName(static::DEFAULT_PRODUCT_ABSTRACT_INDEX_TYPE);
            $searchContextTransfer->setElasticsearchContext($elasticsearchContext);
            $searchContextTransfer->setStoreName($storeName);

            $searchDocumentTransfer->setSearchContext($searchContextTransfer);

            if ($storeName !== null) {
                $searchDocumentTransfer->getSearchContext()->setStoreName($storeName);
            }

            $document = $this->searchClient->readDocument($searchDocumentTransfer);

            return $document !== null ? (array)$document->getData() : null;
        } catch (RuntimeException) {
            return null;
        }
    }

    protected function formatUpdatedAt(?string $updatedAt): string
    {
        if ($updatedAt === null) {
            return static::FALLBACK_VALUE;
        }

        $dateTime = DateTime::createFromFormat('Y-m-d H:i:s.u', $updatedAt)
            ?: DateTime::createFromFormat('Y-m-d H:i:s', $updatedAt);

        if ($dateTime === false) {
            return $updatedAt;
        }

        return $dateTime->format(static::FORMAT_DATE_OUTPUT);
    }
}
