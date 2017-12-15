<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\ProductPageSearch\Business\Model;

use Generated\Shared\Transfer\ProductPageSearchTransfer;
use Orm\Zed\ProductPageSearch\Persistence\SpyProductAbstractPageSearch;

interface ProductPageSearchWriterInterface
{

    /**
     * @param \Generated\Shared\Transfer\ProductPageSearchTransfer $productPageSearchTransfer
     * @param \Orm\Zed\ProductPageSearch\Persistence\SpyProductAbstractPageSearch|null $productPageSearchEntity
     * @param array $data
     *
     * @return void
     */
    public function save(ProductPageSearchTransfer $productPageSearchTransfer, SpyProductAbstractPageSearch $productPageSearchEntity, array $data);

}
