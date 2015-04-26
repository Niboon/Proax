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

namespace XLite\Module\XC\Upselling\View\ItemsList;

/**
 * Related products widget (customer area)
 *
 * @ListChild (list="center.bottom", zone="customer", weight="700")
 */
class UpsellingProducts extends \XLite\View\ItemsList\Product\Customer\ACustomer
{
    /**
     * Widget target
     */
    const WIDGET_TARGET_PRODUCT = 'product';


    /**
     * Return target to retrive this widget from AJAX
     *
     * @return string
     */
    protected static function getWidgetTarget()
    {
        return static::WIDGET_TARGET_PRODUCT;
    }

    /**
     * Get title
     *
     * @return string
     */
    protected function getHead()
    {
        return \XLite\Core\Translation::lbl('Related products');
    }

    /**
     * Return params list to use for search
     *
     * @return \XLite\Core\CommonCell
     */
    protected function getSearchCondition()
    {
        $cnd = parent::getSearchCondition();
        $cnd->{\XLite\Module\XC\Upselling\Model\Repo\UpsellingProduct::SEARCH_PARENT_PRODUCT_ID}
            = $this->getProductId();

        return $cnd;
    }

    /**
     * Get widget parameters
     *
     * @return array
     */
    protected function getWidgetParameters()
    {
        $list = parent::getWidgetParameters();
        $list['product_id'] = $this->getProductId();

        return $list;
    }

    /**
     * Define widget parameters
     *
     * @return void
     */
    protected function defineWidgetParams()
    {
        parent::defineWidgetParams();

        $this->widgetParams[self::PARAM_WIDGET_TYPE]->setValue(self::WIDGET_TYPE_CENTER);
        $this->widgetParams[self::PARAM_DISPLAY_MODE]->setValue(self::DISPLAY_MODE_GRID);
        $this->widgetParams[self::PARAM_GRID_COLUMNS]->setValue(5);

        $this->widgetParams[self::PARAM_SHOW_DISPLAY_MODE_SELECTOR]->setValue(false);
        $this->widgetParams[self::PARAM_SHOW_SORT_BY_SELECTOR]->setValue(false);
    }

    /**
     * Return class name for the list pager
     *
     * @return string
     */
    protected function getPagerClass()
    {
        return '\XLite\View\Pager\Infinity';
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
        if (!isset($this->upsellingProducts) && $this->checkTarget()) {
            $products = array();
            $up = \XLite\Core\Database::getRepo('XLite\Module\XC\Upselling\Model\UpsellingProduct')
               ->search($cnd, false);
            foreach ($up as $product) {
                $products[] = $product->getProduct();
            }

            $this->upsellingProducts = $products;
        }

        return true === $countOnly
            ? count($this->upsellingProducts)
            : $this->upsellingProducts;
    }

    /**
     * Get currently viewed product ID
     *
     * @return integer
     */
    protected function getProductId()
    {
        return intval(\XLite\Core\Request::getInstance()->product_id);
    }

    // {{{ Cache

    /**
     * Get cache parameters
     *
     * @return array
     */
    protected function getCacheParameters()
    {
        $list = parent::getCacheParameters();
        $list[] = $this->getProductId();

        return $list;
    }

    /**
     * Cache availability
     *
     * @return boolean
     */
    protected function isCacheAvailable()
    {
        return true;
    }

    /**
     * Get cache TTL (seconds)
     *
     * @return integer
     */
    protected function getCacheTTL()
    {
        return 3600;
    }

    // }}}

    /**
     * Register the CSS classes for this block
     *
     * @return string
     */
    protected function getBlockClasses()
    {
        return parent::getBlockClasses() . ' block-upselling';
    }
}