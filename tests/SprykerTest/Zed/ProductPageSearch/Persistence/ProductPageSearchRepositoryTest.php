<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerTest\Zed\ProductPageSearch\Persistence;

use Codeception\Test\Unit;
use Orm\Zed\ProductPageSearch\Persistence\SpyProductConcretePageSearch;
use Spryker\Zed\ProductPageSearch\Persistence\ProductPageSearchRepository;

/**
 * Auto-generated group annotations
 *
 * @group SprykerTest
 * @group Zed
 * @group ProductPageSearch
 * @group Persistence
 * @group ProductPageSearchRepositoryTest
 * Add your own group annotations below this line
 */
class ProductPageSearchRepositoryTest extends Unit
{
    /**
     * @var \SprykerTest\Zed\ProductPageSearch\ProductPageSearchPersistenceTester
     */
    protected $tester;

    public function testGetEligibleForAddToCartProductAbstractsIdsReturnsCorrectProductAbstractIds(): void
    {
        // Arrange
        $productConcreteTransfer1 = $this->tester->haveProduct();
        $productConcreteTransfer2 = $this->tester->haveProduct();
        $productAbstractIds = [
            $productConcreteTransfer1->getFkProductAbstract(),
            $productConcreteTransfer2->getFkProductAbstract(),
        ];

        // Act
        $result = array_map(
            'intval',
            (new ProductPageSearchRepository())->getEligibleForAddToCartProductAbstractsIds($productAbstractIds),
        );

        // Assert
        $this->assertCount(2, $result);
        $this->assertContains($productAbstractIds[0], $result);
        $this->assertContains($productAbstractIds[1], $result);
    }

    /**
     * @dataProvider provideTimestampComparisonCases
     */
    public function testGetRelevantProductConcreteIdsToUpdate(int $timestamp, string $storedUpdatedAt, bool $expectedKept): void
    {
        // Arrange
        $productConcreteTransfer = $this->tester->haveProduct();
        $idProduct = $productConcreteTransfer->getIdProductConcrete();
        $this->haveProductConcretePageSearch($idProduct, $storedUpdatedAt);

        // Act
        $result = (new ProductPageSearchRepository())->getRelevantProductConcreteIdsToUpdate([$idProduct => $timestamp]);

        // Assert
        if ($expectedKept) {
            $this->assertArrayHasKey($idProduct, $result);

            return;
        }

        $this->assertArrayNotHasKey($idProduct, $result);
    }

    /**
     * @return iterable<string, array{int, string, bool}>
     */
    public function provideTimestampComparisonCases(): iterable
    {
        yield 'zero timestamp is kept despite an existing page-search entry' => [0, '2024-01-01 00:00:00', true];
        yield 'timestamp older than last update is removed' => [strtotime('2024-01-01 00:00:00'), '2025-01-01 00:00:00', false];
        yield 'timestamp newer than last update is kept' => [strtotime('2025-01-01 00:00:00'), '2024-01-01 00:00:00', true];
    }

    protected function haveProductConcretePageSearch(int $idProduct, string $updatedAt): SpyProductConcretePageSearch
    {
        $productConcretePageSearchEntity = new SpyProductConcretePageSearch();
        $productConcretePageSearchEntity->setFkProduct($idProduct)
            ->setStructuredData('{}')
            ->setStore('DE')
            ->setLocale('de_DE')
            ->setUpdatedAt($updatedAt)
            ->save();

        return $productConcretePageSearchEntity;
    }
}
