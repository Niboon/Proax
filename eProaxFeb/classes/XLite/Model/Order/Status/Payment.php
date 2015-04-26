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
 * Payment status
 *
 * @Entity (repositoryClass="\XLite\Model\Repo\Order\Status\Payment")
 * @Table  (name="order_payment_statuses",
 *      indexes={
 *          @Index (name="code", columns={"code"})
 *      }
 * )
 */
class Payment extends \XLite\Model\Order\Status\AStatus
{
    /**
     * Statuses
     */
    const STATUS_AUTHORIZED     = 'A';
    const STATUS_PART_PAID      = 'PP';
    const STATUS_PAID           = 'P';
    const STATUS_DECLINED       = 'D';
    const STATUS_CANCELED       = 'C';
    const STATUS_QUEUED         = 'Q';
    const STATUS_REFUNDED       = 'R';

    /**
     * List of change status handlers;
     * top index - old status, second index - new one
     * (<old_status> ----> <new_status>: $statusHandlers[$old][$new])
     *
     * @var array
     */
    protected static $statusHandlers = array(
        self::STATUS_QUEUED => array(
            self::STATUS_AUTHORIZED => array('authorize'),
            self::STATUS_PAID       => array('process'),
            self::STATUS_DECLINED   => array('decline', 'uncheckout', 'fail'),
            self::STATUS_CANCELED   => array('decline', 'uncheckout', 'cancel'),
        ),

        self::STATUS_AUTHORIZED => array(
            self::STATUS_PAID       => array('process'),
            self::STATUS_DECLINED   => array('decline', 'uncheckout', 'fail'),
            self::STATUS_CANCELED   => array('decline', 'uncheckout', 'cancel'),
        ),

        self::STATUS_PART_PAID => array(
            self::STATUS_PAID       => array('process'),
            self::STATUS_DECLINED   => array('decline', 'uncheckout', 'fail'),
            self::STATUS_CANCELED   => array('decline', 'uncheckout', 'fail'),
        ),

        self::STATUS_PAID => array(
            self::STATUS_DECLINED   => array('decline', 'uncheckout', 'fail'),
            self::STATUS_CANCELED   => array('decline', 'uncheckout', 'cancel'),
        ),

        self::STATUS_DECLINED => array(
            self::STATUS_AUTHORIZED => array('checkout', 'queue', 'authorize'),
            self::STATUS_PART_PAID  => array('checkout', 'queue'),
            self::STATUS_PAID       => array('checkout', 'queue', 'process'),
            self::STATUS_QUEUED     => array('checkout', 'queue'),
        ),

        self::STATUS_CANCELED => array(
            self::STATUS_AUTHORIZED => array('checkout', 'queue', 'authorize'),
            self::STATUS_PART_PAID  => array('checkout', 'queue'),
            self::STATUS_PAID       => array('checkout', 'queue', 'process'),
            self::STATUS_QUEUED     => array('checkout', 'queue'),
        ),
    );

    /**
     * Disallowed to set manually statuses
     *
     * @var array
     */
    protected static $disallowedToSetManuallyStatuses = array(
        self::STATUS_AUTHORIZED,
    );

    /**
     * Not compatible with Shipping status
     *
     * @var array
     */
    protected static $notCompatibleWithShippingStatus  = array(
        self::STATUS_DECLINED,
        self::STATUS_CANCELED,
    );

    /**
     * Get open order statuses
     *
     * @return array
     */
    public static function getOpenStatuses()
    {
        return array(
            static::STATUS_AUTHORIZED,
            static::STATUS_PART_PAID,
            static::STATUS_PAID,
            static::STATUS_QUEUED,
            static::STATUS_REFUNDED,
        );
    }

    /**
     * Get paid statuses
     *
     * @return array
     */
    public static function getPaidStatuses()
    {
        return array(
            static::STATUS_AUTHORIZED,
            static::STATUS_PART_PAID,
            static::STATUS_PAID,
        );
    }

    /**
     * Payment status is compatible with shipping status
     *
     * @return boolean
     */
    public function isCompatibleWithShippingStatus()
    {
        return !in_array(
            $this->getCode(),
            static::$notCompatibleWithShippingStatus
        );
    }

    /**
     * Status is allowed to set manually
     *
     * @return boolean
     */
    public function isAllowedToSetManually()
    {
        return !in_array(
            $this->getCode(),
            static::$disallowedToSetManuallyStatuses
        );
    }
}
