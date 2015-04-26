<?php
// vim: set ts=4 sw=4 sts=4 et:

/**
 * X-Cart
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the software license agreement
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.x-cart.com/license-agreement.html
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to licensing@x-cart.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not modify this file if you wish to upgrade X-Cart to newer versions
 * in the future. If you wish to customize X-Cart for your needs please
 * refer to http://www.x-cart.com/ for more information.
 *
 * @category  X-Cart 5
 * @author    Qualiteam software Ltd <info@x-cart.com>
 * @copyright Copyright (c) 2011-2015 Qualiteam software Ltd <info@x-cart.com>. All rights reserved
 * @license   http://www.x-cart.com/license-agreement.html X-Cart 5 License Agreement
 * @link      http://www.x-cart.com/
 */

namespace XLite\Module\XC\MultiCurrency\Core;

/**
 * Cache decorator
 */
class MultiCurrency extends \XLite\Base
{
    const RATE_UPDATE_INTERVAL  = 3600;

    const RATE_UPDATE_CELL      = 'MultiCurrencyRateUpdateDate';
    const CURRENCY_CODE_CELL    = 'MultiCurrencySelectedCurrencyCode';
    const CURRENCY_ID_CELL      = 'MultiCurrencySelectedCurrencyId';
    const COUNTRY_CODE_CELL     = 'MultiCurrencySelectedCountry';

    /**
     * Convert value by provided rate
     *
     * @param float   $value     Value
     * @param float   $rate      Rate
     * @param integer $precision Precision OPTIONAL
     *
     * @return float
     */
    public function convertValueByRate($value, $rate, $precision = 0)
    {
        if (0 == $precision) {
            $return = (float)($value * $rate);
        } else {
            $return = (float) round((float)($value * $rate), $precision);
        }

        return $return;
    }

    /**
     * Check if the currency currency is default currency
     *
     * @return boolean
     */
    public function isDefaultCurrencySelected()
    {
        $defaultCurrency = $this->getDefaultCurrency();

        $selectedCurrency = $this->getSelectedCurrency();

        return $selectedCurrency->getCode() == $defaultCurrency->getCode();
    }

    /**
     * Check if there is more than one active currency
     *
     * @return boolean
     */
    public function hasMultipleCurrencies()
    {
        return \XLite\Core\Database::getRepo('XLite\Module\XC\MultiCurrency\Model\ActiveCurrency')
            ->hasMultipleCurrencies();
    }

    /**
     * Check if active currencies has available countries
     *
     * @return boolean
     */
    public function hasAvailableCountries()
    {
        return \XLite\Core\Database::getRepo('XLite\Module\XC\MultiCurrency\Model\ActiveCurrency')
            ->hasEnabledCountries();
    }

    /**
     * Get available currencies
     *
     * @return \XLite\Module\XC\MultiCurrency\Model\ActiveCurrency[]
     */
    public function getAvailableCurrencies()
    {
        return \XLite\Core\Database::getRepo('XLite\Module\XC\MultiCurrency\Model\ActiveCurrency')
            ->getAvailableCurrencies();
    }

    /**
     * Get available countries
     *
     * @return \XLite\Model\Country[]
     */
    public function getAvailableCountries()
    {
        $cnd = new \XLite\Core\CommonCell();

        $cnd->{\XLite\Model\Repo\Country::P_ORDER_BY} = array('translations.country');
        $cnd->{\XLite\Model\Repo\Country::P_ENABLED} = true;

        return \XLite\Core\Database::getRepo('XLite\Model\Country')
            ->search($cnd);
    }

    /**
     * Get countries with assigned currencies
     *
     * @return \XLite\Model\Country[]
     */
    public function getCountriesWithCurrencies()
    {
        return \XLite\Core\Database::getRepo('XLite\Module\XC\MultiCurrency\Model\ActiveCurrency')
            ->getAvailableCountries();
    }

    /**
     * Get default currency
     *
     * @return \XLite\Model\Currency
     */
    public function getDefaultCurrency()
    {
        return \XLite\Core\Database::getRepo('XLite\Module\XC\MultiCurrency\Model\ActiveCurrency')
            ->getDefaultCurrency();
    }

    /**
     * Get default country
     *
     * @return \XLite\Model\Country
     */
    public function getDefaultCountry()
    {
        if (
            !\XLite::isAdminZone()
            && !is_null(\XLite\Core\Auth::getInstance()->getProfile())
            && !is_null(\XLite\Core\Auth::getInstance()->getProfile()->getFirstAddress())
            && !is_null(\XLite\Core\Auth::getInstance()->getProfile()->getFirstAddress()->getCountry())
            && !is_null(
                \XLite\Core\Auth::getInstance()->getProfile()->getFirstAddress()->getCountry()->getActiveCurrency()
            )
        ) {
            $return = \XLite\Core\Auth::getInstance()->getProfile()->getFirstAddress()->getCountry();
        } else {
            $return = \XLite\Core\Database::getRepo('XLite\Module\XC\MultiCurrency\Model\ActiveCurrency')
                ->getDefaultCountry();
        }

        return $return;
    }

    /**
     * Get selected currency
     *
     * @return \XLite\Model\Currency
     */
    public function getSelectedCurrency()
    {
        $selectedCurrency = \XLite\Core\Database::getRepo('XLite\Module\XC\MultiCurrency\Model\ActiveCurrency')
            ->getCurrencyByCode($this->getSelectedCurrencyCode());

        if (!isset($selectedCurrency)) {
            $selectedCurrency = $this->getDefaultCurrency();
            $this->setSelectedCurrency($selectedCurrency);
        } else {
            $selectedCurrency = $selectedCurrency->getCurrency();
        }

        return $selectedCurrency;
    }

    /**
     * Get selected country
     *
     * @return \XLite\Model\Country
     */
    public function getSelectedCountry()
    {
        $selectedCountry = \XLite\Core\Database::getRepo('XLite\Model\Country')->findOneBy(
            array(
                'code' => $this->getSelectedCountryCode()
            )
        );

        if (!isset($selectedCountry)) {
            $selectedCurrency = $this->getSelectedCurrency();

            if (
                !isset($selectedCurrency)
                || !$selectedCurrency->getActiveCurrency()->hasAssignedCountries()
            ) {
                $selectedCountry = $this->getDefaultCountry();
            } else {
                $selectedCountry = $selectedCurrency->getActiveCurrency()->getFirstCountry();
            }

            $this->setSelectedCountry($selectedCountry);
        }

        return $selectedCountry;
    }

    /**
     * Get selected active currency
     *
     * @return \XLite\Module\XC\MultiCurrency\Model\ActiveCurrency
     */
    public function getSelectedMultiCurrency()
    {
        $selectedCurrency = $this->getSelectedCurrency();

        if (!isset($selectedCurrency)) {
            $selectedCurrency = $this->getDefaultCurrency();
        }

        return $selectedCurrency->getActiveCurrency();
    }

    /**
     * Set selected currency
     *
     * @param \XLite\Model\Currency $currency Currency
     *
     * @return void
     */
    public function setSelectedCurrency(\XLite\Model\Currency $currency)
    {
        if (isset($currency)) {
            $this->setSelectedCurrencyCode($currency->getCode());
            $this->setSelectedCurrencyId($currency->getCurrencyId());
        }
    }

    /**
     * Set selected country
     *
     * @param \XLite\Model\Country $country Country
     *
     * @return void
     */
    public function setSelectedCountry(\XLite\Model\Country $country)
    {
        if (isset($country)) {
            $this->setSelectedCountryCode($country->getCode());
        }
    }

    /**
     * Check if rates need to be updated
     *
     * @return boolean
     */
    public function needRateUpdate()
    {
        return $this->hasMultipleCurrencies()
            && \XLite\Module\XC\MultiCurrency\Core\CurrencyRate::PROVIDER_NONE
                != \XLite\Core\Config::getInstance()->XC->MultiCurrency->rateProvider;
    }

    /**
     * Update currency rates
     *
     * @return void
     */
    public function updateRates()
    {
        if ($this->needRateUpdate()) {
            \XLite\Core\Database::getRepo('XLite\Module\XC\MultiCurrency\Model\ActiveCurrency')->updateRates();
        }
    }

    /**
     * Set selected currency
     *
     * @return string
     */
    protected function getSelectedCurrencyCode()
    {
        return \XLite\Core\Session::getInstance()->get(static::CURRENCY_CODE_CELL);
    }

    /**
     * Set selected currency
     *
     * @return string
     */
    protected function getSelectedCurrencyId()
    {
        return \XLite\Core\Session::getInstance()->get(static::CURRENCY_ID_CELL);
    }

    /**
     * Set selected currency
     *
     * @return string
     */
    protected function getSelectedCountryCode()
    {
        return \XLite\Core\Session::getInstance()->get(static::COUNTRY_CODE_CELL);
    }

    /**
     * Set next rate update date
     *
     * @return integer
     */
    protected function getRateUpdateDate()
    {
        return \XLite\Core\Session::getInstance()->get(static::RATE_UPDATE_CELL);
    }

    /**
     * Set selected currency
     *
     * @param string $currencyCode Currency code
     *
     * @return void
     */
    protected function setSelectedCurrencyCode($currencyCode)
    {
        \XLite\Core\Session::getInstance()->set(
            static::CURRENCY_CODE_CELL,
            $currencyCode
        );
    }

    /**
     * Set selected currency
     *
     * @param integer $currencyId Currency ID
     *
     * @return void
     */
    protected function setSelectedCurrencyId($currencyId)
    {
        \XLite\Core\Session::getInstance()->set(
            static::CURRENCY_ID_CELL,
            $currencyId
        );
    }

    /**
     * Set selected currency
     *
     * @param string $countryCode Country code
     *
     * @return void
     */
    protected function setSelectedCountryCode($countryCode)
    {
        \XLite\Core\Session::getInstance()->set(
            static::COUNTRY_CODE_CELL,
            $countryCode
        );
    }

    /**
     * Set next rate update date
     *
     * @param integer $date Date
     *
     * @return void
     */
    protected function setRateUpdateDate($date)
    {
        \XLite\Core\Session::getInstance()->set(
            static::RATE_UPDATE_CELL,
            $date
        );
    }
}