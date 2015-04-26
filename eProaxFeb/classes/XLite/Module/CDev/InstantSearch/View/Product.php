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
 * @copyright Copyright (c) 2011-2014 Qualiteam software Ltd <info@x-cart.com>. All rights reserved
 * @license   http://www.x-cart.com/license-agreement.html X-Cart 5 License Agreement
 * @link      http://www.x-cart.com/
 */

namespace XLite\Module\CDev\InstantSearch\View;

/**
 * Small product details widget
 *
 * @see   ____class_see____
 * @since 1.0.17
 */
class Product extends \XLite\View\AView
{
    /**
     * Widget parameter names
     */
    const PARAM_PRODUCT_ID = 'productId';

    /**
     * Return default template
     * See setWidgetParams()
     *
     * @return string
     * @see    ____func_see____
     * @since  1.0.17
     */
    protected function getDefaultTemplate()
    {
        return 'modules/CDev/InstantSearch/product.tpl';
    }

    /**
     * Maximum product image width 
     * 
     * @return void
     * @see    ____func_see____
     * @since  1.0.17
     */
    protected function getImageMaxWidth()
    {
        return 100;
    }

    /**
     * Maximum product image height 
     * 
     * @return void
     * @see    ____func_see____
     * @since  1.0.17
     */
    protected function getImageMaxHeight()
    {
        return 100;
    }

    /**
     * Get product
     *
     * @return \XLite\Model\Product
     * @see    ____func_see____
     * @since  1.0.17
     */
    protected function getProduct()
    {
        return $this->widgetParams[static::PARAM_PRODUCT_ID]->getObject();
    }

    /**
     * Define widget parameters
     *
     * @return void
     * @see    ____func_see____
     * @since  1.0.17
     */
    protected function defineWidgetParams()
    {
        parent::defineWidgetParams();

        $this->widgetParams += array(
            static::PARAM_PRODUCT_ID => new \XLite\Model\WidgetParam\ObjectId\Product('Product Id', 0, true),
        );
    }

    /**
     * Checks whether a product was added to the cart
     *
     * @return boolean
     * @see    ____func_see____
     * @since  1.0.0
     */
    protected function isProductAdded()
    {
        return $this->getCart()->isProductAdded($this->getProduct()->getProductId());
    }
}
