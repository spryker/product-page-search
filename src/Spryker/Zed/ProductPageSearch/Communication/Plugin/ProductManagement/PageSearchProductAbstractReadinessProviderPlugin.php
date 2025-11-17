<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\ProductPageSearch\Communication\Plugin\ProductManagement;

use ArrayObject;
use Generated\Shared\Transfer\ProductAbstractReadinessRequestTransfer;
use Spryker\Zed\Kernel\Communication\AbstractPlugin;
use Spryker\Zed\ProductManagementExtension\Dependency\Plugin\ProductAbstractReadinessProviderPluginInterface;

/**
 * @method \Spryker\Zed\ProductPageSearch\Business\ProductPageSearchBusinessFactory getBusinessFactory()
 * @method \Spryker\Zed\ProductPageSearch\ProductPageSearchConfig getConfig()
 */
class PageSearchProductAbstractReadinessProviderPlugin extends AbstractPlugin implements ProductAbstractReadinessProviderPluginInterface
{
    /**
     * Specification:
     * {@inheritDoc}
     * - Expands product readiness collection with page search status check.
     * - Checks if product document exists in Elasticsearch for each store/locale combination.
     *
     * @api
     *
     * @param \Generated\Shared\Transfer\ProductAbstractReadinessRequestTransfer $productAbstractReadinessRequestTransfer
     * @param \ArrayObject<int, \Generated\Shared\Transfer\ProductReadinessTransfer> $productReadinessTransfers
     *
     * @return \ArrayObject<int, \Generated\Shared\Transfer\ProductReadinessTransfer>
     */
    public function provide(
        ProductAbstractReadinessRequestTransfer $productAbstractReadinessRequestTransfer,
        ArrayObject $productReadinessTransfers
    ): ArrayObject {
        return $this->getBusinessFactory()
            ->createPageSearchProductAbstractReadinessProvider()
            ->provide($productAbstractReadinessRequestTransfer, $productReadinessTransfers);
    }
}
