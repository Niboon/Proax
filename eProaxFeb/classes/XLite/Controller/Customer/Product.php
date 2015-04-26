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

namespace XLite\Controller\Customer;

/**
 * Product
 */
class Product extends \XLite\Controller\Customer\Base\Catalog
{
    /**
     * Product
     *
     * @var \XLite\Model\Product
     */
    protected $product;

    /**
     * Define and set handler attributes; initialize handler
     *
     * @param array $params Handler params OPTIONAL
     *
     * @return void
     */
    public function __construct(array $params = array())
    {
        parent::__construct($params);

        $this->params[] = 'product_id';
    }

    /**
     * Handles the request.
     * Parses the request variables if necessary. Attempts to call the specified action function
     *
     * @return void
     */
    public function handleRequest()
    {
        \XLite\Core\Request::getInstance()->product_id = intval(\XLite\Core\Request::getInstance()->product_id);

        parent::handleRequest();
    }

    /**
     * Process an action 'preview'
     *
     * @return void
     */
    public function doActionPreview()
    {
        // Do nothing here, just display product page
    }

    /**
     * Check whether the title is to be displayed in the content area
     *
     * @return boolean
     */
    public function isTitleVisible()
    {
        return false;
    }

    /**
     * Get product category id
     *
     * @return integer
     */
    public function getCategoryId()
    {
        $categoryId = parent::getCategoryId();

        if ($this->getRootCategoryId() == $categoryId && $this->getProduct() && $this->getProduct()->getCategoryId()) {
            $categoryId = $this->getProduct()->getCategoryId();
        }

        return $categoryId;
    }

    /**
     * getDescription
     *
     * @return string
     */
    public function getDescription()
    {
        return (parent::getDescription() || !$this->getProduct())
            ? parent::getDescription()
            : $this->getProduct()->getBriefDescription();
    }

    /**
     * Return current (or default) product object
     *
     * @return \XLite\Model\Product
     */
    public function getModelObject()
    {
        return $this->getProduct();
    }

    /**
     * Alias
     *
     * @return \XLite\Model\Product
     */
    public function getProduct()
    {
        if (!isset($this->product)) {
            $this->product = $this->defineProduct();
        }

        return $this->product;
    }

    /**
     * Defines the maximum width of the images
     *
     * @return integer
     */
    public function getMaxImageWidth()
    {
        $maxWidth = 0;
        if ($this->getProduct()->hasImage()) {
            foreach ($this->getProduct()->getPublicImages() as $img) {
                $maxWidth = max($maxWidth, $img->getWidth());
            }
        } else {
            $maxWidth = \XLite\Core\Config::getInstance()->General->product_page_image_width;
        }

        return min($maxWidth, \XLite\Core\Config::getInstance()->General->product_page_image_width);
    }

    /**
     * Check - product has Description tab or not
     *
     * @return boolean
     */
    public function hasDescription()
    {
        return 0 < strlen($this->getProduct()->getDescription())
            || $this->hasAttributes();
    }

    /**
     * Check - product has visible attributes or not
     *
     * @return boolean
     */
    public function hasAttributes()
    {
        return 0 < $this->getProduct()->getWeight()
            || 0 < strlen($this->getProduct()->getSku());
    }

    /**
     * Define body classes
     *
     * @param array $classes Classes
     *
     * @return array
     */
    public function defineBodyClasses(array $classes)
    {
        $classes = parent::defineBodyClasses($classes);

        $classes[] = $this->getProduct() && $this->getCart()->isProductAdded($this->getProduct()->getProductId())
            ? 'added-product'
            : 'non-added-product';

        return $classes;
    }


    /**
     * Define product
     *
     * @return \XLite\Model\Product
     */
    protected function defineProduct()
    {
        return \XLite\Core\Database::getRepo('XLite\Model\Product')->find($this->getProductId());
    }

    /**
     * Common method to determine current location
     *
     * @return string
     */
    protected function getLocation()
    {
        return $this->getProduct() ? $this->getProduct()->getName() : null;
    }

    /**
     * Check controller visibility
     *
     * @return boolean
     */
    protected function isVisible()
    {
        return parent::isVisible()
            && $this->getProduct()
            && (
                $this->getProduct()->isVisible()
                || $this->isPreview()
            );
    }

    /**
     * Check it is preview or not
     *
     * @return boolean
     */
    protected function isPreview()
    {
        return 'preview' == \XLite\Core\Request::getInstance()->action
            && $this->getProfile()
            && $this->getProfile()->isAdmin()
            && (
                \XLite\Core\Auth::getInstance()->isPermissionAllowed(\XLite\Model\Role\Permission::ROOT_ACCESS)
                || \XLite\Core\Auth::getInstance()->isPermissionAllowed('manage catalog')
            );
    }

    /**
     * Defines the common data for JS
     *
     * @return array
     */
    public function defineCommonJSData()
    {
        return array_merge(
            parent::defineCommonJSData(),
            array(
                'product_id'    => $this->getProductId(),
                'category_id'   => $this->getCategoryId(),
            )
        );
    }
}
