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

namespace XLite\Module\XC\MultiCurrency\Model\Repo;

use \XLite\Module\XC\MultiCurrency\Core\MultiCurrency;

/**
 * The MailChimpList model repository
 */
class ActiveCurrency extends \XLite\Model\Repo\Base\I18n
{
    const AC_ENABLED = 'enabled';
    const AC_ORDER_BY_POSITION  = 'orderByPosition';

    /**
     * Current search condition
     *
     * @var \XLite\Core\CommonCell
     */
    protected $currentSearchCnd = null;

    /**
     * Add currently selected main currency to currencies list
     *
     * @return void
     */
    public function initiateMainCurrency()
    {
        $queryBuilder = $this->createPureQueryBuilder('ac');

        $count = $queryBuilder->select('COUNT(DISTINCT ac.active_currency_id)')
            ->andWhere('ac.currency = :selectedCurrency')
            ->setParameter('selectedCurrency', \XLite\Core\Config::getInstance()->General->shop_currency)
            ->getSingleScalarResult();

        if (0 == $count) {
            $selectedCurrency = \XLite\Core\Database::getRepo('XLite\Model\Currency')
                ->find(\XLite\Core\Config::getInstance()->General->shop_currency);

            if (isset($selectedCurrency)) {
                $this->addCurrency($selectedCurrency->getCurrencyId());
            }
        }
    }

    /**
     * Add currency to active currencies
     *
     * @param integer $currencyId Currency ID
     *
     * @return boolean
     */
    public function addCurrency($currencyId)
    {
        $return = false;

        $currency = \XLite\Core\Database::getRepo('XLite\Model\Currency')->find($currencyId);

        if (
            isset($currency)
            && !$currency->isActiveMultiCurrency()
        ) {
            $activeCurrency = new \XLite\Module\XC\MultiCurrency\Model\ActiveCurrency();

            $activeCurrency->setCurrency($currency);

            if ($activeCurrency->isDefaultCurrency()) {
                $activeCurrency->setRate(1);
            } else {
                $rate = \XLite\Module\XC\MultiCurrency\Core\CurrencyRate::getInstance()->getRate(
                    $activeCurrency->getCode()
                );

                if (
                    !isset($rate)
                    || empty($rate)
                ) {
                    $rate = 1;
                }

                $activeCurrency->setRate($rate);
            }

            $activeCurrency->setEnabled(true);

            $activeCurrency->create();

            $return = true;
        }

        return $return;
    }

    /**
     * Update currency rates
     *
     * @return void
     */
    public function updateRates()
    {
        $activeCurrencies = $this->findAll();

        if (count($activeCurrencies) > 0) {
            foreach ($activeCurrencies as $activeCurrency) {
                if (!$activeCurrency->isDefaultCurrency()) {
                    $rate = \XLite\Module\XC\MultiCurrency\Core\CurrencyRate::getInstance()->getRate(
                        $activeCurrency->getCode()
                    );

                    if (
                        isset($rate)
                        && !empty($rate)
                    ) {
                        $activeCurrency->setRate($rate);
                    } else {
                        $activeCurrency->setRateDate(\XLite\Core\Converter::getInstance()->time());
                    }

                    $activeCurrency->update();
                }
            }
        }
    }

    /**
     * Get available countries ids for active currency
     *
     * @param integer $activeCurrencyId Active currency id
     *
     * @return array
     */
    public function getActiveCountriesIds($activeCurrencyId)
    {
        $queryBuilder = $this->createPureQueryBuilder('ac');

        $activeCountries = $queryBuilder->select('c.id')
            ->innerJoin('ac.countries', 'c')
            ->andWhere('ac.active_currency_id = :currency_id')
            ->setParameter('currency_id', $activeCurrencyId)
            ->getArrayResult();

        if (
            is_array($activeCountries)
            && !empty($activeCountries)
        ) {
            foreach ($activeCountries as $i => $value) {
                $activeCountries[$i] = $value['id'];
            }
        } else {
            $activeCountries = array();
        }

        return $activeCountries;
    }

    /**
     * Get available countries ids for active currency
     *
     * @param integer $activeCurrencyId Active currency id
     *
     * @return array
     */
    public function getActiveCountriesCodes($activeCurrencyId)
    {
        $queryBuilder = $this->createPureQueryBuilder('ac');

        $activeCountries = $queryBuilder->select('c.code')
            ->innerJoin('ac.countries', 'c')
            ->andWhere('ac.active_currency_id = :currency_id')
            ->setParameter('currency_id', $activeCurrencyId)
            ->getArrayResult();

        if (
            is_array($activeCountries)
            && !empty($activeCountries)
        ) {
            foreach ($activeCountries as $i => $value) {
                $activeCountries[$i] = $value['code'];
            }
        } else {
            $activeCountries = array();
        }

        return $activeCountries;
    }

    /**
     * Add countries by country code
     *
     * @param integer $activeCurrencyId Active currency ID
     * @param array   $codes            Country codes
     *
     * @return void
     */
    public function updateCountriesByCode($activeCurrencyId, $codes)
    {
        $newCountries = array();
        $removedCountries = array();

        $activeCurrency = $this->find($activeCurrencyId);

        $activeCountries = $this->getActiveCountriesCodes($activeCurrencyId);

        foreach ($codes['add'] as $code) {
            if (!in_array($code, $activeCountries)) {
                $newCountries[] = $code;
            }
        }

        foreach ($codes['remove'] as $code) {
            if (in_array($code, $activeCountries)) {
                $removedCountries[] = $code;
            }
        }

        if (!empty($newCountries)) {
            $countries = \XLite\Core\Database::getRepo('XLite\Model\Country')->findByCode($newCountries);

            foreach ($countries as $country) {
                $country->setActiveCurrency($activeCurrency);
                $country->update();
            }
        }

        if (!empty($removedCountries)) {
            $countries = \XLite\Core\Database::getRepo('XLite\Model\Country')->findByCode($removedCountries);

            foreach ($countries as $country) {
                $country->setActiveCurrency(null);
                $country->update();
            }
        }
    }

    /**
     * Get available currencies
     *
     * @return \XLite\Module\XC\MultiCurrency\Model\ActiveCurrency[]
     */
    public function getAvailableCurrencies()
    {
        $activeCurrencies = $this->createPureQueryBuilder('ac')
            ->andWhere('ac.enabled = :enabled')
            ->setParameter('enabled', true)
            ->getResult();

        return $activeCurrencies;
    }

    /**
     * Get available countries
     *
     * @return \XLite\Model\Country[]
     */
    public function getAvailableCountries()
    {
        $countries = \XLite\Core\Database::getRepo('XLite\Model\Country')->createQueryBuilder('c')
            ->innerJoin('c.active_currency', 'ac')
            ->andWhere('ac.enabled = :enabled')
            ->setParameter('enabled', true)
            ->getResult();

        return $countries;
    }

    /**
     * Get active currency by currency code
     *
     * @param string $code Currency code
     *
     * @return \XLite\Module\XC\MultiCurrency\Model\ActiveCurrency
     */
    public function getCurrencyByCode($code)
    {
        $return = $this->createPureQueryBuilder('ac')
            ->innerJoin('ac.currency', 'c')
            ->andWhere('c.code = :currency_code')
            ->setParameter('currency_code', $code)
            ->getSingleResult();

        return $return;
    }

    /**
     * Check if active currencies has assigned countries
     *
     * @param string $code Currency code
     *
     * @return boolean
     */
    public function hasAssignedCountries($code = '')
    {
        $count = $this->createPureQueryBuilder('ac')
            ->select('COUNT (DISTINCT c.code)')
            ->innerJoin('ac.countries', 'c');

        if (!empty($code)) {
            $count->innerJoin('ac.currency', 'cc')
                ->andWhere('cc.code = :currency_code')
                ->setParameter('currency_code', $code);
        }

        $count = $count->andWhere('ac.enabled = :enabled')
            ->setParameter('enabled', true)
            ->getSingleScalarResult();

        return $count > 0;
    }

    /**
     * Has enabled currencies
     *
     * @return boolean
     */
    public function hasEnabledCountries()
    {
        $count = \XLite\Core\Database::getRepo('\XLite\Model\Country')->createPureQueryBuilder('c')
            ->select('COUNT (DISTINCT c.code)')
            ->andWhere('c.enabled = :enabled')
            ->setParameter('enabled', true)
            ->getSingleScalarResult();

        return $count > 0;
    }

    /**
     * Check if there is more than one active currency
     *
     * @return boolean
     */
    public function hasMultipleCurrencies()
    {
        $count = $this->createPureQueryBuilder('ac')
            ->select('COUNT (DISTINCT ac.active_currency_id)')
            ->andWhere('ac.enabled = :enabled')
            ->setParameter('enabled', true)
            ->getSingleScalarResult();

        return $count > 1;
    }

    /**
     * Get default country code
     *
     * @return \XLite\Model\Country
     */
    public function getDefaultCountry()
    {
        // Select the firs country assigned to the selected currency
        $country = \XLite\Core\Database::getRepo('XLite\Model\Country')->createQueryBuilder('c')
            ->innerJoin('c.active_currency', 'ac')
            ->innerJoin('ac.currency', 'cu')
            ->andWhere('cu.code = :currency_code')
            ->andWhere('ac.enabled = :enabled')
            ->andWhere('c.enabled = :enabled')
            ->setParameter('enabled', 1)
            ->setParameter('currency_code',  MultiCurrency::getInstance()->getSelectedCurrency()->getCode())
            ->getSingleResult();

        // Select country from the location config and check if it is in conflict with the selected currency
        if (!isset($country)) {
            $country = \XLite\Core\Database::getRepo('XLite\Model\Country')->findOneBy(
                array(
                    'code'      => \XLite\Core\Config::getInstance()->Company->location_country,
                    'enabled'   => true
                )
            );

            if (
                isset($country)
                && $country->hasAssignedCurrencies()
                && MultiCurrency::getInstance()->getSelectedCurrency()->getCode()
                != $country->setActiveCurrency()->getCode()
            ) {
                unset($country);
            }
        }

        // If all fails, select first enabled country that does not have assigned currency
        if (!isset($country)) {
            $country = \XLite\Core\Database::getRepo('XLite\Model\Country')->createQueryBuilder('c')
                ->leftJoin('c.active_currency', 'ac')
                ->andWhere('ac.active_currency_id IS NULL')
                ->andWhere('c.enabled = :enabled')
                ->setParameter('enabled', 1)
                ->getSingleResult();
        }

        return $country;
    }

    /**
     * Get the last rate update date
     *
     * @return integer
     */
    public function getLastRateUpdateDate()
    {
        return $this->createPureQueryBuilder('ac')
            ->select('ac.rateDate')
            ->andWhere('ac.rateDate <> 0')
            ->orderBy('ac.rateDate', 'ASC')
            ->setMaxResults(1)
            ->getSingleScalarResult();
    }

    /**
     * Get default currency
     *
     * @return \XLite\Model\Currency
     */
    public function getDefaultCurrency()
    {
        $defaultCurrency = \XLite\Core\Database::getRepo('XLite\Model\Currency')
            ->find(\XLite\Core\Config::getInstance()->General->shop_currency);

        return $defaultCurrency;
    }

    /**
     * Common search
     *
     * @param \XLite\Core\CommonCell $cnd       Condition
     * @param boolean                $countOnly Count only OPTIONAL
     *
     * @return \XLite\Module\XC\MailChimp\Model\MailChimpList[] | integer
     */
    public function search(\XLite\Core\CommonCell $cnd, $countOnly = false)
    {
        $queryBuilder = $this->createPureQueryBuilder('ac');

        $this->currentSearchCnd = $cnd;

        foreach ($this->currentSearchCnd as $key => $value) {
            $this->callSearchConditionHandler($value, $key, $queryBuilder, $countOnly);
        }

        return $countOnly
            ? $this->searchCount($queryBuilder)
            : $this->searchResult($queryBuilder);
    }

    /**
     * Return list of handling search params
     *
     * @return array
     */
    protected function getHandlingSearchParams()
    {
        return array(
            self::AC_ENABLED,
            self::AC_ORDER_BY_POSITION
        );
    }

    /**
     * Check if param can be used for search
     *
     * @param string $param Name of param to check
     *
     * @return boolean
     */
    protected function isSearchParamHasHandler($param)
    {
        return in_array($param, $this->getHandlingSearchParams());
    }

    /**
     * Call corresponded method to handle a search condition
     *
     * @param mixed                      $value        Condition data
     * @param string                     $key          Condition name
     * @param \Doctrine\ORM\QueryBuilder $queryBuilder Query builder to prepare
     * @param boolean                    $countOnly    Count only OPTIONAL
     *
     * @return void
     */
    protected function callSearchConditionHandler($value, $key, \Doctrine\ORM\QueryBuilder $queryBuilder, $countOnly = false)
    {
        if ($this->isSearchParamHasHandler($key)) {
            $this->{'prepareCnd' . ucfirst($key)}($queryBuilder, $value, $countOnly);
        }
    }

    /**
     * Prepare certain search condition
     *
     * @param \Doctrine\ORM\QueryBuilder $queryBuilder Query builder to prepare
     * @param mixed                      $value        Condition data
     *
     * @return void
     */
    protected function prepareCndEnabled(\Doctrine\ORM\QueryBuilder $queryBuilder, $value)
    {
        $queryBuilder->andWhere('ac.enabled = :enabled')
            ->setParameter('enabled', $value);
    }

    /**
     * Prepare certain search condition
     *
     * @param \Doctrine\ORM\QueryBuilder $queryBuilder Query builder to prepare
     * @param mixed                      $value        Condition data
     *
     * @return void
     */
    protected function prepareCndOrderByPosition(\Doctrine\ORM\QueryBuilder $queryBuilder, $value)
    {
        $queryBuilder->orderBy('ac.position', $value);
    }

    /**
     * Search count only routine.
     *
     * @param \Doctrine\ORM\QueryBuilder $qb Query builder routine
     *
     * @return \Doctrine\ORM\PersistentCollection|integer
     */
    protected function searchCount(\Doctrine\ORM\QueryBuilder $qb)
    {
        $qb->select('COUNT(DISTINCT ac.active_currency_id)');

        return intval($qb->getSingleScalarResult());
    }

    /**
     * Search result routine.
     *
     * @param \Doctrine\ORM\QueryBuilder $qb Query builder routine
     *
     * @return \Doctrine\ORM\PersistentCollection|integer
     */
    protected function searchResult(\Doctrine\ORM\QueryBuilder $qb)
    {
        return $qb->getOnlyEntities();
    }
}