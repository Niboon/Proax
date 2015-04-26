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

namespace XLite\Module\XC\ProductVariants\Model;

/**
 * Something customer can put into his cart
 *
 */
class OrderItem extends \XLite\Model\OrderItem implements \XLite\Base\IDecorator
{
    /**
     * Product variant
     *
     * @var \XLite\Module\XC\ProductVariants\Model\ProductVariant
     *
     * @ManyToOne  (targetEntity="XLite\Module\XC\ProductVariants\Model\ProductVariant", inversedBy="orderItems", cascade={"merge","detach"})
     * @JoinColumn (name="variant_id", referencedColumnName="id", onDelete="SET NULL")
     */
    protected $variant;

    /**
     * Get item clear price. This value is used as a base item price for calculation of netPrice
     *
     * @return float
     */
    public function getClearPrice()
    {
        return $this->getVariant()
            ? $this->getVariant()->getClearPrice()
            : parent::getClearPrice();
    }

    /**
     * Get item weight
     *
     * @return float
     */
    public function getClearWeight()
    {
        return $this->getVariant()
            ? $this->getVariant()->getClearWeight()
            : parent::getClearWeight();
    }

    /**
     * Check if item is valid
     *
     * @return boolean
     */
    public function isValid()
    {
        $result = parent::isValid();

        if (
            $result
            && (
                $this->getProduct()->mustHaveVariants()
                || $this->getVariant()
            )
        ) {
            $variant = $this->getProduct()->getVariantByAttributeValuesIds($this->getAttributeValuesIds());
            $result = $variant
                && $this->getVariant()
                && $variant->getId() == $this->getVariant()->getId()
                && (
                    !$this->product->getInventory()->getEnabled()
                    || $this->getVariant()->getDefaultAmount()
                    || 0 < $variant->getAmount()
                );
        }

        return $result;
    }

    /**
     * Check if item has a wrong amount
     *
     * @return boolean
     */
    public function hasWrongAmount()
    {
        return $this->getVariant() && !$this->getVariant()->getDefaultAmount()
            ? $this->getVariant()->getAmount() < $this->getAmount()
            : parent::hasWrongAmount();
    }

    /**
     * Renew order item
     *
     * @return boolean
     */
    public function renew()
    {
        $available = parent::renew();

        if ($available && $this->getVariant()) {
            $this->setSKU($this->getVariant()->getDisplaySku());
            $this->setPrice($this->getVariant()->getDisplayPrice());
        }

        return $available;
    }

    /**
     * Increase / decrease product inventory amount
     *
     * @param integer $delta Amount delta
     *
     * @return void
     */
    public function changeAmount($delta)
    {
        if ($this->getVariant() && !$this->getVariant()->getDefaultAmount()) {
            $this->getVariant()->changeAmount($delta);

        } else {
            parent::changeAmount($delta);
        }
    }

    /**
     * Check - item price is controlled by server or not
     *
     * @return boolean
     */
    public function isPriceControlledServer()
    {
        return parent::isPriceControlledServer()
            || ($this->getProduct() && $this->getProduct()->hasVariants());
    }

    /**
     * Check item amount
     *
     * @return boolean
     */
    protected function checkAmount()
    {
        return $this->getVariant() && !$this->getVariant()->getDefaultAmount()
            ? $this->getVariant()->getAvailableAmount() >= 0
            : parent::checkAmount();
    }

    /**
     * Check - can change item's amount or not
     *
     * @return boolean
     */
    public function canChangeAmount()
    {
        $product = $this->getProduct();

        if (
            $product
            && $product->getInventory()->getEnabled()
            && $this->getVariant()
            && !$this->getVariant()->getDefaultAmount()
        ) {
            $result = (0 < $this->getVariant()->getAmount());

        } else {
            $result = parent::canChangeAmount();
        }

        return $result;
    }

    /**
     * Return extended item description
     *
     * @return string
     */
    public function getExtendedDescription()
    {
        $result = '';

        if ($this->getVariant()) {
            $attrs = $variantsAttributes = array();
            foreach ($this->getProduct()->getVariantsAttributes() as $a) {
                $variantsAttributes[$a->getId()] = $a->getId();
            }

            foreach ($this->getAttributeValues() as $v) {
                $av = $v->getAttributeValue();
                if ($av->getAttribute()->isVariable($this->getProduct())) {
                    $attrs[] = $av->getAttribute()->getName() . ': ' . $av->asString();
                }
            }

            $result = '(' . implode(', ', $attrs) . ')';
        }

        return $result ?: parent::getExtendedDescription();
    }

    /**
     * Get available amount for the product
     *
     * @return integer
     */
    public function getProductAvailableAmount()
    {
        return $this->getVariant() && !$this->getVariant()->getDefaultAmount()
            ? $this->getVariant()->getAvailableAmount()
            : parent::getProductAvailableAmount();
    }

    /**
     * Clone order item object
     *
     * @return \XLite\Model\OrderItem
     */
    public function cloneEntity()
    {
        $newItem = parent::cloneEntity();

        if ($this->getVariant()) {
            $newItem->setVariant($this->getVariant());
        }

        return $newItem;
    }
}
