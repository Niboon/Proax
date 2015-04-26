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

namespace XLite\Module\XC\MultiCurrency\View\ItemsList\Model\Currency;

/**
 * Active currencies list
 */
class ActiveCurrencies extends \XLite\View\ItemsList\Model\Table
{
    /**
     * Define repository name
     *
     * @return string
     */
    protected function defineRepositoryName()
    {
        return 'XLite\Module\XC\MultiCurrency\Model\ActiveCurrency';
    }

    /**
     * Define columns structure
     *
     * @return array
     */
    protected function defineColumns()
    {
        return array(
            'name'              => array(
                static::COLUMN_NAME     => \XLite\Core\Translation::lbl('Name'),
                static::COLUMN_CLASS    => '\XLite\View\FormField\Inline\Label',
                static::COLUMN_MAIN     => true,
                static::COLUMN_NO_WRAP  => true,
                static::COLUMN_ORDERBY  => 100,
            ),
            'code'              => array(
                static::COLUMN_NAME     => \XLite\Core\Translation::lbl('Code'),
                static::COLUMN_CLASS    => '\XLite\View\FormField\Inline\Label',
                static::COLUMN_NO_WRAP  => true,
                static::COLUMN_ORDERBY  => 200,
            ),
            'format'            => array(
                static::COLUMN_NAME     => \XLite\Core\Translation::lbl('Format'),
                static::COLUMN_TEMPLATE => 'modules/XC/MultiCurrency/multi_currency/cell.format.tpl',
                static::COLUMN_ORDERBY  => 300,
            ),
            'prefix'            => array(
                static::COLUMN_NAME     => \XLite\Core\Translation::lbl('Prefix'),
                static::COLUMN_CLASS    => '\XLite\View\FormField\Inline\Input\Text',
                static::COLUMN_NO_WRAP  => true,
                static::COLUMN_ORDERBY  => 400,
            ),
            'suffix'            => array(
                static::COLUMN_NAME     => \XLite\Core\Translation::lbl('Suffix'),
                static::COLUMN_CLASS    => '\XLite\View\FormField\Inline\Input\Text',
                static::COLUMN_NO_WRAP  => true,
                static::COLUMN_ORDERBY  => 500,
            ),
            'rate'              => array(
                static::COLUMN_NAME     => \XLite\Core\Translation::lbl('Rate'),
                static::COLUMN_CLASS    => '\XLite\View\FormField\Inline\Input\Text\Float',
                static::COLUMN_PARAMS   => array(
                    \XLite\View\FormField\Input\Text\Float::PARAM_E => 4
                ),
                static::COLUMN_NO_WRAP  => true,
                static::COLUMN_ORDERBY  => 600,
            ),
            'countriesList'=> array (
                static::COLUMN_NAME     => \XLite\Core\Translation::lbl('Countries'),
                static::COLUMN_MAIN     => true,
                static::COLUMN_LINK     => 'currency_countries',
                static::COLUMN_ORDERBY  => 700,
            )
        );
    }

    /**
     * Mark list as removable
     *
     * @return boolean
     */
    protected function isRemoved()
    {
        return true;
    }

    /**
     * Mark list item as default
     *
     * @return boolean
     */
    protected function isDefault()
    {
        return true;
    }

    /**
     * Mark list as switchable (enable / disable)
     *
     * @return boolean
     */
    protected function isSwitchable()
    {
        return true;
    }

    /**
     * Defines the position MOVE widget class name
     *
     * @return string
     */
    protected function getMovePositionWidgetClassName()
    {
        return 'XLite\View\FormField\Inline\Input\Text\Position\Move';
    }

    /**
     * Defines the position OrderBy widget class name
     *
     * @return string
     */
    protected function getOrderByWidgetClassName()
    {
        return 'XLite\View\FormField\Inline\Input\Text\Position\OrderBy';
    }

    /**
     * Mark list as sortable
     *
     * @return integer
     */
    protected function getSortableType()
    {
        return static::SORT_TYPE_MOVE;
    }

    /**
     * Return params list to use for search
     *
     * @return \XLite\Core\CommonCell
     */
    protected function getSearchCondition()
    {
        $cnd = parent::getSearchCondition();

        $cnd->{\XLite\Module\XC\MultiCurrency\Model\Repo\ActiveCurrency::AC_ORDER_BY_POSITION} = 'ASC';

        return $cnd;
    }

    /**
     * Get active currency ID
     *
     * @param \XLite\Module\XC\MultiCurrency\Model\ActiveCurrency $activeCurrency Active multi currency
     *
     * @return integer
     */
    protected function getActiveCurrencyId(\XLite\Module\XC\MultiCurrency\Model\ActiveCurrency $activeCurrency)
    {
        return $activeCurrency->getId();
    }

    /**
     * Check if active currency is default currency
     *
     * @param \XLite\Module\XC\MultiCurrency\Model\ActiveCurrency $activeCurrency Active multi currency
     *
     * @return boolean
     */
    protected function isDefaultCurrency(\XLite\Module\XC\MultiCurrency\Model\ActiveCurrency $activeCurrency)
    {
        return $activeCurrency->isDefaultCurrency();
    }

    /**
     * Get format selector name
     *
     * @param array                                                $column         Column
     * @param \XLite\Module\XC\MultiCurrency\Model\ActiveCurrency $activeCurrency Active currency
     *
     * @return string
     */
    protected function getFormatSelectorName($column, \XLite\Module\XC\MultiCurrency\Model\ActiveCurrency $activeCurrency)
    {
        return 'data[' . $activeCurrency->getId() . '][format]';
    }

    /**
     * Get format selector id
     *
     * @param array                                                $column         Column
     * @param \XLite\Module\XC\MultiCurrency\Model\ActiveCurrency $activeCurrency Active currency
     *
     * @return string
     */
    protected function getFormatSelectorId($column, \XLite\Module\XC\MultiCurrency\Model\ActiveCurrency $activeCurrency)
    {
        return 'currency-format-' . $activeCurrency->getId();
    }

    /**
     * Get panel class
     *
     * @return \XLite\View\Base\FormStickyPanel
     */
    protected function getPanelClass()
    {
        return 'XLite\Module\XC\MultiCurrency\View\StickyPanel\CurrencyCountriesListForm';
    }
}