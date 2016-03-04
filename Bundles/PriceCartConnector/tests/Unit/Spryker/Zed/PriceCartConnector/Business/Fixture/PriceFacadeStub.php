<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Unit\Spryker\Zed\PriceCartConnector\Business\Fixture;

use Spryker\Zed\Price\Business\PriceFacade;

class PriceFacadeStub extends PriceFacade
{

    /**
     * @var array
     */
    private $prices = [];

    /**
     * @var array
     */
    private $validities = [];

    /**
     * @param string $sku
     * @param string|null $priceType
     *
     * @return mixed
     */
    public function getPriceBySku($sku, $priceType = null)
    {
        return $this->prices[$sku];
    }

    /**
     * @param string $sku
     * @param string|null $priceType
     *
     * @return bool
     */
    public function hasValidPrice($sku, $priceType = null)
    {
        if (!isset($this->validities[$sku])) {
            return false;
        }

        return $this->validities[$sku];
    }

    /**
     * @return void
     */
    public function addPriceStub($sku, $price)
    {
        $this->prices[$sku] = $price;
    }

    /**
     * @return void
     */
    public function addValidityStub($sku, $validity = true)
    {
        $this->validities[$sku] = $validity;
    }

}
