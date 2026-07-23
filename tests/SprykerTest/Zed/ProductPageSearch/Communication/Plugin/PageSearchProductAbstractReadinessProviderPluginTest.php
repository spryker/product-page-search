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
use Generated\Shared\Transfer\SearchDocumentTransfer;
use Generated\Shared\Transfer\SynchronizationDataTransfer;
use Spryker\Client\Search\SearchClientInterface;
use Spryker\Service\Synchronization\Dependency\Plugin\SynchronizationKeyGeneratorPluginInterface;
use Spryker\Service\Synchronization\SynchronizationServiceInterface;
use Spryker\Zed\ProductPageSearch\Business\ProductPageSearchBusinessFactory;
use Spryker\Zed\ProductPageSearch\Business\Provider\PageSearchProductAbstractReadinessProvider;
use Spryker\Zed\ProductPageSearch\Communication\Plugin\ProductManagement\PageSearchProductAbstractReadinessProviderPlugin;
use Spryker\Zed\ProductPageSearch\Persistence\ProductPageSearchRepositoryInterface;
use Spryker\Zed\ProductPageSearch\ProductPageSearchConfig;

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
    protected const string DOCUMENT_KEY = 'product_abstract:de:de_de:123';

    protected const string INDEX_NAME = 'spryker_de_page';

    protected const string DOCUMENT_KEY_URL_PART = '/search-elasticsearch-gui/maintenance/document-info?documentId=';

    public function testProvideReturnsFallbackWhenNoEntriesExist(): void
    {
        // Arrange
        $plugin = $this->createPlugin(
            $this->createRepositoryMockReturning([]),
            null,
        );

        // Act
        $result = $plugin->provide($this->createRequest(456), new ArrayObject());

        // Assert
        $this->assertCount(1, $result->getArrayCopy());
        $this->assertSame('-', $result->getArrayCopy()[0]->getValues()[0]);
    }

    public function testProvideReturnsDocumentKeyLinkInRow(): void
    {
        // Arrange
        $plugin = $this->createPlugin(
            $this->createRepositoryMockReturning([
                $this->buildEntry('DE', 'de_DE', null, null),
            ]),
            null,
        );

        // Act
        $result = $plugin->provide($this->createRequest(123), new ArrayObject());

        // Assert
        $row = $result->getArrayCopy()[0]->getValues()[0];
        $this->assertStringContainsString(static::DOCUMENT_KEY_URL_PART . static::DOCUMENT_KEY, $row);
        $this->assertStringContainsString('index=' . static::INDEX_NAME, $row);
    }

    public function testProvideReturnsSyncedStatusWhenDocumentMatchesDatabase(): void
    {
        // Arrange
        $dbData = ['name' => 'Test Product', 'sku' => 'SKU-123'];
        $plugin = $this->createPlugin(
            $this->createRepositoryMockReturning([
                $this->buildEntry('DE', 'de_DE', $dbData, '2024-01-15 10:30:00'),
            ]),
            (new SearchDocumentTransfer())->setData($dbData),
        );

        // Act
        $result = $plugin->provide($this->createRequest(123), new ArrayObject());

        // Assert
        $row = $result->getArrayCopy()[0]->getValues()[0];
        $this->assertStringContainsString('Synced', $row);
        $this->assertStringContainsString('DE', $row);
        $this->assertStringContainsString('de_DE', $row);
        $this->assertStringContainsString('2024-01-15 10:30:00 UTC', $row);
    }

    public function testProvideReturnsUnsyncedStatusWhenDocumentDoesNotExistInSearch(): void
    {
        // Arrange - document not present in Elasticsearch
        $plugin = $this->createPlugin(
            $this->createRepositoryMockReturning([
                $this->buildEntry('DE', 'de_DE', ['key' => 'value'], null),
            ]),
            null,
        );

        // Act
        $result = $plugin->provide($this->createRequest(123), new ArrayObject());

        // Assert
        $row = $result->getArrayCopy()[0]->getValues()[0];
        $this->assertStringContainsString('Unsynced', $row);
    }

    public function testProvideReturnsUnsyncedStatusWhenDataDiffersFromSearch(): void
    {
        // Arrange
        $dbData = ['name' => 'DB Product'];
        $esData = ['name' => 'ES Product']; // different
        $plugin = $this->createPlugin(
            $this->createRepositoryMockReturning([
                $this->buildEntry('DE', 'de_DE', $dbData, null),
            ]),
            (new SearchDocumentTransfer())->setData($esData),
        );

        // Act
        $result = $plugin->provide($this->createRequest(123), new ArrayObject());

        // Assert
        $this->assertStringContainsString('Unsynced', $result->getArrayCopy()[0]->getValues()[0]);
    }

    public function testProvideReturnsUnsyncedStatusWhenDatabaseDataIsNull(): void
    {
        // Arrange - entry exists but data column is empty
        $plugin = $this->createPlugin(
            $this->createRepositoryMockReturning([
                $this->buildEntry('DE', 'de_DE', null, null),
            ]),
            (new SearchDocumentTransfer())->setData(['key' => 'value']),
        );

        // Act
        $result = $plugin->provide($this->createRequest(123), new ArrayObject());

        // Assert
        $this->assertStringContainsString('Unsynced', $result->getArrayCopy()[0]->getValues()[0]);
    }

    public function testProvideReturnsOneValuePerEntry(): void
    {
        // Arrange - two store+locale combinations
        $plugin = $this->createPlugin(
            $this->createRepositoryMockReturning([
                $this->buildEntry('DE', 'de_DE', null, null),
                $this->buildEntry('DE', 'en_US', null, null),
            ]),
            null,
        );

        // Act
        $result = $plugin->provide($this->createRequest(123), new ArrayObject());

        // Assert
        $this->assertCount(2, $result->getArrayCopy()[0]->getValues());
    }

    protected function createPlugin(
        ProductPageSearchRepositoryInterface $repositoryMock,
        SearchDocumentTransfer|null $searchDocument,
    ): PageSearchProductAbstractReadinessProviderPlugin {
        $provider = new PageSearchProductAbstractReadinessProvider(
            $this->createSearchClientMockReturning($searchDocument),
            $this->createSynchronizationServiceMock(),
            $repositoryMock,
            $this->createConfigMock(),
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
     * @param array<int, array<string, mixed>> $entries
     *
     * @return \Spryker\Zed\ProductPageSearch\Persistence\ProductPageSearchRepositoryInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected function createRepositoryMockReturning(array $entries): ProductPageSearchRepositoryInterface
    {
        $mock = $this->getMockBuilder(ProductPageSearchRepositoryInterface::class)->getMock();
        $mock->method('getProductAbstractPageSearchEntriesByIdProductAbstract')->willReturn($entries);

        return $mock;
    }

    /**
     * @return \Spryker\Client\Search\SearchClientInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected function createSearchClientMockReturning(?SearchDocumentTransfer $document): SearchClientInterface
    {
        $mock = $this->getMockBuilder(SearchClientInterface::class)->getMock();
        $mock->method('readDocument')->willReturn($document);

        return $mock;
    }

    /**
     * @return \Spryker\Service\Synchronization\SynchronizationServiceInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected function createSynchronizationServiceMock(): SynchronizationServiceInterface
    {
        $keyBuilderMock = $this->getMockBuilder(SynchronizationKeyGeneratorPluginInterface::class)->getMock();
        $keyBuilderMock->method('generateKey')
            ->willReturnCallback(function (SynchronizationDataTransfer $dataTransfer): string {
                return sprintf(
                    'product_abstract:%s:%s:%s',
                    $dataTransfer->getStore(),
                    strtolower($dataTransfer->getLocale()),
                    $dataTransfer->getReference(),
                );
            });

        $mock = $this->getMockBuilder(SynchronizationServiceInterface::class)->getMock();
        $mock->method('getStorageKeyBuilder')->willReturn($keyBuilderMock);

        return $mock;
    }

    /**
     * @return \Spryker\Zed\ProductPageSearch\ProductPageSearchConfig|\PHPUnit\Framework\MockObject\MockObject
     */
    protected function createConfigMock(): ProductPageSearchConfig
    {
        $mock = $this->getMockBuilder(ProductPageSearchConfig::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getSearchIndexPrefix'])
            ->getMock();

        $mock->method('getSearchIndexPrefix')->willReturn('spryker');

        return $mock;
    }

    protected function createRequest(int $idProductAbstract): ProductAbstractReadinessRequestTransfer
    {
        return (new ProductAbstractReadinessRequestTransfer())
            ->setProductAbstract((new ProductAbstractTransfer())->setIdProductAbstract($idProductAbstract));
    }

    /**
     * @param array<string, mixed>|null $data
     */
    protected function buildEntry(string $store, string $locale, ?array $data, ?string $updatedAt): array
    {
        return [
            'store' => $store,
            'locale' => $locale,
            'data' => $data,
            'updated_at' => $updatedAt,
        ];
    }
}
