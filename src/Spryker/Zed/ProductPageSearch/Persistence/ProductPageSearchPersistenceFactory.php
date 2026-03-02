<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\ProductPageSearch\Persistence;

use Orm\Zed\Category\Persistence\SpyCategoryClosureTableQuery;
use Orm\Zed\Category\Persistence\SpyCategoryNodeQuery;
use Orm\Zed\PriceProduct\Persistence\SpyPriceProductQuery;
use Orm\Zed\Product\Persistence\SpyProductQuery;
use Orm\Zed\ProductCategory\Persistence\SpyProductCategoryQuery;
use Orm\Zed\ProductImage\Persistence\SpyProductImageSetQuery;
use Orm\Zed\ProductPageSearch\Persistence\SpyProductAbstractPageSearchQuery;
use Orm\Zed\ProductPageSearch\Persistence\SpyProductConcretePageSearchQuery;
use Orm\Zed\ProductSearch\Persistence\SpyProductSearchQuery;
use Spryker\Zed\Kernel\Persistence\AbstractPersistenceFactory;
use Spryker\Zed\ProductPageSearch\Persistence\Mapper\ProductPageSearchMapper;
use Spryker\Zed\ProductPageSearch\Persistence\Mapper\ProductPageSearchMapperInterface;
use Spryker\Zed\ProductPageSearch\ProductPageSearchDependencyProvider;

/**
 * @method \Spryker\Zed\ProductPageSearch\ProductPageSearchConfig getConfig()
 * @method \Spryker\Zed\ProductPageSearch\Persistence\ProductPageSearchQueryContainerInterface getQueryContainer()
 * @method \Spryker\Zed\ProductPageSearch\Persistence\ProductPageSearchEntityManagerInterface getEntityManager()
 * @method \Spryker\Zed\ProductPageSearch\Persistence\ProductPageSearchRepositoryInterface getRepository()
 */
class ProductPageSearchPersistenceFactory extends AbstractPersistenceFactory
{
    /**
     * @return \Spryker\Zed\ProductPageSearch\Dependency\QueryContainer\ProductPageSearchToProductQueryContainerInterface
     */
    public function getProductQueryContainer()
    {
        return $this->getProvidedDependency(ProductPageSearchDependencyProvider::QUERY_CONTAINER_PRODUCT);
    }

    /**
     * @return \Orm\Zed\ProductPageSearch\Persistence\SpyProductAbstractPageSearchQuery
     */
    public function createProductAbstractPageSearch()
    {
        return SpyProductAbstractPageSearchQuery::create();
    }

    public function createProductConcretePageSearchQuery(): SpyProductConcretePageSearchQuery
    {
        return SpyProductConcretePageSearchQuery::create();
    }

    public function createProductPageSearchMapper(): ProductPageSearchMapperInterface
    {
        return new ProductPageSearchMapper();
    }

    /**
     * @return \Spryker\Zed\ProductPageSearch\Dependency\QueryContainer\ProductPageToPriceProductQueryContainerInterface
     */
    public function getPriceQueryContainer()
    {
        return $this->getProvidedDependency(ProductPageSearchDependencyProvider::QUERY_CONTAINER_PRICE);
    }

    /**
     * @return \Spryker\Zed\ProductPageSearch\Dependency\QueryContainer\ProductPageSearchToProductImageQueryContainerInterface
     */
    public function getProductImageQueryContainer()
    {
        return $this->getProvidedDependency(ProductPageSearchDependencyProvider::QUERY_CONTAINER_PRODUCT_IMAGE);
    }

    /**
     * @return \Spryker\Zed\ProductPageSearch\Dependency\QueryContainer\ProductPageSearchToCategoryQueryContainerInterface
     */
    public function getCategoryQueryContainer()
    {
        return $this->getProvidedDependency(ProductPageSearchDependencyProvider::QUERY_CONTAINER_CATEGORY);
    }

    /**
     * @return \Spryker\Zed\ProductPageSearch\Dependency\QueryContainer\ProductPageSearchToProductCategoryQueryContainerInterface
     */
    public function getProductCategoryQueryContainer()
    {
        return $this->getProvidedDependency(ProductPageSearchDependencyProvider::QUERY_CONTAINER_PRODUCT_CATEGORY);
    }

    /**
     * @deprecated Use {@link getCategoryQueryContainer()} instead.
     *
     * @return \Spryker\Zed\ProductPageSearch\Dependency\QueryContainer\ProductPageSearchToCategoryQueryContainerInterface
     */
    public function getCategoryAttributeQuery()
    {
        return $this->getProvidedDependency(ProductPageSearchDependencyProvider::QUERY_CONTAINER_CATEGORY);
    }

    public function getCategoryNodeQueryContainer(): SpyCategoryNodeQuery
    {
        return $this->getProvidedDependency(ProductPageSearchDependencyProvider::PROPEL_QUERY_CATEGORY_NODE);
    }

    public function getProductImageSetQuery(): SpyProductImageSetQuery
    {
        return $this->getProvidedDependency(ProductPageSearchDependencyProvider::PROPEL_QUERY_PRODUCT_IMAGE_SET);
    }

    public function getProductQuery(): SpyProductQuery
    {
        return $this->getProvidedDependency(ProductPageSearchDependencyProvider::PROPEL_QUERY_PRODUCT);
    }

    public function getProductCategoryQuery(): SpyProductCategoryQuery
    {
        return $this->getProvidedDependency(ProductPageSearchDependencyProvider::PROPEL_QUERY_PRODUCT_CATEGORY);
    }

    public function getCategoryClosureTableQuery(): SpyCategoryClosureTableQuery
    {
        return $this->getProvidedDependency(ProductPageSearchDependencyProvider::PROPEL_QUERY_CATEGORY_CLOSURE_TABLE);
    }

    public function getProductSearchQuery(): SpyProductSearchQuery
    {
        return $this->getProvidedDependency(ProductPageSearchDependencyProvider::PROPEL_QUERY_PRODUCT_SEARCH);
    }

    public function getPriceProductPropelQuery(): SpyPriceProductQuery
    {
        return $this->getProvidedDependency(ProductPageSearchDependencyProvider::PROPEL_QUERY_PRICE_PRODUCT);
    }
}
