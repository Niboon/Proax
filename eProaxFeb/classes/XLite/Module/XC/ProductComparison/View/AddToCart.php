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

namespace XLite\Module\XC\ProductComparison\View;

/**
 * Add to cart widget
 *
 */
class AddToCart extends \XLite\View\Dialog
{
    /**
     * Widget parameter names
     */
    const PARAM_PRODUCT = 'product';

    /**
     * Cart amount
     *
     * @var integer
     */
    protected $cartAmount;

    /**
     * Get product
     *
     * @return \XLite\Model\Product
     */
    protected function getProduct()
    {
        return $this->getParam(static::PARAM_PRODUCT);
    }

    /**
     * Get product id
     *
     * @return integer
     */
    protected function getProductId()
    {
        return $this->getProduct()
            ? $this->getProduct()->getProductId()
            : null;
    }

    /**
     * Get cart amount
     *
     * @return integer
     */
    protected function getCartAmount()
    {
        if (!isset($this->cartAmount)) {
            $this->cartAmount = 0;
            foreach ($this->getCart()->getItems() as $item) {
                $product = $item->getProduct();
    
                if ($product && $product->getProductId() == $this->getProductId()) {
                    $this->cartAmount += $item->getAmount(); 
                }
            }

        }

        return $this->cartAmount;
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
            self::PARAM_PRODUCT  => new \XLite\Model\WidgetParam\Object(
                null,
                false,
                'XLite\Model\Product'
            ),
        );
    }

    /**
     * Get widget templates directory
     *
     * @return string
     */
    protected function getDir()
    {
        return 'modules/XC/ProductComparison/add_to_cart';
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

}
