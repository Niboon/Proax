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

namespace XLite\Module\CDev\SalesTax\View\ItemsList\Model;

/**
 * Rate items list
 */
class Rate extends \XLite\View\ItemsList\Model\Table
{
    /**
     * Define columns structure
     *
     * @return array
     */
    protected function defineColumns()
    {
        return array(
            'taxClass' => array(
                static::COLUMN_NAME          => static::t('Tax class'),
                static::COLUMN_CLASS         => 'XLite\View\Taxes\Inline\TaxClass',
                static::COLUMN_ORDERBY       => 100,
            ),
            'membership' => array(
                static::COLUMN_NAME          => static::t('User membership'),
                static::COLUMN_CLASS         => 'XLite\View\FormField\Inline\Select\Membership',
                static::COLUMN_ORDERBY       => 200,
            ),
            'zone' => array(
                static::COLUMN_NAME          => static::t('Zone'),
                static::COLUMN_CLASS         => 'XLite\View\Taxes\Inline\Zone',
                static::COLUMN_ORDERBY       => 300,
                static::COLUMN_HEAD_TEMPLATE => 'modules/CDev/SalesTax/zone_head.tpl',
            ),
            'taxableBase' => array(
                static::COLUMN_NAME          => static::t('Taxable base'),
                static::COLUMN_CLASS         => 'XLite\Module\CDev\SalesTax\View\FormField\Inline\RateTaxableBase',
                static::COLUMN_ORDERBY       => 350,
            ),
            'value' => array(
                static::COLUMN_NAME          => static::t('Rate') . ', (%)',
                static::COLUMN_CLASS         => 'XLite\View\FormField\Inline\Input\Text\Float',
                static::COLUMN_PARAMS        => array(
                    \XLite\View\FormField\Input\Text\Float::PARAM_E => 4,
                ),
                static::COLUMN_ORDERBY       => 400,
            ),
        );
    }

    /**
     * Define repository name
     *
     * @return string
     */
    protected function defineRepositoryName()
    {
        return 'XLite\Module\CDev\SalesTax\Model\Tax\Rate';
    }

    /**
     * Get create entity URL
     *
     * @return string
     */
    protected function getCreateURL()
    {
        return \XLite\Core\Converter::buildUrl('vat_sales_rate');
    }

    /**
     * Get create button label
     *
     * @return string
     */
    protected function getCreateButtonLabel()
    {
        return 'New sale tax rate';
    }

    /**
     * Inline creation mechanism position
     *
     * @return integer
     */
    protected function isInlineCreation()
    {
        return static::CREATE_INLINE_TOP;
    }

    // {{{ Behaviors

    /**
     * Mark list as removable
     *
     * @return boolean
     */
    protected function isRemoved()
    {
        return true;
    }

    // }}}

    /**
     * Get container class
     *
     * @return string
     */
    protected function getContainerClass()
    {
        return parent::getContainerClass() . ' rates';
    }

    /**
     * Get panel class
     *
     * @return \XLite\View\Base\FormStickyPanel
     */
    protected function getPanelClass()
    {
        return null;
    }

    /**
     * Check - pager box is visible or not
     *
     * @return boolean
     */
    protected function isPagerVisible()
    {
        return false;
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
        $result = new \XLite\Core\CommonCell;

        $result->{\XLite\Module\CDev\SalesTax\Model\Repo\Tax\Rate::PARAM_EXCL_TAXABLE_BASE}
            = \XLite\Module\CDev\SalesTax\Model\Tax\Rate::TAXBASE_SHIPPING;

        return $result;
    }

    /**
     * Return "empty list" catalog
     *
     * @return string
     */
    protected function getEmptyListDir()
    {
        return 'modules/CDev/SalesTax/items_list';
    }

    /**
     * getEmptyListFile
     *
     * @return string
     */
    protected function getEmptyListFile()
    {
        return 'empty.tpl';
    }
}
