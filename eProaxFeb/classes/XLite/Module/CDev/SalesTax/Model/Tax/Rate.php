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

namespace XLite\Module\CDev\SalesTax\Model\Tax;

/**
 * Rate
 *
 * @Entity
 * @Table (name="sales_tax_rates")
 */
class Rate extends \XLite\Model\AEntity
{
    /**
     * Rate type codes
     */
    const TYPE_ABSOLUTE = 'a';
    const TYPE_PERCENT  = 'p';

    /**
     * Taxable bases
     */
    const TAXBASE_SUBTOTAL                     = 'ST';
    const TAXBASE_DISCOUNTED_SUBTOTAL          = 'DST';
    const TAXBASE_SUBTOTAL_SHIPPING            = 'ST+SH';
    const TAXBASE_DISCOUNTED_SUBTOTAL_SHIPPING = 'DST+SH';
    const TAXBASE_SHIPPING                     = 'SH';
    const TAXBASE_PERSONAL                     = 'P';

    /**
     * Rate unique ID
     *
     * @var integer
     *
     * @Id
     * @GeneratedValue (strategy="AUTO")
     * @Column         (type="integer", options={ "unsigned": true })
     */
    protected $id;

    /**
     * Value
     *
     * @var float
     *
     * @Column (type="decimal", precision=14, scale=4)
     */
    protected $value = 0.0000;

    /**
     * Type
     *
     * @var string
     *
     * @Column (type="string", options={ "fixed": true }, length=1)
     */
    protected $type = self::TYPE_PERCENT;

    /**
     * Position
     *
     * @var integer
     *
     * @Column (type="integer")
     */
    protected $position = 0;

    /**
     * Tax (relation)
     *
     * @var \XLite\Module\CDev\SalesTax\Model\Tax
     *
     * @ManyToOne  (targetEntity="XLite\Module\CDev\SalesTax\Model\Tax", inversedBy="rates")
     * @JoinColumn (name="tax_id", referencedColumnName="id", onDelete="CASCADE")
     */
    protected $tax;

    /**
     * Zone (relation)
     *
     * @var \XLite\Model\Zone
     *
     * @ManyToOne  (targetEntity="XLite\Model\Zone")
     * @JoinColumn (name="zone_id", referencedColumnName="zone_id", onDelete="CASCADE")
     */
    protected $zone;

    /**
     * Tax class (relation)
     *
     * @var \XLite\Model\TaxClass
     *
     * @ManyToOne  (targetEntity="XLite\Model\TaxClass")
     * @JoinColumn (name="tax_class_id", referencedColumnName="id", onDelete="CASCADE")
     */
    protected $taxClass;

    /**
     * Membership (relation)
     *
     * @var \XLite\Model\Membership
     *
     * @ManyToOne  (targetEntity="XLite\Model\Membership")
     * @JoinColumn (name="membership_id", referencedColumnName="membership_id", onDelete="CASCADE")
     */
    protected $membership;

    /**
     * For product without tax class
     *
     * @var boolean
     *
     * @Column (type="boolean")
     */
    protected $noTaxClass = false;

    /**
     * Taxable base
     *
     * @var string
     *
     * @Column (type="string", length=16)
     */
    protected $taxableBase = self::TAXBASE_SUBTOTAL;

    /**
     * Check if rate is applied by specified zones and membership
     *
     * @param array                   $zones      Zone id list
     * @param \XLite\Model\Membership $membership Membership OPTIONAL
     * @param \XLite\Model\TaxClass   $taxClass   Tax class OPTIONAL
     *
     * @return boolean
     */
    public function isApplied(
        array $zones,
        \XLite\Model\Membership $membership = null,
        \XLite\Model\TaxClass $taxClass = null
    ) {

        $result = $this->getZone() && in_array($this->getZone()->getZoneId(), $zones);

        if (
            $result
            && !\XLite\Core\Config::getInstance()->CDev->SalesTax->ignore_memberships
            && $this->getMembership()
        ) {
            $result = $membership && $this->getMembership()->getMembershipId() == $membership->getMembershipId();
        }

        return $result;
    }

    /**
     * Check if rate is applied to object
     *
     * @param mixed $object
     *
     * @return boolean
     */
    public function isAppliedToObject($object)
    {
        return (
            $this->getNoTaxClass()
            && !$object->getTaxClass()
        )
        || (
            !$this->getNoTaxClass()
            && (
                !$this->getTaxClass()
                || (
                    $object->getTaxClass()
                    && $object->getTaxClass()->getId() == $this->getTaxClass()->getId()
                )
            )
        );
    }

    // {{{ Calculation

    /**
     * Calculate
     *
     * @param array $items Items
     *
     * @return float
     */
    public function calculate(array $items)
    {
        $cost = 0;

        if ($this->getBasis($items) && $this->getQuantity($items)) {
            $cost = $this->getType() == static::TYPE_PERCENT
                ? $this->calculatePercent($items)
                : $this->calculateAbsolute($items);
        }

        return $cost;
    }

    /**
     * Calculate shipping tax cost
     *
     * @param float $shippingCost Shipping cost
     *
     * @return float
     */
    public function calculateShippingTax($shippingCost)
    {
        $cost = 0;

        if ($shippingCost) {
            $cost = $this->getType() == static::TYPE_PERCENT
                ? $shippingCost * $this->getValue() / 100
                : $this->getValue();
        }

        return $cost;
    }

    /**
     * Get list of allowed taxable base types
     *
     * @return array
     */
    protected static function getAllowedTaxableBaseTypes()
    {
        return array(
            static::TAXBASE_SUBTOTAL,
            static::TAXBASE_DISCOUNTED_SUBTOTAL,
            static::TAXBASE_SUBTOTAL_SHIPPING,
            static::TAXBASE_DISCOUNTED_SUBTOTAL_SHIPPING,
            static::TAXBASE_SHIPPING,
        );
    }

    /**
     * Get taxable base type
     *
     * @return string
     */
    public function getTaxableBaseType()
    {
        $result = \XLite\Core\Config::getInstance()->CDev->SalesTax->taxableBase;

        if (!in_array($result, static::getAllowedTaxableBaseTypes())) {
            $result = $this->getTaxableBase();
        }

        return $result;
    }

    /**
     * Get basis
     *
     * @param array $items Items
     *
     * @return float
     */
    protected function getBasis(array $items)
    {
        $basis = 0;

        foreach ($items as $item) {
            $basis += $this->getItemBasis($item);
        }

        return $basis;
    }

    /**
     * Get item basis
     *
     * @param \XLite\Model\OrderItem $item Order item
     *
     * @return float
     */
    protected function getItemBasis($item)
    {
        $basis = 0;

        $formulaParts = explode('+', $this->getTaxableBaseType());

        foreach ($formulaParts as $part) {
            switch ($part) {
                case 'ST':
                    $basis += $item->getSubtotal();
                    break;

                case 'DST':
                    $basis += $item->getDiscountedSubtotal();
                    break;

                case 'SH':
                    $basis += $item->getShippingCost();
                    break;
            }
        }

        return $basis;
    }

    /**
     * Get quantity
     *
     * @param array $items Items
     *
     * @return integer
     */
    protected function getQuantity(array $items)
    {
        $quantity = 0;

        foreach ($items as $item) {
            $quantity += $item->getAmount();
        }

        return $quantity;
    }

    /**
     * calculateExcludePercent
     *
     * @param array $items ____param_comment____
     *
     * @return array
     */
    protected function calculatePercent(array $items)
    {
        return $this->getBasis($items) * $this->getValue() / 100;
    }

    /**
     * Calculate tax as percent
     *
     * @param array $items Items
     *
     * @return array
     */
    protected function calculateAbsolute(array $items)
    {
        return $this->getValue() * $this->getQuantity($items);
    }

    // }}}
}
