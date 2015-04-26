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
 * Payment method callback
 */
abstract class ACheckoutReturn extends \XLite\Controller\Customer\ACustomer
{
    /**
     * Define the detection method to check the ownership of the transaction
     *
     * @return string
     */
    abstract protected function getDetectionMethodName();

    /**
     * This controller is always accessible
     * TODO - check if it's really needed; remove if not
     *
     * @return void
     */
    protected function checkStorefrontAccessability()
    {
        return true;
    }

    /**
     * Detect the transaction from the request directly
     *
     * @return \XLite\Model\Payment\Transaction
     */
    protected function detectTransactionFromRequest()
    {
        $txn = null;
        $txnIdName = \XLite\Model\Payment\Base\Online::RETURN_TXN_ID;
        if (isset(\XLite\Core\Request::getInstance()->txn_id_name)) {
            /**
             * some of gateways can't accept return url on run-time and
             * use the one set in merchant account, so we can't pass
             * 'order_id' in run-time, instead pass the order id parameter name
             */
            $txnIdName = \XLite\Core\Request::getInstance()->txn_id_name;
        }

        if (!empty(\XLite\Core\Request::getInstance()->$txnIdName)) {
            $txn = \XLite\Core\Database::getRepo('XLite\Model\Payment\Transaction')
                ->findOneByPublicTxnId(\XLite\Core\Request::getInstance()->$txnIdName);
        }

        return $txn;
    }

    /**
     * Detect the transaction from the inner detection method in the payment module
     * You must define the getCallbackOwnerTransaction method which must return
     * true if the callback transaction is owned by the definite payment method
     *
     * @return \XLite\Model\Payment\Transaction
     */
    protected function detectTransactionFromMethods()
    {
        $txn = null;
        $methods = \XLite\Core\Database::getRepo('XLite\Model\Payment\Method')->findAllActive();
        foreach ($methods as $method) {
            if (method_exists($method->getProcessor(), $this->getDetectionMethodName())) {
                $txn = $method->getProcessor()->{$this->getDetectionMethodName()}();
                if ($txn) {
                    break;
                }
            }
        }
        return $txn;
    }

    /**
     * Detect transaction from request or from the inner payment methods detection
     *
     * @return \XLite\Model\Payment\Transaction
     */
    protected function detectTransaction()
    {
        $txn = $this->detectTransactionFromMethods();
        if (!$txn) {
            $txn = $this->detectTransactionFromRequest();
        }
        return $txn;
    }

}
