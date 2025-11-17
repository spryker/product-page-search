<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerTest\Zed\ProductPageSearch\Communication\Plugin;

use ArrayObject;
use Codeception\Test\Unit;
use Generated\Shared\Transfer\ProductAbstractReadinessRequestTransfer;
use Generated\Shared\Transfer\ProductAbstractTransfer;
use Generated\Shared\Transfer\StoreTransfer;
use Generated\Shared\Transfer\SynchronizationDataTransfer;
use Spryker\Client\Search\SearchClientInterface;
use Spryker\Service\Synchronization\Dependency\Plugin\SynchronizationKeyGeneratorPluginInterface;
use Spryker\Service\Synchronization\SynchronizationServiceInterface;
use Spryker\Zed\ProductPageSearch\Business\ProductPageSearchBusinessFactory;
use Spryker\Zed\ProductPageSearch\Business\Provider\PageSearchProductAbstractReadinessProvider;
use Spryker\Zed\ProductPageSearch\Communication\Plugin\ProductManagement\PageSearchProductAbstractReadinessProviderPlugin;
use Spryker\Zed\ProductPageSearch\Dependency\Facade\ProductPageSearchToStoreFacadeInterface;

/**
 * Auto-generated group annotations
 *
 * @group SprykerTest
 * @group Zed
 * @group ProductPageSearch
 * @group Communication
 * @group Plugin
 * @group PageSearchProductAbstractReadinessProviderPluginTest
 * Add your own group annotations below this line
 */
class PageSearchProductAbstractReadinessProviderPluginTest extends Unit
{
    /**
     * @return void
     */
    public function testProvideFormatsStoresAndLocales(): void
    {
        // Arrange
        $idProductAbstract = 123;
        $plugin = $this->createPluginWithMocks(
            $this->createStoreFacadeMockWithLocales([
                'DE' => ['de_DE', 'en_US'],
                'US' => ['en_US'],
            ]),
            $this->createSearchClientMockForLocales([
                'product_abstract:de:de_de:123' => true,
                'product_abstract:de:en_us:123' => false,
                'product_abstract:us:en_us:123' => true,
            ]),
            $this->createSynchronizationServiceMock(),
        );

        $requestTransfer = (new ProductAbstractReadinessRequestTransfer())
            ->setProductAbstract((new ProductAbstractTransfer())->setIdProductAbstract($idProductAbstract));

        // Act
        $result = $plugin->provide($requestTransfer, new ArrayObject());

        // Assert
        $this->assertCount(1, $result);
        $productReadiness = $result[0];
        $this->assertSame('In Page Search for stores/locale', $productReadiness->getTitle());
        $this->assertSame('DE: de_DE | US: en_US', $productReadiness->getValues()[0]);
    }

    /**
     * @return void
     */
    public function testProvideReturnsDashWhenNoStoresProvided(): void
    {
        // Arrange
        $plugin = $this->createPluginWithMocks(
            $this->createEmptyStoreFacadeMock(),
            $this->createSearchClientMockForLocales([]),
            $this->createSynchronizationServiceMock(),
        );

        $requestTransfer = (new ProductAbstractReadinessRequestTransfer())
            ->setProductAbstract((new ProductAbstractTransfer())->setIdProductAbstract(456));

        // Act
        $result = $plugin->provide($requestTransfer, new ArrayObject());

        // Assert
        $this->assertSame('-', $result[0]->getValues()[0]);
    }

    /**
     * @return void
     */
    public function testProvideHandlesPartialLocaleCoverage(): void
    {
        // Arrange
        $idProductAbstract = 789;
        $plugin = $this->createPluginWithMocks(
            $this->createStoreFacadeMockWithLocales([
                'DE' => ['de_DE', 'en_US'],
                'US' => ['en_US'],
            ]),
            $this->createSearchClientMockForLocales([
                'product_abstract:de:de_de:789' => true,
                'product_abstract:de:en_us:789' => false,
                'product_abstract:us:en_us:789' => false,
            ]),
            $this->createSynchronizationServiceMock(),
        );

        $requestTransfer = (new ProductAbstractReadinessRequestTransfer())
            ->setProductAbstract((new ProductAbstractTransfer())->setIdProductAbstract($idProductAbstract));

        // Act
        $result = $plugin->provide($requestTransfer, new ArrayObject());

        // Assert
        $this->assertSame('DE: de_DE | US: -', $result[0]->getValues()[0]);
    }

    /**
     * @param \Spryker\Zed\ProductPageSearch\Dependency\Facade\ProductPageSearchToStoreFacadeInterface $storeFacadeMock
     * @param \Spryker\Client\Search\SearchClientInterface $searchClientMock
     * @param \Spryker\Service\Synchronization\SynchronizationServiceInterface $synchronizationServiceMock
     *
     * @return \Spryker\Zed\ProductPageSearch\Communication\Plugin\ProductManagement\PageSearchProductAbstractReadinessProviderPlugin
     */
    protected function createPluginWithMocks(
        ProductPageSearchToStoreFacadeInterface $storeFacadeMock,
        SearchClientInterface $searchClientMock,
        SynchronizationServiceInterface $synchronizationServiceMock
    ): PageSearchProductAbstractReadinessProviderPlugin {
        $provider = new PageSearchProductAbstractReadinessProvider(
            $storeFacadeMock,
            $searchClientMock,
            $synchronizationServiceMock,
        );

        $factoryMock = $this->getMockBuilder(ProductPageSearchBusinessFactory::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['createPageSearchProductAbstractReadinessProvider'])
            ->getMock();

        $factoryMock->method('createPageSearchProductAbstractReadinessProvider')->willReturn($provider);

        $plugin = new PageSearchProductAbstractReadinessProviderPlugin();
        $plugin->setBusinessFactory($factoryMock);

        return $plugin;
    }

    /**
     * @param array<string, array<string>> $storeToAvailableLocales
     *
     * @return \Spryker\Zed\ProductPageSearch\Dependency\Facade\ProductPageSearchToStoreFacadeInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected function createStoreFacadeMockWithLocales(array $storeToAvailableLocales): ProductPageSearchToStoreFacadeInterface
    {
        $storeFacadeMock = $this->getMockBuilder(ProductPageSearchToStoreFacadeInterface::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getAllStores', 'getStoreByName'])
            ->getMock();

        $stores = [];
        foreach ($storeToAvailableLocales as $storeName => $locales) {
            $store = (new StoreTransfer())->setName($storeName)->setAvailableLocaleIsoCodes($locales);
            $stores[] = $store;
            $storeFacadeMock->method('getStoreByName')->with($storeName)->willReturn($store);
        }

        $storeFacadeMock->method('getAllStores')->willReturn($stores);

        return $storeFacadeMock;
    }

    /**
     * @return \Spryker\Zed\ProductPageSearch\Dependency\Facade\ProductPageSearchToStoreFacadeInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected function createEmptyStoreFacadeMock(): ProductPageSearchToStoreFacadeInterface
    {
        $storeFacadeMock = $this->getMockBuilder(ProductPageSearchToStoreFacadeInterface::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getAllStores', 'getStoreByName'])
            ->getMock();

        $storeFacadeMock->method('getAllStores')->willReturn([]);
        $storeFacadeMock->method('getStoreByName')->willReturn(new StoreTransfer());

        return $storeFacadeMock;
    }

    /**
     * @param array<string, bool> $documentExistenceMap
     *
     * @return \Spryker\Client\Search\SearchClientInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected function createSearchClientMockForLocales(array $documentExistenceMap): SearchClientInterface
    {
        $searchClientMock = $this->getMockBuilder(SearchClientInterface::class)
            ->getMock();

        $searchClientMock->method('readDocument')
            ->willReturnCallback(function ($searchDocumentTransfer) use ($documentExistenceMap) {
                $documentId = $searchDocumentTransfer->getId();
                $exists = $documentExistenceMap[$documentId] ?? false;

                return $exists ? ['data'] : null;
            });

        return $searchClientMock;
    }

    /**
     * @return \Spryker\Service\Synchronization\SynchronizationServiceInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected function createSynchronizationServiceMock(): SynchronizationServiceInterface
    {
        $synchronizationServiceMock = $this->getMockBuilder(SynchronizationServiceInterface::class)
            ->getMock();

        $keyBuilderMock = $this->getMockBuilder(SynchronizationKeyGeneratorPluginInterface::class)
            ->getMock();

        $keyBuilderMock->method('generateKey')
            ->willReturnCallback(function (SynchronizationDataTransfer $dataTransfer) {
                $store = $dataTransfer->getStore();
                $locale = $dataTransfer->getLocale();
                $reference = $dataTransfer->getReference();

                return sprintf('product_abstract:%s:%s:%s', $store, strtolower($locale), $reference);
            });

        $synchronizationServiceMock->method('getStorageKeyBuilder')->willReturn($keyBuilderMock);

        return $synchronizationServiceMock;
    }
}
