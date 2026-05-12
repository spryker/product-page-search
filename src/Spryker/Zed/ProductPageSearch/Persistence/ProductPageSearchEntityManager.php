<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\ProductPageSearch\Persistence;

use Generated\Shared\Transfer\ProductConcretePageSearchTransfer;
use Orm\Zed\ProductPageSearch\Persistence\SpyProductConcretePageSearch;
use Spryker\Zed\Kernel\Persistence\AbstractEntityManager;
use Spryker\Zed\Propel\Persistence\BatchProcessor\ActiveRecordBatchProcessorTrait;

/**
 * @method \Spryker\Zed\ProductPageSearch\Persistence\ProductPageSearchPersistenceFactory getFactory()
 */
class ProductPageSearchEntityManager extends AbstractEntityManager implements ProductPageSearchEntityManagerInterface
{
    use ActiveRecordBatchProcessorTrait;

    public function deleteProductConcretePageSearch(ProductConcretePageSearchTransfer $productConcretePageSearchTransfer): bool
    {
        $productConcreteSearchPageEntity = $this->getFactory()
            ->createProductConcretePageSearchQuery()
            ->filterByIdProductConcretePageSearch($productConcretePageSearchTransfer->getIdProductConcretePageSearch())
            ->findOne();

        if ($productConcreteSearchPageEntity === null) {
            return false;
        }

        $productConcreteSearchPageEntity->delete();

        return true;
    }

    /**
     * @param array<\Generated\Shared\Transfer\ProductConcretePageSearchTransfer> $productConcretePageSearchTransfers
     */
    public function saveProductConcretePageSearchBatch(array $productConcretePageSearchTransfers): void
    {
        if (!$productConcretePageSearchTransfers) {
            return;
        }

        $productIds = array_unique(array_map(
            fn (ProductConcretePageSearchTransfer $transfer) => $transfer->getFkProduct(),
            $productConcretePageSearchTransfers,
        ));

        $existingEntitiesMap = $this->indexExistingEntitiesByProductStoreLocale($productIds);
        $mapper = $this->getFactory()->createProductPageSearchMapper();

        foreach ($productConcretePageSearchTransfers as $productConcretePageSearchTransfer) {
            $entity = $existingEntitiesMap[$productConcretePageSearchTransfer->getFkProduct()][$productConcretePageSearchTransfer->getStore()][$productConcretePageSearchTransfer->getLocale()]
                ?? new SpyProductConcretePageSearch();

            $entity = $mapper->mapProductConcretePageSearchTransferToEntity($productConcretePageSearchTransfer, $entity);

            $this->persist($entity);
        }

        $this->commit();
    }

    /**
     * @param array<int> $productIds
     *
     * @return array<int, array<string, array<string, \Orm\Zed\ProductPageSearch\Persistence\SpyProductConcretePageSearch>>>
     */
    protected function indexExistingEntitiesByProductStoreLocale(array $productIds): array
    {
        $existingEntities = $this->getFactory()
            ->createProductConcretePageSearchQuery()
            ->filterByFkProduct_In($productIds)
            ->find();

        $indexedEntities = [];
        foreach ($existingEntities as $entity) {
            $indexedEntities[$entity->getFkProduct()][$entity->getStore()][$entity->getLocale()] = $entity;
        }

        return $indexedEntities;
    }
}
