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

namespace XLite\Controller\Admin;

/**
 * Zones page controller
 */
class Zones extends \XLite\Controller\Admin\AAdmin
{

    /**
     * Return the current page title (for the content area)
     *
     * @return string
     */
    public function getTitle()
    {
        return static::t('Zones');
    }

    /**
     * Add elements into the specified zone
     *
     * @param \XLite\Model\Zone $zone Zone object
     * @param array             $data Array of elements: array(<elementType> => array(value1, value2, value3...))
     *
     * @return \XLite\Model\Zone
     */
    public function addElements($zone, $data)
    {
        foreach ($data as $elementType => $elements) {
            if (is_array($elements) && !empty($elements)) {

                foreach ($elements as $elementValue) {
                    $newElement = new \XLite\Model\ZoneElement();

                    $newElement->setElementValue($elementValue);
                    $newElement->setElementType($elementType);
                    $newElement->setZone($zone);

                    $zone->addZoneElements($newElement);
                }
            }
        }

        return $zone;
    }

    /**
     * Do action 'Update'
     *
     * @return void
     */
    protected function doActionUpdateList()
    {
        $list = new \XLite\View\ItemsList\Model\Zone;
        $list->processQuick();
    }

    /**
     * Do action 'Update'
     *
     * @return void
     */
    protected function doActionUpdate()
    {
        $postedData = \XLite\Core\Request::getInstance()->getData();
        $zoneId = intval($postedData['zone_id']);

        if (isset($postedData['zone_id']) && 0 < $zoneId) {
            $zone = \XLite\Core\Database::getRepo('XLite\Model\Zone')->find($zoneId);
        }

        if (isset($zone)) {
            $data = $this->getElementsData($postedData);

            if (1 == $zoneId || !empty($data[\XLite\Model\ZoneElement::ZONE_ELEMENT_COUNTRY])) {

                // Remove all zone elements if exists
                if ($zone->hasZoneElements()) {

                    foreach ($zone->getZoneElements() as $element) {
                        \XLite\Core\Database::getEM()->remove($element);
                    }

                    $zone->getZoneElements()->clear();

                    \XLite\Core\Database::getEM()->persist($zone);
                    \XLite\Core\Database::getEM()->flush();
                }

                // Insert new elements from POST
                $zone = $this->addElements($zone, $data);

                // Prepare value for 'zone_name' field
                $zoneName = trim($postedData['zone_name']);

                if (!empty($zoneName) && $zoneName != $zone->getZoneName()) {
                    // Update zone name
                    $zone->setZoneName($zoneName);
                }

                \XLite\Core\Database::getEM()->persist($zone);
                \XLite\Core\Database::getEM()->flush();
                \XLite\Core\Database::getEM()->clear();

                \XLite\Core\Database::getRepo('XLite\Model\Zone')->cleanCache();

                \XLite\Core\TopMessage::addInfo(static::t('Zone details have been updated successfully'));

            } else {
                \XLite\Core\TopMessage::addError(static::t('The countries list for zone is empty. Please specify it.'));
            }

            $this->redirect(\XLite\Core\Converter::buildURL('zones', '', array('zone_id' => $zoneId)));

        } else {
            \XLite\Core\TopMessage::addError(static::t('Zone not found (X)', array('zoneId' => $zoneId)));
        }
    }

    /**
     * Get zone elements passed from post request
     *
     * @param array $postedData Array of data posted via post request
     *
     * @return array
     */
    protected function getElementsData($postedData)
    {
        $data = array();

        $data[\XLite\Model\ZoneElement::ZONE_ELEMENT_COUNTRY] = !empty($postedData['zone_countries'])
            ? array_filter(explode(';', $postedData['zone_countries']))
            : array();

        $data[\XLite\Model\ZoneElement::ZONE_ELEMENT_STATE] = !empty($postedData['zone_states'])
            ? array_filter(explode(';', $postedData['zone_states']))
            : array();

        $data[\XLite\Model\ZoneElement::ZONE_ELEMENT_TOWN] = !empty($postedData['zone_cities'])
            ? array_filter(explode("\n", $postedData['zone_cities']))
            : array();

        $data[\XLite\Model\ZoneElement::ZONE_ELEMENT_ZIPCODE] = !empty($postedData['zone_zipcodes'])
            ? array_filter(explode("\n", $postedData['zone_zipcodes']))
            : array();

        $data[\XLite\Model\ZoneElement::ZONE_ELEMENT_ADDRESS] = !empty($postedData['zone_addresses'])
            ? array_filter(explode("\n", $postedData['zone_addresses']))
            : array();

        foreach ($data[\XLite\Model\ZoneElement::ZONE_ELEMENT_STATE] as $value) {

            $codes = explode('_', $value);

            if (!in_array($codes[0], $data[\XLite\Model\ZoneElement::ZONE_ELEMENT_COUNTRY])) {
                $data[\XLite\Model\ZoneElement::ZONE_ELEMENT_COUNTRY][] = $codes[0];
            }
        }

        return $data;
    }
}
