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
 * Shipping settings management page controller
 */
class ShippingSettings extends \XLite\Controller\Admin\AAdmin
{
    /**
     * Define the actions with no secure token
     *
     * @return array
     */
    public static function defineFreeFormIdActions()
    {
        return array_merge(parent::defineFreeFormIdActions(), array('test'));
    }

    /**
     * Returns shipping options
     *
     * @return array
     */
    public function getOptions()
    {
        return \XLite\Core\Database::getRepo('\XLite\Model\Config')
            ->findByCategoryAndVisible($this->getOptionsCategory());
    }

    /**
     * Return the current page title (for the content area)
     *
     * @return string
     */
    public function getTitle()
    {
        return static::t('Default customer address');
    }

    /**
     * Do action 'Update'
     *
     * @return void
     */
    public function doActionUpdate()
    {
        $postedData = \XLite\Core\Request::getInstance()->getData();
        $options    = \XLite\Core\Database::getRepo('\XLite\Model\Config')
            ->findBy(array('category' => $this->getOptionsCategory()));
        $isUpdated  = false;

        foreach ($options as $option) {
            $name = $option->getName();
            $type = $option->getType();

            if (isset($postedData[$name])) {
                $postedData[$name] = ('checkbox' === $type)
                    ? ('' === $postedData[$name] ? 'N' : 'Y')
                    : $postedData[$name];

                $option->setValue($postedData[$name]);
                $isUpdated = true;
                \XLite\Core\Database::getEM()->persist($option);
            }
        }

        if ($isUpdated) {
            \XLite\Core\Database::getEM()->flush();
        }
    }

    /**
     * getStateById
     *
     * @param mixed $stateId State id
     *
     * @return \XLite\Model\State
     */
    public function getStateById($stateId)
    {
        return \XLite\Core\Database::getRepo('XLite\Model\State')->find($stateId);
    }

    /**
     * Get schema of an array for test rates routine
     *
     * @return array
     */
    protected function getTestRatesSchema()
    {
        return array(
            'weight' => \XLite\View\Model\TestRates::SCHEMA_FIELD_WEIGHT,
            'subtotal' => \XLite\View\Model\TestRates::SCHEMA_FIELD_SUBTOTAL,
            'srcAddress' => array(
                'city' => \XLite\View\Model\TestRates::SCHEMA_FIELD_SRC_CITY,
                'state' => \XLite\View\Model\TestRates::SCHEMA_FIELD_SRC_STATE,
                'country' => \XLite\View\Model\TestRates::SCHEMA_FIELD_SRC_COUNTRY,
                'zipcode' => \XLite\View\Model\TestRates::SCHEMA_FIELD_SRC_ZIPCODE,
            ),
            'dstAddress' => array(
                'city' => \XLite\View\Model\TestRates::SCHEMA_FIELD_DST_CITY,
                'state' => \XLite\View\Model\TestRates::SCHEMA_FIELD_DST_STATE,
                'country' => \XLite\View\Model\TestRates::SCHEMA_FIELD_DST_COUNTRY,
                'zipcode' => \XLite\View\Model\TestRates::SCHEMA_FIELD_DST_ZIPCODE,
                'type' => \XLite\View\Model\TestRates::SCHEMA_FIELD_DST_TYPE,
            ),
            'cod_enabled' => \XLite\View\Model\TestRates::SCHEMA_FIELD_COD_ENABLED,
        );
    }

    /**
     * Recursive routine to prepare input data for test rates calculation
     *
     * @param array $schema  Input data schema
     * @param array &$errors Array of fields which are not set
     *
     * @return array
     */
    protected function prepareTestDataFields(array $schema, &$errors)
    {
        $data = array();

        $postedData = \XLite\Core\Request::getInstance()->getData();

        foreach ($schema as $k => $v) {
            if (is_array($v)) {
                $data[$k] = $this->prepareTestDataFields($v, $errors);

            } elseif ('cod_enabled' == $k) {
                $data[$k] = !empty($postedData[$v]);

            } elseif (isset($postedData[$v])) {
                $methodName = 'prepareTestDataField' . ucfirst($k);
                $data[$k] = (method_exists($this, $methodName))
                    ? $this->$methodName($postedData[$v])
                    : $postedData[$v];

            } else {
                $errors[] = $v;
            }
        }

        return $data;
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
        return $this->postprocessTestDataFields($this->prepareTestDataFields($schema, $errors));
    }

    /**
     * Prepare specific value for test rates routine
     *
     * @param string $value Value
     *
     * @return float
     */
    protected function prepareTestDataFieldWeight($value)
    {
        return 0 < doubleval($value) ? doubleval($value) : 1;
    }

    /**
     * Prepare specific value for test rates routine
     *
     * @param string $value Value
     *
     * @return float
     */
    protected function prepareTestDataFieldSubtotal($value)
    {
        return 0 < doubleval($value) ? doubleval($value) : 1;
    }

    /**
     * Prepare specific value for test rates routine
     *
     * @param string $value Value
     *
     * @return string
     */
    protected function prepareTestDataFieldState($value)
    {
        return \XLite\Core\Database::getRepo('XLite\Model\State')->getCodeById($value);
    }

    /**
     * Postprocess input data for test rates routine
     *
     * @param array $data Prepared input data array
     *
     * @return array
     */
    protected function postprocessTestDataFields($data)
    {
        $postedData = \XLite\Core\Request::getInstance()->getData();

        // Sanitize the state value
        foreach (array('srcAddress', 'dstAddress') as $address) {
            $isValidState = false;

            if (isset($data[$address]['state'])) {
                if (!empty($data[$address]['state']) && !empty($data[$address]['country'])) {
                    $isValidState = (boolean) \XLite\Core\Database::getRepo('XLite\Model\State')
                        ->findOneByCountryAndCode($data[$address]['country'], $data[$address]['state']);
                }

                if (!$isValidState) {
                    $data[$address]['state'] = 'srcAddress' == $address
                        ? $postedData[\XLite\View\Model\TestRates::SCHEMA_FIELD_SRC_CUSTOM_STATE]
                        : $postedData[\XLite\View\Model\TestRates::SCHEMA_FIELD_DST_CUSTOM_STATE];
                }
            }
        }

        return $data;
    }

    /**
     * Get shipping processor
     *
     * @return object
     */
    protected function getProcessor()
    {
        return null;
    }

    /**
     * doActionTest
     *
     * @return void
     */
    protected function doActionTest()
    {
        // Generate input data array for rates calculator

        $errorFields = array();

        $data = $this->getTestRatesData($this->getTestRatesSchema(), $errorFields);

        static::sendHeaders();

        $this->printInputData($data);

        $processor = $this->getProcessor();

        if (empty($errorFields) && isset($processor)) {
            // Get rates

            $startTime = microtime(true);

            $rates = $processor->getRates($data, true);

            $proceedTime = microtime(true) - $startTime;

            $errorMsg = $processor->getErrorMsg();

            if (!isset($errorMsg)) {
                if (!empty($rates)) {
                    // Rates have been successfully calculated, display them
                    echo ('<h2>' . static::t('Rates') . ':</h2>');

                    foreach ($rates as $rate) {
                        echo (sprintf('%s (%0.2f)<br>', $rate->getMethodName(), $rate->getBaseRate()));
                    }

                    echo (sprintf('<br /><i>Time elapsed: %0.3f seconds</i>', $proceedTime));

                } else {
                    $errorMsg = static::t(
                        'There are no rates available for specified source/destination and/or package measurements/weight.'
                    );
                }
            }

        } else {
            $errorMsg = static::t(
                'The following expected input data have wrong format or empty: X',
                array('fields' => implode(', ', $errorFields))
            );
        }

        if (!empty($errorMsg)) {
            echo ('<h3>' . $errorMsg . '</h3>');
        }

        if (isset($processor)) {
            $cmLog = $processor->getApiCommunicationLog();
        }

        if (isset($cmLog)) {
            echo ('<h2>' . static::t('Communication log') . '</h2>');

            ob_start();

            foreach ($cmLog as $log) {
                print_r($log);
                echo (PHP_EOL . '<hr />' . PHP_EOL);
            }

            $msg = '<pre>' . ob_get_contents() . '</pre>';
            ob_clean();

            echo ($msg);
        }

        die ();
    }

    /**
     * Print input data
     *
     * @param array $data Input data array
     *
     * @return void
     */
    protected function printInputData($data)
    {
        echo ('<h2>' . static::t('Input data') . '</h2>');

        ob_start();
        print_r($data);
        $dataStr = '<pre>' . ob_get_contents() . '</pre>';
        ob_clean();

        echo ($dataStr);
    }

    /**
     * Returns options category
     *
     * @return string
     */
    protected function getOptionsCategory()
    {
        return \XLite\Model\Config::SHIPPING_CATEGORY;
    }
}
