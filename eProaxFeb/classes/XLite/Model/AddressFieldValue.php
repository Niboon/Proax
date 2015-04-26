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

namespace XLite\Model;

/**
 * Address field value (additional fields) model
 *
 * @Entity (repositoryClass="\XLite\Model\Repo\AddressFieldValue")
 * @Table  (name="address_field_value")
 */
class AddressFieldValue extends \XLite\Model\AEntity
{
    /**
     * Unique id
     *
     * @var integer
     *
     * @Id
     * @GeneratedValue (strategy="AUTO")
     * @Column         (type="integer", nullable=false)
     */
    protected $id;

    /**
     * Additional field value
     *
     * @var string
     * @Column(type="string", length=255, nullable=true)
     */
    protected $value = '';

    /**
     * Address field model relation
     *
     * @var \XLite\Model\AddressField
     *
     * @ManyToOne (targetEntity="XLite\Model\AddressField", cascade={"persist","merge","detach"})
     * @JoinColumn (name="address_field_id", referencedColumnName="id", onDelete="SET NULL")
     */
    protected $addressField;

    /**
     * Address model relation
     *
     * @var \XLite\Model\Address
     *
     * @ManyToOne (targetEntity="XLite\Model\Address", inversedBy="addressFields", cascade={"persist","merge","detach"})
     * @JoinColumn (name="address_id", referencedColumnName="address_id", onDelete="CASCADE")
     */
    protected $address;

}
