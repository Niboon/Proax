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

namespace XLite\Module\CDev\Sale\View;

/**
 * Sale products abstract widget class
 *
 */
abstract class ASale extends \XLite\View\ItemsList\Product\Customer\ACustomer
{
    /**
     * Widget target
     */
    const WIDGET_TARGET_SALE_PRODUCTS = 'sale_products';


    /**
     * Return target to retrive this widget from AJAX
     *
     * @return string
     */
    protected static function getWidgetTarget()
    {
        return static::WIDGET_TARGET_SALE_PRODUCTS;
    }

    /**
     * Returns CSS classes for the container element
     *
     * @return string
     */
    public function getListCSSClasses()
    {
        return parent::getListCSSClasses() . ' sale-products';
    }

    /**
     * Initialize widget (set attributes)
     *
     * @param array $params Widget params
     *
     * @return void
     */
    public function setWidgetParams(array $params)
    {
        parent::setWidgetParams($params);

        unset($this->widgetParams[self::PARAM_SHOW_SORT_BY_SELECTOR]);
    }

    /**
     * Get title
     *
     * @return string
     */
    protected function getHead()
    {
        return 'Sale';
    }

    /**
     * Return class name for the list pager
     *
     * @return string
     */
    protected function getPagerClass()
    {
        return '\XLite\Module\CDev\Sale\View\Pager\Pager';
    }

    /**
     * Return params list to use for search
     *
     * @return \XLite\Core\CommonCell
     */
    protected function getSearchConditions(\XLite\Core\CommonCell $cnd)
    {
        $cnd->{\XLite\Module\CDev\Sale\Model\Repo\Product::P_PARTICIPATE_SALE} = true;

        $cnd->{\XLite\Model\Repo\Product::P_ORDER_BY} = array(
            \XLite\Module\CDev\Sale\Model\Repo\Product::PERCENT_CALCULATED_FIELD,
            'DESC'
        );

        return $cnd;
    }

    /**
     * Return products list
     *
     * @param \XLite\Core\CommonCell $cnd       Search condition
     * @param boolean                $countOnly Return items list or only its size OPTIONAL
     *
     * @return mixed
     */
    protected function getData(\XLite\Core\CommonCell $cnd, $countOnly = false)
    {
        return \XLite\Core\Database::getRepo('XLite\Model\Product')
            ->search($this->getSearchConditions($cnd), $countOnly);
    }

    /**
     * Get max number of products displayed in block
     *
     * @return integer
     */
    protected function getMaxCountInBlock()
    {
        return intval(\XLite\Core\Config::getInstance()->CDev->Sale->sale_max_count_in_block) ?: 3;
    }

    /**
     * Register the CSS classes for this block
     *
     * @return string
     */
    protected function getBlockClasses()
    {
        return parent::getBlockClasses() . ' block-sale-products';
    }
}
