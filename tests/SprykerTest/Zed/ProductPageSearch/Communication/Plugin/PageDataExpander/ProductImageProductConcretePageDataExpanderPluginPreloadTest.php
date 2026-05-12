<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerTest\Zed\ProductPageSearch\Communication\Plugin\PageDataExpander;

use Codeception\Test\Unit;
use Generated\Shared\Transfer\ProductImageSetTransfer;
use ReflectionClass;
use Spryker\Zed\ProductPageSearch\Business\Expander\ProductConcretePageSearchExpander;
use Spryker\Zed\ProductPageSearch\Communication\Plugin\PageDataExpander\ProductImageProductConcretePageDataExpanderPlugin;
use SprykerTest\Zed\ProductPageSearch\ProductPageSearchCommunicationTester;

/**
 * Auto-generated group annotations
 *
 * @group SprykerTest
 * @group Zed
 * @group ProductPageSearch
 * @group Communication
 * @group Plugin
 * @group PageDataExpander
 * @group ProductImageProductConcretePageDataExpanderPluginPreloadTest
 * Add your own group annotations below this line
 */
class ProductImageProductConcretePageDataExpanderPluginPreloadTest extends Unit
{
    protected ProductPageSearchCommunicationTester $tester;

    public function setUp(): void
    {
        parent::setUp();

        $this->clearExpanderCache();
    }

    /**
     * @dataProvider preloadDataProvider
     */
    public function testPreloadPopulatesImageSetCache(
        int $productCountWithImageSets,
        int $productCountWithoutImageSets,
        int $imageSetsPerProduct,
        int $expectedCacheSize,
    ): void {
        // Arrange
        $productsWithSets = [];

        for ($i = 0; $i < $productCountWithImageSets; $i++) {
            $product = $this->tester->haveProduct();

            for ($j = 0; $j < $imageSetsPerProduct; $j++) {
                $this->tester->haveProductImageSet([
                    ProductImageSetTransfer::ID_PRODUCT => $product->getIdProductConcrete(),
                ]);
            }

            $productsWithSets[] = $product;
        }

        $productsWithoutSets = [];

        for ($i = 0; $i < $productCountWithoutImageSets; $i++) {
            $productsWithoutSets[] = $this->tester->haveProduct();
        }

        $allProducts = array_merge($productsWithSets, $productsWithoutSets);
        $plugin = new ProductImageProductConcretePageDataExpanderPlugin();

        // Act
        $plugin->preload($allProducts);

        // Assert — cache has exactly one entry per requested product
        $cache = $this->getExpanderCache();

        $this->assertCount($expectedCacheSize, $cache);

        foreach ($productsWithSets as $product) {
            $idProduct = $product->getIdProductConcrete();

            $this->assertArrayHasKey($idProduct, $cache);
            $this->assertCount($imageSetsPerProduct, $cache[$idProduct]);

            foreach ($cache[$idProduct] as $imageSetTransfer) {
                $this->assertInstanceOf(ProductImageSetTransfer::class, $imageSetTransfer);
            }
        }

        foreach ($productsWithoutSets as $product) {
            $idProduct = $product->getIdProductConcrete();

            $this->assertArrayHasKey($idProduct, $cache);
            $this->assertEmpty($cache[$idProduct]);
        }
    }

    /**
     * @return array<string, array<mixed>>
     */
    public static function preloadDataProvider(): array
    {
        return [
            'empty input leaves cache empty' => [
                'productCountWithImageSets' => 0,
                'productCountWithoutImageSets' => 0,
                'imageSetsPerProduct' => 0,
                'expectedCacheSize' => 0,
            ],
            'single product with one image set' => [
                'productCountWithImageSets' => 1,
                'productCountWithoutImageSets' => 0,
                'imageSetsPerProduct' => 1,
                'expectedCacheSize' => 1,
            ],
            'single product with multiple image sets' => [
                'productCountWithImageSets' => 1,
                'productCountWithoutImageSets' => 0,
                'imageSetsPerProduct' => 3,
                'expectedCacheSize' => 1,
            ],
            'multiple products each with image sets' => [
                'productCountWithImageSets' => 3,
                'productCountWithoutImageSets' => 0,
                'imageSetsPerProduct' => 1,
                'expectedCacheSize' => 3,
            ],
            'product without image sets gets empty array in cache' => [
                'productCountWithImageSets' => 0,
                'productCountWithoutImageSets' => 1,
                'imageSetsPerProduct' => 0,
                'expectedCacheSize' => 1,
            ],
            'mixed: products with and without image sets' => [
                'productCountWithImageSets' => 2,
                'productCountWithoutImageSets' => 2,
                'imageSetsPerProduct' => 1,
                'expectedCacheSize' => 4,
            ],
        ];
    }

    /**
     * Verifies that a second preload call with already-cached product IDs
     * leaves the cache entries unchanged (no re-fetch from the database).
     */
    public function testPreloadSkipsAlreadyCachedProducts(): void
    {
        // Arrange
        $firstProduct = $this->tester->haveProduct();
        $secondProduct = $this->tester->haveProduct();

        $this->tester->haveProductImageSet([
            ProductImageSetTransfer::ID_PRODUCT => $firstProduct->getIdProductConcrete(),
        ]);
        $this->tester->haveProductImageSet([
            ProductImageSetTransfer::ID_PRODUCT => $secondProduct->getIdProductConcrete(),
        ]);

        $plugin = new ProductImageProductConcretePageDataExpanderPlugin();
        $plugin->preload([$firstProduct, $secondProduct]);

        $cacheAfterFirstPreload = $this->getExpanderCache();

        // Act: preload again with only the first product (already in cache)
        $plugin->preload([$firstProduct]);

        // Assert: cache is identical — the cached entry was not re-fetched or overwritten
        $this->assertEquals($cacheAfterFirstPreload, $this->getExpanderCache());
        $this->assertCount(2, $this->getExpanderCache());
    }

    /**
     * @return array<int, array<\Generated\Shared\Transfer\ProductImageSetTransfer>>
     */
    protected function getExpanderCache(): array
    {
        $reflection = new ReflectionClass(ProductConcretePageSearchExpander::class);

        return $reflection->getProperty('imageSetCollectionsResolved')->getValue();
    }

    protected function clearExpanderCache(): void
    {
        $reflection = new ReflectionClass(ProductConcretePageSearchExpander::class);
        $reflection->getProperty('imageSetCollectionsResolved')->setValue(null, []);
    }
}
