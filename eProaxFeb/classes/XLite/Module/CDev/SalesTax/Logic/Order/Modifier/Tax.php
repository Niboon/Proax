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

namespace XLite\Module\CDev\SalesTax\Logic\Order\Modifier;

/**
 * Tax  business logic
 */
class Tax extends \XLite\Logic\Order\Modifier\ATax
{

    const MODIFIER_CODE = 'CDEV.STAX';

    /**
     * Taxes (cache)
     *
     * @var array
     */
    protected $taxes;

    /**
     * Zones (cache)
     *
     * @var array
     */
    protected $zones;

    /**
     * Modifier unique code
     *
     * @var string
     */
    protected $code = self::MODIFIER_CODE;

    /**
     * Surcharge identification pattern
     *
     * @var string
     */
    protected $identificationPattern = '/^CDEV\.STAX\.\d+$/Ss';


    /**
     * Check - can apply this modifier or not
     *
     * @return boolean
     */
    public function canApply()
    {
        return parent::canApply()
            && $this->getTaxes();
    }

    // {{{ Widget

    /**
     * Get widget class
     *
     * @return string
     */
    public static function getWidgetClass()
    {
        return '\XLite\Module\CDev\SalesTax\View\Order\Details\Admin\Modifier\Tax';
    }

    // }}}

    /**
     * Get surcharge code
     *
     * @return string
     */
    public static function getSurchargeCode()
    {
        $result = null;

        $taxes = \XLite\Core\Database::getRepo('XLite\Module\CDev\SalesTax\Model\Tax')->findActive();

        if ($taxes) {
            // Get the first tax, ignore the rest
            // TODO: Rework this after multi-tax will be removed
            foreach ($taxes as $tax) {
                $result =  \XLite\Module\CDev\SalesTax\Logic\Order\Modifier\Tax::MODIFIER_CODE . '.' . $tax->getId();
                break;
            }
        }

        return $result;
    }

    // {{{ Calculation

    /**
     * Calculate
     *
     * @return array
     */
    public function calculate()
    {
        $result = array();

        $zones = $this->getZonesList();
        $membership = $this->getMembership();

        foreach ($this->getTaxes() as $tax) {
            $previousItems = array();
            $previousClasses = array();
            $cost = 0;
            $ratesExists = false;

            // Get general tax rates
            $rates = $tax->getFilteredRates($zones, $membership);

            if ($rates) {
                $ratesExists = true;

                $this->distributeShippingCost();

                foreach ($rates as $rate) {
                    $taxClass = $rate->getTaxClass() ?: null;

                    if (!in_array($taxClass, $previousClasses)) {
                        // Get tax cost for products in the cart with specified product class
                        $items = $this->getTaxableItems($rate, $previousItems);

                        if ($items) {
                            foreach ($items as $item) {
                                $previousItems[] = $item->getProduct()->getProductId();
                            }

                            $cost += $rate->calculate($items);
                        }
                        $previousClasses[] = $taxClass;
                    }
                }
            }

            // Get tax rates on shipping cost
            $rates = $tax->getFilteredShippingRates($zones, $membership);

            if ($rates) {
                foreach ($rates as $rate) {
                    $cost += $this->getShippingTaxCost($rate);
                }
            }

            if ($cost) {
                $result[] = $this->addOrderSurcharge(
                    $this->code . '.' . $tax->getId(),
                    doubleval($cost),
                    false,
                    $ratesExists
                );
            }
        }

        return $result;
    }

    /**
     * Distribute shipping cost among an order items
     *
     * @return void
     */
    protected function distributeShippingCost()
    {
        $totalShippingCost = 0;

        $modifier = $this->order->getModifier(\XLite\Model\Base\Surcharge::TYPE_SHIPPING, 'SHIPPING');

        if ($modifier) {
            $shippingRate = $modifier->getSelectedRate();

            if ($shippingRate && $shippingRate->getMethod()) {
                $totalShippingCost = $shippingRate->getTaxableBasis();
                $totalWeight       = $modifier->getModifier()->getWeight();
                $orderSubtotal     = $modifier->getModifier()->getSubtotal();
                $orderItems        = $modifier->getModifier()->getItems();
            }
        }

        if (0 < $totalShippingCost) {
            // Order currency
            $currency = $this->getOrder()->getCurrency();

            // Initialize service variables
            $distributedSum = 0;
            $lastItemKey = null;

            foreach ($orderItems as $key => $item) {
                if ($item->isShippable()) {
                    // Calculate item shipping cost
                    $cost = $totalShippingCost
                        * ($item->getWeight() / $totalWeight + $item->getSubtotal() / $orderSubtotal)
                        / 2;

                    // Set shipping cost for item
                    $orderItems[$key]->setShippingCost($cost);

                    // Update distributed shipping cost value
                    $distributedSum += $cost;

                    // Remember last used item
                    $lastItemKey = $key;
                }
            }

            if ($distributedSum != $totalShippingCost) {
                // Correct last item's shipping cost
                $orderItems[$lastItemKey]->setShippingCost(
                    $orderItems[$lastItemKey]->getShippingCost() + $totalShippingCost - $distributedSum
                );
            }
        }
    }

    /**
     * Get shipping tax cost
     *
     * @param \XLite\Module\CDev\SalesTax\Model\Tax\Rate $rate Rate
     *
     * @return float
     */
    protected function getShippingTaxCost(\XLite\Module\CDev\SalesTax\Model\Tax\Rate $rate)
    {
        $result = 0;

        $modifier = $this->order->getModifier(\XLite\Model\Base\Surcharge::TYPE_SHIPPING, 'SHIPPING');

        if ($modifier && $modifier->getSelectedRate() && $modifier->getSelectedRate()->getMethod()) {
            $shippingRate = $modifier->getSelectedRate();

            if ($rate->isAppliedToObject($shippingRate->getMethod())) {
                $result = $rate->calculateShippingTax($shippingRate->getTaxableBasis());
            }
        }

        return $result;
    }

    /**
     * Get taxes
     *
     * @param boolean $force Force renew taxes list flag OPTIONAL
     *
     * @return array
     */
    protected function getTaxes($force = false)
    {
        if (!isset($this->taxes) || $force) {
            $this->taxes = $this->defineTaxes();
        }

        return $this->taxes;
    }

    /**
     * Define taxes
     *
     * @return array
     */
    protected function defineTaxes()
    {
        return \XLite\Core\Database::getRepo('XLite\Module\CDev\SalesTax\Model\Tax')->findActive();
    }

    /**
     * Get zones list
     *
     * @param boolean $force Force renew zones list flag OPTIONAL
     *
     * @return array
     */
    protected function getZonesList($force = false)
    {
        $address = $this->getAddress();

        $hash = $address ? $this->getAddressHash($address) : 0;

        if (!isset($this->zones[$hash]) || $force) {

            $this->zones[$hash] = $address
                ? \XLite\Core\Database::getRepo('XLite\Model\Zone')->findApplicableZones($address)
                : array();

            foreach ($this->zones[$hash] as $i => $zone) {
                $this->zones[$hash][$i] = $zone->getZoneId();
            }
        }

        return $this->zones[$hash];
    }

    /**
     * Get hash of address fields
     *
     * @param array $address Address
     *
     * @return string
     */
    protected function getAddressHash($address)
    {
        $str = '';

        foreach ($address as $field => $value) {
            $str .= $value;
        }

        return md5($str);
    }

    /**
     * Get membership
     *
     * @return \XLite\Model\Membership
     */
    protected function getMembership()
    {
        return $this->getOrder()->getProfile()
            ? $this->getOrder()->getProfile()->getMembership()
            : null;
    }

    /**
     * Get taxable items
     *
     * @param \XLite\Module\CDev\SalesTax\Model\Tax\Rate $rate          Rate
     * @param array                                      $previousItems Previous selected items OPTIONAL
     *
     * @return array
     */
    protected function getTaxableItems(\XLite\Module\CDev\SalesTax\Model\Tax\Rate $rate, array $previousItems = array())
    {
        $list = array();

        foreach ($this->getOrder()->getItems() as $item) {
            if (
                $item->getProduct()->getTaxable()
                && !in_array($item->getProduct()->getProductId(), $previousItems)
                && $rate->isAppliedToObject($item->getProduct())
            ) {
                $list[] = $item;
            }
        }

        return $list;
    }

    /**
     * Get address for zone calculator
     *
     * @return array
     */
    protected function getAddress()
    {
        $address = null;
        $addressObj = $this->getOrderAddress();
        if ($addressObj) {
            // Profile is exists
            $address = \XLite\Model\Shipping::prepareAddressData($addressObj);
        }

        if (!isset($address)) {
            $address = $this->getDefaultAddress();
        }

        return $address;
    }

    /**
     * Get order-based address
     *
     * @return \XLite\Model\Address
     */
    protected function getOrderAddress()
    {
        $profile = $this->getOrder()->getProfile();
        $result = null;

        if ($profile) {
            $result = 'shipping' == \XLite\Core\Config::getInstance()->CDev->SalesTax->addressType
                ? $profile->getShippingAddress()
                : $profile->getBillingAddress();

            if (!$result) {
                $result = $profile->getShippingAddress();
            }
        }

        return $result;
    }

    // }}}

    // {{{ Surcharge operations

    /**
     * Get surcharge name
     *
     * @param \XLite\Model\Base\Surcharge $surcharge Surcharge
     *
     * @return \XLite\DataSet\Transport\Order\Surcharge
     */
    public function getSurchargeInfo(\XLite\Model\Base\Surcharge $surcharge)
    {
        $info = new \XLite\DataSet\Transport\Order\Surcharge;

        if (0 === strpos($surcharge->getCode(), $this->code . '.')) {
            $id = intval(substr($surcharge->getCode(), strlen($this->code) + 1));
            $tax = \XLite\Core\Database::getRepo('XLite\Module\CDev\SalesTax\Model\Tax')->find($id);
            $info->name = $tax
                ? $tax->getName()
                : \XLite\Core\Translation::lbl('Sales tax');

        } else {
            $info->name = \XLite\Core\Translation::lbl('Sales tax');
        }

        $info->notAvailableReason = \XLite\Core\Translation::lbl('Billing address is not defined');

        return $info;
    }

    // }}}
}
