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

namespace XLite\Module\CDev\Paypal\View\Checkout;

/**
 * Payment widget
 */
class InContextCheckout extends \XLite\View\AView
{
    /**
     * Return widget default template
     *
     * @return string
     */
    protected function getDefaultTemplate()
    {
        return 'modules/CDev/Paypal/checkout/in_context_checkout/body.tpl';
    }

    /**
     * Check if widget is visible
     *
     * @return boolean
     */
    protected function isVisible()
    {
        return parent::isVisible()
            && (
                !$this->isTokenValid()
                || \XLite\Module\CDev\Paypal\Model\Payment\Processor\ExpressCheckout::EC_TYPE_SHORTCUT
                    !== \XLite\Core\Session::getInstance()->ec_type
            )
            && \XLite\Module\CDev\Paypal\Main::isExpressCheckoutEnabled()
            && \XLite\Module\CDev\Paypal\Main::isInContextCheckoutAvailable();
    }

    /**
     * Returns true if token initialized and is not expired
     *
     * @return boolean
     */
    protected function isTokenValid()
    {
        return !empty(\XLite\Core\Session::getInstance()->ec_token)
            && \XLite\Module\CDev\Paypal\Model\Payment\Processor\ExpressCheckout::TOKEN_TTL
                > \XLite\Core\Converter::time() - \XLite\Core\Session::getInstance()->ec_date
            && !empty(\XLite\Core\Session::getInstance()->ec_payer_id);
    }

    /**
     * Get popup url
     *
     * @return string
     */
    protected function getPopupUrl()
    {
        $params = array(
            'inContext' => true,
            'cancelUrl' => \XLite\Core\URLManager::getSelfURI(),
        );

        return \XLite::getInstance()->getShopURL($this->buildURL('checkout', 'start_express_checkout', $params));
    }
}
