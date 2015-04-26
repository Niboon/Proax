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

namespace XLite\Model\Shipping\Processor;

/**
 * Shipping processor model
 */
abstract class AProcessor extends \XLite\Base\SuperClass
{
    /**
     * Unique processor Id
     *
     * @var string
     */
    protected $processorId = null;

    /**
     * Processor's shipping methods
     *
     * @var array
     */
    protected $methods = null;

    /**
     * Url of shipping server for rates calculation
     *
     * @var string
     */
    protected $apiURL = null;

    /**
     * Log of request/response pairs during communitation with a shipping server
     *
     * @var array
     */
    protected $apiCommunicationLog = null;

    /**
     * Error message
     *
     * @var string
     */
    protected $errorMsg = null;


    /**
     * Returns processor name
     *
     * @return string
     */
    abstract public function getProcessorName();

    /**
     * Returns processor's shipping methods rates
     *
     * @param array|\XLite\Logic\Order\Modifier\Shipping $inputData   Shipping order modifier or array of data
     * @param boolean                                    $ignoreCache Flag: if true then do not get rates
     *                                                                from cache OPTIONAL
     *
     * @return array
     */
    abstract public function getRates($inputData, $ignoreCache = false);

    /**
     * Define public constructor
     */
    public function __construct()
    {
    }

    /**
     * Returns processor's shipping methods
     *
     * @return array
     */
    public function getShippingMethods()
    {
        if (!isset($this->methods)) {
            $this->methods = \XLite\Core\Database::getRepo('XLite\Model\Shipping\Method')
                ->findMethodsByProcessor($this->getProcessorId());
        }

        return $this->methods;
    }

    /**
     * Returns processor Id
     *
     * @return string
     */
    public function getProcessorId()
    {
        return $this->processorId;
    }

    /**
     * Returns true if shipping methods names may be modified by admin
     *
     * @return boolean
     */
    public function isMethodNamesAdjustable()
    {
        return true;
    }

    /**
     * Returns true if shipping methods can be removed by admin
     *
     * @return boolean
     */
    public function isMethodDeleteEnabled()
    {
        return false;
    }

    /**
     * Returns an API URL
     *
     * @return string
     */
    public function getApiURL()
    {
        return $this->apiURL;
    }

    /**
     * Returns an API communication log
     *
     * @return array
     */
    public function getApiCommunicationLog()
    {
        return $this->apiCommunicationLog;
    }

    /**
     * Returns $errorMsg
     *
     * @return string
     */
    public function getErrorMsg()
    {
        return $this->errorMsg;
    }

    /**
     * Write transaction log
     *
     * @return void
     */
    public function logTransaction()
    {
        \XLite\Logger::getInstance()->log($this->getLogMessage());
    }

    /**
     * Get packages for shipment
     *
     * @param \XLite\Logic\Order\Modifier\Shipping $modifier Shipping modifier
     *
     * @return array
     */
    public function getPackages($modifier)
    {
        return \XLite\Core\Package::getInstance()->getPackages($modifier->getItems(), $this->getPackageLimits());
    }

    /**
     * Defines whether the form must be used for tracking information.
     * The 'getTrackingInformationURL' result will be used as tracking link instead
     *
     * @param string $trackingNumber Tracking number value
     *
     * @return boolean
     */
    public function isTrackingInformationForm($trackingNumber)
    {
        return true;
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
        return null;
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
        return 'get';
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
        return array();
    }

    /**
     * Check - processor is configured or not
     *
     * @return boolean
     */
    public function isPublicConfigured()
    {
        return method_exists($this, 'isConfigured')
            ? $this->isConfigured()
            : true;
    }

    /**
     * Get key hash
     *
     * @param string $key Key
     *
     * @return string
     */
    protected function getKeyHash($key)
    {
        return md5($key);
    }

    /**
     * getDataFromCache
     *
     * @param string $key Key of a cache cell
     *
     * @return mixed
     */
    protected function getDataFromCache($key)
    {
        $data = null;
        $cacheDriver = \XLite\Core\Database::getCacheDriver();
        $key = $this->getKeyHash($key);

        if ($cacheDriver->contains($key)) {
            $data = $cacheDriver->fetch($key);
        }

        return $data;
    }

    /**
     * saveDataInCache
     *
     * @param string  $key      Key of a cache cell
     * @param mixed   $data     Data object for saving in the cache
     * @param integer $lifeTime The cache lifetime.
     *
     * @return void
     */
    protected function saveDataInCache($key, $data, $lifeTime = 0)
    {
        \XLite\Core\Database::getCacheDriver()->save($this->getKeyHash($key), $data, $lifeTime);
    }

    /**
     * getLogMessage
     *
     * @return string
     */
    protected function getLogMessage()
    {
        return sprintf('[%s] Error: %s', $this->getProcessorName(), $this->getErrorMsg());
    }

    /**
     * Get default package limits
     *
     * @return array
     */
    protected function getPackageLimits()
    {
        return array();
    }
}
