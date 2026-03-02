<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\ProductPageSearch\Persistence;

use Generated\Shared\Transfer\ProductConcretePageSearchTransfer;

interface ProductPageSearchEntityManagerInterface
{
    public function saveProductConcretePageSearch(ProductConcretePageSearchTransfer $productConcretePageSearchTransfer): ProductConcretePageSearchTransfer;

    public function deleteProductConcretePageSearch(ProductConcretePageSearchTransfer $productConcretePageSearchTransfer): bool;
}
