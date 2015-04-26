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
 * @copyright Copyright (c) 2011-2014 Qualiteam software Ltd <info@x-cart.com>. All rights reserved
 * @license   http://www.x-cart.com/license-agreement.html X-Cart 5 License Agreement
 * @link      http://www.x-cart.com/
 */

namespace XLite\Module\XC\UPS\Controller\Admin;

/**
 * UPS shipping module settings controller
 */
class Ups extends \XLite\Controller\Admin\ShippingSettings
{
    /**
     * Return the current page title (for the content area)
     *
     * @return string
     */
    public function getTitle()
    {
        return 'UPS settings';
    }

    /**
     * Get package type options array (for selector on UPS configuration page)
     *
     * @return array
     */
    public function getPackageTypeOptions()
    {
        return \XLite\Module\XC\UPS\Model\Shipping\Processor\UPS::getPackageTypeOptions();
    }

    /**
     * Get pickup type options array (for selector on UPS configuration page)
     *
     * @return array
     */
    public function getPickupTypeOptions()
    {
        return \XLite\Module\XC\UPS\Model\Shipping\Processor\UPS::getPickupTypeOptions();
    }

    /**
     * Get delivery confirmation options array (for selector on UPS configuration page)
     *
     * @return array
     */
    public function getUPSDevileryConfOptions()
    {
        return \XLite\Module\XC\UPS\Model\Shipping\Processor\UPS::getUPSDevileryConfOptions();
    }

    /**
     * Get dimension unit (for UPS configuration page)
     *
     * @return array
     */
    public function getDimUnit()
    {
        return \XLite\Module\XC\UPS\Model\Shipping\Processor\UPS::getDimUnit();
    }

    /**
     * Get currency code by Company country
     *
     * @return array
     */
    public function getCurrencyByCountry()
    {
        $currencyCode = null;

        $country = \XLite\Core\Database::getRepo('XLite\Model\Country')->findOneBy(
            array(
                'code' => \XLite\Core\Config::getInstance()->Company->location_country,
            )
        );

        if ($country && $country->getCurrency()) {
            $currencyCode = $country->getCurrency()->getCode();
        }

        return $currencyCode;
    }

    /**
     * Update settings
     *
     * @return void
     */
    public function doActionUpdate()
    {
        $postedData = \XLite\Core\Request::getInstance()->getData();
        $options    = \XLite\Core\Database::getRepo('\XLite\Model\Config')
            ->findBy(array('category' => $this->getOptionsCategory(), 'type' => 'checkbox'));

        foreach ($options as $key => $option) {

            $name = $option->getName();

            if (!isset($postedData[$name])) {
                \XLite\Core\Request::getInstance()->{$name} = '';
            }
        }

        parent::doActionUpdate();
    }

    /**
     * getOptionsCategory
     *
     * @return string
     */
    protected function getOptionsCategory()
    {
        return 'XC\UPS';
    }

    /**
     * Get shipping processor
     *
     * @return object
     */
    protected function getProcessor()
    {
        return new \XLite\Module\XC\UPS\Model\Shipping\Processor\UPS();
    }

    /**
     * Get schema of an array for test rates routine
     *
     * @return array
     */
    protected function getTestRatesSchema()
    {
        $schema = parent::getTestRatesSchema();

        unset($schema['dstAddress']['type']);

        return $schema;
    }

    /**
     * Get input data to calculate test rates
     *
     * @param array $schema  Input data schema
     * @param array &$errors Array of fields which are not set
     *
     * @return array
     */
    protected function getTestRatesData(array $schema, &$errors)
    {
        $data = parent::getTestRatesData($schema, $errors);

        $package = array(
            'weight'   => $data['weight'],
            'subtotal' => $data['subtotal'],
        );

        $data['packages'] = array();
        $data['packages'][] = $package;

        unset($data['weight']);
        unset($data['subtotal']);

        return $data;
    }
}
