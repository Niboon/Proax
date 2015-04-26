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

namespace XLite\Module\XC\ProductFilter\View;

/**
 * Product comparison widget
 *
 * @ListChild (list="sidebar.single", zone="customer", weight="110")
 * @ListChild (list="sidebar.first", zone="customer", weight="110")
 */
class Filter extends \XLite\View\SideBarBox
{
    /**
     * Return list of targets allowed for this widget
     *
     * @return array
     */
    public static function getAllowedTargets()
    {
        $result = parent::getAllowedTargets();
        $result[] = 'category';
        $result[] = 'category_filter';

        return $result;
    }

    /**
     * Register CSS files
     *
     * @return array
     */
    public function getCSSFiles()
    {
        $list = parent::getCSSFiles();
        $list[] = $this->getDir() . '/style.css';

        return $list;
    }

    /**
     * Register JS files
     *
     * @return array
     */
    public function getJSFiles()
    {
        $list = parent::getJSFiles();
        $list[] = $this->getDir() . '/script.js';

        return $list;
    }

    /**
     * Get title
     *
     * @return string
     */
    protected function getHead()
    {
        return 'Shopping options';
    }

    /**
     * Get widget templates directory
     *
     * @return string
     */
    protected function getDir()
    {
        return 'modules/XC/ProductFilter/sidebar';
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
     * Check if widget is visible
     *
     * @return boolean
     */
    protected function isVisible()
    {
        $result = parent::isVisible()
            && $this->getCategory()
            && 1 < $this->getCategory()->getProductsCount();

        if ($result) {
            $config = \XLite\Core\Config::getInstance()->XC->ProductFilter;
            $result = $config->enable_in_stock_only_filter
                || $config->enable_price_range_filter;
            if (
                !$result
                && $config->enable_attributes_filter
            ) {
                $filterAttributes = new \XLite\Module\XC\ProductFilter\View\Filter\AttributeList;
                $result = $filterAttributes->isVisible();
            }
        }

        return $result;
    }

    /**
     * Get requested category object
     *
     * @return \XLite\Model\Category
     */
    protected function getCategory()
    {
        return \XLite\Core\Database::getRepo('XLite\Model\Category')->find($this->getCategoryId());
    }

    /**
     * Get requested category ID
     *
     * @return integer
     */
    protected function getCategoryId()
    {
        return \XLite\Core\Request::getInstance()->category_id;
    }

    /**
     * Get cache parameters
     *
     * @return array
     */
    protected function getCacheParameters()
    {
        $list = parent::getCacheParameters();
        $list[] = $this->getCategoryId();

        return $list;
    }

    /**
     * Register the CSS classes for this block
     *
     * @return string
     */
    protected function getBlockClasses()
    {
        return parent::getBlockClasses() . ' block-product-filter';
    }
}
