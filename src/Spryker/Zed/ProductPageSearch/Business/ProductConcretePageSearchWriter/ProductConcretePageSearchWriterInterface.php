<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\ProductPageSearch\Business\ProductConcretePageSearchWriter;

use Generated\Shared\Transfer\ProductConcretePageSearchTransfer;

interface ProductConcretePageSearchWriterInterface
{
    public function saveProductConcretePageSearch(ProductConcretePageSearchTransfer $productConcretePageSearchTransfer): ProductConcretePageSearchTransfer;

    public function deleteProductConcretePageSearch(ProductConcretePageSearchTransfer $productConcretePageSearchTransfer): bool;
}
