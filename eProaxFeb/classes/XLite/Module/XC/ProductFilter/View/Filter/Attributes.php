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
 * Attributes widget
 *
 * @ListChild (list="sidebar.filter", zone="customer", weight="300")
 */
class Attributes extends \XLite\View\AView
{
    /**
     * Product classes
     *
     * @var array
     */
    protected $productClasses;

    /**
     * Get active product classes
     *
     * @return array
     */
    public function getProductClasses()
    {
        if (!isset($this->productClasses)) {
            $category = $this->getCategory();
            switch ($category->getUseClasses()) {
                case $category::USE_CLASSES_NO:
                    $this->productClasses = array();
                    break;

                case $category::USE_CLASSES_DEFINE:
                    $this->productClasses = $category->getProductClasses();
                    break;

                default:
                    $iList = new \XLite\Module\XC\ProductFilter\View\ItemsList\Product\Customer\Category\CategoryFilter;
                    $this->productClasses = \XLite\Core\Database::getRepo('\XLite\Model\Product')
                        ->findFilteredProductClasses($iList->getSearchCondition());
            }
        }

        return $this->productClasses;
    }

    /**
     * Has global filtered attributes flag
     *
     * @return boolean
     */
    protected function hasGlobalFilteredAttributes()
    {
        $cnd = new \XLite\Core\CommonCell;

        $cnd->productClass = null;
        $cnd->product = null;
        $cnd->type = \XLite\Model\Attribute::getFilteredTypes();

        return 0 < \XLite\Core\Database::getRepo('\XLite\Model\Attribute')->search($cnd, true);
    }

    /**
     * Get global groups
     *
     * @return mixed
     */
    protected function getGlobalGroups()
    {
        return \XLite\Core\Database::getRepo('XLite\Model\AttributeGroup')->findByProductClass(null);
    }

    /**
     * Get widget templates directory
     *
     * @return string
     */
    protected function getDir()
    {
        return 'modules/XC/ProductFilter/filter/attributes';
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
            && \XLite\Core\Config::getInstance()->XC->ProductFilter->enable_attributes_filter
            && (
                0 < count($this->getProductClasses())
                || $this->hasGlobalFilteredAttributes()
            );
    }
}
