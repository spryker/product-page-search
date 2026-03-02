<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\ProductPageSearch\Business\Reader;

use Spryker\Zed\ProductPageSearch\Dependency\Facade\ProductPageSearchToCategoryInterface;
use Spryker\Zed\ProductPageSearch\Persistence\ProductPageSearchRepositoryInterface;

class CategoryReader implements CategoryReaderInterface
{
    /**
     * @var \Spryker\Zed\ProductPageSearch\Dependency\Facade\ProductPageSearchToCategoryInterface
     */
    protected ProductPageSearchToCategoryInterface $categoryFacade;

    /**
     * @var \Spryker\Zed\ProductPageSearch\Persistence\ProductPageSearchRepositoryInterface
     */
    protected ProductPageSearchRepositoryInterface $productPageSearchRepository;

    public function __construct(
        ProductPageSearchToCategoryInterface $categoryFacade,
        ProductPageSearchRepositoryInterface $productPageSearchRepository
    ) {
        $this->categoryFacade = $categoryFacade;
        $this->productPageSearchRepository = $productPageSearchRepository;
    }

    /**
     * @param array<int> $categoryIds
     *
     * @return array<int>
     */
    public function getRelatedCategoryIdsByCategoryIds(array $categoryIds): array
    {
        $categoryNodeTransfers = [];

        foreach ($categoryIds as $idCategory) {
            $categoryNodeTransfers = array_merge($categoryNodeTransfers, $this->categoryFacade->getAllNodesByIdCategory($idCategory));
        }

        $categoryNodeIds = $this->extractCategoryNodeIdsFromCategoryNodes($categoryNodeTransfers);

        return array_unique($this->productPageSearchRepository->getCategoryIdsByCategoryNodeIds($categoryNodeIds));
    }

    /**
     * @param array<\Generated\Shared\Transfer\NodeTransfer> $categoryNodeTransfers
     *
     * @return list<int>
     */
    protected function extractCategoryNodeIdsFromCategoryNodes(array $categoryNodeTransfers): array
    {
        $categoryNodeIds = [];

        foreach ($categoryNodeTransfers as $categoryNodeTransfer) {
            $categoryNodeIds[] = $categoryNodeTransfer->getIdCategoryNodeOrFail();
        }

        return $categoryNodeIds;
    }
}
