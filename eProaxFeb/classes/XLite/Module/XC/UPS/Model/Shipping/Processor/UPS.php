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

namespace XLite\Module\XC\UPS\Model\Shipping\Processor;

/**
 * Shipping processor model
 * API: UPS Developer API (XML)
 * API documentation: XCN-1348
 * Shipments supported: Worldwide -> Worldwide
 */
class UPS extends \XLite\Model\Shipping\Processor\AProcessor
{
    /**
     * Unique processor Id
     *
     * @var string
     */
    protected $processorId = 'ups';

    /**
     * UPS live API URL
     *
     * @var string
     */
    protected $liveApiURL = 'https://onlinetools.ups.com:443/ups.app/xml';

    /**
     * UPS test API URL
     *
     * @var string
     */
    protected $testApiURL = 'https://wwwcie.ups.com/ups.app/xml';

    /**
     * This table provides correct service codes for different origins
     * <ServiceCode returned from UPS> => array (<origin> => <code of shipping method>)
     *
     * @var array
     */
    protected $upsServices = array(
        '01' => array(
            'US' => 'NDA',
            'CA' => 'EXP',
            'PR' => 'NDA',
        ),
        '02' => array(
            'US' => '2DA',
            'CA' => 'WEXDSM',
            'PR' => '2DA',
        ),
        '03' => array(
            'US' => 'GND',
            'PR' => 'GND',
        ),
        '07' => array(
            'US' => 'WEXPSM',
            'EU' => 'EXP',
            'CA' => 'EXP',
            'PL' => 'EXP',
            'PR' => 'WEXPSM',
            'MX' => 'EXP',
            'OTHER_ORIGINS' => 'EXP',
        ),
        '08' => array(
            'US' => 'WEXDSM',
            'EU' => 'EXDSM',
            'PL' => 'EXDSM',
            'PR' => 'WEXDSM',
            'MX' => 'EXDSM',
            'OTHER_ORIGINS' => 'WEXDSM',
        ),
        '11' => array(
            'US' => 'STD',
            'EU' => 'STD',
            'CA' => 'STD',
            'MX' => 'STD',
            'PL' => 'STD',
            'OTHER_ORIGINS' => 'STD',
        ),
        '12' => array(
            'US' => '3DS',
            'CA' => '3DS',
        ),
        '13' => array(
            'US' => 'NDAS',
            'CA' => 'SAVSM',
        ),
        '14' => array(
            'US' => 'NDAEAMSM',
            'CA' => 'EXPEAMSM',
            'PR' => 'NDAEAMSM',
        ),
        '54' => array(
            'US' => 'WEXPPSM',
            'EU' => 'WEXPPSM',
            'PL' => 'WEXPPSM',
            'PR' => 'WEXPPSM',
            'MX' => 'EXPP',
            'OTHER_ORIGINS' => 'WEXPPSM',
        ),
        '59' => array(
            'US' => '2DAAM',
        ),
        '65' => array(
            'US' => 'SAV',
            'EU' => 'SAV',
            'PL' => 'SAV',
            'PR' => 'SAV',
            'MX' => 'SAV',
            'OTHER_ORIGINS' => 'SAV',
        ),
        '82' => array(
            'PL' => 'TSTD',
        ),
        '83' => array(
            'PL' => 'TDC',
        ),
        '84' => array(
            'PL' => 'TI',
        ),
        '85' => array(
            'PL' => 'TEXP',
        ),
        '86' => array(
            'PL' => 'TEXPS',
        ),
        '96' => array(
            'US' => 'WEXPF',
            'EU' => 'WEXPF',
            'CA' => 'WEXPF',
            'PL' => 'WEXPF',
            'PR' => 'WEXPF',
            'MX' => 'WEXPF',
            'OTHER_ORIGINS' => 'WEXPF',
        ),
    );

    /**
     * Packages parameters: weight (lbs), dimensions (inches)
     *
     * @var array
     */
    protected static $upsPackages = array(
        '00' => array(
            'name' => 'Unknown',
            'limits' => array(
                'weight' => 150,
                'length' => 108,
                'width' => 108,
                'height' => 108
            )
        ),
        '01' => array(
            'name' => 'UPS Letter / UPS Express Envelope',
            'limits' => array(
                'weight' => 1,
                'length' => 9.5,
                'width' => 12.5,
                'height' => 0.25
            )
        ),
        '02' => array(
            'name' => 'Package'
        ),
        '03' => array(
            'name' => 'UPS Tube',
            'limits' => array(
                'length' => 6,
                'width' => 38,
                'height' => 6
            )
        ),
        '04' => array(
            'name' => 'UPS Pak',
            'limits' => array(
                'length' => 12.75,
                'width' => 16,
                'height' => 2
            )
        ),
        '21' => array(
            'name' => 'UPS Express Box',
            'limits' => array(
                'length' => 13,
                'width' => 18,
                'height' => 3,
                'weight' => 30
            )
        ),
        '24' => array(
            'name' => 'UPS 25 Kg Box&#174;',
            'limits' => array(
                'length' => 17.375,
                'width' => 19.375,
                'height' => 14,
                'weight' => 55.1
            )
        ),
        '25' => array(
            'name' => 'UPS 10 Kg Box&#174;',
            'limits' => array(
                'length' => 13.25,
                'width' => 16.5,
                'height' => 10.75,
                'weight' => 22
            )
        ),
        '30' => array(
            'name' => 'Pallet (for GB or PL domestic shipments only)'
        ),
        '2a' => array(
            'name' => 'Small Express Box'
        ),
        '2b' => array(
            'name' => 'Medium Express Box'
        ),
        '2c' => array(
            'name' => 'Large Express Box'
        )
    );


    /**
     * Get data for pickup type selector
     *
     * @return array
     */
    public static function getPickupTypeOptions()
    {
        return array(
            '01' => 'Daily Pickup',
            '03' => 'Customer counter',
            '06' => 'One time pickup',
            '07' => 'On call air',
            '11' => 'Suggested retail rates',
            '19' => 'Letter center',
            '20' => 'Air service center',
        );
    }

    /**
     * Get data for package type selector
     *
     * @return array
     */
    public static function getPackageTypeOptions()
    {
        return self::$upsPackages;
    }

    /**
     * Get data for delivery confirmation selector
     *
     * @return array
     */
    public static function getUPSDevileryConfOptions()
    {
        return array(
            '0' => 'No confirmation',
            '1' => 'Delivery confirmation - no signature',
            '2' => 'Delivery confirmation - signature required',
            '3' => 'Delivery confirmation - adult signature required',
        );
    }

    /**
     * Get dimension unit (for UPS configuration page)
     *
     * @return string
     */
    public static function getDimUnit()
    {
        list($wUnit, $dUnit) = static::getWeightAndDimUnits();

        return $dUnit;
    }

    /**
     * Check if 'Cash on delivery (UPS)' payment method enabled
     *
     * @return boolean
     */
    public static function isCODPaymentEnabled()
    {
        $method = \XLite\Core\Database::getRepo('XLite\Model\Payment\Method')
            ->findOneBy(array('service_name' => 'COD_UPS'));

        return $method && $method->getEnabled();
    }


    /**
     * Returns weight and dimensional units for specified source country code
     *
     * @param string $countryCode Country code
     *
     * @return array
     */
    protected static function getWeightAndDimUnits($countryCode = null)
    {
        if (empty($countryCode)) {
            $countryCode = \XLite\Core\Config::getInstance()->Company->location_country;
        }

        if (in_array($countryCode, array('DO', 'PR', 'US', 'CA'))) {
            $wUnit = 'LBS';
            $dUnit = 'IN';

        } else {
            $wUnit = 'KGS';
            $dUnit = 'CM';
        }

        return array($wUnit, $dUnit);
    }

    /**
     * This method must return the form method 'post' or 'get' value.
     *
     * @param string $trackingNumber
     *
     * @return string
     */
    public function getTrackingInformationMethod($trackingNumber)
    {
        return 'post';
    }

    /**
     * This method must return the URL to the detailed tracking information about the package.
     * Tracking number is provided.
     *
     * @param string $trackingNumber
     *
     * @return null|string
     */
    public function getTrackingInformationURL($trackingNumber)
    {
        return 'http://wwwapps.ups.com/tracking/tracking.cgi';
    }

    /**
     * Defines the form parameters of tracking information form
     *
     * @param string $trackingNumber Tracking number
     *
     * @return array Array of form parameters
     */
    public function getTrackingInformationParams($trackingNumber)
    {
        $list = parent::getTrackingInformationParams($trackingNumber);
        $list['tracknum']                       = $trackingNumber;
        $list['accept_UPS_license_agreement']   = 'yes';
        $list['nonUPS_title']                   = '';
        $list['nonUPS_header']                  = '';
        $list['nonUPS_body']                    = '';
        $list['nonUPS_footer']                  = '';

        return $list;
    }

    /**
     * getProcessorName
     *
     * @return string
     */
    public function getProcessorName()
    {
        return 'UPS';
    }

    /**
     * Disable the possibility to edit the names of shipping methods in the interface of administrator
     *
     * @return boolean
     */
    public function isMethodNamesAdjustable()
    {
        return false;
    }

    /**
     * Get API URL
     *
     * @return string
     */
    public function getApiURL()
    {
        return \XLite\Core\Config::getInstance()->XC->UPS->test_mode
            ? $this->testApiURL
            : $this->liveApiURL;
    }

    /**
     * Returns processor's shipping methods (this depends on origin country)
     *
     * @return array
     */
    public function getShippingMethods()
    {
        $list = parent::getShippingMethods();

        $methods = array();

        $originCode = $this->getOriginCode(\XLite\Core\Config::getInstance()->Company->location_country);

        $allowedMethods = array();

        foreach ($this->upsServices as $service) {
            foreach ($service as $countryCode => $methodCode) {
                if ($countryCode == $originCode) {
                    $allowedMethods[] = $methodCode;
                }
            }
        }

        foreach ($list as $k => $method) {
            if (in_array($method->getCode(), $allowedMethods)) {
                $methods[] = $method;
            }
        }

        return $methods;
    }

    /**
     * Returns shipping rates
     *
     * @param array|\XLite\Logic\Order\Modifier\Shipping $inputData   Shipping order modifier or array of data for request
     * @param boolean                                    $ignoreCache Flag: if true then do not get rates from cache OPTIONAL
     *
     * @return array
     */
    public function getRates($inputData, $ignoreCache = false)
    {
        $this->errorMsg = null;
        $rates = array();

        if ($this->isConfigured()) {

            $data = $this->prepareInputData($inputData);

            if (!empty($data)) {

                $rates = $this->doQuery($data, $ignoreCache);

                if (
                    !$ignoreCache
                    && !$data['cod_enabled']
                    && $rates
                    && self::isCODPaymentEnabled()
                    && $this->isCODAllowed('all', $data['srcAddress']['country'], $data['dstAddress']['country'])
                ) {

                    // Calculate rates with COD enabled

                    $data['cod_enabled'] = true;

                    $ratesWithCOD = $this->doQuery($data, $ignoreCache);

                    if ($ratesWithCOD) {

                        foreach ($rates as $k => $rate) {

                            $rateCode = $rate->getMethod()->getCode();

                            foreach ($ratesWithCOD as $rt) {

                                if ($rt->getMethod()->getCode() == $rateCode) {

                                    $extra = $rates[$k]->getExtraData() ?: new \XLite\Core\CommonCell();
                                    $extra->cod_supported = true;
                                    $extra->cod_rate = $rt->getBaseRate();
                                    $rates[$k]->setExtraData($extra);
                                    break;
                                }
                            }
                        }
                    }
                }

            } else {
                $this->errorMsg = 'Wrong input data';
            }

        } elseif (\XLite\Module\XC\UPS\Main::isStrictMode()) {
            $this->errorMsg = 'UPS module is not configured';
        }

        // Return shipping rates list
        return $rates;
    }


    /**
     * Get package limits
     *
     * @return array
     */
    protected function getPackageLimits()
    {
        $limits = parent::getPackageLimits();

        $upsOptions = \XLite\Core\Config::getInstance()->XC->UPS;

        $limits['weight'] = $upsOptions->max_weight;

        $limits['length'] = $upsOptions->length;
        $limits['width']  = $upsOptions->width;
        $limits['height'] = $upsOptions->height;

        return $limits;
    }

    /**
     * Returns true if UPS module is configured
     *
     * @return boolean
     */
    protected function isConfigured()
    {
        return \XLite\Core\Config::getInstance()->XC->UPS->accessKey
            && \XLite\Core\Config::getInstance()->XC->UPS->userID
            && \XLite\Core\Config::getInstance()->XC->UPS->password;
    }

    /**
     * prepareInputData
     *
     * @param array|\XLite\Logic\Order\Modifier\Shipping $inputData Shipping order modifier (from order) or array of input data (from test controller)
     *
     * @return void
     */
    protected function prepareInputData($inputData)
    {
        $data = array();

        if ($inputData instanceOf \XLite\Logic\Order\Modifier\Shipping) {
            $data = $this->prepareDataFromModifier($inputData);

        } else {
            $data = $inputData;
        }

        if (!empty($data['packages'])) {

            $data['total'] = 0;

            list($wUnit, $dUnit) = static::getWeightAndDimUnits($data['srcAddress']['country']);

            foreach ($data['packages'] as $key => $package) {

                $data['packages'][$key]['weight'] = \XLite\Core\Converter::convertWeightUnits(
                    $package['weight'],
                    \XLite\Core\Config::getInstance()->Units->weight_unit,
                    'KGS' == $wUnit ? 'kg' : 'lbs'
                );

                $data['packages'][$key]['weight'] = max(0.1, $data['packages'][$key]['weight']);

                $data['packages'][$key]['subtotal'] = $this->getPackagesSubtotal($package['subtotal']);
                $data['total'] += $data['packages'][$key]['subtotal'];
            }

        } else {
            $data = array();
            $this->errorMsg = 'There are no defined packages to delivery';
        }

        return $this->getQuoted($data);
    }

    /**
     * Prepare input data from order shipping modifier
     *
     * @param \XLite\Logic\Order\Modifier\Shipping $modifier Shipping order modifier
     *
     * @return array
     */
    protected function prepareDataFromModifier($modifier)
    {
        $data = array();

        $data['srcAddress'] = array(
            'city' => \XLite\Core\Config::getInstance()->Company->location_city,
            'zipcode' => \XLite\Core\Config::getInstance()->Company->location_zipcode,
            'country' => \XLite\Core\Config::getInstance()->Company->location_country,
        );

        if (isset(\XLite\Core\Config::getInstance()->Company->location_state)) {
            $data['srcAddress']['state'] = \XLite\Core\Database::getRepo('XLite\Model\State')->getCodeById(
                \XLite\Core\Config::getInstance()->Company->location_state
            );
        }

        $data['dstAddress'] = \XLite\Model\Shipping::getInstance()->getDestinationAddress($modifier);

        if (isset($data['dstAddress']['state'])) {
            $data['dstAddress']['state'] = \XLite\Core\Database::getRepo('XLite\Model\State')->getCodeById(
                $data['dstAddress']['state']
            );
        }

        $data['packages'] = $this->getPackages($modifier);

        $data['cod_enabled'] = false;

        // Detect if COD payment method has been selected by customer on checkout

        if ($modifier->getOrder()->getFirstOpenPaymentTransaction()) {

            $paymentMethod = $modifier->getOrder()->getPaymentMethod();

            if ($paymentMethod && 'COD_UPS' == $paymentMethod->getServiceName()) {
                $data['cod_enabled'] = true;
            }
        }

        return $data;
    }

    /**
     * doQuery
     *
     * @param mixed   $data        Can be either \XLite\Model\Order instance or an array
     * @param boolean $ignoreCache Flag: if true then do not get rates from cache
     *
     * @return void
     */
    protected function doQuery($data, $ignoreCache)
    {
        $rates = array();

        // Get all available rates
        $availableMethods = \XLite\Core\Database::getRepo('XLite\Model\Shipping\Method')
            ->findMethodsByProcessor($this->getProcessorId(), !$ignoreCache);

        if ($availableMethods) {

            // Do rates calculation if there are enabled shipping methods or calculation test is running

            $xmlData = $this->getXMLData($data);

            $postData = preg_split("/(\r\n|\r|\n)/", $xmlData, -1, PREG_SPLIT_NO_EMPTY);

            $postURL = $this->getApiURL();

            try {

                if (!$ignoreCache) {
                    $cachedRate = $this->getDataFromCache($xmlData);
                }

                if (isset($cachedRate)) {
                    $result = $cachedRate;

                } elseif (\XLite\Model\Shipping::isIgnoreLongCalculations()) {
                    // Ignore rates calculation
                    return array();

                } else {

                    // Prepare request XML for logging
                    $xmlDataLog = preg_replace('|<AccessLicenseNumber>.+</AccessLicenseNumber>|i','<AccessLicenseNumber>xxx</AccessLicenseNumber>',$xmlData);
                    $xmlDataLog = preg_replace('|<UserId>.+</UserId>|i','<UserId>xxx</UserId>',$xmlDataLog);
                    $xmlDataLog = preg_replace('|<Password>.+</Password>|i','<Password>xxx</Password>',$xmlDataLog);
                    $xmlDataLog = preg_replace('|<ShipperNumber>.+</ShipperNumber>|i','<ShipperNumber>xxx</ShipperNumber>',$xmlDataLog);

                    // Do request
                    $bouncer = new \XLite\Core\HTTP\Request($postURL . '/Rate');
                    $bouncer->body = $xmlData;
                    $bouncer->verb = 'POST';
                    $bouncer->requestTimeout = 5;
                   //TODO: Remove SSL ByPass when there is a live SSL Niboon
                    $bouncer->setAdditionalOption(CURLOPT_SSL_VERIFYPEER, FALSE);

                    $response = $bouncer->sendRequest();

                    if (200 == $response->code || !empty($response->body)) {

                        $result = $response->body;

                        if (200 == $response->code) {
                            $this->saveDataInCache($xmlData, $result);
                        }

                        if (\XLite\Core\Config::getInstance()->XC->UPS->debug_enabled) {

                            \XLite\Logger::logCustom(
                                'UPS',
                                var_export(
                                    array(
                                        'Request URL'  => $postURL,
                                        'Request XML'  => $xmlDataLog,
                                        'Response XML' => \XLite\Core\XML::getInstance()->getFormattedXML($result),
                                    ),
                                    true
                                )
                            );
                        }

                    } else {
                        $this->errorMsg = sprintf('Error while connecting to the UPS server (%s)', $this->getApiURL());
                    }
                }

                if (!isset($this->errorMsg)) {
                    $response = $this->parseResponse($result);

                } else {
                    $response = array();
                }

                // Save communication log for test request only (ignoreCache is set for test requests only)

                if ($ignoreCache === true) {
                    $this->apiCommunicationLog[] = array(
                        'post URL' => $postURL,
                        'request'  => htmlentities($xmlDataLog),
                        'response' => htmlentities(\XLite\Core\XML::getInstance()->getFormattedXML($result))
                    );
                }

                if (!isset($this->errorMsg) && !isset($response['err_msg'])) {

                    foreach ($response as $row) {

                        $method = $this->getShippingMethod(
                            $row['serviceCode'],
                            $data['srcAddress']['country'],
                            $availableMethods
                        );

                        if ($method) {

                            $rate = new \XLite\Model\Shipping\Rate();
                            $rate->setBaseRate($row['totalCharges']);
                            $rate->setMethod($method);

                            $extraData = null;

                            if (!empty($row['deliveryTime'])) {
                                $extraData = new \XLite\Core\CommonCell();
                                $extraData->deliveryDays = $row['deliveryTime'];
                                $rate->setExtraData($extraData);
                            }

                            if ($data['cod_enabled'] && $this->isCODAllowed('all', $data['srcAddress']['country'], $data['dstAddress']['country'])) {
                                $extraData = $extraData ?: new \XLite\Core\CommonCell();
                                $extraData->cod_supported = true;
                                $extraData->cod_rate = $rate->getBaseRate();
                                $rate->setExtraData($extraData);
                            }

                            $rates[] = $rate;
                        }
                    }

                } elseif (!isset($this->errorMsg)) {
                    $this->errorMsg = (isset($response['err_msg']) ? $response['err_msg'] : 'Unknown error');
                }

            } catch (\Exception $e) {
                $this->errorMsg = 'Exception: ' . $e->getMessage();
            }
        }

        return $rates;
    }

    /**
     * Get XML string for request
     *
     * @param array $data Array of input data
     *
     * @return string
     */
    protected function getXMLData($data)
    {
        $upsOptions = \XLite\Core\Config::getInstance()->XC->UPS;

        switch ($upsOptions->pickup_type) {
            case '01':
                $upsOptions->customer_classification_code = '01';
                break;

            case '06':
            case '07':
            case '19':
            case '20':
                $upsOptions->customer_classification_code = '03';
                break;

            case '03':
            case '11':
                $upsOptions->customer_classification_code = '04';

            default:
        }

        if ('US' == $data['srcAddress']['country'] && !empty($upsOptions->customer_classification_code)) {

            // CustomerClassification section is valid for origin country = 'US' only

            $customerClassificationCode = $upsOptions->customer_classification_code;
            $customerClassificationQuery = <<<EOT
    <CustomerClassification>
        <Code>$customerClassificationCode</Code>
    </CustomerClassification>
EOT;
        } else {
            $customerClassificationQuery = '';
        }

        // Prepare service options

        $srvopts = array();

        if ($upsOptions->saturday_pickup) {
            $srvopts[] = "\t    <SaturdayPickupIndicator/>\n";
        }

        if ($upsOptions->saturday_delivery) {
            $srvopts[] = "\t    <SaturdayDeliveryIndicator/>\n";
        }

        if (!empty($upsOptions->shipper_number)) {
            $shipperNumberXML = <<<EOT
            <ShipperNumber>{$upsOptions->shipper_number}</ShipperNumber>
EOT;

        } else {
            $shipperNumberXML = '';
        }


        if ($data['cod_enabled'] && $this->isCODAllowed('shipment', $data['srcAddress']['country'], $data['dstAddress']['country'])) {

            // COD

            $CODFundsCode = '9'; // Values is: 9 = check, cashiers check or money order - no cash allowed
            $CODAmount = round(doubleval($data['total']), 2);

            // Detect COD currency by destination country
            $country = \XLite\Core\Database::getRepo('XLite\Model\Country')
                ->findOneBy(array('code' => $data['dstAddress']['country']));

            $dstCurrency = $country && $country->getCurrency()
                ? $country->getCurrency()->getCode()
                : $upsOptions->currency_code;

            $srvopts[] =<<<EOT
                <COD>
                    <CODFundsCode>$CODFundsCode</CODFundsCode>
                    <CODAmount>
                        <CurrencyCode>{$dstCurrency}</CurrencyCode>
                        <MonetaryValue>$CODAmount</MonetaryValue>
                    </CODAmount>
                </COD>

EOT;
        }

        // Residential / commercial address indicator
        if (isset($data['dstAddress']['type']) && 'R' == $data['dstAddress']['type']) {
            $residentalFlag = "\t\t\t<ResidentialAddressIndicator/>";

        } else {
            $residentalFlag = '';
        }

        $shipmentOptionsXML = $negotiatedRatesXML = '';

        if (count($srvopts) > 0)
            $shipmentOptionsXML = "\t<ShipmentServiceOptions>\n".join('', $srvopts)."\t</ShipmentServiceOptions>";

        if (!empty($upsOptions->negotiated_rates)) {
            $negotiatedRatesXML = <<<EOT
        <RateInformation>
            <NegotiatedRatesIndicator/>
        </RateInformation>
EOT;
        }

        $pickupType = $upsOptions->pickup_type;
        $packagingType = $upsOptions->packaging_type;

        $packagesXML = '';

        // Prepare packages part of XML

        foreach ($data['packages'] as $package) {

            $pkgopt = array();

            // Dimensions of a package

            if (isset($package['box'])) {
                $length = $package['box']['length'];
                $width = $package['box']['width'];
                $height = $package['box']['height'];

            } else {
                $length = $upsOptions->length;
                $width = $upsOptions->width;
                $height = $upsOptions->height;
            }

            if ($length + $width + $height > 0) {

                // Insert the Dimensions section

                list($wunit, $dunit) = static::getWeightAndDimUnits($data['srcAddress']['country']);

                $length = round($length, 2);
                $width  = round($width, 2);
                $height = round($height, 2);

                $dimensionsQuery = <<<DIM
            <Dimensions>
                <UnitOfMeasurement>
                    <Code>$dunit</Code>
                </UnitOfMeasurement>
                <Length>$length</Length>
                <Width>$width</Width>
                <Height>$height</Height>
            </Dimensions>

DIM;

                $girth = $length + (2 * $width) + (2 * $height);

                if ($girth > 165) {
                    $dimensionsQuery .=<<<DIM
            <LargePackageIndicator />
DIM;
                }
            }

            // Declared value

            if ($upsOptions->extra_cover) {

                $insuredValue = doubleval($upsOptions->extra_cover_value) ?: $package['subtotal'];

                if (!empty($insuredValue)) {
                    $insuredValue = round(doubleval($insuredValue), 2);
                    if ($insuredValue > 0.1) {
                        $pkgopt[] =<<<EOT
                <InsuredValue>
                    <CurrencyCode>{$upsOptions->currency_code}</CurrencyCode>
                    <MonetaryValue>$insuredValue</MonetaryValue>
                </InsuredValue>

EOT;
                    }
                }
            }

            if ($data['cod_enabled'] && $this->isCODAllowed('package', $data['srcAddress']['country'], $data['dstAddress']['country'])) {

                // COD

                $CODFundsCode = '0';
                $CODAmount = round(doubleval($package['subtotal']), 2);

                // Detect COD currency by destination country
                $country = \XLite\Core\Database::getRepo('XLite\Model\Country')
                    ->findOneBy(array('code' => $data['dstAddress']['country']));

                $dstCurrency = $country && $country->getCurrency()
                    ? $country->getCurrency()->getCode()
                    : $upsOptions->currency_code;

                $pkgopt[] =<<<EOT
                <COD>
                    <CODFundsCode>$CODFundsCode</CODFundsCode>
                    <CODAmount>
                        <CurrencyCode>{$dstCurrency}</CurrencyCode>
                        <MonetaryValue>$CODAmount</MonetaryValue>
                    </CODAmount>
                </COD>

EOT;

            } else {

                // Delivery confirmation option
                // DeliveryConfirmation and COD cannot coexist on a single Package

                $deliveryConf = intval($upsOptions->delivery_conf);

                if (
                    $deliveryConf > 0
                    && $deliveryConf < 4
                    && 'US' == $data['srcAddress']['country']
                    && 'US' == $data['dstAddress']['country']
                ) {
                    $pkgopt[] =<<<EOT
                <DeliveryConfirmation>
                    <DCISType>$deliveryConf</DCISType>
                </DeliveryConfirmation>

EOT;
                }
            }

            $pkgparams = (count($pkgopt) > 0)
                ? "\t    <PackageServiceOptions>\n" . implode('', $pkgopt) . "\t    </PackageServiceOptions>\n"
                : '';

            if ($upsOptions->additional_handling) {
                $pkgparams .= "\t    <AdditionalHandling/>";
            }

            // Package description XML

            $packageXML=<<<EOT
        <Package>
            <PackagingType>
                <Code>$packagingType</Code>
            </PackagingType>
            <PackageWeight>
                <UnitOfMeasurement>
                    <Code>$wunit</Code>
                </UnitOfMeasurement>
                <Weight>{$package['weight']}</Weight>
            </PackageWeight>
$dimensionsQuery
$pkgparams
        </Package>
EOT;
            $packagesXML .= "\n" . $packageXML;
        } // foreach

        // Assemble XML request

        $query=<<<EOT
<?xml version='1.0'?>
<AccessRequest xml:lang='en-US'>
    <AccessLicenseNumber>{$upsOptions->accessKey}</AccessLicenseNumber>
    <UserId>{$upsOptions->userID}</UserId>
    <Password>{$upsOptions->password}</Password>
</AccessRequest>
<?xml version='1.0'?>
<RatingServiceSelectionRequest xml:lang='en-US'>
    <Request>
        <TransactionReference>
            <CustomerContext>Rating and Service</CustomerContext>
            <XpciVersion>1.0001</XpciVersion>
        </TransactionReference>
        <RequestAction>Rate</RequestAction>
        <RequestOption>shop</RequestOption>
    </Request>
    <PickupType>
        <Code>$pickupType</Code>
    </PickupType>
$customerClassificationQuery
    <Shipment>
        <Shipper>
$shipperNumberXML
            <Address>
                <City>{$data['srcAddress']['city']}</City>
                <StateProvinceCode>{$data['srcAddress']['state']}</StateProvinceCode>
                <PostalCode>{$data['srcAddress']['zipcode']}</PostalCode>
                <CountryCode>{$data['srcAddress']['country']}</CountryCode>
            </Address>
        </Shipper>
        <ShipFrom>
            <Address>
                <City>{$data['srcAddress']['city']}</City>
                <StateProvinceCode>{$data['srcAddress']['state']}</StateProvinceCode>
                <PostalCode>{$data['srcAddress']['zipcode']}</PostalCode>
                <CountryCode>{$data['srcAddress']['country']}</CountryCode>
            </Address>
        </ShipFrom>
        <ShipTo>
            <Address>
                <City>{$data['dstAddress']['city']}</City>
                <StateProvinceCode>{$data['dstAddress']['state']}</StateProvinceCode>
                <PostalCode>{$data['dstAddress']['zipcode']}</PostalCode>
                <CountryCode>{$data['dstAddress']['country']}</CountryCode>
$residentalFlag
            </Address>
        </ShipTo>
$packagesXML
$shipmentOptionsXML
$negotiatedRatesXML
    </Shipment>
</RatingServiceSelectionRequest>
EOT;

        return $query;
    }

    /**
     * Parses response and returns an associative array
     *
     * @param string $response XML response of UPS API
     *
     * @return void
     */
    protected function parseResponse($response)
    {
        $result = array();

        $xml = \XLite\Core\XML::getInstance();

        $xmlParsed = $xml->parse($response, $err);

        if (!empty($xmlParsed['RatingServiceSelectionResponse']['#']['Response'][0]['#']['Error'])) {

            // Error detected

            $this->errorMsg = sprintf(
                'Error: %s - %s - %s',
                $xmlParsed['RatingServiceSelectionResponse']['#']['Response'][0]['#']['Error'][0]['#']['ErrorCode'][0]['#'],
                $xmlParsed['RatingServiceSelectionResponse']['#']['Response'][0]['#']['Error'][0]['#']['ErrorSeverity'][0]['#'],
                $xmlParsed['RatingServiceSelectionResponse']['#']['Response'][0]['#']['Error'][0]['#']['ErrorDescription'][0]['#']
            );
        }

        if (empty($this->errorMsg)) {

            // Parse rates

            $resultXML = $xml->getArrayByPath($xmlParsed, 'RatingServiceSelectionResponse/#/RatedShipment');

            if ($resultXML && is_array($resultXML)) {

                $conversionRate = $this->getCurrencyConversionRate();
                $currency = null;

                foreach ($resultXML as $service) {

                    $rate = array();
                    $rate['serviceCode']  = $service['#']['Service'][0]['#']['Code'][0]['#'];
                    $rate['deliveryTime'] = $service['#']['GuaranteedDaysToDelivery'][0]['#'];

                    $currency = $service['#']['TotalCharges'][0]['#']['CurrencyCode'][0]['#'];

                    if (isset($service['#']['NegotiatedRates'])) {
                        $rate['totalCharges'] = $service['#']['NegotiatedRates'][0]['#']['NetSummaryCharges'][0]['#']['GrandTotal'][0]['#']['MonetaryValue'][0]['#'];

                    } else {
                        $rate['totalCharges'] = $service['#']['TotalCharges'][0]['#']['MonetaryValue'][0]['#'];
                    }

                    $rate['totalCharges'] *= $conversionRate;

                    $result[] = $rate;
                }

                if ($currency && \XLite\Core\Config::getInstance()->XC->UPS->currency_code != $currency) {
                    \XLite\Core\Database::getRepo('XLite\Model\Config')->createOption(
                        array(
                            'category' => 'XC\\UPS',
                            'name'     => 'currency_code',
                            'value'    => $currency,
                        )
                    );
                }
            }
        }

        return $result;
    }

    /**
     * Get package subtotal with consideration of currency conversion rate
     *
     * @param float $subtotal
     *
     * @return float
     */
    protected function getPackagesSubtotal($subtotal)
    {
        return round($subtotal / $this->getCurrencyConversionRate(), 2);
    }

    /**
     * Get currency conversion rate
     *
     * @return float
     */
    protected function getCurrencyConversionRate()
    {
        $rate = doubleval(\XLite\Core\Config::getInstance()->XC->UPS->currency_rate);

        return $rate ?: 1;
    }

    /**
     * Get UPS origin location depended on country code
     *
     * @param string $code Country code
     *
     * @return string
     */
    protected function getOriginCode($code)
    {
        $originCode = '';

        // EU members (Poland is also EU member, but has different location in $upsServices)
        $euMembers = array(
            'AT', // Austria
            'BE', // Belgium
            'BU', // Bulgaria
            'CY', // Cyprus
            'CZ', // Czech Republic
            'DK', // Denmark
            'EE', // Estonia
            'FI', // Finland
            'FR', // France
            'DE', // Germany
            'GR', // Greece
            'HU', // Hungary
            'IE', // Ireland
            'IT', // Italy
            'LV', // Latvia
            'LT', // Lithuania
            'LU', // Luxembourg
            'MT', // Malta
            'MC', // Monaco
            'NL', // Netherlands
            'PT', // Portugal
            'RO', // Romania
            'SK', // Slovakia
            'SI', // Slovenia
            'ES', // Spain
            'SE', // Sweden
            'GB', // United Kingdom
        );

        if (in_array($code, array('US','CA','PR','MX','PL'))) {

            // Origin is US, Canada, Puerto Rico or Mexico
            $originCode = $code;

        } elseif (in_array($code, $euMembers)) {

            // Origin is European Union
            $originCode = 'EU';

        } else {

            // Origin is other countries
            $originCode = 'OTHER_ORIGINS';
        }

        return $originCode;
    }

    /**
     * Validate string for using in XML node
     *
     * @param mixed $arg Arguments string/array
     *
     * @return mixed
     */
    protected function getQuoted($arg)
    {
        if (is_array($arg)) {

            foreach ($arg as $k=>$v) {

                if ($k == 'phone') {
                    $arg[$k] = preg_replace('/[^0-9]/', "", $v);

                } elseif (is_string($v)) {
                    $arg[$k] = func_htmlspecialchars($v);
                }
            }

        } elseif (is_string($arg)) {
            $arg = func_htmlspecialchars($arg);
        }

        return $arg;
    }

    /**
     * Get shipping method from the list by service code
     *
     * @param string $serviceCode      Service code returned by UPS
     * @param string $countryCode      Shipment origin country code
     * @param array  $availableMethods Array of shipping methods objects gathered from database
     *
     * @return \XLite\Model\Shipping\Method
     */
    protected function getShippingMethod($serviceCode, $countryCode, $availableMethods)
    {
        $result = null;

        $originCode = $this->getOriginCode($countryCode);

        if (isset($this->upsServices[$serviceCode][$originCode])) {

            $code = $this->upsServices[$serviceCode][$originCode];

            // Check if method with $code exists in $availableMethods
            if (!empty($availableMethods) && is_array($availableMethods)) {

                foreach ($availableMethods as $method) {

                    if ($method->getCode() == $code) {
                        $result = $method;
                        break;
                    }
                }
            }
        }

        return $result;
    }

    /**
     * Check if COD is allowed
     *
     * @param boolean $section    Section type (package | shipment)
     * @param string  $srcCountry Origin country code
     * @param string  $dstCountry Destination country code
     *
     * @return boolean
     */
    protected function isCODAllowed($section, $srcCountry, $dstCountry)
    {
        $result = false;

        // Define rules
        $rules = array(
            'package'  => array(
                array(
                    'src' => array('US', 'PR'),
                    'dst' => array('US', 'PR'),
                ),
                array(
                    'src' => array('CA'),
                    'dst' => array('CA', 'US'),
                ),
            ),
            'shipment' => array(
                array(
                    'src' => array('EU'),
                    'dst' => array('EU'),
                ),
            ),
        );

        $rules['all'] = array_merge($rules['package'], $rules['shipment']);

        $srcCode = $this->getOriginCode($srcCountry);
        $dstCode = $this->getOriginCode($dstCountry);

        if (isset($rules[$section])) {

            foreach($rules[$section] as $rule) {

                if (in_array($srcCode, $rule['src']) && (empty($rule['dst']) || in_array($dstCode, $rule['dst']))) {

                    // COD is allowed

                    $result = true;

                    if ('shipment' == $section && '01' != \XLite\Core\Config::getInstance()->XC->UPS->pickup_type) {
                        // Shipment COD is only available for EU origin countries
                        // and for shipper's account type Daily Pickup and Drop Shipping
                        $result = false;
                    } elseif ('package' == $section && 'CA' == $srcCode && 'US' == $dstCode && '01' == \XLite\Core\Config::getInstance()->XC->UPS->packaging_type) {
                        // CA to US COD is not allowed for package Letter/ Envelope
                        $result = false;
                    }

                    break;
                }
            }
        }

        return $result;
    }
}
