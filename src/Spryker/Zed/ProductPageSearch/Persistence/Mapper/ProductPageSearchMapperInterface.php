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

interface ProductPageSearchMapperInterface
{
    public function mapProductConcretePageSearchEntityToTransfer(
        SpyProductConcretePageSearch $productConcretePageSearchEntity,
        ProductConcretePageSearchTransfer $productConcretePageSearchTransfer
    ): ProductConcretePageSearchTransfer;

    public function mapProductConcretePageSearchTransferToEntity(
        ProductConcretePageSearchTransfer $productConcretePageSearchTransfer,
        SpyProductConcretePageSearch $productConcretePageSearchEntity
    ): SpyProductConcretePageSearch;

    /**
     * @param \Propel\Runtime\Collection\ObjectCollection<\Orm\Zed\ProductPageSearch\Persistence\SpyProductConcretePageSearch> $productConcretePageSearchEntityCollection
     *
     * @return array<\Generated\Shared\Transfer\SynchronizationDataTransfer>
     */
    public function mapProductConcretePageSearchEntityCollectionToSynchronizationDataTransfers(
        ObjectCollection $productConcretePageSearchEntityCollection
    ): array;

    public function mapProductConcretePageSearchEntityToSynchronizationDataTransfer(
        SpyProductConcretePageSearch $productConcretePageSearchEntity,
        SynchronizationDataTransfer $synchronizationDataTransfer
    ): SynchronizationDataTransfer;
}
