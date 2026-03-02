<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\ProductPageSearch\Business;

use Spryker\Client\Search\SearchClientInterface;
use Spryker\Service\Synchronization\SynchronizationServiceInterface;
use Spryker\Zed\Kernel\Business\AbstractBusinessFactory;
use Spryker\Zed\ProductPageSearch\Business\Attribute\ProductPageAttribute;
use Spryker\Zed\ProductPageSearch\Business\DataMapper\AbstractProductSearchDataMapper;
use Spryker\Zed\ProductPageSearch\Business\DataMapper\PageMapBuilder;
use Spryker\Zed\ProductPageSearch\Business\DataMapper\ProductAbstractSearchDataMapper;
use Spryker\Zed\ProductPageSearch\Business\DataMapper\ProductConcreteSearchDataMapper;
use Spryker\Zed\ProductPageSearch\Business\Expander\Elasticsearch\ProductPageMapCategoryExpander;
use Spryker\Zed\ProductPageSearch\Business\Expander\Elasticsearch\ProductPageMapCategoryExpanderInterface;
use Spryker\Zed\ProductPageSearch\Business\Expander\PriceProductPageExpander;
use Spryker\Zed\ProductPageSearch\Business\Expander\PriceProductPageExpanderInterface;
use Spryker\Zed\ProductPageSearch\Business\Expander\ProductConcretePageSearchExpander;
use Spryker\Zed\ProductPageSearch\Business\Expander\ProductConcretePageSearchExpanderInterface;
use Spryker\Zed\ProductPageSearch\Business\Mapper\ProductPageSearchMapper;
use Spryker\Zed\ProductPageSearch\Business\Model\ProductPageSearchWriter;
use Spryker\Zed\ProductPageSearch\Business\ProductConcretePageSearchReader\ProductConcretePageSearchReader;
use Spryker\Zed\ProductPageSearch\Business\ProductConcretePageSearchReader\ProductConcretePageSearchReaderInterface;
use Spryker\Zed\ProductPageSearch\Business\ProductConcretePageSearchWriter\ProductConcretePageSearchWriter;
use Spryker\Zed\ProductPageSearch\Business\ProductConcretePageSearchWriter\ProductConcretePageSearchWriterInterface;
use Spryker\Zed\ProductPageSearch\Business\Provider\PageSearchProductAbstractReadinessProvider;
use Spryker\Zed\ProductPageSearch\Business\Provider\ProductAbstractReadinessProviderInterface;
use Spryker\Zed\ProductPageSearch\Business\Publisher\ProductAbstractPagePublisher;
use Spryker\Zed\ProductPageSearch\Business\Publisher\ProductConcretePageSearchPublisher;
use Spryker\Zed\ProductPageSearch\Business\Publisher\ProductConcretePageSearchPublisherInterface;
use Spryker\Zed\ProductPageSearch\Business\Reader\AddToCartSkuReader;
use Spryker\Zed\ProductPageSearch\Business\Reader\AddToCartSkuReaderInterface;
use Spryker\Zed\ProductPageSearch\Business\Reader\CategoryReader;
use Spryker\Zed\ProductPageSearch\Business\Reader\CategoryReaderInterface;
use Spryker\Zed\ProductPageSearch\Business\Refresher\ProductAbstractPageRefresher;
use Spryker\Zed\ProductPageSearch\Business\Refresher\ProductPageRefresherInterface;
use Spryker\Zed\ProductPageSearch\Business\Unpublisher\ProductConcretePageSearchUnpublisher;
use Spryker\Zed\ProductPageSearch\Business\Unpublisher\ProductConcretePageSearchUnpublisherInterface;
use Spryker\Zed\ProductPageSearch\Business\Writer\ProductAbstractPageSearchWriter;
use Spryker\Zed\ProductPageSearch\Business\Writer\ProductAbstractPageSearchWriterInterface;
use Spryker\Zed\ProductPageSearch\Business\Writer\ProductConcretePageSearchByProductEventsWriter;
use Spryker\Zed\ProductPageSearch\Business\Writer\ProductConcretePageSearchByProductEventsWriterInterface;
use Spryker\Zed\ProductPageSearch\Dependency\Facade\ProductPageSearchToCategoryInterface;
use Spryker\Zed\ProductPageSearch\Dependency\Facade\ProductPageSearchToEventBehaviorFacadeInterface;
use Spryker\Zed\ProductPageSearch\Dependency\Facade\ProductPageSearchToPriceProductInterface;
use Spryker\Zed\ProductPageSearch\Dependency\Facade\ProductPageSearchToProductImageFacadeInterface;
use Spryker\Zed\ProductPageSearch\Dependency\Facade\ProductPageSearchToStoreFacadeInterface;
use Spryker\Zed\ProductPageSearch\ProductPageSearchDependencyProvider;
use Spryker\Zed\ProductPageSearchExtension\Dependency\PageMapBuilderInterface;

/**
 * @method \Spryker\Zed\ProductPageSearch\ProductPageSearchConfig getConfig()
 * @method \Spryker\Zed\ProductPageSearch\Persistence\ProductPageSearchQueryContainerInterface getQueryContainer()
 * @method \Spryker\Zed\ProductPageSearch\Persistence\ProductPageSearchRepositoryInterface getRepository()
 * @method \Spryker\Zed\ProductPageSearch\Persistence\ProductPageSearchEntityManagerInterface getEntityManager()
 */
class ProductPageSearchBusinessFactory extends AbstractBusinessFactory
{
    /**
     * @return \Spryker\Zed\ProductPageSearch\Business\Publisher\ProductAbstractPagePublisherInterface
     */
    public function createProductAbstractPagePublisher()
    {
        return new ProductAbstractPagePublisher(
            $this->getRepository(),
            $this->getQueryContainer(),
            $this->getProductPageDataExpanderPlugins(),
            $this->getProductPageDataLoaderPlugins(),
            $this->getProductPageSearchCollectionFilterPlugins(),
            $this->createProductPageMapper(),
            $this->createProductPageWriter(),
            $this->getConfig(),
            $this->getStoreFacade(),
            $this->createAddToCartSkuReader(),
        );
    }

    public function createProductConcretePageSearchPublisher(): ProductConcretePageSearchPublisherInterface
    {
        return new ProductConcretePageSearchPublisher(
            $this->getRepository(),
            $this->createProductConcretePageSearchReader(),
            $this->createProductConcretePageSearchWriter(),
            $this->getProductFacade(),
            $this->getUtilEncoding(),
            $this->createProductConcreteSearchDataMapper(),
            $this->getStoreFacade(),
            $this->getProductSearchFacade(),
            $this->getConfig(),
            $this->getProductConcretePageDataExpanderPlugins(),
            $this->getProductConcreteCollectionFilterPlugins(),
        );
    }

    public function createProductConcretePageSearchUnpublisher(): ProductConcretePageSearchUnpublisherInterface
    {
        return new ProductConcretePageSearchUnpublisher(
            $this->createProductConcretePageSearchReader(),
            $this->createProductConcretePageSearchWriter(),
        );
    }

    public function createProductAbstractPageSearchWriter(): ProductAbstractPageSearchWriterInterface
    {
        return new ProductAbstractPageSearchWriter(
            $this->getEventBehaviorFacade(),
            $this->getRepository(),
            $this->createProductAbstractPagePublisher(),
            $this->createCategoryReader(),
        );
    }

    public function createCategoryReader(): CategoryReaderInterface
    {
        return new CategoryReader(
            $this->getCategoryFacade(),
            $this->getRepository(),
        );
    }

    public function createProductConcretePageSearchReader(): ProductConcretePageSearchReaderInterface
    {
        return new ProductConcretePageSearchReader($this->getRepository());
    }

    public function createProductConcretePageSearchWriter(): ProductConcretePageSearchWriterInterface
    {
        return new ProductConcretePageSearchWriter($this->getEntityManager());
    }

    public function createProductConcretePageSearchExpander(): ProductConcretePageSearchExpanderInterface
    {
        return new ProductConcretePageSearchExpander(
            $this->getProductImageFacade(),
        );
    }

    /**
     * @return array<\Spryker\Zed\ProductPageSearchExtension\Dependency\Plugin\ProductConcretePageDataExpanderPluginInterface>
     */
    public function getProductConcretePageDataExpanderPlugins(): array
    {
        return $this->getProvidedDependency(ProductPageSearchDependencyProvider::PLUGINS_PRODUCT_CONCRETE_PAGE_DATA_EXPANDER);
    }

    public function createProductAbstractPageRefresher(): ProductPageRefresherInterface
    {
        return new ProductAbstractPageRefresher(
            $this->createProductAbstractPagePublisher(),
            $this->getProductPageRefreshPlugins(),
        );
    }

    /**
     * @return array<\Spryker\Zed\ProductPageSearchExtension\Dependency\Plugin\ProductAbstractCollectionRefreshPluginInterface>
     */
    public function getProductPageRefreshPlugins(): array
    {
        return $this->getProvidedDependency(ProductPageSearchDependencyProvider::PLUGINS_PRODUCT_PAGE_REFRESH);
    }

    /**
     * @return \Spryker\Zed\ProductPageSearch\Business\Mapper\ProductPageSearchMapperInterface
     */
    protected function createProductPageMapper()
    {
        return new ProductPageSearchMapper(
            $this->createProductPageAttribute(),
            $this->createProductAbstractSearchDataMapper(),
            $this->getUtilEncoding(),
        );
    }

    /**
     * @return \Spryker\Zed\ProductPageSearch\Business\Attribute\ProductPageAttributeInterface
     */
    protected function createProductPageAttribute()
    {
        return new ProductPageAttribute(
            $this->getProductFacade(),
        );
    }

    public function createAddToCartSkuReader(): AddToCartSkuReaderInterface
    {
        return new AddToCartSkuReader(
            $this->getRepository(),
            $this->getProductAbstractAddToCartPlugins(),
        );
    }

    public function createProductConcretePageSearchByProductEventsWriter(): ProductConcretePageSearchByProductEventsWriterInterface
    {
        return new ProductConcretePageSearchByProductEventsWriter(
            $this->createProductConcretePageSearchPublisher(),
            $this->getEventBehaviorFacade(),
            $this->getRepository(),
        );
    }

    /**
     * @return \Spryker\Zed\ProductPageSearch\Business\Model\ProductPageSearchWriterInterface
     */
    protected function createProductPageWriter()
    {
        return new ProductPageSearchWriter(
            $this->getUtilEncoding(),
            $this->getConfig()->isSendingToQueue(),
        );
    }

    /**
     * @return \Spryker\Zed\ProductPageSearch\Dependency\Service\ProductPageSearchToUtilEncodingInterface
     */
    protected function getUtilEncoding()
    {
        return $this->getProvidedDependency(ProductPageSearchDependencyProvider::SERVICE_UTIL_ENCODING);
    }

    /**
     * @return \Spryker\Zed\ProductPageSearch\Dependency\Facade\ProductPageSearchToProductInterface
     */
    protected function getProductFacade()
    {
        return $this->getProvidedDependency(ProductPageSearchDependencyProvider::FACADE_PRODUCT);
    }

    /**
     * @return \Spryker\Zed\ProductPageSearch\Dependency\Facade\ProductPageSearchToSearchInterface
     */
    protected function getSearchFacade()
    {
        return $this->getProvidedDependency(ProductPageSearchDependencyProvider::FACADE_SEARCH);
    }

    public function getStoreFacade(): ProductPageSearchToStoreFacadeInterface
    {
        return $this->getProvidedDependency(ProductPageSearchDependencyProvider::FACADE_STORE);
    }

    public function getProductImageFacade(): ProductPageSearchToProductImageFacadeInterface
    {
        return $this->getProvidedDependency(ProductPageSearchDependencyProvider::FACADE_PRODUCT_IMAGE);
    }

    /**
     * @return array<\Spryker\Zed\ProductPageSearch\Dependency\Plugin\ProductPageDataExpanderInterface>
     */
    protected function getProductPageDataExpanderPlugins()
    {
        return $this->getProvidedDependency(ProductPageSearchDependencyProvider::PLUGIN_PRODUCT_PAGE_DATA_EXPANDER);
    }

    /**
     * @return array<\Spryker\Zed\ProductPageSearchExtension\Dependency\Plugin\ProductPageDataLoaderPluginInterface>
     */
    protected function getProductPageDataLoaderPlugins()
    {
        return $this->getProvidedDependency(ProductPageSearchDependencyProvider::PLUGIN_PRODUCT_PAGE_DATA_LOADER);
    }

    /**
     * @return array<\Spryker\Zed\ProductPageSearchExtension\Dependency\Plugin\ProductPageSearchCollectionFilterPluginInterface>
     */
    public function getProductPageSearchCollectionFilterPlugins(): array
    {
        return $this->getProvidedDependency(ProductPageSearchDependencyProvider::PLUGINS_PRODUCT_PAGE_SEARCH_COLLECTION_FILTER);
    }

    /**
     * @return array<\Spryker\Zed\ProductPageSearchExtension\Dependency\Plugin\ProductConcreteCollectionFilterPluginInterface>
     */
    public function getProductConcreteCollectionFilterPlugins(): array
    {
        return $this->getProvidedDependency(ProductPageSearchDependencyProvider::PLUGINS_PRODUCT_CONCRETE_COLLECTION_FILTER);
    }

    public function createProductAbstractSearchDataMapper(): AbstractProductSearchDataMapper
    {
        return new ProductAbstractSearchDataMapper(
            $this->createPageMapBuilder(),
            $this->getSearchFacade(),
            $this->getProductSearchFacade(),
            $this->getProductAbstractMapExpanderPlugins(),
        );
    }

    public function createProductConcreteSearchDataMapper(): AbstractProductSearchDataMapper
    {
        return new ProductConcreteSearchDataMapper(
            $this->createPageMapBuilder(),
            $this->getProductConcreteMapExpanderPlugins(),
        );
    }

    public function createPageMapBuilder(): PageMapBuilderInterface
    {
        return new PageMapBuilder();
    }

    /**
     * @return \Spryker\Zed\ProductPageSearch\Dependency\Facade\ProductPageSearchToProductSearchInterface
     */
    public function getProductSearchFacade()
    {
        return $this->getProvidedDependency(ProductPageSearchDependencyProvider::FACADE_PRODUCT_SEARCH);
    }

    /**
     * @return array<\Spryker\Zed\ProductPageSearchExtension\Dependency\Plugin\ProductConcretePageMapExpanderPluginInterface>
     */
    public function getProductConcreteMapExpanderPlugins(): array
    {
        return $this->getProvidedDependency(ProductPageSearchDependencyProvider::PLUGINS_CONCRETE_PRODUCT_MAP_EXPANDER);
    }

    /**
     * @return array<\Spryker\Zed\ProductPageSearchExtension\Dependency\Plugin\ProductAbstractMapExpanderPluginInterface>
     */
    public function getProductAbstractMapExpanderPlugins(): array
    {
        return $this->getProvidedDependency(ProductPageSearchDependencyProvider::PLUGINS_PRODUCT_ABSTRACT_MAP_EXPANDER);
    }

    public function createProductPageMapCategoryExpander(): ProductPageMapCategoryExpanderInterface
    {
        return new ProductPageMapCategoryExpander();
    }

    public function getPriceProductFacade(): ProductPageSearchToPriceProductInterface
    {
        return $this->getProvidedDependency(ProductPageSearchDependencyProvider::FACADE_PRICE_PRODUCT);
    }

    public function getEventBehaviorFacade(): ProductPageSearchToEventBehaviorFacadeInterface
    {
        return $this->getProvidedDependency(ProductPageSearchDependencyProvider::FACADE_EVENT_BEHAVIOR);
    }

    public function getCategoryFacade(): ProductPageSearchToCategoryInterface
    {
        return $this->getProvidedDependency(ProductPageSearchDependencyProvider::FACADE_CATEGORY);
    }

    public function createPriceProductPageExpander(): PriceProductPageExpanderInterface
    {
        return new PriceProductPageExpander(
            $this->getPriceProductFacade(),
            $this->getStoreFacade(),
        );
    }

    /**
     * @return array<\Spryker\Zed\ProductPageSearchExtension\Dependency\Plugin\ProductAbstractAddToCartPluginInterface>
     */
    public function getProductAbstractAddToCartPlugins(): array
    {
        return $this->getProvidedDependency(ProductPageSearchDependencyProvider::PLUGINS_PRODUCT_ABSTRACT_ADD_TO_CART);
    }

    public function createPageSearchProductAbstractReadinessProvider(): ProductAbstractReadinessProviderInterface
    {
        return new PageSearchProductAbstractReadinessProvider(
            $this->getStoreFacade(),
            $this->getSearchClient(),
            $this->getSynchronizationService(),
        );
    }

    protected function getSearchClient(): SearchClientInterface
    {
        return $this->getProvidedDependency(ProductPageSearchDependencyProvider::CLIENT_SEARCH);
    }

    protected function getSynchronizationService(): SynchronizationServiceInterface
    {
        return $this->getProvidedDependency(ProductPageSearchDependencyProvider::SERVICE_SYNCHRONIZATION);
    }
}
