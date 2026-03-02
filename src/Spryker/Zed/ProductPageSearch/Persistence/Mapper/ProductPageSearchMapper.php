<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\ProductPageSearch\Persistence\Mapper;

use Generated\Shared\Transfer\ProductConcretePageSearchTransfer;
use Generated\Shared\Transfer\SynchronizationDataTransfer;
use Orm\Zed\ProductPageSearch\Persistence\SpyProductConcretePageSearch;
use Propel\Runtime\Collection\ObjectCollection;

class ProductPageSearchMapper implements ProductPageSearchMapperInterface
{
    public function mapProductConcretePageSearchEntityToTransfer(
        SpyProductConcretePageSearch $productConcretePageSearchEntity,
        ProductConcretePageSearchTransfer $productConcretePageSearchTransfer
    ): ProductConcretePageSearchTransfer {
        return $productConcretePageSearchTransfer->fromArray(
            $productConcretePageSearchEntity->toArray(),
            true,
        );
    }

    public function mapProductConcretePageSearchTransferToEntity(
        ProductConcretePageSearchTransfer $productConcretePageSearchTransfer,
        SpyProductConcretePageSearch $productConcretePageSearchEntity
    ): SpyProductConcretePageSearch {
        $productConcretePageSearchEntity->fromArray(
            $productConcretePageSearchTransfer->toArray(),
        );
        if ($productConcretePageSearchTransfer->getTimestamp() !== null) {
            $productConcretePageSearchEntity->setUpdatedAt($productConcretePageSearchTransfer->getTimestamp());
        }

        return $productConcretePageSearchEntity;
    }

    /**
     * @param \Propel\Runtime\Collection\ObjectCollection<\Orm\Zed\ProductPageSearch\Persistence\SpyProductConcretePageSearch> $productConcretePageSearchEntityCollection
     *
     * @return array<\Generated\Shared\Transfer\SynchronizationDataTransfer>
     */
    public function mapProductConcretePageSearchEntityCollectionToSynchronizationDataTransfers(
        ObjectCollection $productConcretePageSearchEntityCollection
    ): array {
        $synchronizationDataTransfers = [];

        foreach ($productConcretePageSearchEntityCollection as $productConcretePageSearchEntity) {
            $synchronizationDataTransfers[] = $this->mapProductConcretePageSearchEntityToSynchronizationDataTransfer(
                $productConcretePageSearchEntity,
                new SynchronizationDataTransfer(),
            );
        }

        return $synchronizationDataTransfers;
    }

    public function mapProductConcretePageSearchEntityToSynchronizationDataTransfer(
        SpyProductConcretePageSearch $productConcretePageSearchEntity,
        SynchronizationDataTransfer $synchronizationDataTransfer
    ): SynchronizationDataTransfer {
        return $synchronizationDataTransfer
            ->setData($productConcretePageSearchEntity->getData())
            ->setKey($productConcretePageSearchEntity->getKey())
            ->setStore($productConcretePageSearchEntity->getStore());
    }
}
