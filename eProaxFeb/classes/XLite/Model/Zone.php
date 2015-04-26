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
 * Zone model
 *
 * @Entity (repositoryClass="\XLite\Model\Repo\Zone")
 * @Table  (name="zones",
 *      indexes={
 *          @Index (name="zone_name", columns={"zone_name"}),
 *          @Index (name="zone_default", columns={"is_default"})
 *      }
 * )
 */
class Zone extends \XLite\Model\AEntity
{
    /**
     * Zone unique id
     *
     * @var integer
     *
     * @Id
     * @GeneratedValue (strategy="AUTO")
     * @Column         (type="integer")
     */
    protected $zone_id;

    /**
     * Zone name
     *
     * @var string
     *
     * @Column (type="string", length=64)
     */
    protected $zone_name = '';

    /**
     * Zone default flag
     *
     * @var integer
     *
     * @Column (type="boolean")
     */
    protected $is_default = false;

    /**
     * Zone elements (relation)
     *
     * @var \Doctrine\Common\Collections\ArrayCollection
     *
     * @OneToMany (targetEntity="XLite\Model\ZoneElement", mappedBy="zone", cascade={"all"})
     */
    protected $zone_elements;

    /**
     * Shipping rates (relation)
     *
     * @var \Doctrine\Common\Collections\ArrayCollection
     *
     * @OneToMany (targetEntity="XLite\Model\Shipping\Markup", mappedBy="zone", cascade={"all"})
     */
    protected $shipping_markups;


    /**
     * Comparison states function for usort()
     *
     * @param \XLite\Model\State $a First state object
     * @param \XLite\Model\State $b Second state object
     *
     * @return integer
     */
    static public function sortStates($a, $b)
    {
        $aCountry = $a->getCountry()->getCountry();
        $aState = $a->getState();

        $bCountry = $b->getCountry()->getCountry();
        $bState = $b->getState();

        if ($aCountry == $bCountry && $aState == $bState) {
            $result = 0;

        } elseif ($aCountry == $bCountry) {
            $result = ($aState > $bState) ? 1 : -1;

        } else {
            $result = ($aCountry > $bCountry) ? 1 : -1;
        }

        return $result;
    }


    /**
     * Constructor
     *
     * @param array $data Entity properties OPTIONAL
     *
     * @return void
     */
    public function __construct(array $data = array())
    {
        $this->zone_elements    = new \Doctrine\Common\Collections\ArrayCollection();
        $this->shipping_markups = new \Doctrine\Common\Collections\ArrayCollection();

        parent::__construct($data);
    }

    /**
     * Get zone's countries list
     *
     * @param boolean $excluded Flag: true - get countries except zone countries OPTIONAL
     *
     * @return array
     */
    public function getZoneCountries($excluded = false)
    {
        $zoneCountries = array();
        $countryCodes  = $this->getElementsByType(\XLite\Model\ZoneElement::ZONE_ELEMENT_COUNTRY);

        if (!empty($countryCodes) || $excluded) {
            $allCountries = \XLite\Core\Database::getRepo('XLite\Model\Country')->findAllCountries();

            foreach ($allCountries as $key => $country) {
                $condition = in_array($country->getCode(), $countryCodes);

                if ($condition && !$excluded || !$condition && $excluded) {
                    $zoneCountries[] = $country;
                }
            }
        }

        return $zoneCountries;
    }

    /**
     * Get zone's states list
     *
     * @param boolean $excluded Flag: true - get states except zone states OPTIONAL
     *
     * @return array
     */
    public function getZoneStates($excluded = false)
    {
        $zoneStates = array();
        $stateCodes = $this->getElementsByType(\XLite\Model\ZoneElement::ZONE_ELEMENT_STATE);

        if (!empty($stateCodes) || $excluded) {
            $allStates = \XLite\Core\Database::getRepo('XLite\Model\State')->findAllStates();
            usort($allStates, array('\XLite\Model\Zone', 'sortStates'));

            foreach ($allStates as $key => $state) {
                $condition = in_array($state->getCountry()->getCode() . '_' . $state->getCode(), $stateCodes);

                if ($condition && !$excluded || !$condition && $excluded) {
                    $zoneStates[] = $state;
                }
            }
        }

        return $zoneStates;
    }

    /**
     * Get zone's city masks list
     *
     * @param boolean $asString As string OPTIONAL
     *
     * @return array|string
     */
    public function getZoneCities($asString = false)
    {
        $elements = $this->getElementsByType(\XLite\Model\ZoneElement::ZONE_ELEMENT_TOWN);

        return $asString ? (!empty($elements) ? implode(PHP_EOL, $elements) : '') : $elements;
    }

    /**
     * Get zone's zip code masks list
     *
     * @param boolean $asString As string OPTIONAL
     *
     * @return array|string
     */
    public function getZoneZipCodes($asString = false)
    {
        $elements = $this->getElementsByType(\XLite\Model\ZoneElement::ZONE_ELEMENT_ZIPCODE);

        return $asString ? (!empty($elements) ? implode(PHP_EOL, $elements) : '') : $elements;
    }

    /**
     * Get zone's address masks list
     *
     * @param boolean $asString As string OPTIONAL
     *
     * @return array|string
     */
    public function getZoneAddresses($asString = false)
    {
        $elements = $this->getElementsByType(\XLite\Model\ZoneElement::ZONE_ELEMENT_ADDRESS);

        return $asString ? (!empty($elements) ? implode(PHP_EOL, $elements) : '') : $elements;
    }

    /**
     * hasZoneElements
     *
     * @return boolean
     */
    public function hasZoneElements()
    {
        return 0 < count($this->getZoneElements());
    }

    /**
     * Returns the list of zone elements by specified element type
     *
     * @param string $elementType Element type
     *
     * @return array
     */
    public function getElementsByType($elementType)
    {
        $result = array();

        if ($this->hasZoneElements()) {

            foreach ($this->getZoneElements() as $element) {
                if ($elementType == $element->getElementType()) {
                    $result[] = trim($element->getElementValue());
                }
            }
        }

        return $result;
    }

    /**
     * getZoneWeight
     *
     * @param mixed $address Address
     *
     * @return integer
     */
    public function getZoneWeight($address)
    {
        $zoneWeight = 0;

        $elementTypesData = \XLite\Model\ZoneElement::getElementTypesData();

        if ($this->hasZoneElements()) {

            foreach ($elementTypesData as $type => $data) {

                $checkFuncName = 'checkZone' . $data['funcSuffix'];

                // Get zone elements
                $elements = $this->getElementsByType($type);

                if (!empty($elements)) {

                    // Check if address field belongs to the elements
                    $found = $this->$checkFuncName($address, $elements);

                    if ($found) {
                        // Increase the total zone weight
                        $zoneWeight += $data['weight'] * count($elements);

                    } elseif ($data['required']) {
                        // Break the comparing
                        $zoneWeight = 0;
                        break;
                    }
                }
            }

            if ($zoneWeight) {
                $zoneWeight++;
            }

        } else {
            $zoneWeight = 1;
        }

        return $zoneWeight;
    }

    /**
     * checkZoneCountries
     *
     * @param mixed $address  Address
     * @param mixed $elements Elements
     *
     * @return boolean
     */
    protected function checkZoneCountries($address, $elements)
    {
        return !empty($elements)
            && isset($address['country'])
            && in_array($address['country'], $elements);
    }

    /**
     * checkZoneStates
     *
     * @param mixed $address  Address
     * @param mixed $elements Elements
     *
     * @return boolean
     */
    protected function checkZoneStates($address, $elements)
    {
        return empty($elements)
            || (
                isset($address['country'])
                && isset($address['state'])
                && in_array($address['country'] . '_' . $address['state'], $elements)
            );
    }

    /**
     * checkZoneZipCodes
     *
     * @param mixed $address  Address
     * @param mixed $elements Elements
     *
     * @return boolean
     */
    protected function checkZoneZipCodes($address, $elements)
    {
        return empty($elements)
            || (
                isset($address['zipcode'])
                && $this->checkMasks($address['zipcode'], $elements)
            );
    }

    /**
     * checkZoneCities
     *
     * @param mixed $address  Address
     * @param mixed $elements Elements
     *
     * @return boolean
     */
    protected function checkZoneCities($address, $elements)
    {
        return empty($elements)
            || (
                isset($address['city'])
                && $this->checkMasks($address['city'], $elements)
            );
    }

    /**
     * checkZoneAddresses
     *
     * @param mixed $address  Address
     * @param mixed $elements Elements
     *
     * @return boolean
     */
    protected function checkZoneAddresses($address, $elements)
    {
        return empty($elements)
            || (
                isset($address['address'])
                && $this->checkMasks($address['address'], $elements)
            );
    }

    /**
     * checkMasks
     *
     * @param mixed $value     Value
     * @param mixed $masksList Mask list
     *
     * @return boolean
     */
    protected function checkMasks($value, $masksList)
    {
        $found = false;

        foreach ($masksList as $mask) {

            $mask = str_replace('%', '.*', preg_quote($mask));

            if (preg_match('/^' . $mask . '$/i', $value)) {
                $found = true;
                break;
            }
        }

        return $found;
    }
}
