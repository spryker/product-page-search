<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\ProductPageSearch\Business\Expander;

use ArrayObject;
use Generated\Shared\Transfer\ProductConcretePageSearchTransfer;
use Generated\Shared\Transfer\ProductConcreteTransfer;
use Generated\Shared\Transfer\ProductImageSetTransfer;
use Spryker\Zed\ProductPageSearch\Dependency\Facade\ProductPageSearchToProductImageFacadeInterface;

class ProductConcretePageSearchExpander implements ProductConcretePageSearchExpanderInterface
{
    /**
     * @var array<int, array<\Generated\Shared\Transfer\ProductImageSetTransfer>>
     */
    protected static array $imageSetCollectionsResolved = [];

    /**
     * @var \Spryker\Zed\ProductPageSearch\Dependency\Facade\ProductPageSearchToProductImageFacadeInterface
     */
    protected $productImageFacade;

    public function __construct(ProductPageSearchToProductImageFacadeInterface $productImageFacade)
    {
        $this->productImageFacade = $productImageFacade;
    }

    public function expandProductConcretePageSearchTransferWithProductImages(
        ProductConcretePageSearchTransfer $productConcretePageSearchTransfer
    ): ProductConcretePageSearchTransfer {
        $productConcretePageSearchTransfer
            ->requireFkProduct()
            ->requireLocale();

        $images = [];

        $productImageSetTransfers = $this->getProductImagesSetCollectionByProductId($productConcretePageSearchTransfer->getFkProduct());
        $productImageSetTransfers = $this->productImageFacade->resolveProductImageSetsForLocale(
            new ArrayObject($productImageSetTransfers),
            $productConcretePageSearchTransfer->getLocale(),
        );

        foreach ($productImageSetTransfers as $productImageSetTransfer) {
            $images = array_merge($images, $this->mapImageSetTransferToImages($productImageSetTransfer));
        }

        $productConcretePageSearchTransfer->setImages($images);

        return $productConcretePageSearchTransfer;
    }

    /**
     * @param int $idProduct
     *
     * @return array<\Generated\Shared\Transfer\ProductImageSetTransfer>
     */

    /**
     * @param array<\Generated\Shared\Transfer\ProductConcreteTransfer> $productConcreteTransfers
     *
     * @return void
     */
    public function preloadProductImagesSetCollectionByProductIds(array $productConcreteTransfers): void
    {
        $uncachedIds = array_values(array_filter(
            array_map(
                static fn (ProductConcreteTransfer $transfer): int => $transfer->getIdProductConcreteOrFail(),
                $productConcreteTransfers,
            ),
            static fn (int $idProduct): bool => !array_key_exists($idProduct, static::$imageSetCollectionsResolved),
        ));

        if (!$uncachedIds) {
            return;
        }

        $imageSetsIndexed = $this->productImageFacade->getProductImagesSetCollectionIndexedByProductId($uncachedIds);

        foreach ($imageSetsIndexed as $idProduct => $imageSets) {
            static::$imageSetCollectionsResolved[$idProduct] = $imageSets;
        }
    }

    protected function getProductImagesSetCollectionByProductId(int $idProduct): array
    {
        if (!array_key_exists($idProduct, static::$imageSetCollectionsResolved)) {
            static::$imageSetCollectionsResolved[$idProduct] = $this->productImageFacade
                ->getProductImagesSetCollectionByProductId($idProduct);
        }

        return static::$imageSetCollectionsResolved[$idProduct];
    }

    protected function mapImageSetTransferToImages(ProductImageSetTransfer $productImageSetTransfer): array
    {
        $images = [];

        foreach ($productImageSetTransfer->getProductImages() as $productImageTransfer) {
            $images[] = $productImageTransfer->toArray(false, true);
        }

        return $images;
    }
}
