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

namespace XLite\Logic\Order\Modifier;

/**
 * Shipping modifier
 */
class Shipping extends \XLite\Logic\Order\Modifier\AShipping
{

    /**
     * Modifier code 
     */
    const MODIFIER_CODE = 'SHIPPING';

    /**
     * Modifier unique code
     *
     * @var string
     */
    protected $code = self::MODIFIER_CODE;

    /**
     * Selected rate (cache)
     *
     * @var \XLite\Model\Shipping\Rate
     */
    protected $selectedRate;

    /**
     * Check - can apply this modifier or not
     *
     * @return boolean
     */
    public function canApply()
    {
        return parent::canApply()
            && $this->isShippable();
    }

    /**
     * Calculate
     *
     * @return \XLite\Model\Order\Surcharge
     */
    public function calculate()
    {
        $surcharge = null;
        $cost = 0;

        if ($this->isShippable()) {

            if (!$this->isCart() || !$this->order->isIgnoreLongCalculations()) {

                $rate = $this->getSelectedRate();

                if (isset($rate)) {
                    $cost = $this->getOrder()->getCurrency()->roundValue($rate->getTotalRate());
                }

            } else {
                $cost = $this->getOrder()->getSurchargesSubtotal(\XLite\Model\Base\Surcharge::TYPE_SHIPPING);
            }

            $surcharge = $this->addOrderSurcharge($this->code, doubleval($cost), false, true);

        } else {

            foreach ($this->order->getSurcharges() as $s) {
                if ($s->getType() == $this->type && $s->getCode() == $this->code) {
                    $this->getOrder()->getSurcharges()->removeElement($s);
                    \XLite\Core\Database::getEM()->remove($s);
                }
            }
        }

        return $surcharge;
    }

    /**
     * Check - shipping rates exists or not
     *
     * @return boolean
     */
    public function isRatesExists()
    {
        return (bool)$this->getRates();
    }

    /**
     * Get shipping rates
     * TODO: add checking if rates should be recalculated else get rates from cache
     *
     * @return array(\XLite\Model\Shipping\Rate)
     */
    public function getRates()
    {
        return $this->isCart()
            ? $this->calculateRates()
            : $this->restoreRates();
    }

    /**
     * Returns true if any of order items are shipped
     *
     * @return boolean
     */
    protected function isShippable()
    {
        $result = false;

        foreach ($this->getItems() as $item) {
            if ($item->isShippable()) {
                $result = true;
                break;
            }
        }

        return $result;
    }

    // {{{ Shipping rates

    /**
     * Calculate shipping rates
     *
     * @return array(\XLite\Model\Shipping\Rate)
     */
    protected function calculateRates()
    {
        $rates = array();

        if ($this->isShippable()) {

            $rates = \XLite\Model\Shipping::getInstance()->getRates($this);

            uasort($rates, array($this, 'compareRates'));
        }

        return $rates;
    }

    /**
     * Restore rates
     *
     * @return array(\XLite\Model\Shipping\Rate)
     */
    protected function restoreRates()
    {
        $rates = array();

        if ($this->order->getShippingId()) {
            $method = \XLite\Core\Database::getRepo('XLite\Model\Shipping\Method')->find($this->order->getShippingId());

            if ($method) {
                $rate = new \XLite\Model\Shipping\Rate();
                $rate->setMethod($method);
                $rate->setBaseRate(0);
                $rate->setMarkupRate($this->order->getSurchargeSumByType($this->type));

                $rates[] = $rate;
            }
        }

        return $rates;
    }

    /**
     * Shipping rates sorting callback
     *
     * @param \XLite\Model\Shipping\Rate $a First shipping rate
     * @param \XLite\Model\Shipping\Rate $b Second shipping rate
     *
     * @return integer
     */
    protected function compareRates(\XLite\Model\Shipping\Rate $a, \XLite\Model\Shipping\Rate $b)
    {
        $result = 0;

        $sa = $a->getMethod();
        $sb = $b->getMethod();

        if (isset($sa) && isset($sb)) {

            if ($sa->getPosition() > $sb->getPosition()) {
                $result = 1;

            } elseif ($sa->getPosition() < $sb->getPosition()) {
                $result = -1;
            }
        }

        return $result;
    }

    // }}}

    // {{{ Current shipping method and rate

    /**
     * Get selected shipping rate
     *
     * @return \XLite\Model\Shipping\Rate
     */
    public function getSelectedRate()
    {
        if (
            !isset($this->selectedRate)
            || $this->selectedRate->getMethodId() != $this->order->getShippingId()
        ) {
            // Get shipping rates
            $rates = $this->getRates();

            $selectedRate = null;

            if (!empty($rates)) {

                if (
                    !$this->order->getShippingId()
                    && $this->order->getProfile()
                    && $this->order->getProfile()->getLastShippingId()
                    && $this->order instanceOf \XLite\Model\Cart
                ) {

                    // Remember last shipping id
                    $this->order->setShippingId($this->order->getProfile()->getLastShippingId());
                }

                if (0 < intval($this->order->getShippingId())) {
                    // Set selected rate from the rates list if shipping_id is already assigned

                    foreach ($rates as $rate) {

                        if ($this->order->getShippingId() == $rate->getMethodId()) {
                            $selectedRate = $rate;
                            break;
                        }
                    }
                }
            }

            $this->setSelectedRate($selectedRate);
        }

        return $this->selectedRate;
    }

    /**
     * Set shipping rate and shipping method id
     *
     * @param \XLite\Model\Shipping\Rate $rate Shipping rate object OPTIONAL
     *
     * @return void
     */
    public function setSelectedRate(\XLite\Model\Shipping\Rate $rate = null)
    {
        $newShippingId = $this->order->getShippingId();

        $this->selectedRate = $rate;
        $newShippingId = $rate ? $rate->getMethodId() : 0;

        if ($this->order->getShippingId() != $newShippingId) {

            $this->order->setShippingId($newShippingId);

            \XLite\Core\Database::getEM()->flush();
        }
    }

    /**
     * Reset selected rate
     *
     * @return void
     */
    public function resetSelectedRate()
    {
        $this->selectedRate = null;
    }

    /**
     * Get shipping method
     *
     * @return \XLite\Model\Shipping\Method
     */
    public function getMethod()
    {
        $result = null;

        $rate = $this->getSelectedRate();

        if (isset($rate)) {
            $result = $rate->getMethod();
        }

        return $result;
    }

    /**
     * Get shipping method name
     *
     * @return string|void
     */
    public function getActualName()
    {
        $name = null;

        if ($this->getMethod()) {
            $name = $this->getMethod()->getName();
        } elseif ($this->order->getShippingMethodName()) {
            $name = $this->order->getShippingMethodName();
        }

        return $name;
    }

    // }}}

    // {{{ Shipping calculation data

    /**
     * Get shipped items
     *
     * @return array
     */
    public function getItems()
    {
        $result = array();

        foreach ($this->order->getItems() as $item) {
            if ($item->isShippable()) {
                $result[] = $item;
            }
        }

        return $result;
    }

    /**
     * Get order weight
     *
     * @return float
     */
    public function getWeight()
    {
        $weight = 0;

        foreach ($this->getItems() as $item) {
            $weight += $item->getWeight();
        }

        return $weight;
    }

    /**
     * Count shipped items quantity
     *
     * @return integer
     */
    public function countItems()
    {
        $result = 0;

        foreach ($this->getItems() as $item) {
            $result += $item->getAmount();
        }

        return $result;
    }

    /**
     * Get order subtotal only for shipped items
     *
     * @return float
     */
    public function getSubtotal()
    {
        $subtotal = 0;

        foreach ($this->getItems() as $item) {
            $subtotal += $item->getTotal();
        }

        return $subtotal;
    }

    /**
     * Get shipped items for check condition
     *
     * @return array
     */
    public function getItemsCondition()
    {
        $result = array();

        foreach ($this->order->getItems() as $item) {
            if ($item->isShippable()) {
                $result[] = $item;
            }
        }

        return $result;
    }

    /**
     * Get order weight for check condition
     *
     * @return float
     */
    public function getWeightCondition()
    {
        $weight = 0;

        foreach ($this->getItemsCondition() as $item) {
            $weight += $item->getWeight();
        }

        return $weight;
    }

    /**
     * Count shipped items quantity for check condition
     *
     * @return integer
     */
    public function countItemsCondition()
    {
        $result = 0;

        foreach ($this->getItemsCondition() as $item) {
            $result += $item->getAmount();
        }

        return $result;
    }

    /**
     * Get order subtotal only for shipped items for check condition
     *
     * @return float
     */
    public function getSubtotalCondition()
    {
        $subtotal = 0;

        foreach ($this->getItemsCondition() as $item) {
            $subtotal += $item->getTotal();
        }

        return $subtotal;
    }


    // }}}

    // {{{ Surcharge operations

    /**
     * Get surcharge name
     *
     * @param \XLite\Model\Order\Surcharge $surcharge Surcharge
     *
     * @return \XLite\DataSet\Transport\Order\Surcharge
     */
    public function getSurchargeInfo(\XLite\Model\Base\Surcharge $surcharge)
    {
        $info = new \XLite\DataSet\Transport\Order\Surcharge;

        $info->name = \XLite\Core\Translation::lbl('Shipping cost');
        $info->notAvailableReason = \XLite\Core\Translation::lbl('Shipping address is not defined');

        return $info;
    }

    // }}}
}
