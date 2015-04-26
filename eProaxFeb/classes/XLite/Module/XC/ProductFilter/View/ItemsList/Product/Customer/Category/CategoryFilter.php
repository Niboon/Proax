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

namespace XLite\Module\XC\ProductFilter\View\ItemsList\Product\Customer\Category;

/**
 * Category filters list widget
 *
 * @ListChild (list="center.bottom", zone="customer", weight="200")
 *
 */
class CategoryFilter extends \XLite\View\ItemsList\Product\Customer\Category\ACategory
{
    /**
     * Widget parameter names
     */
    const PARAM_FILTER = 'filter';

    /**
     * Items count before filter
     *
     * @var integer
     */
    protected $itemsCountBefore;

    /**
     * Widget target
     */
    const WIDGET_TARGET = 'category_filter';

    /**
     * Return list of targets allowed for this widget
     *
     * @return array
     */
    public static function getAllowedTargets()
    {
        return array(self::WIDGET_TARGET);
    }

    /**
     * Get session cell name for the certain list items widget
     *
     * @return string
     */
    public static function getSessionCellName()
    {
        return parent::getSessionCellName()
            . \XLite\Core\Request::getInstance()->{self::PARAM_CATEGORY_ID};
    }

    /**
     * Return target to retrive this widget from AJAX
     *
     * @return string
     */
    protected static function getWidgetTarget()
    {
        return self::WIDGET_TARGET;
    }

    /**
     * Register CSS files
     *
     * @return array
     */
    public function getCSSFiles()
    {
        $list = parent::getCSSFiles();
        $list[] = 'modules/XC/ProductFilter/category_filter/style.css';

        return $list;
    }

    /**
     * Return params list to use for search
     *
     * @return \XLite\Core\CommonCell
     */
    public function getSearchCondition()
    {
        $cnd = parent::getSearchCondition();
        if (!isset($cnd)) {
            $cnd = new \XLite\Core\CommonCell();
        }
        $cnd->{\XLite\Model\Repo\Product::P_CATEGORY_ID} = $this->getCategoryId();

        return $cnd;
    }

    /**
     * Returns CSS classes for the container element
     *
     * @return string
     */
    public function getListCSSClasses()
    {
        return parent::getListCSSClasses() . ' filtered-products';
    }

    /**
     * Return number of items in products list before filter
     *
     * @return array
     */
    protected function getItemsCountBefore()
    {
        if (!isset($this->itemsCountBefore)) {
            $this->itemsCountBefore = parent::getData($this->getSearchCondition(), true);
        }

        return $this->itemsCountBefore;
    }

    /**
     * Define widget parameters
     *
     * @return void
     */
    protected function defineWidgetParams()
    {
        parent::defineWidgetParams();

        $this->widgetParams += array(
            self::PARAM_FILTER => new \XLite\Model\WidgetParam\Collection('Product filter', array()),
        );
    }

    /**
     * Define so called "request" parameters
     *
     * @return void
     */
    protected function defineRequestParams()
    {
        parent::defineRequestParams();

        $this->requestParams[] = self::PARAM_FILTER;
    }

    /**
     * Return products list
     *
     * @param \XLite\Core\CommonCell $cnd       Search condition
     * @param boolean                $countOnly Return items list or only its size OPTIONAL
     *
     * @return array|void
     */
    protected function getData(\XLite\Core\CommonCell $cnd, $countOnly = false)
    {
        if (!isset($cnd)) {
            $cnd = new \XLite\Core\CommonCell();
        }

        $cnd->filter = $this->getParam(self::PARAM_FILTER);
    
        return parent::getData($cnd, $countOnly);        
    }

    /**
     * Check if header is visible
     *
     * @return boolean
     */
    protected function isHeaderVisible()
    {
        return $this->hasResults();
    }

    /**
     * Check if pager is visible
     *
     * @return boolean
     */
    protected function isPagerVisible()
    {
        return $this->hasResults();
    }

    /**
     * Get empty list template
     *
     * @return string
     */
    protected function getEmptyListTemplate()
    {
        return 'modules/XC/ProductFilter/category_filter/empty.tpl';
    }

    /**
     * Mark list as switchable (enable / disable)
     *
     * @return boolean
     */
    protected function isDisplayWithEmptyList()
    {
        return true;
    }

    /**
     * Return name of the session cell identifier
     *
     * @return string
     */
    protected function getSessionCell()
    {
        return parent::getSessionCell() 
            . \XLite\Core\Request::getInstance()->{self::PARAM_CATEGORY_ID};
    }

    // {{{ Cache

    /**
     * Cache allowed
     *
     * @param string $template Template
     *
     * @return boolean
     */
    protected function isCacheAllowed($template)
    {
        return parent::isCacheAllowed($template)
            && !isset($template)
            && $this->isStaticProductList();
    }

    /**
     * Cache availability
     *
     * @return boolean
     */
    protected function isCacheAvailable()
    {
        return $this->getCart()->isEmpty();
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

    /**
     * Get cache parameters
     *
     * @return array
     */
    protected function getCacheParameters()
    {
        $list = parent::getCacheParameters();

        $list[] = md5(serialize($this->getParam(self::PARAM_FILTER)));

        return $list;
    }

    // }}}

}
