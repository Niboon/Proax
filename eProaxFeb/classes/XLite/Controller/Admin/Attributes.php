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

namespace XLite\Controller\Admin;

/**
 * Attributes controller
 */
class Attributes extends \XLite\Controller\Admin\ACL\Catalog
{
    /**
     * Controller parameters
     *
     * @var array
     */
    protected $params = array('target');

    /**
     * Product class
     *
     * @var \XLite\Model\ProductClass
     */
    protected $productClass;

    /**
     * Check ACL permissions
     *
     * @return boolean
     */
    public function checkACL()
    {
        return parent::checkACL() && (
            $this->getProductClass()
            || !\XLite\Core\Request::getInstance()->product_class_id
        );
    }

    /**
     * Return the current page title (for the content area)
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->getProductClass()
            ? static::t(
                'Attributes for X product class',
                array(
                    'class' => $this->getProductClass()->getName()
                )
            )
            : static::t('Global attributes');
    }

    /**
     * Get product class
     *
     * @return \XLite\Model\ProductClass
     */
    public function getProductClass()
    {
        if (
            is_null($this->productClass)
            && \XLite\Core\Request::getInstance()->product_class_id
        ) {
            $this->productClass = \XLite\Core\Database::getRepo('XLite\Model\ProductClass')
                ->find(intval(\XLite\Core\Request::getInstance()->product_class_id));
        }

        return $this->productClass;
    }

    /**
     * Get attribute groups
     *
     * @return array
     */
    public function getAttributeGroups()
    {
        return \XLite\Core\Database::getRepo('XLite\Model\AttributeGroup')->findByProductClass(
            $this->getProductClass()
        );
    }

    /**
     * Get attributes count
     *
     * @return array
     */
    public function getAttributesCount()
    {
        return count(
            \XLite\Core\Database::getRepo('XLite\Model\Attribute')->findBy(
                array('productClass' => $this->getProductClass(), 'product' => null)
            )
        );
    }

    /**
     * Update list
     *
     * @return void
     */
    protected function doActionUpdate()
    {
        $list = new \XLite\View\ItemsList\Model\Attribute;
        $list->processQuick();
    }

    // {{{ Search

    /**
     * Get search condition parameter by name
     *
     * @param string $paramName Parameter name
     *
     * @return mixed
     */
    public function getCondition($paramName)
    {
        $searchParams = $this->getConditions();

        return isset($searchParams[$paramName])
            ? $searchParams[$paramName]
            : null;
    }

    /**
     * Save search conditions
     *
     * @return void
     */
    protected function doActionSearch()
    {
        $cellName = \XLite\View\ItemsList\Model\Attribute::getSessionCellName();

        \XLite\Core\Session::getInstance()->$cellName = $this->getSearchParams();
    }

    /**
     * Return search parameters
     *
     * @return array
     */
    protected function getSearchParams()
    {
        $searchParams = $this->getConditions();

        foreach (
            \XLite\View\ItemsList\Model\Attribute::getSearchParams() as $requestParam
        ) {
            if (isset(\XLite\Core\Request::getInstance()->$requestParam)) {
                $searchParams[$requestParam] = \XLite\Core\Request::getInstance()->$requestParam;
            }
        }

        return $searchParams;
    }

    /**
     * Get search conditions
     *
     * @return array
     */
    protected function getConditions()
    {
        $cellName = \XLite\View\ItemsList\Model\Attribute::getSessionCellName();

        $searchParams = \XLite\Core\Session::getInstance()->$cellName;

        if (!is_array($searchParams)) {
            $searchParams = array();
        }

        return $searchParams;
    }

    // }}}

}
