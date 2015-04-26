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
 * Common shipping method
 */
class Shipping extends \XLite\Base\Singleton
{
    /**
     * List of registered shipping processors
     *
     * @var array
     */
    protected static $registeredProcessors = array();

    /**
     * Flag: Ignore long shipping rates calculations mode (if true)
     * Only offline rates are calculated in this mode
     *
     * @var boolean
     */
    protected static $ignoreLongCalculations = false;

    /**
     * Set $ignoreLongCalculations variable
     *
     * @param boolean $value Mode
     *
     * @return void
     */
    public static function setIgnoreLongCalculationsMode($value)
    {
        static::$ignoreLongCalculations = $value;
    }

    /**
     * Get $ignoreLongCalculations variable value
     *
     * @return boolean
     */
    public static function isIgnoreLongCalculations()
    {
        return static::$ignoreLongCalculations;
    }

    /**
     * Register new shipping processor. All processors classes must be
     * derived from \XLite\Model\Shipping\Processor\AProcessor class
     *
     * @param string $processorClass Processor class
     *
     * @return void
     */
    public static function registerProcessor($processorClass)
    {
        if (!isset(self::$registeredProcessors[$processorClass])) {

            if (\XLite\Core\Operator::isClassExists($processorClass)) {
                self::$registeredProcessors[$processorClass] = new $processorClass();
                uasort(self::$registeredProcessors, array(\XLite\Model\Shipping::getInstance(), 'compareProcessors'));

            }
        }
    }

    /**
     * Unregister shipping processor.
     *
     * @param string $processorClass Processor class
     *
     * @return void
     */
    public static function unregisterProcessor($processorClass)
    {
        if (isset(self::$registeredProcessors[$processorClass])) {
            unset(self::$registeredProcessors[$processorClass]);
        }
    }

    /**
     * Returns the list of registered shipping processors
     *
     * @return array
     */
    public static function getProcessors()
    {
        return self::$registeredProcessors;
    }


    /**
     * __constructor
     *
     * @return void
     */
    public function __construct()
    {
        self::registerProcessor('\XLite\Model\Shipping\Processor\Offline');
    }

    /**
     * Retrieves shipping methods: all or by specified processor
     *
     * @param mixed $processorClass Processor class
     *
     * @return array
     */
    public function getShippingMethods($processorClass = null)
    {
        $methods = array();

        if (isset($processorClass) && isset(self::$registeredProcessors[$processorClass])) {
            $methods = self::$registeredProcessors[$processorClass]->getShippingMethods();

        } else {

            foreach (self::$registeredProcessors as $processor) {
                $methods = array_merge($processor->getShippingMethods());
            }
        }

        return $methods;
    }

    /**
     * Return shipping rates
     *
     * @param \XLite\Logic\Order\Modifier\Shipping $modifier Shipping order modifier
     *
     * @return array
     */
    public function getRates(\XLite\Logic\Order\Modifier\Shipping $modifier)
    {
        $rates = array();

        foreach (self::$registeredProcessors as $processor) {
            // Get rates from processors
            $rates = array_merge($rates, $processor->getRates($modifier));

            if ($processor->getErrorMsg()) {
                $processor->logTransaction();
            }
        }

        $rates = $this->applyMarkups($modifier, $rates);

        $rates = $this->postProcessRates($rates);

        return $rates;
    }

    /**
     * Get destination address
     *
     * @param \XLite\Logic\Order\Modifier\Shipping $modifier Shipping order modifier
     *
     * @return array
     */
    public function getDestinationAddress(\XLite\Logic\Order\Modifier\Shipping $modifier)
    {
        $address = null;

        if ($modifier->getOrder()->getProfile() && $modifier->getOrder()->getProfile()->getShippingAddress()) {

            // Profile is exists
            $address = static::prepareAddressData($modifier->getOrder()->getProfile()->getShippingAddress());
        }

        if (!isset($address)) {
            $address = static::getDefaultAddress();
        }

        return $address;
    }

    /**
     * Prepare the specific data format for address
     *
     * @param \XLite\Model\Address $address Address
     *
     * @return array
     */
    public static function prepareAddressData($address)
    {
        return $address
            ? array(
                'address' => $address->getStreet(),
                'city'    => $address->getCity(),
                'state'   => $address->getState()->getStateId(),
                'custom_state' => $address->getCustomState(),
                'zipcode' => $address->getZipcode(),
                'country' => $address->getCountry() ? $address->getCountry()->getCode() : '',
                'type'    => $address->getType() ?: \XLite\View\FormField\Select\AddressType::TYPE_RESIDENTIAL,
            ) : null;
    }

    /**
     * Get default customer address
     *
     * @return array
     */
    public static function getDefaultAddress()
    {
        $config = \XLite\Core\Config::getInstance()->Shipping;

        return array(
            'address'      => $config->anonymous_address,
            'city'         => $config->anonymous_city,
            'state'        => $config->anonymous_state,
            'custom_state' => $config->anonymous_custom_state,
            'zipcode'      => $config->anonymous_zipcode,
            'country'      => $config->anonymous_country,
        );
    }

    /**
     * Sort function for sorting processors by class
     *
     * @param \XLite\Model\Shipping\Processor\AProcessor $a First processor
     * @param \XLite\Model\Shipping\Processor\AProcessor $b Second processor
     *
     * @return integer
     */
    protected function compareProcessors($a, $b)
    {
        $bottomProcessorId = 'offline';

        $a1 = $a->getProcessorId();
        $b1 = $b->getProcessorId();

        if ($a1 == $bottomProcessorId) {
            $result = 1;

        } elseif ($b1 == $bottomProcessorId) {
            $result = -1;

        } else {
            $result = strcasecmp($a1, $b1);
        }

        return $result;
    }

    /**
     * Apply murkups to the rates and return list of modified rates
     *
     * @param \XLite\Logic\Order\Modifier\Shipping $modifier Shipping order modifier
     * @param array                                $rates    List of rates
     *
     * @return array
     */
    protected function applyMarkups($modifier, $rates)
    {
        if (!empty($rates)) {

            $markups = array();

            // Calculate markups
            foreach ($rates as $id => $rate) {

                // If markup has already been calculated for rate then continue iteration
                if (null !== $rate->getMarkup()) {
                    continue;
                }

                $processor = $rate->getMethod()->getProcessor();

                if (!isset($markups[$processor])) {
                    $markups[$processor] = \XLite\Core\Database::getRepo('XLite\Model\Shipping\Markup')
                        ->findMarkupsByProcessor($processor, $modifier);
                }

                // Set markup to the rate
                if (isset($markups[$processor])) {

                    foreach ($markups[$processor] as $method) {

                        if ($method->getMethodId() == $rate->getMethodId()) {
                            $rate->setMarkup($markup);
                            $rate->setMarkupRate($markup->getMarkupValue());
                            $rates[$id] = $rate;
                        }
                    }
                }
            }
        }

        return $rates;
    }

    /**
     * Post process the list of rates
     *
     * @param array $rates List of rates
     *
     * @return array
     */
    protected function postProcessRates($rates)
    {
        $savedRatesValues = \XLite\Core\Session::getInstance()->savedRatesValues ?: array();
        $hash = array();

        if (!empty($rates)) {
            // Generate hash of rate values: methodId => baseRate
            foreach ($rates as $rate) {
                $hash[$rate->getMethodId()] = 1;
                $extraData = $rate->getExtraData();
                if (
                    $extraData 
                    && $extraData->cod_rate 
                    && $extraData->cod_rate > $rate->getBaseRate()
                ) {
                    $savedRatesValues[$rate->getMethodId()] = $rate->getBaseRate();
                }
            }

            // Remove obsolete rates from saved rate values
            foreach ($savedRatesValues as $methodId => $rateInfo) {
                if (!isset($hash[$methodId])) {
                    unset($savedRatesValues[$methodId]);
                }
            }
        } else {
            $savedRatesValues = array();
        }

        // Save rate values in the session
        \XLite\Core\Session::getInstance()->savedRatesValues = $savedRatesValues;

        return $rates;
    }
}
