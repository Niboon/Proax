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

namespace XLite\Module\CDev\Paypal\View\Button\Minicart;

/**
 * Express Checkout button
 *
 * @ListChild (list="minicart.horizontal.buttons", weight="100")
 */
class ExpressCheckout extends \XLite\Module\CDev\Paypal\View\Button\AExpressCheckout
{
    /**
     * Returns true if widget is visible
     *
     * @return boolean
     */
    protected function isVisible()
    {
        /** @var \XLite\Model\Cart $cart */
        $cart = $this->getCart();

        return parent::isVisible()
            && $cart
            && (0 < $cart->getTotal())
            && $cart->checkCart();
    }

    /**
     * Returns widget default template
     *
     * @return string
     */
    protected function getDefaultTemplate()
    {
        return 'modules/CDev/Paypal/button/minicart/default/express_checkout.tpl';
    }

    /**
     * Return current template
     *
     * @return string
     */
    protected function getTemplate()
    {
        return $this->isInContextAvailable()
            ? 'modules/CDev/Paypal/button/minicart/in_context/express_checkout.tpl'
            : 'modules/CDev/Paypal/button/minicart/default/express_checkout.tpl';
    }

    /**
     * Returns additional link params
     *
     * @return array
     */
    protected function getAdditionalLinkParams()
    {
        $result = parent::getAdditionalLinkParams();

        if ($this->isInContextAvailable()) {
            $result['inContext'] = true;
            $result['cancelUrl'] = $this->isAjax()
                ? $this->getReferrerURL()
                : \XLite\Core\URLManager::getSelfURI();
        }

        return $result;
    }
}
