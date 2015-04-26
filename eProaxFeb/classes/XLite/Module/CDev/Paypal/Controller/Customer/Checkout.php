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

namespace XLite\Module\CDev\Paypal\Controller\Customer;

/**
 * Checkout controller
 */
class Checkout extends \XLite\Controller\Customer\Checkout implements \XLite\Base\IDecorator
{
    /**
     * Order placement is success
     *
     * @param boolean $fullProcess Full process or not OPTIONAL
     *
     * @return void
     */
    public function processSucceed($fullProcess = true)
    {
        parent::processSucceed($fullProcess);

        if (\XLite\Core\Request::getInstance()->inContext) {
            \XLite\Core\Session::getInstance()->inContextRedirect = true;
            \XLite\Core\Session::getInstance()->cancelUrl = \XLite\Core\Request::getInstance()->cancelUrl;
        }
    }

    /**
     * doActionStartExpressCheckout 
     * 
     * @return void
     */
    protected function doActionStartExpressCheckout()
    {
        if (\XLite\Module\CDev\Paypal\Main::isExpressCheckoutEnabled()) {
            $paymentMethod = $this->getExpressCheckoutPaymentMethod();

            $this->getCart()->setPaymentMethod($paymentMethod);

            $this->updateCart();

            \XLite\Core\Session::getInstance()->ec_type
                = \XLite\Module\CDev\Paypal\Model\Payment\Processor\ExpressCheckout::EC_TYPE_SHORTCUT;

            $processor = $paymentMethod->getProcessor();
            $token = $processor->doSetExpressCheckout($paymentMethod);

            if (isset($token)) {
                \XLite\Core\Session::getInstance()->ec_token = $token;
                \XLite\Core\Session::getInstance()->ec_date = \XLite\Core\Converter::time();
                \XLite\Core\Session::getInstance()->ec_payer_id = null;

                $processor->redirectToPaypal($token);

                exit ();

            } else {
                if (\XLite\Core\Request::getInstance()->inContext) {
                    \XLite\Core\Session::getInstance()->cancelUrl = \XLite\Core\Request::getInstance()->cancelUrl;
                    \XLite\Core\Session::getInstance()->inContextRedirect = true;
                    $this->setReturnURL($this->buildURL('checkout_failed'));
                }

                \XLite\Core\TopMessage::getInstance()->addError(
                    $processor->getErrorMessage() ?: 'Failure to redirect to PayPal.'
                );
            }
        }
    }

    /**
     * doExpressCheckoutReturn 
     * 
     * @return void
     */
    protected function doActionExpressCheckoutReturn()
    {
        $request = \XLite\Core\Request::getInstance();
        $cart = $this->getCart();

        \XLite\Module\CDev\Paypal\Main::addLog('doExpressCheckoutReturn()', $request->getData());

        $checkoutAction = false;

        if (isset($request->cancel)) {
            \XLite\Core\Session::getInstance()->ec_token = null;
            \XLite\Core\Session::getInstance()->ec_date = null;
            \XLite\Core\Session::getInstance()->ec_payer_id = null;
            \XLite\Core\Session::getInstance()->ec_type = null;

            $cart->unsetPaymentMethod();

            \XLite\Core\TopMessage::getInstance()->addWarning('Express Checkout process stopped.');

        } elseif (!isset($request->token) || $request->token != \XLite\Core\Session::getInstance()->ec_token) {
            \XLite\Core\TopMessage::getInstance()->addError('Wrong token of Express Checkout.');

        } elseif (!isset($request->PayerID)) {
            \XLite\Core\TopMessage::getInstance()->addError('PayerID value was not returned by PayPal.');

        } else {

            // Express Checkout shortcut flow processing

            \XLite\Core\Session::getInstance()->ec_type
                = \XLite\Module\CDev\Paypal\Model\Payment\Processor\ExpressCheckout::EC_TYPE_SHORTCUT;

            \XLite\Core\Session::getInstance()->ec_payer_id = $request->PayerID;
            $paymentMethod = $this->getExpressCheckoutPaymentMethod();
            $processor = $paymentMethod->getProcessor();

            $buyerData = $processor->doGetExpressCheckoutDetails($paymentMethod, $request->token);

            if (empty($buyerData)) {
                \XLite\Core\TopMessage::getInstance()->addError('Your address data was not received from PayPal.');

            } else {
                // Fill the cart with data received from Paypal
                $this->requestData = $this->prepareBuyerData($processor, $buyerData);

                if (!\XLite\Core\Auth::getInstance()->isLogged()) {
                    $this->updateProfile();
                }

                $modifier = $cart->getModifier(\XLite\Model\Base\Surcharge::TYPE_SHIPPING, 'SHIPPING');
                if ($modifier && $modifier->canApply()) {
                    $this->requestData['billingAddress'] = $this->requestData['shippingAddress'];
                    $this->requestData['same_address'] = true;

                    $this->updateShippingAddress();

                    $this->updateBillingAddress();
                }

                $this->setCheckoutAvailable();

                $this->updateCart();

                $this->doActionCheckout();

                $checkoutAction = true;
            }
        }

        if (!$checkoutAction) {
            $this->setReturnURL(\XLite\Core\Request::getInstance()->cancelUrl ?: $this->buildURL('checkout'));
        }
    }

    /**
     * Set up ec_type flag to 'mark' value if payment method selected on checkout
     * 
     * @return void
     */
    protected function doActionPayment()
    {
        \XLite\Core\Session::getInstance()->ec_type
            = \XLite\Module\CDev\Paypal\Model\Payment\Processor\ExpressCheckout::EC_TYPE_MARK;

        parent::doActionPayment();
    }

    /**
     * Translate array of data received from Paypal to the array for updating cart
     *
     * @param \XLite\Model\Payment\Base\Processor $processor  Payment processor
     * @param array                               $paypalData Array of customer data received from Paypal
     *  
     * @return array
     */
    protected function prepareBuyerData($processor, $paypalData)
    {
        $data = $processor->prepareBuyerData($paypalData);

        if (!\XLite\Core\Auth::getInstance()->isLogged()) {
            $data += array(
                'email' => $paypalData['EMAIL'],
                'create_profile' => false,
            );
        }

        return $data;
    }

    /**
     * Get Express Checkout payment method
     * 
     * @return \XLite\Model\Payment\Method
     */
    protected function getExpressCheckoutPaymentMethod()
    {
        $serviceName = \XLite\Core\Request::getInstance()->paypalCredit
            ? \XLite\Module\CDev\Paypal\Main::PP_METHOD_PC
            : \XLite\Module\CDev\Paypal\Main::PP_METHOD_EC;

        return \XLite\Core\Database::getRepo('XLite\Model\Payment\Method')
            ->findOneBy(array('service_name' => $serviceName));
    }
}
