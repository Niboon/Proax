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

namespace XLite\Module\CDev\Paypal\Core;

/**
 * PaypalAPI
 */
class PaypalAPI extends \XLite\Module\CDev\Paypal\Core\AAPI
{
    /**
     * Merchant API version
     * https://developer.paypal.com/webapps/developer/docs/classic/release-notes/#MerchantAPI
     */
    const API_VERSION = 115;

    // {{{ Common

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->method = \XLite\Module\CDev\Paypal\Main::getPaymentMethod(
            \XLite\Module\CDev\Paypal\Main::PP_METHOD_EC
        );
    }

    // }}}

    // {{{ Configuration

    /**
     * Check - payment method is configured or not
     *
     * @return boolean
     */
    public function isConfigured()
    {
        return 'email' == $this->getSetting('api_type')
            ? $this->isConfiguredEmailSolution()
            : $this->isConfiguredApiSolution();
    }

    /**
     * Check configured email solution
     * todo: check email format
     *
     * @return boolean
     */
    public function isConfiguredEmailSolution()
    {
        return (bool) $this->getSetting('email');
    }

    /**
     * Check configured api solution
     * todo: check certificate file availability
     *
     * @return boolean
     */
    public function isConfiguredApiSolution()
    {
        return $this->getSetting('api_username')
            && $this->getSetting('api_password')
            && ($this->getSetting('signature') || $this->getSetting('certificate'));
    }

    // }}}

    // {{{ Helpers

    /**
     * Get payment action
     * Auth available only for API Credentials
     *
     * @return string
     */
    public function getPaymentAction()
    {
        return ($this->isConfiguredApiSolution() && 'A' === $this->getSetting('transaction_type'))
            ? 'Authorization'
            : 'Sale';
    }

    /**
     * Returns order items
     *
     * @param \XLite\Model\Order $order Order
     *
     * @return array
     */
    protected function getItems($order)
    {
        $result = array();

        $itemsSubtotal  = 0;

        if ($order->countItems()) {
            $index = 0;

            /** @var \XLite\Model\Currency $currency */
            $currency = $order->getCurrency();

            foreach ($order->getItems() as $item) {
                $amt = $currency->roundValue($item->getItemNetPrice());
                $result['L_PAYMENTREQUEST_0_AMT' . $index] = $amt;

                /** @var \XLite\Model\Product $product */
                $product = $item->getProduct();
                $result['L_PAYMENTREQUEST_0_NAME' . $index] = $product->getName();

                if ($product->getSku()) {
                    $result['L_PAYMENTREQUEST_0_NUMBER' . $index] = $product->getSku();
                }

                $qty = $item->getAmount();
                $result['L_PAYMENTREQUEST_0_QTY' . $index] = $qty;
                $itemsSubtotal += $amt * $qty;
                $index += 1;
            }

            // Prepare data about discount

            $discount = $currency->roundValue(
                $order->getSurchargeSumByType(\XLite\Model\Base\Surcharge::TYPE_DISCOUNT)
            );

            if (0 != $discount) {
                $result['L_PAYMENTREQUEST_0_AMT' . $index]  = $discount;
                $result['L_PAYMENTREQUEST_0_NAME' . $index] = 'Discount';
                $result['L_PAYMENTREQUEST_0_QTI' . $index]  = 1;
                $itemsSubtotal += $discount;
            }

            $result += array('PAYMENTREQUEST_0_ITEMAMT' => $itemsSubtotal);

            // Prepare data about summary tax cost

            $taxCost = $currency->roundValue(
                $order->getSurchargeSumByType(\XLite\Model\Base\Surcharge::TYPE_TAX)
            );

            if (0 < $taxCost) {
                $result['L_PAYMENTREQUEST_0_TAXAMT' . $index] = $taxCost;
                $result['PAYMENTREQUEST_0_TAXAMT'] = $taxCost;
            }
        }

        return $result;
    }

    // }}}

    // {{{ GetPalDetails

    /**
     * Do GetPalDetails
     *
     * @return mixed
     * @see    https://developer.paypal.com/docs/classic/api/merchant/GetPalDetails_API_Operation_NVP/
     */
    public function doGetPalDetails()
    {
        return $this->doRequest('GetPalDetails');
    }

    /**
     * Returns merchant id
     *
     * @return string
     */
    public function getMerchantID()
    {
        $palDetails = $this->doGetPalDetails();

        return is_array($palDetails) && isset($palDetails['PAL'])
            ? $palDetails['PAL']
            : '';
    }

    // }}}

    // {{{ SetExpressCheckout

    /**
     * Convert order to array for SetExpressCheckout
     *
     * @param \XLite\Model\Order $order Order
     *
     * @return array
     * @see    https://developer.paypal.com/docs/classic/api/merchant/SetExpressCheckout_API_Operation_NVP/
     */
    public function convertSetExpressCheckoutParams($order)
    {
        /** @var \XLite\Model\Currency $currency */
        $currency = $order->getCurrency();

        $orderTotal = $currency->roundValue($order->getTotal());

        $shippingCost = $this->getShippingCost($order);
        $orderNumber = $this->getSetting('prefix') . $order->getOrderNumber();

        /** @var \XLite\Module\CDev\Paypal\Model\Payment\Processor\ExpressCheckoutMerchantAPI $processor */
        $processor = $this->getProcessor();

        $params = array(
            'RETURNURL'                      => $processor->getPaymentReturnUrl(),
            'CANCELURL'                      => $processor->getPaymentCancelUrl(),
            'NOSHIPPING'                     => null === $shippingCost ? '1' : '0',
            'ALLOWNOTE'                      => 1,
            'PAYMENTREQUEST_0_INVNUM'        => $orderNumber,
            'PAYMENTREQUEST_0_CUSTOM'        => $orderNumber,
            'PAYMENTREQUEST_0_AMT'           => $orderTotal,
            'PAYMENTREQUEST_0_PAYMENTACTION' => $this->getPaymentAction(),
            'PAYMENTREQUEST_0_CURRENCYCODE'  => $currency->getCode(),
            'PAYMENTREQUEST_0_HANDLINGAMT'   => 0,
            'PAYMENTREQUEST_0_INSURANCEAMT'  => 0,
            'PAYMENTREQUEST_0_SHIPPINGAMT'   => (float) $shippingCost,
        );

        if (\XLite\Core\Config::getInstance()->Security->customer_security) {
            $postData['HDRIMG'] = urlencode(\XLite\Module\CDev\Paypal\Main::getLogo());
        }

        $items = $this->getItems($order);

        // To avoid total mismatch clear tax and shipping cost
        $taxAmt = isset($items['PAYMENTREQUEST_0_TAXAMT']) ? $items['PAYMENTREQUEST_0_TAXAMT'] : 0;
        if (abs($orderTotal - $items['PAYMENTREQUEST_0_ITEMAMT'] - $taxAmt - $shippingCost) <= 0.0000000001) {
            $params += $items;

        } else {
            $itemsAmt = $orderTotal - (float) $shippingCost;
            $params['PAYMENTREQUEST_0_ITEMAMT'] = $itemsAmt;
        }

        $type = \XLite\Core\Session::getInstance()->ec_type;

        /** @var \XLite\Model\Profile $profile */
        $profile = $order->getProfile();

        if (\XLite\Module\CDev\Paypal\Model\Payment\Processor\ExpressCheckout::EC_TYPE_SHORTCUT == $type) {
            $params['REQCONFIRMSHIPPING'] = 0;
        }

        if ($profile && $profile->getLogin()) {
            $params += array(
                'EMAIL' => $profile->getLogin(),
            );
        }

        if ($profile && $profile->getBillingAddress()) {
            $params += array(
                'PHONENUM' => $profile->getBillingAddress()->getPhone(),
            );
        }

        if (null !== $shippingCost && $profile && $profile->getShippingAddress()) {
            /** @var \XLite\Model\Address $address */
            $address = $profile->getShippingAddress();

            $params += array(
                'ADDROVERRIDE'  => 1,
                'PAYMENTREQUEST_0_SHIPTONAME'    => trim($address->getFirstname() . ' ' . $address->getLastname()),
                'PAYMENTREQUEST_0_SHIPTOSTREET'  => $address->getStreet(),
                'PAYMENTREQUEST_0_SHIPTOSTREET2' => '',
                'PAYMENTREQUEST_0_SHIPTOCITY'    => $address->getCity(),
                'PAYMENTREQUEST_0_SHIPTOSTATE'   => $address->getState()->getCode(),
                'PAYMENTREQUEST_0_SHIPTOZIP'     => $address->getZipcode(),
                'PAYMENTREQUEST_0_SHIPTOCOUNTRY' => $address->getCountry()->getCode(),
            );
        }

        if (\XLite\Core\Auth::getInstance()->isLogged()) {
            $profile = \XLite\Core\Auth::getInstance()->getProfile();

            if (
                $profile->isSocialProfile()
                && 'PayPal' == $profile->getSocialLoginProvider()
                && \XLite\Core\Session::getInstance()->paypalAccessToken
            ) {

                $accessToken = \XLite\Core\Session::getInstance()->paypalAccessToken;

                if (LC_START_TIME < $accessToken['expirationTime']) {
                    $params['IDENTITYACCESSTOKEN'] = $accessToken['access_token'];
                }
            }
        }

        return $params;
    }

    // }}}

    // {{{ GetExpressCheckoutDetails

    /**
     * Convert order to array for GetExpressCheckoutDetails
     *
     * @param string $token Token
     *
     * @return array
     * @see    https://developer.paypal.com/docs/classic/api/merchant/GetExpressCheckoutDetails_API_Operation_NVP/
     */
    public function convertGetExpressCheckoutDetailsParams($token)
    {
        return array(
            'TOKEN' => $token
        );
    }

    // }}}

    // {{{ DoExpressCheckoutPayment

    /**
     * Convert order to array for DoExpressCheckoutPayment
     *
     * @param \XLite\Model\Payment\Transaction $transaction Transaction
     * @param string                           $token       Token
     * @param string                           $payerId     Payer id
     *
     * @return array
     * @see    https://developer.paypal.com/docs/classic/api/merchant/DoExpressCheckoutPayment_API_Operation_NVP/
     */
    public function convertDoExpressCheckoutPaymentParams($transaction, $token, $payerId)
    {
        /** @var \XLite\Model\Order $order */
        $order = $transaction->getOrder();

        /** @var \XLite\Model\Currency $currency */
        $currency = $order->getCurrency();

        $orderTotal = $currency->roundValue($transaction->getValue());

        $shippingCost = $this->getShippingCost($order);
        $orderNumber = $this->getSetting('prefix') . $order->getOrderNumber();

        /** @var \XLite\Module\CDev\Paypal\Model\Payment\Processor\ExpressCheckoutMerchantAPI $processor */
        $processor = $this->getProcessor();

        $params = array(
            'TOKEN'                          => $token,
            'PAYERID'                        => $payerId,
            'PAYMENTREQUEST_0_INVNUM'        => $orderNumber,
            'PAYMENTREQUEST_0_CUSTOM'        => $orderNumber,
            'PAYMENTREQUEST_0_AMT'           => $orderTotal,
            'PAYMENTREQUEST_0_PAYMENTACTION' => $this->getPaymentAction(),
            'PAYMENTREQUEST_0_CURRENCYCODE'  => $currency->getCode(),
            'PAYMENTREQUEST_0_HANDLINGAMT'   => 0,
            'PAYMENTREQUEST_0_INSURANCEAMT'  => 0,
            'PAYMENTREQUEST_0_SHIPPINGAMT'   => (float) $shippingCost,
            'PAYMENTREQUEST_0_NOTIFYURL'     => $processor->getPaymentCallbackUrl(),
        );

        $items = $this->getItems($order);

        // To avoid total mismatch clear tax and shipping cost
        $taxAmt = isset($items['PAYMENTREQUEST_0_TAXAMT']) ? $items['PAYMENTREQUEST_0_TAXAMT'] : 0;
        if (abs($orderTotal - $items['PAYMENTREQUEST_0_ITEMAMT'] - $taxAmt - $shippingCost) > 0.0000000001) {

            $correction = $orderTotal - $items['PAYMENTREQUEST_0_ITEMAMT'] - $taxAmt - $shippingCost;
            $correction = round($correction, 2);

            $index = $order->countItems() + 1;
            $items['L_PAYMENTREQUEST_0_AMT' . $index]  = $correction;
            $items['L_PAYMENTREQUEST_0_NAME' . $index] = 'Correction';
            $items['L_PAYMENTREQUEST_0_QTI' . $index]  = 1;

            $items['PAYMENTREQUEST_0_ITEMAMT'] += $correction;
        }

        $params += $items;

        return $params;
    }

    // }}}

    // {{{ DoVoid

    /**
     * Convert order to array for DoVoid
     *
     * @param string $authorizationId Authorization id
     *
     * @return array
     * @see    https://developer.paypal.com/docs/classic/api/merchant/DoVoid_API_Operation_NVP/
     */
    public function convertDoVoidParams($authorizationId)
    {
        return array(
            'AUTHORIZATIONID' => $authorizationId,
        );
    }

    // }}}

    // {{{ DoCapture

    /**
     * Convert order to array for DoCapture
     *
     * @param \XLite\Model\Payment\BackendTransaction $transaction     Transaction
     * @param string                                  $authorizationId Authorization id
     *
     * @return array
     * @see    https://developer.paypal.com/docs/classic/api/merchant/DoVoid_API_Operation_NVP/
     */
    public function convertDoCaptureParams($transaction, $authorizationId)
    {
        /** @var \XLite\Model\Order $order */
        $order = $transaction->getPaymentTransaction()->getOrder();

        /** @var \XLite\Model\Currency $currency */
        $currency = $order->getCurrency();

        $amount = $currency->roundValue($transaction->getValue());

        return array(
            'AUTHORIZATIONID' => $authorizationId,
            'AMT'             => $amount,
            'COMPLETETYPE'    => 'Complete',
        );
    }

    // }}}

    // {{{ RefundTransaction

    /**
     * Convert order to array for RefundTransaction
     *
     * @param \XLite\Model\Payment\BackendTransaction $transaction   Transaction
     * @param string                                  $transactionId Transaction id
     *
     * @return array
     * @see    https://developer.paypal.com/docs/classic/api/merchant/DoVoid_API_Operation_NVP/
     */
    public function convertRefundTransactionParams($transaction, $transactionId)
    {
        /** @var \XLite\Model\Order $order */
        $order = $transaction->getPaymentTransaction()->getOrder();

        /** @var \XLite\Model\Currency $currency */
        $currency = $order->getCurrency();

        $amount = $currency->roundValue($transaction->getValue());

        return array(
            'TRANSACTIONID' => $transactionId,
            'AMT'           => $amount,
        );
    }

    // }}}

    // {{{ Backend request

    /**
     * Prepare request
     *
     * @param \XLite\Core\HTTP\Request $request Request
     * @param string                   $type    Request type
     * @param array                    $params  Request params
     *
     * @return \XLite\Core\HTTP\Request
     */
    protected function prepareRequest($request, $type, $params)
    {
        if (
            'api' === $this->getSetting('api_type')
            && 'certificate' === $this->getSetting('auth_method')
        ) {
            $request->setAdditionalOption(\CURLOPT_SSLCERT, $this->getSetting('certificate'));
        }

        return parent::prepareRequest($request, $type, $params);
    }

    /**
     * Prepare request params
     *
     * @param string $type   Request type
     * @param array  $params Request params
     *
     * @return string
     */
    protected function prepareParams($type, $params)
    {
        $params['METHOD'] = $type;

        return parent::prepareParams($type, $params);
    }

    /**
     * Returns common request params required for all requests
     *
     * @return array
     */
    protected function getCommonParams()
    {
        $params = array(
            'VERSION' => static::API_VERSION,
        );

        if ('email' === $this->getSetting('api_type')) {
            $params['SUBJECT'] = $this->getSetting('email');

        } else {
            $params['USER'] = $this->getSetting('api_username');
            $params['PWD'] = $this->getSetting('api_password');

            if ('signature' === $this->getSetting('auth_method')) {
                $params['SIGNATURE'] = $this->getSetting('signature');
            }
        }

        return $params;
    }

    /**
     * Prepare url
     *
     * @param string $url    Url
     * @param string $type   Request type
     * @param array  $params Request params
     *
     * @return string
     */
    protected function prepareUrl($url, $type, $params)
    {
        $url = $this->isTestMode()
            ? 'https://api-3t.sandbox.paypal.com/nvp'
            : 'https://api-3t.paypal.com/nvp';

        return parent::prepareUrl($url, $type, $params);
    }

    // }}}
}
