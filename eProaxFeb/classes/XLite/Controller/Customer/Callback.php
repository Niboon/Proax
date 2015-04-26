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
class Callback extends \XLite\Controller\Customer\ACheckoutReturn
{
    /**
     * Payment transaction cache
     *
     * @var \XLite\Model\Payment\Transaction
     */
    protected $transaction;

    /**
     * Hard-coded value to prevent the doAction{action}() calls if the request goes with the "action" parameter
     *
     * @return string
     */
    public function getAction()
    {
        return 'callback';
    }

    /**
     * Define the detection method to check the ownership of the transaction
     *
     * @return string
     */
    protected function getDetectionMethodName()
    {
        return 'getCallbackOwnerTransaction';
    }

    /**
     * Process callback
     *
     * @return void
     */
    protected function doActionCallback()
    {
        $transaction = $this->detectTransaction();

        if ($transaction) {
            $this->transaction = $transaction;

            $transaction->getPaymentMethod()->getProcessor()->processCallback($transaction);
            $cart = $transaction->getOrder();

            if ($cart instanceOf \XLite\Model\Cart) {
                $cart->tryClose();
            }
            \XLite\Core\Database::getEM()->flush();

        } else {
            \XLite\Logger::getInstance()->log(
                'Request callback with undefined payment transaction' . PHP_EOL
                . 'Data: ' . var_export(\XLite\Core\Request::getInstance()->getData(), true),
                LOG_ERR
            );
        }

        $this->set('silent', true);
    }

    /**
     * Check - is service controller or not
     *
     * @return boolean
     */
    protected function isServiceController()
    {
        return true;
    }

}
