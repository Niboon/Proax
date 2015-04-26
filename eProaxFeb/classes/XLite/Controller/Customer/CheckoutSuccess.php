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
 * Checkout success page
 */
class CheckoutSuccess extends \XLite\Controller\Customer\ACustomer
{
    /**
     * Controller parameters
     *
     * @var array
     */
    protected $params = array('target', 'order_id', 'order_number');

    /**
     * Order (cache)
     *
     * @var \XLite\Model\Order
     */
    protected $order;


    /**
     * Get page title
     *
     * @return string
     */
    public function getTitle()
    {
        return static::t('Thank you for your order');
    }

    /**
     * Handles the request.
     * Parses the request variables if necessary. Attempts to call the specified action function
     *
     * @return void
     */
    public function handleRequest()
    {
        \XLite\Core\Session::getInstance()->iframePaymentData = null;

        // security check on return page
        $order = $this->getOrder();
        if (!$order) {
            $this->redirect($this->buildURL());

        } elseif (
            $order->getOrderId() != \XLite\Core\Session::getInstance()->last_order_id
            && $order->getOrderId() != $this->getCart()->getOrderId()
        ) {
            $this->redirect($this->buildURL('cart'));

        } else {
            parent::handleRequest();
        }
    }

    /**
     * Get order
     *
     * @return \XLite\Model\Order
     */
    public function getOrder()
    {
        if (!isset($this->order)) {
            if (\XLite\Core\Request::getInstance()->order_id) {
                $this->order = \XLite\Core\Database::getRepo('XLite\Model\Order')->find(
                    intval(\XLite\Core\Request::getInstance()->order_id)
                );

            } elseif (\XLite\Core\Request::getInstance()->order_number) {
                $this->order = \XLite\Core\Database::getRepo('XLite\Model\Order')->findOneByOrderNumber(
                    \XLite\Core\Request::getInstance()->order_number
                );
            }
        }

        return $this->order;
    }

    /**
     * Common method to determine current location
     *
     * @return string
     */
    protected function getLocation()
    {
        return static::t('Checkout');
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
