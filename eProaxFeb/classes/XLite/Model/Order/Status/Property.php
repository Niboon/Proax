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
 * Order status config
 *
 * @Entity (repositoryClass="\XLite\Model\Repo\Order\Status\Property")
 * @Table  (name="order_status_properties",
 *      indexes={
 *          @Index (name="payment_status", columns={"payment_status_id"}),
 *          @Index (name="shipping_status", columns={"shipping_status_id"})
 *      }
 * )
 */
class Property extends \XLite\Model\AEntity
{
    /**
     * ID
     *
     * @var integer
     *
     * @Id
     * @GeneratedValue (strategy="AUTO")
     * @Column         (type="integer", options={ "unsigned": true })
     */
    protected $id;

    /**
     * Payment status
     *
     * @var \XLite\Model\Order\Status\Payment
     *
     * @ManyToOne  (targetEntity="XLite\Model\Order\Status\Payment", cascade={"all"})
     * @JoinColumn (name="payment_status_id", referencedColumnName="id", onDelete="CASCADE")
     */
    protected $paymentStatus;

    /**
     * Shipping status
     *
     * @var \XLite\Model\Order\Status\Shipping
     *
     * @ManyToOne  (targetEntity="XLite\Model\Order\Status\Shipping", cascade={"all"})
     * @JoinColumn (name="shipping_status_id", referencedColumnName="id", onDelete="CASCADE")
     */
    protected $shippingStatus;

    /**
     * Increase (true) or decrease (false) inventory
     *
     * @var boolean 
     *
     * @Column (type="boolean", nullable=true)
     */
    protected $incStock;
}
