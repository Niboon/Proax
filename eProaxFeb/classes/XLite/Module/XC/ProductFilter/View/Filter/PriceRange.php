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

namespace XLite\Module\XC\ProductFilter\View\Filter;

/**
 * Price range widget
 *
 * @ListChild (list="sidebar.filter", zone="customer", weight="200")
 */
class PriceRange extends \XLite\Module\XC\ProductFilter\View\Filter\AFilter
{
    /**
     * Return min price
     *
     * @return float
     */
    public function getMinPrice()
    {
        $itemList = new \XLite\Module\XC\ProductFilter\View\ItemsList\Product\Customer\Category\CategoryFilter;

        return number_format(
            \XLite\Core\Database::getRepo('\XLite\Model\Product')->findMinPrice(
                $itemList->getSearchCondition()
            ),
            \XLite::getInstance()->getCurrency()->getE(),
            '.',
            ''
        );
    }

    /**
     * Return max value
     *
     * @return float
     */
    public function getMaxPrice()
    {
        $itemList = new \XLite\Module\XC\ProductFilter\View\ItemsList\Product\Customer\Category\CategoryFilter;

        return number_format(
            \XLite\Core\Database::getRepo('\XLite\Model\Product')->findMaxPrice(
                $itemList->getSearchCondition()
            ),
            \XLite::getInstance()->getCurrency()->getE(),
            '.',
            ''
        );
    }

    /**
     * Get currency symbol
     *
     * @return string
     */
    public function getSymbol()
    {
        $currency = \XLite::getInstance()->getCurrency();

        return $currency->getSymbol() ?: $currency->getCode();
    }

    /**
     * Get widget templates directory
     *
     * @return string
     */
    protected function getDir()
    {
        return 'modules/XC/ProductFilter/filter/price_range';
    }

    /**
     * Return default template
     *
     * @return string
     */
    protected function getDefaultTemplate()
    {
        return $this->getDir() . '/body.tpl';
    }

    /**
     * Check widget visibility
     *
     * @return boolean
     */
    protected function isVisible()
    {
        return parent::isVisible()
            && \XLite\Core\Config::getInstance()->XC->ProductFilter->enable_price_range_filter;
    }

    /**
     * Get value
     *
     * @return array
     */
    protected function getValue()
    {
        $filterValues = $this->getFilterValues();

        return (
            isset($filterValues['price'])
            && is_array($filterValues['price'])
        ) ? $filterValues['price'] : array();
    }
}
