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

namespace XLite\Module\XC\ProductVariants\Controller\Admin;

/**
 * Product variant
 */
class ProductVariant extends \XLite\Controller\Admin\ACL\Catalog
{
   /**
     * Backward compatibility
     *
     * @var array
     */
    protected $params = array('target', 'id', 'page', 'backURL');

    /**
     * Check if current page is accessible
     *
     * @return boolean
     */
    public function checkAccess()
    {
        return parent::checkAccess()
            && $this->getProductVariant()
            && $this->getPages();
    }

    /**
     * Return product variant
     *
     * @return \XLite\Module\XC\ProductVariants\Model\ProductVariant
     */
    public function getProductVariant()
    {
        if (is_null($this->productVariant)) {
            $this->productVariant = \XLite\Core\Database::getRepo('XLite\Module\XC\ProductVariants\Model\ProductVariant')
            ->find(intval(\XLite\Core\Request::getInstance()->id));
        }

        return $this->productVariant;
    }

    /**
     * Return product
     *
     * @return \XLite\Model\Product
     */
    public function getProduct()
    {
        return $this->getProductVariant()
            ? $this->getProductVariant()->getProduct()
            : null;
    }

    /**
     * Return the current page title (for the content area)
     *
     * @return string
     */
    public function getTitle()
    {
        $result = $this->getProduct()
            ? $this->getProduct()->getName()
            : '';

        $pages = $this->getPages();
        if (
            $result
            && $pages
            && 1 == count($pages)
        ) {
            $result .= ' - ' . array_shift($pages);
        }

        return $result;
    }
}
