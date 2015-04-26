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

namespace XLite\Model\Payment;

/**
 * Transaction data storage
 *
 * @Entity
 * @Table (name="payment_transaction_data",
 *      indexes={
 *          @Index (name="tn", columns={"transaction_id","name"})
 *      }
 * )
 */
class TransactionData extends \XLite\Model\AEntity
{
    /**
     * Access level codes
     */
    const ACCESS_ADMIN    = 'A';
    const ACCESS_CUSTOMER = 'C';


    /**
     * Primary key
     *
     * @var integer
     *
     * @Id
     * @GeneratedValue (strategy="AUTO")
     * @Column         (type="integer")
     */
    protected $data_id;

    /**
     * Record name
     *
     * @var string
     *
     * @Column (type="string", length=128)
     */
    protected $name;

    /**
     * Record public name
     *
     * @var string
     *
     * @Column (type="string", length=255)
     */
    protected $label = '';

    /**
     * Access level
     *
     * @var string
     *
     * @Column (type="string", options={ "fixed": true }, length=1)
     */
    protected $access_level = self::ACCESS_ADMIN;

    /**
     * Value
     *
     * @var string
     *
     * @Column (type="text")
     */
    protected $value;

    /**
     * Transaction
     *
     * @var \XLite\Model\Payment\Transaction
     *
     * @ManyToOne  (targetEntity="XLite\Model\Payment\Transaction", inversedBy="data")
     * @JoinColumn (name="transaction_id", referencedColumnName="transaction_id", onDelete="CASCADE")
     */
    protected $transaction;

    /**
     * Check record availability
     *
     * @return boolean
     */
    public function isAvailable()
    {
        return (\XLite::isAdminZone() && self::ACCESS_ADMIN == $this->getAccessLevel())
            || self::ACCESS_CUSTOMER == $this->getAccessLevel();
    }
}
