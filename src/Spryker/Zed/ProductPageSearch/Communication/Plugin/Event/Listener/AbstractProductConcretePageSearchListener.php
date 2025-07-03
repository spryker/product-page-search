<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\ProductPageSearch\Communication\Plugin\Event\Listener;

use Spryker\Zed\Event\Dependency\Plugin\EventBulkHandlerInterface;
use Spryker\Zed\Kernel\Communication\AbstractPlugin;

/**
 * @method \Spryker\Zed\ProductPageSearch\Business\ProductPageSearchFacadeInterface getFacade()
 */
abstract class AbstractProductConcretePageSearchListener extends AbstractPlugin implements EventBulkHandlerInterface
{
    /**
     * @var array<int>
     */
    protected static $publishedProductConcreteIds = [];

    /**
     * @var array<int>
     */
    protected static $unpublishedProductConcreteIds = [];

    /**
     * @param array<int> $productIds
     *
     * @return void
     */
    protected function publish(array $productIds): void
    {
        $productIds = array_values(array_unique(array_diff($productIds, static::$publishedProductConcreteIds)));
        if ($productIds) {
            $this->getFacade()->publishProductConcretes($productIds);
        }
        static::$publishedProductConcreteIds = array_merge(static::$publishedProductConcreteIds, $productIds);
    }

    /**
     * @param array<int> $productIds
     *
     * @return void
     */
    protected function unpublish(array $productIds): void
    {
        $productIds = array_values(array_unique(array_diff($productIds, static::$unpublishedProductConcreteIds)));
        if ($productIds) {
            $this->getFacade()->unpublishProductConcretes($productIds);
        }

        static::$unpublishedProductConcreteIds = array_merge(static::$unpublishedProductConcreteIds, $productIds);
    }
}
