<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\ProductPageSearch\Communication\Plugin\PageDataLoader;

use Generated\Shared\Transfer\ProductPageLoadTransfer;
use Propel\Runtime\Collection\ObjectCollection;
use Spryker\Zed\Kernel\Communication\AbstractPlugin;
use Spryker\Zed\ProductPageSearchExtension\Dependency\Plugin\ProductPageDataLoaderPluginInterface;

/**
 * @method \Spryker\Zed\ProductPageSearch\Communication\ProductPageSearchCommunicationFactory getFactory()
 * @method \Spryker\Zed\ProductPageSearch\Persistence\ProductPageSearchQueryContainerInterface getQueryContainer()
 * @method \Spryker\Zed\ProductPageSearch\Business\ProductPageSearchFacadeInterface getFacade()
 * @method \Spryker\Zed\ProductPageSearch\ProductPageSearchConfig getConfig()
 */
class ImagePageDataLoaderPlugin extends AbstractPlugin implements ProductPageDataLoaderPluginInterface
{
    /**
     * {@inheritDoc}
     *
     * @api
     *
     * @param \Generated\Shared\Transfer\ProductPageLoadTransfer $productPageLoadTransfer
     *
     * @return \Generated\Shared\Transfer\ProductPageLoadTransfer
     */
    public function expandProductPageDataTransfer(ProductPageLoadTransfer $productPageLoadTransfer)
    {
        $payloadTransfers = $this->setProductImages($productPageLoadTransfer->getProductAbstractIds(), $productPageLoadTransfer->getPayloadTransfers());
        $productPageLoadTransfer->setPayloadTransfers($payloadTransfers);

        return $productPageLoadTransfer;
    }

    /**
     * @param array<int> $productAbstractIds
     * @param array<\Generated\Shared\Transfer\ProductPayloadTransfer> $payloadTransfers
     *
     * @return array
     */
    protected function setProductImages(array $productAbstractIds, array $payloadTransfers): array
    {
        $productImageSetCollection = $this->getQueryContainer()
            ->queryAllProductImageSetsByProductAbstractIds($productAbstractIds)
            ->find();

        $imageSets = $this->getImageSets($productImageSetCollection);
        $defaultProductImageSets = $this->getDefaultProductImageSets($productImageSetCollection);

        $imageSets = $this->setDefaultImageSetsToMissedImageSets($imageSets, $defaultProductImageSets);

        foreach ($payloadTransfers as $payloadTransfer) {
            if (!isset($imageSets[$payloadTransfer->getIdProductAbstract()])) {
                continue;
            }

            $images = $imageSets[$payloadTransfer->getIdProductAbstract()];
            $payloadTransfer->setImages($images);
        }

        return $payloadTransfers;
    }

    /**
     * @param \Propel\Runtime\Collection\ObjectCollection|\Orm\Zed\ProductImage\Persistence\SpyProductImageSet[] $productImageSetCollection
     *
     * @return array
     */
    protected function getImageSets(ObjectCollection $productImageSetCollection): array
    {
        $imageSets = [];

        foreach ($productImageSetCollection as $productImageSet) {
            $imageSets[$productImageSet->getFkProductAbstract()][$productImageSet->getFkLocale()][] = $productImageSet;
        }

        return $imageSets;
    }

    /**
     * @param \Propel\Runtime\Collection\ObjectCollection|\Orm\Zed\ProductImage\Persistence\SpyProductImageSet[] $productImageSetCollection
     *
     * @return array
     */
    protected function getDefaultProductImageSets(ObjectCollection $productImageSetCollection): array
    {
        $defaultProductImageSets = [];

        foreach ($productImageSetCollection as $productImageSet) {
            if ($productImageSet->getFkLocale() !== null) {
                continue;
            }

            $defaultProductImageSets[$productImageSet->getFkProductAbstract()][] = $productImageSet;
        }

        return $defaultProductImageSets;
    }

    /**
     * @param array<array> $imageSets
     * @param array<array> $defaultProductImageSets
     *
     * @return array
     */
    protected function setDefaultImageSetsToMissedImageSets(array $imageSets, array $defaultProductImageSets): array
    {
        $productAbstractIds = array_keys($imageSets);

        $abstractProducts = $this->getQueryContainer()
            ->queryProductAbstractWithLocalizedAttributesByIds($productAbstractIds)
            ->find();

        foreach ($abstractProducts as $abstractProduct) {
            $productAbstractId = $abstractProduct->getFkProductAbstract();
            $localeId = $abstractProduct->getFkLocale();

            if (isset($imageSets[$productAbstractId][$localeId])) {
                continue;
            }

            if (isset($defaultProductImageSets[$productAbstractId])) {
                $imageSets[$productAbstractId][$localeId] = $defaultProductImageSets[$productAbstractId];
            }
        }

        return $imageSets;
    }
}
