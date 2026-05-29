<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Shared\ProductPageSearch;

/**
 * Declares global environment configuration keys. Do not use it for other class constants.
 */
class ProductPageSearchConstants
{
    /**
     * Specification:
     * - Queue name as used for processing Product messages
     *
     * @api
     *
     * @var string
     */
    public const PRODUCT_SYNC_SEARCH_QUEUE = 'sync.search.product';

    /**
     * Specification:
     * - Queue name as used for processing Product messages
     *
     * @api
     *
     * @var string
     */
    public const PRODUCT_SYNC_SEARCH_ERROR_QUEUE = 'sync.search.product.error';

    /**
     * Specification:
     * - Resource name, this will use for key generating
     *
     * @api
     *
     * @var string
     */
    public const PRODUCT_ABSTRACT_RESOURCE_NAME = 'product_abstract';

    /**
     * Specification:
     * - Resource name, will be used for key generating
     *
     * @api
     *
     * @var string
     */
    public const PRODUCT_CONCRETE_RESOURCE_NAME = 'product_concrete';

    /**
     * @uses \Spryker\Shared\Search\SearchConstants::FULL_TEXT_BOOSTED_BOOSTING_VALUE
     *
     * @api
     *
     * @deprecated Use {@link \Spryker\Shared\SearchElasticsearch\SearchElasticsearchConstants::FULL_TEXT_BOOSTED_BOOSTING_VALUE} instead.
     *
     * @var string
     */
    public const FULL_TEXT_BOOSTED_BOOSTING_VALUE = 'FULL_TEXT_BOOSTED_BOOSTING_VALUE';

    /**
     * Specification:
     * - When set to `true`, concrete product search results are served from Redis (product storage) instead of OpenSearch.
     * - `ProductConcretePageSearchPublisher::publish()` will remove existing search index entries rather than writing new ones.
     * - Enable only after product-concrete storage data has been fully published.
     *
     * @api
     *
     * @var string
     */
    public const string PRODUCT_CONCRETE_SEARCH_IN_STORAGE_ENABLED = 'ProductPageSearch:PRODUCT_CONCRETE_SEARCH_IN_STORAGE_ENABLED';
}
