<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\ProductPageSearch\Business\Provider;

use ArrayObject;
use Generated\Shared\Transfer\ElasticsearchSearchContextTransfer;
use Generated\Shared\Transfer\ProductAbstractReadinessRequestTransfer;
use Generated\Shared\Transfer\ProductReadinessTransfer;
use Generated\Shared\Transfer\SearchContextTransfer;
use Generated\Shared\Transfer\SearchDocumentTransfer;
use Generated\Shared\Transfer\SynchronizationDataTransfer;
use RuntimeException;
use Spryker\Client\Search\SearchClientInterface;
use Spryker\Service\Synchronization\SynchronizationServiceInterface;
use Spryker\Zed\ProductPageSearch\Dependency\Facade\ProductPageSearchToStoreFacadeInterface;
use Spryker\Zed\ProductPageSearch\ProductPageSearchConfig;

class PageSearchProductAbstractReadinessProvider implements ProductAbstractReadinessProviderInterface
{
    /**
     * @var string
     */
    protected const TITLE_IN_PAGE_SEARCH = 'In Page Search for stores/locale';

    /**
     * @var string
     */
    protected const FALLBACK_VALUE_NO_LOCALES = '-';

    /**
     * @var string
     */
    protected const FALLBACK_VALUE_NO_STORES = '-';

    /**
     * @var string
     */
    protected const FORMAT_LOCALE_SEPARATOR = ', ';

    /**
     * @var string
     */
    protected const FORMAT_STORE_LOCALE_SEPARATOR = ' | ';

    /**
     * @var string
     */
    protected const FORMAT_STORE_LOCALE = '%s: %s';

    /**
     * @var string
     */
    protected const DEFAULT_PRODUCT_ABSTRACT_INDEX_TYPE = 'page';

    /**
     * @var string
     */
    protected const DEFAULT_SOURCE_IDENTIFIER = 'page';

    /**
     * @param \Spryker\Zed\ProductPageSearch\Dependency\Facade\ProductPageSearchToStoreFacadeInterface $storeFacade
     * @param \Spryker\Client\Search\SearchClientInterface $searchClient
     * @param \Spryker\Service\Synchronization\SynchronizationServiceInterface $synchronizationService
     */
    public function __construct(
        protected ProductPageSearchToStoreFacadeInterface $storeFacade,
        protected SearchClientInterface $searchClient,
        protected SynchronizationServiceInterface $synchronizationService
    ) {
    }

    /**
     * @param \Generated\Shared\Transfer\ProductAbstractReadinessRequestTransfer $productAbstractReadinessRequestTransfer
     * @param \ArrayObject<int, \Generated\Shared\Transfer\ProductReadinessTransfer> $productReadinessTransfers
     *
     * @return \ArrayObject<int, \Generated\Shared\Transfer\ProductReadinessTransfer>
     */
    public function provide(
        ProductAbstractReadinessRequestTransfer $productAbstractReadinessRequestTransfer,
        ArrayObject $productReadinessTransfers
    ): ArrayObject {
        $idProductAbstract = $productAbstractReadinessRequestTransfer->getProductAbstract()->getIdProductAbstract();

        $storeLocaleMap = $this->getProductAbstractExistenceStoreLocaleMap($idProductAbstract);
        $values = $this->formatStoreLocaleCombinations($storeLocaleMap);

        $productReadinessTransfers->append(
            (new ProductReadinessTransfer())
                ->setTitle(static::TITLE_IN_PAGE_SEARCH)
                ->setValues($values),
        );

        return $productReadinessTransfers;
    }

    /**
     * @param int $idProductAbstract
     *
     * @return array<string, array<string>>
     */
    protected function getProductAbstractExistenceStoreLocaleMap(int $idProductAbstract): array
    {
        $productAbstractExistenceStoreLocaleMap = [];
        $stores = $this->storeFacade->getAllStores();

        foreach ($stores as $storeTransfer) {
            $locales = $this->getProductAbstractExistenceStore($idProductAbstract, $storeTransfer);

            if ($locales) {
                $productAbstractExistenceStoreLocaleMap[$storeTransfer->getName()] = $locales;
            }
        }

        return $productAbstractExistenceStoreLocaleMap;
    }

    /**
     * @param int $idProductAbstract
     * @param \Generated\Shared\Transfer\StoreTransfer $storeTransfer
     *
     * @return array<string>
     */
    protected function getProductAbstractExistenceStore(int $idProductAbstract, $storeTransfer): array
    {
        $locales = $storeTransfer->getAvailableLocaleIsoCodes();

        if (!$locales) {
            return [];
        }

        $availableLocales = [];
        foreach ($locales as $localeIsoCode) {
            if ($this->hasProductDocument($idProductAbstract, $localeIsoCode, $storeTransfer->getName())) {
                $availableLocales[] = $localeIsoCode;
            }
        }

        sort($availableLocales);

        return $availableLocales;
    }

    /**
     * @param int $idProductAbstract
     * @param string $localeIsoCode
     * @param string $storeName
     *
     * @return bool
     */
    protected function hasProductDocument(int $idProductAbstract, string $localeIsoCode, string $storeName): bool
    {
        $documentKey = $this->buildProductAbstractDocumentKey($idProductAbstract, $storeName, $localeIsoCode);

        return $this->documentExists($documentKey, $storeName);
    }

    /**
     * @param int $idProductAbstract
     * @param string $storeName
     * @param string $localeName
     *
     * @return string
     */
    protected function buildProductAbstractDocumentKey(int $idProductAbstract, string $storeName, string $localeName): string
    {
        $synchronizationDataTransfer = new SynchronizationDataTransfer();
        $synchronizationDataTransfer->setStore(strtolower($storeName));
        $synchronizationDataTransfer->setLocale($localeName);
        $synchronizationDataTransfer->setReference((string)$idProductAbstract);

        $storageKeyBuilder = $this->synchronizationService
            ->getStorageKeyBuilder(ProductPageSearchConfig::PRODUCT_ABSTRACT_RESOURCE_NAME);

        return $storageKeyBuilder->generateKey($synchronizationDataTransfer);
    }

    /**
     * @param string $documentKey
     * @param string|null $storeName
     *
     * @return bool
     */
    protected function documentExists(string $documentKey, ?string $storeName = null): bool
    {
        try {
            $searchDocumentTransfer = new SearchDocumentTransfer();
            $searchDocumentTransfer->setId($documentKey);
            $searchDocumentTransfer->setType(static::DEFAULT_PRODUCT_ABSTRACT_INDEX_TYPE);

            $searchContextTransfer = new SearchContextTransfer();
            $searchContextTransfer->setSourceIdentifier(static::DEFAULT_SOURCE_IDENTIFIER);

            $elasticsearchContext = new ElasticsearchSearchContextTransfer();
            $elasticsearchContext->setTypeName(static::DEFAULT_PRODUCT_ABSTRACT_INDEX_TYPE);
            $searchContextTransfer->setElasticsearchContext($elasticsearchContext);
            $searchContextTransfer->setStoreName($storeName);

            $searchDocumentTransfer->setSearchContext($searchContextTransfer);

            if ($storeName !== null) {
                $searchDocumentTransfer->getSearchContext()->setStoreName($storeName);
            }

            $document = $this->searchClient->readDocument($searchDocumentTransfer);

            return $document !== null;
        } catch (RuntimeException $e) {
            return false;
        }
    }

    /**
     * @param array<string, array<string>> $storeLocaleMap
     *
     * @return array<string>
     */
    protected function formatStoreLocaleCombinations(array $storeLocaleMap): array
    {
        $formattedParts = [];

        foreach ($this->storeFacade->getAllStores() as $storeTransfer) {
            $storeName = $storeTransfer->getName();
            $locales = $storeLocaleMap[$storeName] ?? [];

            $formattedParts[] = $this->formatStoreLocales($storeName, $locales);
        }

        return (bool)$formattedParts
            ? [implode(static::FORMAT_STORE_LOCALE_SEPARATOR, $formattedParts)]
            : [static::FALLBACK_VALUE_NO_STORES];
    }

    /**
     * @param string $storeName
     * @param array<string> $locales
     *
     * @return string
     */
    protected function formatStoreLocales(string $storeName, array $locales): string
    {
        if (!$locales) {
            return sprintf(static::FORMAT_STORE_LOCALE, $storeName, static::FALLBACK_VALUE_NO_LOCALES);
        }

        $localeList = implode(static::FORMAT_LOCALE_SEPARATOR, $locales);

        return sprintf(static::FORMAT_STORE_LOCALE, $storeName, $localeList);
    }
}
