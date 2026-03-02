<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\ProductPageSearch\Business\ProductConcretePageSearchWriter;

use Generated\Shared\Transfer\ProductConcretePageSearchTransfer;
use Spryker\Zed\ProductPageSearch\Persistence\ProductPageSearchEntityManagerInterface;

class ProductConcretePageSearchWriter implements ProductConcretePageSearchWriterInterface
{
    /**
     * @var \Spryker\Zed\ProductPageSearch\Persistence\ProductPageSearchEntityManagerInterface
     */
    protected $entityManager;

    public function __construct(ProductPageSearchEntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function saveProductConcretePageSearch(ProductConcretePageSearchTransfer $productConcretePageSearchTransfer): ProductConcretePageSearchTransfer
    {
        return $this->entityManager->saveProductConcretePageSearch($productConcretePageSearchTransfer);
    }

    public function deleteProductConcretePageSearch(ProductConcretePageSearchTransfer $productConcretePageSearchTransfer): bool
    {
        return $this->entityManager->deleteProductConcretePageSearch($productConcretePageSearchTransfer);
    }
}
