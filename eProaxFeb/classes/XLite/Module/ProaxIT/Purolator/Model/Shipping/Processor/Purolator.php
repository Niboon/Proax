<?php
// vim: set ts=4 sw=4 sts=4 et:

namespace XLite\Module\ProaxIT\Purolator\Model\Shipping\Processor;

use SoapClient;
use SoapHeader;
use stdClass;

/**
 * Shipping processor model
 * API: Purolator Web Services API (SOAP)
 */
class Purolator extends \XLite\Model\Shipping\Processor\AProcessor
{
    const LAVAL_POSTAL_CODE = "H7P0C9";
    const OAKVILLE_POSTAL_CODE = "L6H5S1";
    /**
     * Unique processor Id
     *
     * @var string
     */
    protected $processorId = 'purolator';

    /**
     * getProcessorName
     *
     * @return string
     */
    public function getProcessorName()
    {
        return 'Purolator';
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
        return 'https://webservices.purolator.com/PWS/V1/Estimating/EstimatingService.asmx';
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
            } else {
                $this->errorMsg = 'Wrong input data';
            }
        } elseif (\XLite\Module\ProaxIT\Purolator\Main::isStrictMode()) {
            $this->errorMsg = 'Purolator module is not configured';
        }
        // Return shipping rates list
        return $rates;
    }

    /**
     * Returns true if Purolator module is configured
     *
     * @return boolean
     */
    protected function isConfigured()
    {
        return \XLite\Core\Config::getInstance()->ProaxIT->Purolator->accessKey
            && \XLite\Core\Config::getInstance()->ProaxIT->Purolator->accessPass;
    }

    /**
     * prepareInputData
     *
     * @param array|\XLite\Logic\Order\Modifier\Shipping $inputData Shipping order modifier (from order) or array of input data (from test controller)
     *
     * @return mixed
     */
    protected function prepareInputData($inputData)
    {
        if ($inputData instanceOf \XLite\Logic\Order\Modifier\Shipping) {
            $data = $this->prepareDataFromModifier($inputData);
        } else {
            $data = $inputData;
        }

        if (!empty($data['packages'])) {
            $data['total'] = 0;
            foreach ($data['packages'] as $key => $package) {
                $wUnit = \XLite\Core\Config::getInstance()->Units->weight_unit;
                $data['packages'][$key]['weight'] = \XLite\Core\Converter::convertWeightUnits(
                    $package['weight'],
                    $wUnit,
                    $wUnit
                );
                $data['packages'][$key]['weight'] = max(0.1, $data['packages'][$key]['weight']);
                $data['packages'][$key]['subtotal'] = $this->getPackagesSubtotal($package['subtotal']);
                $data['total'] += $data['packages'][$key]['subtotal'];
            }
        } else {
            $data = array();
            $this->errorMsg = 'There are no defined packages to delivery';
        }
        return $data;
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

        $config = \XLite\Core\Config::getInstance();
        $data['srcAddress'] = array(
            'city' => $config->Company->location_city,
            'zipcode' => $config->Company->location_zipcode,
            'country' => $config->Company->location_country,
        );

        if (isset($config->Company->location_state)) {
            $data['srcAddress']['state'] = \XLite\Core\Database::getRepo('XLite\Model\State')->getCodeById(
                $config->Company->location_state
            );
        }

        $data['dstAddress'] = \XLite\Model\Shipping::getInstance()->getDestinationAddress($modifier);

        if (isset($data['dstAddress']['state'])) {
            $data['dstAddress']['state'] = \XLite\Core\Database::getRepo('XLite\Model\State')->getCodeById(
                $data['dstAddress']['state']
            );
        }
        $data['packages'] = $this->getPackages($modifier);
        return $data;
    }

    /**
     * Get quick estimated rates from Purolator WebServices
     *
     * @param mixed   $data        Can be either \XLite\Model\Order instance or an array
     * @param boolean $ignoreCache Flag: if true then do not get rates from cache
     *
     * @return array
     */
    protected function doQuery($data, $ignoreCache)
    {
        $rates = array();
        $postURL = $this->getApiURL();

        // Get all available rates
        $availableMethods = \XLite\Core\Database::getRepo('XLite\Model\Shipping\Method')
            ->findMethodsByProcessor($this->processorId, !$ignoreCache);

        $purolatorOptions = \XLite\Core\Config::getInstance()->ProaxIT->Purolator;
        $billingAccount = $purolatorOptions->billingAccount;

        if ($availableMethods) {
            $client = $this->createPWSSOAPClient();
            //Populate the Billing Account Number
            $request = new stdClass();
            $request->BillingAccountNumber = $billingAccount;
            $country = $data['dstAddress']['country'];
            $request->ReceiverAddress->Province = $data['dstAddress']['state'];
            // If testing, Use Test params for source postal code
            if ($ignoreCache === true) {
                //Populate the Origin Information
                $request->SenderPostalCode = $data['srcAddress']['zipcode'];
            } else {
                // If not testing use East and West Canada Shipping Logic
                if ($country=="CA"){
                    $shipToEastOfOntario = strcmp(strtoupper(substr($data['dstAddress']['zipcode'], 0, 1)), "K") < 0;
                    $request->SenderPostalCode = ($shipToEastOfOntario ? self::LAVAL_POSTAL_CODE : self::OAKVILLE_POSTAL_CODE);
                }
            }
            $request->ReceiverAddress->Country = $data['dstAddress']['country'];
            $request->ReceiverAddress->PostalCode = $data['dstAddress']['zipcode'];
            //Populate the Package Information
            $request->PackageType = "CustomerPackaging";
            //Populate the Shipment Weight
            foreach ($data['packages'] as $package){
                $weight = $package['weight'];
                $request->TotalWeight->Value += max($weight, 1360.78) / 1000; // 3 pounds min. (3 pounds = 1360.78 grams)
            }
            $request->TotalWeight->WeightUnit = "kg";

            try {
                if (!$ignoreCache) {
                    $cachedRates = $this->getDataFromCache(json_encode($request));
                }
                if (isset($cachedRates)) {
                    $result = $cachedRates;
                } else {
                    //Execute the request and capture the response
                    $result = $client->GetQuickEstimate($request);
                    $this->saveDataInCache(json_encode($request), $result);
                }
                $errors = $result->ResponseInformation->Errors;
                if (empty($errors)) {
                    $this->errorMsg = '[' . $errors->Error->Code . ']' . $errors->Error->Description;
                }

                if (!isset($this->errorMsg)) {
                    $response = $this->parseResponse($result, $country);
                } else {
                    $response = array();
                }

                // Save communication log for test request only (ignoreCache is set for test requests only)
                if ($ignoreCache === true) {
                    $this->apiCommunicationLog[] = array(
                        'post URL' => $postURL,
                        'request'  => $request,
                        'response' => $result
                    );
                }

                if (!isset($this->errorMsg)) {
                    foreach ($response as $serviceID => $price) {
                        $method = $this->getShippingMethod(
                            $serviceID,
                            $availableMethods
                        );
                        if ($method) {
                            $rate = new \XLite\Model\Shipping\Rate();
                            $rate->setBaseRate($price);
                            $rate->setMethod($method);
                            $rates[] = $rate;
                        }
                    }
                }
            } catch (\Exception $e) {
                $this->errorMsg = 'Exception: ' . $e->getMessage();
            }
        }

        return $rates;
    }

    /**
     * Parses response and returns an associative array
     *
     * @param stdClass $response SOAP Envelope response of Purolator Webservice API
     *
     * @param String $country
     * @return array $result with shipping cost estimates and services
     */
    protected function parseResponse($response, $country)
    {
        $result = array();

        //Determine the services and associated charges for this shipment
        if($response && $response->ShipmentEstimates->ShipmentEstimate)	{
            //Loop through each Service returned and display the ID and BasePrice
            foreach($response->ShipmentEstimates->ShipmentEstimate as $estimate)	  {
                if ($country == "CA" && $estimate->ServiceID == 'PurolatorGround') {
                    $result[$estimate->ServiceID] = max (12, ($estimate->BasePrice)*1.5);
                } else if ($country == "US" && $estimate->ServiceID == 'PurolatorExpressU.S.') {
                    // TODO: Currency conversion
                    $result[$estimate->ServiceID] = (max (20, ($estimate->BasePrice)*1.5));
                } else {
                    $result[$estimate->ServiceID] = max (20, ($estimate->BasePrice)*1.5);
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
        return round($subtotal, 2);
    }

    /**
     * Get shipping method from the list by service code
     *
     * @param string $serviceID      Service code returned by Purolator
     * @param array  $availableMethods Array of shipping methods objects gathered from database
     *
     * @return \XLite\Model\Shipping\Method
     */
    protected function getShippingMethod($serviceID, $availableMethods)
    {
        $result = null;
        // Check if method with $code exists in $availableMethods
        if (!empty($availableMethods) && is_array($availableMethods)) {

            foreach ($availableMethods as $method) {
                if ($method->getCode() == $serviceID) {
                    $result = $method;
                    break;
                }
            }
        }

        return $result;
    }

    /**
     * Create Purolator WS soap client
     *
     */
    protected function createPWSSOAPClient() {
        /** Purpose : Creates a SOAP Client in Non-WSDL mode with the appropriate authentication and
         *           header information
         **/
        $purolatorOptions = \XLite\Core\Config::getInstance()->ProaxIT->Purolator;

        $accessKey = $purolatorOptions->accessKey;
        $accessPass = $purolatorOptions->accessPass;
        //Set the parameters for the Non-WSDL mode SOAP communication with your Development/Production credentials
        $client = new \SoapClient( LC_DIR_MODULES . "ProaxIT/Purolator/Model/Shipping/Processor/EstimatingService.wsdl",
            array	(
                'trace'			=>	true,
                'location'	=>  $this->getApiURL(),
                'uri'				=>	"http://purolator.com/pws/datatypes/v1",
                'login'			=>	$accessKey,
                'password'	=>	$accessPass
            )
        );
        //Define the SOAP Envelope Headers
        $headers[] = new \SoapHeader ( 'http://purolator.com/pws/datatypes/v1',
            'RequestContext',
            array (
                'Version'           =>  '1.3',
                'Language'          =>  'en',
                'GroupID'           =>  'xxx',
                'RequestReference'  =>  'Request Reference'
            )
        );
        //Apply the SOAP Header to your client
        $client->__setSoapHeaders($headers);

        return $client;
    }
}
