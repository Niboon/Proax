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

namespace XLite\Model\Payment\Processor;

/**
 * 'COD' payment processor
 */
class COD extends \XLite\Model\Payment\Processor\Offline
{
    /**
     * Shipping method carrier code which is allowed to make COD payment method available at checkout
     *
     * @var string
     */
    protected $carrierCode = null;


    /**
     * Return true if processor is for COD payment method
     *
     * @return boolean
     */
    public function isCOD()
    {
        return true;
    }

    /**
     * Check if payment method allowed for the order
     *
     * @param \XLite\Model\Order          $order  Order
     * @param \XLite\Model\Payment\Method $method Payment method
     *
     * @return boolean
     */
    public function isApplicable(\XLite\Model\Order $order, \XLite\Model\Payment\Method $method)
    {
        return parent::isApplicable($order, $method) && $this->getCarrierCode() && $this->isCODAllowed($order);
    }

    /**
     * Get shipping rate value with COD consideration
     *
     * @param \XLite\Model\Order $order Order
     *
     * @return float
     */
    public function getCODValue($order)
    {
        $value = null;

        $rate = $this->getShippingRate($order);

        if ($rate) {

            if ($rate->getExtraData()->cod_rate > $rate->getBaseRate()) {
                // When payment option COD is available but still not selected at checkout
                $value = $rate->getExtraData()->cod_rate - $rate->getBaseRate();

            } elseif (
                \XLite\Core\Session::getInstance()->savedRatesValues
                && !empty(\XLite\Core\Session::getInstance()->savedRatesValues[$rate->getMethodId()])
                && \XLite\Core\Session::getInstance()->savedRatesValues[$rate->getMethodId()] < $rate->getBaseRate()
            ) {
                // When payment option COD is available and selected at checkout
                $value = $rate->getBaseRate() - \XLite\Core\Session::getInstance()->savedRatesValues[$rate->getMethodId()];
            }
        }

        return $value;
    }

    /**
     * Get the carrier code
     *
     * @return string
     */
    protected function getCarrierCode()
    {
        return $this->carrierCode;
    }

    /**
     * Check if COD is allowed for selected shipping method
     *
     * @param \XLite\Model\Order $order Order
     *
     * @return boolean
     */
    protected function isCODAllowed($order)
    {
        return $this->getShippingRate($order) ? true : false;
    }

    /**
     * Get selected shipping rate object
     *
     * @param \XLite\Model\Order $order Order
     *
     * @return \XLite\Model\Shipping\Rate
     */
    protected function getShippingRate($order)
    {
        $result = null;

        $modifier = $order->getModifier(\XLite\Model\Base\Surcharge::TYPE_SHIPPING, 'SHIPPING');

        if ($modifier && $modifier->getMethod() && $this->getCarrierCode() == $modifier->getMethod()->getCarrier()) {

            $rate = $modifier->getSelectedRate();

            if ($rate && $rate->getExtraData() && $rate->getExtraData()->cod_supported) {
                $result = $rate;
            }
        }

        return $result;
    }
}
