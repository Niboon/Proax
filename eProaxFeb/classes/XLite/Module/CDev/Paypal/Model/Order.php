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

namespace XLite\Module\CDev\Paypal\Model;

/**
 * Order model
 */
class Order extends \XLite\Model\Order implements \XLite\Base\IDecorator
{
    /**
     * Exclude Express Checkout from the list of available for checkout payment methods
     * if Payflow Link or Paypal Advanced are avavilable
     * 
     * @return array
     */
    public function getPaymentMethods()
    {
        $list = parent::getPaymentMethods();

        $transaction = $this->getFirstOpenPaymentTransaction();

        $paymentMethod = $transaction ? $transaction->getPaymentMethod() : null;

        if (
            !isset($paymentMethod)
            || (
                !$this->isExpressCheckout($paymentMethod)
                && !$this->isPaypalCredit($paymentMethod)
            )
        ) {
    
            $expressCheckoutKey = null;
            $found = false;

            foreach ($list as $k => $method) {
                if ($this->isExpressCheckout($method)) {
                    $expressCheckoutKey = $k;
                }

                if (in_array($method->getServiceName(), array('PayflowLink', 'PaypalAdvanced'))) {
                    $found = true;
                }

                if (isset($expressCheckoutKey) && $found) {

                    break;
                }
            }

            if (isset($expressCheckoutKey) && $found) {
                unset($list[$expressCheckoutKey]);
            }
        }

        $list = $this->sortPaypalMethods($list);

        return $list;
    }

    /**
     * Returns true if specified payment method is ExpressCheckout 
     * 
     * @param \XLite\Model\Payment\Method $method Payment method object
     *  
     * @return boolean
     */
    public function isExpressCheckout($method)
    {
        return 'ExpressCheckout' == $method->getServiceName();
    }

    /**
     * Returns true if specified payment method is ExpressCheckout
     *
     * @param \XLite\Model\Payment\Method $method Payment method object
     *
     * @return boolean
     */
    public function isPaypalCredit($method)
    {
        return 'PaypalCredit' == $method->getServiceName();
    }

    /**
     * Returns the associative array of transaction IDs: PPREF and/or PNREF
     * 
     * @return array
     */
    public function getTransactionIds()
    {
        $result = array();

        foreach ($this->getPaymentTransactions() as $t) {

            if ($this->isPaypalMethod($t->getPaymentMethod())) {

                $isTestMode = $t->getDataCell('test_mode');

                if (isset($isTestMode)) {
                    $result[] = array(
                        'url'   => '',
                        'name'  => 'Test mode',
                        'value' => 'yes',
                    );
                }

                $ppref = $t->getDataCell('PPREF');
                if (isset($ppref)) {
                    $result[] = array(
                        'url'   => $this->getTransactionIdURL($t, $ppref->getValue()),
                        'name'  => 'Unique PayPal transaction ID (PPREF)',
                        'value' => $ppref->getValue(),
                    );
                }

                $pnref = $t->getDataCell('PNREF');
                if (isset($pnref)) {
                    $result[] = array(
                        'url'   => '',
                        'name'  => 'Unique Payflow transaction ID (PNREF)',
                        'value' => $pnref->getValue(),
                    );
                }
            }
        }

        return $result;
    }

    /**
     * Place paypalCredit after paypalExpress
     *
     * @param array $paymentMethods Payment methods
     *
     * @return array
     */
    protected function sortPaypalMethods($paymentMethods)
    {
        $paypalCreditMethod = null;

        foreach ($paymentMethods as $key => $method) {
            if ($this->isPaypalCredit($method)) {
                $paypalCreditMethod = $method;
                unset($paymentMethods[$key]);
            }
        }


        if ($paypalCreditMethod) {
            $list = array();
            foreach ($paymentMethods as $method) {
                $list[] = $method;

                if ($this->isExpressCheckout($method)) {
                    $list[] = $paypalCreditMethod;
                }
            }

            $paymentMethods = $list;
        }

        return $paymentMethods;
    }



    /**
     * Get specific transaction URL on PayPal side
     * 
     * @param \XLite\Model\Payment\Transaction $transaction Payment transaction object
     * @param string                           $id          Transaction ID (PPREF)
     *  
     * @return string
     */
    protected function getTransactionIdURL($transaction, $id)
    {
        $isTestMode = $transaction->getDataCell('test_mode');

        return isset($isTestMode)
            ? 'https://www.sandbox.paypal.com/cgi-bin/webscr?cmd=_view-a-trans&id=' . $id
            : 'https://www.paypal.com/cgi-bin/webscr?cmd=_view-a-trans&id=' . $id;
    }

    /**
     * Return true if current payment method is PayPal
     * 
     * @param \XLite\Model\Payment\Method $method Payment method object
     *  
     * @return boolean
     */
    protected function isPaypalMethod($method)
    {
        return isset($method)
            && in_array(
                $method->getServiceName(),
                array(
                    \XLite\Module\CDev\Paypal\Main::PP_METHOD_PPA,
                    \XLite\Module\CDev\Paypal\Main::PP_METHOD_PFL,
                    \XLite\Module\CDev\Paypal\Main::PP_METHOD_EC,
                    \XLite\Module\CDev\Paypal\Main::PP_METHOD_PPS,
                    \XLite\Module\CDev\Paypal\Main::PP_METHOD_PC,
                )
            );
    }
}
