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

namespace XLite\Controller\Customer\Base;

/**
 * Order
 */
abstract class Order extends \XLite\Controller\Customer\ACustomer
{
    /**
     * Controller parameters
     *
     * @var array
     */
    protected $params = array('profile_id', 'order_number');


    /**
     * Order (cache)
     *
     * @var \XLite\Model\Order
     */
    protected $order;

    /**
     * Return current order ID
     *
     * @return integer
     */
    protected function getOrderId()
    {
        return intval(\XLite\Core\Request::getInstance()->order_id);
    }

    /**
     * Return current order
     *
     * @return \XLite\Model\Order
     */
    public function getOrder()
    {
        if (!isset($this->order)) {
            if ($this->getOrderId()) {
                $this->order = \XLite\Core\Database::getRepo('XLite\Model\Order')->find($this->getOrderId());

            } elseif (\XLite\Core\Request::getInstance()->order_number) {
                $this->order = \XLite\Core\Database::getRepo('XLite\Model\Order')->findOneByOrderNumber(
                    \XLite\Core\Request::getInstance()->order_number
                );
            }
        }

        return $this->order;
    }

    /**
     * Return current order number
     *
     * @return string
     */
    protected function getOrderNumber()
    {
        return $this->getOrder()->getOrderNumber();
    }

    /**
     * Check if currently logged user is an admin
     *
     * @return boolean
     */
    protected function isAdmin()
    {
        return \XLite\Core\Auth::getInstance()->isAdmin();
    }

    /**
     * Check if order corresponds to current user
     *
     * @return boolean
     */
    protected function checkOrderProfile()
    {
        return \XLite\Core\Auth::getInstance()->getProfile()->getProfileId()
            == $this->getOrder()->getOrigProfile()->getProfileId();
    }

    /**
     * Check order access
     *
     * @return boolean
     */
    protected function checkOrderAccess()
    {
        return \XLite\Core\Auth::getInstance()->isLogged() && ($this->isAdmin() || $this->checkOrderProfile());
    }

    /**
     * Check if current page is accessible
     *
     * @return boolean
     */
    protected function checkAccess()
    {
        return parent::checkAccess() && $this->getOrder() && $this->checkOrderAccess();
    }

    /**
     * Add the base part of the location path
     *
     * @return void
     */
    protected function addBaseLocation()
    {
        parent::addBaseLocation();

        $this->addLocationNode(static::t('Search for orders'), $this->buildURL('order_list'));
    }
}
