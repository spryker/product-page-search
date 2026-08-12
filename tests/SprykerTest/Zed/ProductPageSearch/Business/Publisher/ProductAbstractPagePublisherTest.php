<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerTest\Zed\ProductPageSearch\Business\Publisher;

use Codeception\Stub;
use Codeception\Test\Unit;
use Generated\Shared\Transfer\ProductPageSearchTransfer;
use Spryker\Zed\ProductPageSearch\Dependency\Facade\ProductPageSearchToSearchBridge;
use Spryker\Zed\ProductPageSearch\ProductPageSearchDependencyProvider;
use SprykerTest\Zed\ProductPageSearch\ProductPageSearchBusinessTester;

/**
 * Auto-generated group annotations
 *
 * @group SprykerTest
 * @group Zed
 * @group ProductPageSearch
 * @group Business
 * @group Publisher
 * @group ProductAbstractPagePublisherTest
 * Add your own group annotations below this line
 */
class ProductAbstractPagePublisherTest extends Unit
{
    /**
     * @var \SprykerTest\Zed\ProductPageSearch\ProductPageSearchBusinessTester
     */
    protected ProductPageSearchBusinessTester $tester;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tester->setUp();
        $this->tester->setDependency(ProductPageSearchDependencyProvider::PLUGIN_PRODUCT_PAGE_DATA_EXPANDER, []);
        $this->tester->setDependency(ProductPageSearchDependencyProvider::FACADE_SEARCH, Stub::make(
            ProductPageSearchToSearchBridge::class,
            [
                'transformPageMapToDocumentByMapperName' => function () {
                    return [];
                },
            ],
        ));
    }

    public function testPublishAddsSearchableConcreteSkuToProductPageSearchTransfer(): void
    {
        // Arrange
        $productAbstractTransfer = $this->tester->getProductAbstractTransfer();
        $productConcreteTransfer = $this->tester->getProductConcreteTransfer();

        // Act
        $this->tester->getFacade()->publish([$productAbstractTransfer->getIdProductAbstract()]);

        // Assert
        $productPageSearchTransfer = $this->findFirstPublishedProductPageSearchTransfer(
            $productAbstractTransfer->getIdProductAbstractOrFail(),
        );

        $this->assertNotNull(
            $productPageSearchTransfer,
            'Expected the product abstract to be published to at least one store.',
        );
        $this->assertStringContainsString(
            $productConcreteTransfer->getSkuOrFail(),
            (string)$productPageSearchTransfer->getConcreteSkus(),
            'Expected the searchable concrete sku to be part of the published concrete skus.',
        );
    }

    public function testPublishSetsAbstractSkuOnProductPageSearchTransfer(): void
    {
        // Arrange
        $productAbstractTransfer = $this->tester->getProductAbstractTransfer();

        // Act
        $this->tester->getFacade()->publish([$productAbstractTransfer->getIdProductAbstract()]);

        // Assert
        $productPageSearchTransfer = $this->findFirstPublishedProductPageSearchTransfer(
            $productAbstractTransfer->getIdProductAbstractOrFail(),
        );

        $this->assertNotNull(
            $productPageSearchTransfer,
            'Expected the product abstract to be published to at least one store.',
        );
        $this->assertSame(
            $productAbstractTransfer->getSku(),
            $productPageSearchTransfer->getSku(),
            'Expected the abstract sku to be mapped onto the published product page search transfer.',
        );
    }

    protected function findFirstPublishedProductPageSearchTransfer(int $idProductAbstract): ?ProductPageSearchTransfer
    {
        foreach ($this->tester->getStoreNames() as $storeName) {
            $productPageSearchTransfer = $this->tester->findProductPageSearchTransfer($idProductAbstract, $storeName);

            if ($productPageSearchTransfer !== null) {
                return $productPageSearchTransfer;
            }
        }

        return null;
    }
}
