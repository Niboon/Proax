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

namespace XLite\Model\Order\Status;

/**
 * Shipping status
 *
 * @Entity (repositoryClass="\XLite\Model\Repo\Order\Status\Shipping")
 * @Table  (name="order_shipping_statuses",
 *      indexes={
 *          @Index (name="code", columns={"code"})
 *      }
 * )
 */
class Shipping extends \XLite\Model\Order\Status\AStatus
{
    /**
     * Statuses
     */
    const STATUS_NEW              = 'N';
    const STATUS_PROCESSING       = 'P';
    const STATUS_SHIPPED          = 'S';
    const STATUS_DELIVERED        = 'D';
    const STATUS_WILL_NOT_DELIVER = 'WND';
    const STATUS_RETURNED         = 'R';

    /**
     * List of change status handlers;
     * top index - old status, second index - new one
     * (<old_status> ----> <new_status>: $statusHandlers[$old][$new])
     *
     * @var array
     */
    protected static $statusHandlers = array(
        self::STATUS_NEW => array(
            self::STATUS_SHIPPED => array('ship'),
        ),

        self::STATUS_PROCESSING => array(
            self::STATUS_SHIPPED => array('ship'),
        ),

        self::STATUS_DELIVERED => array(
            self::STATUS_SHIPPED => array('ship'),
        ),

        self::STATUS_WILL_NOT_DELIVER => array(
            self::STATUS_SHIPPED => array('ship'),
        ),

        self::STATUS_RETURNED => array(
            self::STATUS_SHIPPED => array('ship'),
        ),
    );

    /**
     * Status is allowed to set manually
     *
     * @return boolean
     */
    public function isAllowedToSetManually()
    {
        return true;
    }
}
