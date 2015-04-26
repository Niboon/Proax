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

namespace XLite\Module\CDev\Paypal\View\Button;

/**
 * Express Checkout base button
 */
abstract class AExpressCheckout extends \XLite\View\Button\Link
{
    const PARAM_IN_CONTEXT = 'inContext';

    /**
     * Returns true if widget is visible
     *
     * @return boolean
     */
    protected function isVisible()
    {
        $cart = $this->getCart();

        return parent::isVisible()
            && \XLite\Module\CDev\Paypal\Main::isExpressCheckoutEnabled($cart);
    }

    /**
     * Get CSS class name
     *
     * @return string
     */
    protected function getClass()
    {
        return 'pp-ec-button';
    }

    /**
     * Get merchant id
     *
     * @return string
     */
    protected function getMerchantId()
    {
        return \XLite\Module\CDev\Paypal\Main::getMerchantId();
    }

    /**
     * Check for merchant id is present
     *
     * @return boolean
     */
    protected function hasMerchantId()
    {
        return (bool) $this->getMerchantId();
    }

    /**
     * defineWidgetParams
     *
     * @return void
     */
    protected function defineWidgetParams()
    {
        parent::defineWidgetParams();

        $this->widgetParams[static::PARAM_LOCATION] = new \XLite\Model\WidgetParam\String(
            'Redirect to',
            $this->buildURL('checkout', 'start_express_checkout')
        );

        $this->widgetParams[static::PARAM_IN_CONTEXT] = new \XLite\Model\WidgetParam\Bool(
            'Is In-Context checkout',
            $this->defineInContext()
        );
    }

    /**
     * Returns additional link params
     *
     * @return array
     */
    protected function getAdditionalLinkParams()
    {
        return array();
    }

    /**
     * Define inContext widget param
     *
     * @return boolean
     */
    protected function defineInContext()
    {
        return \XLite\Module\CDev\Paypal\Main::isInContextCheckoutAvailable();
    }

    /**
     * Check if In-Context checkout available
     *
     * @return boolean
     */
    protected function isInContextAvailable()
    {
        return $this->getParam(static::PARAM_IN_CONTEXT);
    }

    /**
     * We make the full location path for the provided URL
     *
     * @return string
     */
    protected function getLocationURL()
    {
        $url = $this->getParam(static::PARAM_LOCATION);

        $params = $this->getAdditionalLinkParams();
        if ($params) {
            $url = $this->buildURL('checkout', 'start_express_checkout', $params);
        }

        return \XLite::getInstance()->getShopURL($url);
    }
}
