<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\ProductPageSearch\Business\Expander;

use Generated\Shared\Transfer\ProductPageLoadTransfer;

interface PriceProductPageExpanderInterface
{
    public function expandProductPageLoadTransferWithPricesData(
        ProductPageLoadTransfer $productPageLoadTransfer
    ): ProductPageLoadTransfer;
}
