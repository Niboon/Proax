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

namespace XLite\Module\CDev\Paypal\View\Model;

/**
 * ExpressCheckout
 */
class ExpressCheckout extends \XLite\Module\CDev\Paypal\View\Model\ASettings
{
    /**
     * Schema of the "Your account settings" section
     *
     * @var array
     */
    protected $schemaAccount = array(
        'section_email_sep' => array(
            self::SCHEMA_CLASS    => 'XLite\Module\CDev\Paypal\View\FormField\Separator\Selectable',
            self::SCHEMA_LABEL    => 'E-Mail address to receive PayPal payment',
            \XLite\Module\CDev\Paypal\View\FormField\Separator\Selectable::PARAM_GROUP_NAME => 'api_type',
            \XLite\Module\CDev\Paypal\View\FormField\Separator\Selectable::PARAM_SELECTED => true,
        ),
        'email' => array(
            self::SCHEMA_CLASS    => '\XLite\View\FormField\Input\Text\Email',
            self::SCHEMA_LABEL    => '',
            self::SCHEMA_HELP     => 'Start accepting Express Checkout payments immediately by simply plugging in the email address where you would like to receive payments.',
            self::SCHEMA_REQUIRED => true,
            \XLite\View\FormField\AFormField::PARAM_USE_COLON => false,
        ),
        'section_api_sep' => array(
            self::SCHEMA_CLASS    => 'XLite\Module\CDev\Paypal\View\FormField\Separator\Selectable',
            self::SCHEMA_LABEL    => 'API credentials for payments and post-checkout operations',
            self::SCHEMA_HELP     => 'Can be set up later',
            \XLite\Module\CDev\Paypal\View\FormField\Separator\Selectable::PARAM_GROUP_NAME => 'api_type',
            \XLite\Module\CDev\Paypal\View\FormField\Separator\Selectable::PARAM_SELECTED => false,
        ),
        'api_solution' => array(
            self::SCHEMA_CLASS    => '\XLite\Module\CDev\Paypal\View\FormField\Select\APIType',
            self::SCHEMA_LABEL    => 'Paypal API solution',
            self::SCHEMA_HELP     => 'PayPal API (Merchant API) will work for most merchants; however, some merchants may have access only to Payflow API.',
            \XLite\View\FormField\AFormField::PARAM_WRAPPER_CLASS => 'section_api',
        ),
        'partner' => array(
            self::SCHEMA_CLASS    => '\XLite\View\FormField\Input\Text',
            self::SCHEMA_LABEL    => 'Partner name',
            self::SCHEMA_HELP     => 'Your partner name is PayPal',
            self::SCHEMA_REQUIRED => true,
            \XLite\View\FormField\AFormField::PARAM_WRAPPER_CLASS => 'section_api',
            self::SCHEMA_DEPENDENCY => array(
                self::DEPENDENCY_SHOW => array(
                    'api_solution' => 'payflow',
                ),
            ),
        ),
        'vendor' => array(
            self::SCHEMA_CLASS    => '\XLite\View\FormField\Input\Text',
            self::SCHEMA_LABEL    => 'Merchant login',
            self::SCHEMA_HELP     => 'This is the login name you created when signing up for PayPal Payments Advanced.',
            self::SCHEMA_REQUIRED => true,
            \XLite\View\FormField\AFormField::PARAM_WRAPPER_CLASS => 'section_api',
            self::SCHEMA_DEPENDENCY => array(
                self::DEPENDENCY_SHOW => array(
                    'api_solution' => 'payflow',
                ),
            ),
        ),
        'user' => array(
            self::SCHEMA_CLASS    => '\XLite\View\FormField\Input\Text',
            self::SCHEMA_LABEL    => 'User',
            self::SCHEMA_HELP     => 'PayPal recommends entering a User Login here instead of your Merchant Login',
            self::SCHEMA_REQUIRED => true,
            \XLite\View\FormField\AFormField::PARAM_WRAPPER_CLASS => 'section_api',
            self::SCHEMA_DEPENDENCY => array(
                self::DEPENDENCY_SHOW => array(
                    'api_solution' => 'payflow',
                ),
            ),
        ),
        'pwd' => array(
            self::SCHEMA_CLASS    => '\XLite\View\FormField\Input\Text',
            self::SCHEMA_LABEL    => 'Password',
            self::SCHEMA_HELP     => 'This is the password you created when signing up for PayPal Payments Advanced or the password you created for API calls.',
            self::SCHEMA_REQUIRED => true,
            \XLite\View\FormField\AFormField::PARAM_WRAPPER_CLASS => 'section_api',
            self::SCHEMA_DEPENDENCY => array(
                self::DEPENDENCY_SHOW => array(
                    'api_solution' => 'payflow',
                ),
            ),
        ),
        'api_username' => array(
            self::SCHEMA_CLASS    => '\XLite\View\FormField\Input\Text',
            self::SCHEMA_LABEL    => 'API access username',
            self::SCHEMA_HELP     => '',
            self::SCHEMA_REQUIRED => true,
            \XLite\View\FormField\AFormField::PARAM_WRAPPER_CLASS => 'section_api',
            self::SCHEMA_DEPENDENCY => array(
                self::DEPENDENCY_SHOW => array(
                    'api_solution' => 'paypal',
                ),
            ),
        ),
        'api_password' => array(
            self::SCHEMA_CLASS    => '\XLite\View\FormField\Input\Text',
            self::SCHEMA_LABEL    => 'API access password',
            self::SCHEMA_HELP     => '',
            self::SCHEMA_REQUIRED => true,
            \XLite\View\FormField\AFormField::PARAM_WRAPPER_CLASS => 'section_api',
            self::SCHEMA_DEPENDENCY => array(
                self::DEPENDENCY_SHOW => array(
                    'api_solution' => 'paypal',
                ),
            ),
        ),
        'auth_method' => array(
            self::SCHEMA_CLASS    => '\XLite\Module\CDev\Paypal\View\FormField\Select\APIAuthMethod',
            self::SCHEMA_LABEL    => 'Use PayPal authentication method',
            self::SCHEMA_HELP     => '',
            \XLite\View\FormField\AFormField::PARAM_WRAPPER_CLASS => 'section_api',
            self::SCHEMA_DEPENDENCY => array(
                self::DEPENDENCY_SHOW => array(
                    'api_solution' => 'paypal',
                ),
            ),
        ),
        'signature' => array(
            self::SCHEMA_CLASS    => '\XLite\View\FormField\Input\Text',
            self::SCHEMA_LABEL    => 'API signature',
            self::SCHEMA_HELP     => '',
            self::SCHEMA_REQUIRED => true,
            \XLite\View\FormField\AFormField::PARAM_WRAPPER_CLASS => 'section_api',
            self::SCHEMA_DEPENDENCY => array(
                self::DEPENDENCY_SHOW => array(
                    'api_solution'    => 'paypal',
                    'auth_method' => 'signature',
                ),
            ),
        ),
        'certificate' => array(
            self::SCHEMA_CLASS    => '\XLite\View\FormField\Input\Text',
            self::SCHEMA_LABEL    => 'API certificate filename',
            self::SCHEMA_HELP     => '',
            self::SCHEMA_REQUIRED => true,
            \XLite\View\FormField\AFormField::PARAM_WRAPPER_CLASS => 'section_api',
            self::SCHEMA_DEPENDENCY => array(
                self::DEPENDENCY_SHOW => array(
                    'api_solution'    => 'paypal',
                    'auth_method' => 'certificate',
                ),
            ),
        ),
    );

    /**
     * Save current form reference and initialize the cache
     *
     * @param array $params   Widget params OPTIONAL
     * @param array $sections Sections list OPTIONAL
     */
    public function __construct(array $params = array(), array $sections = array())
    {
        parent::__construct($params, $sections);

        $this->schemaAdditional['transaction_type'][\XLite\View\FormField\AFormField::PARAM_WRAPPER_CLASS]
            = 'section_api';
    }

    /**
     * Get JS files
     *
     * @return array
     */
    public function getJSFiles()
    {
        $list = parent::getJSFiles();
        $list[] = 'modules/CDev/Paypal/settings/ExpressCheckout/controller.js';

        return $list;
    }

    /**
     * Retrieve property from the request or from  model object
     *
     * @param string $name Field/property name
     *
     * @return mixed
     */
    public function getDefaultFieldValue($name)
    {
        switch ($name) {
            case 'section_email_sep':
                $value = 'email';
                break;

            case 'section_api_sep':
                $value = 'api';
                break;

            default:
                $value = parent::getDefaultFieldValue($name);
                break;
        }

        return $value;
    }

    /**
     * Perform some operations when creating fields list by schema
     *
     * @param string $name Node name
     * @param array  $data Field description
     *
     * @return array
     */
    protected function getFieldSchemaArgs($name, array $data)
    {
        $data = parent::getFieldSchemaArgs($name, $data);
        $method = $this->getModelObject();

        switch ($name) {
            case 'section_email_sep':
                $processor = $method->getProcessor();
                if (
                    'api' !== $method->getSetting('api_type')
                    || !$processor->isConfigured($method)
                ) {
                    $value = true;
                } else {
                    $value = false;
                }

                $data[\XLite\Module\CDev\Paypal\View\FormField\Separator\Selectable::PARAM_SELECTED] = $value;
                break;

            case 'section_api_sep':
                $processor = $method->getProcessor();
                if (
                    'api' === $method->getSetting('api_type')
                    && $processor->isConfigured($method)
                ) {
                    $value = true;
                } else {
                    $value = false;
                }

                $data[\XLite\Module\CDev\Paypal\View\FormField\Separator\Selectable::PARAM_SELECTED] = $value;
                break;

            default:
                break;
        }

        return $data;
    }

    /**
     * Prepare and save passed data
     *
     * @param array       $data Passed data OPTIONAL
     * @param string|null $name Index in request data array (optional) OPTIONAL
     *
     * @return void
     */
    protected function defineRequestData(array $data = array(), $name = null)
    {
        parent::defineRequestData($data, $name);

        $this->requestData['api_type'] = \XLite\Core\Request::getInstance()->api_type ?: 'email';
    }

    /**
     * Check required validation
     *
     * @param array $data Widget params
     *
     * @return array
     */
    protected function prepareFieldParamsEmail($data)
    {
        if ('api' == \XLite\Core\Request::getInstance()->api_type) {
            $data[static::SCHEMA_REQUIRED] = false;
        }

        return $data;
    }

    /**
     * Check required validation
     *
     * @param array $data Widget params
     *
     * @return array
     */
    protected function prepareFieldParamsPartner($data)
    {
        if ('email' == \XLite\Core\Request::getInstance()->api_type) {
            $data[static::SCHEMA_REQUIRED] = false;
        }

        return $data;
    }

    /**
     * Check required validation
     *
     * @param array $data Widget params
     *
     * @return array
     */
    protected function prepareFieldParamsVendor($data)
    {
        if ('email' == \XLite\Core\Request::getInstance()->api_type) {
            $data[static::SCHEMA_REQUIRED] = false;
        }

        return $data;
    }

    /**
     * Check required validation
     *
     * @param array $data Widget params
     *
     * @return array
     */
    protected function prepareFieldParamsUser($data)
    {
        if ('email' == \XLite\Core\Request::getInstance()->api_type) {
            $data[static::SCHEMA_REQUIRED] = false;
        }

        return $data;
    }

    /**
     * Check required validation
     *
     * @param array $data Widget params
     *
     * @return array
     */
    protected function prepareFieldParamsPwd($data)
    {
        if ('email' == \XLite\Core\Request::getInstance()->api_type) {
            $data[static::SCHEMA_REQUIRED] = false;
        }

        return $data;
    }

    /**
     * Check required validation
     *
     * @param array $data Widget params
     *
     * @return array
     */
    protected function prepareFieldParamsApiUsername($data)
    {
        if ('email' == \XLite\Core\Request::getInstance()->api_type) {
            $data[static::SCHEMA_REQUIRED] = false;
        }

        return $data;
    }

    /**
     * Check required validation
     *
     * @param array $data Widget params
     *
     * @return array
     */
    protected function prepareFieldParamsApiPassword($data)
    {
        if ('email' == \XLite\Core\Request::getInstance()->api_type) {
            $data[static::SCHEMA_REQUIRED] = false;
        }

        return $data;
    }

    /**
     * Check required validation
     *
     * @param array $data Widget params
     *
     * @return array
     */
    protected function prepareFieldParamsSignature($data)
    {
        if ('email' == \XLite\Core\Request::getInstance()->api_type) {
            $data[static::SCHEMA_REQUIRED] = false;
        }

        return $data;
    }

    /**
     * Check required validation
     *
     * @param array $data Widget params
     *
     * @return array
     */
    protected function prepareFieldParamsCertificate($data)
    {
        if ('email' == \XLite\Core\Request::getInstance()->api_type) {
            $data[static::SCHEMA_REQUIRED] = false;
        }

        return $data;
    }

    /**
     * Perform some actions on success
     *
     * @return void
     */
    protected function postprocessSuccessActionUpdate()
    {
        parent::postprocessSuccessActionUpdate();

        $model = $this->getModelObject();
        $processor = $model->getProcessor();

        $merchantId = $processor->retrieveMerchantId();

        $model->setSetting('merchantId', $merchantId);
        $model->update();

        if (\XLite\View\FormField\Select\TestLiveMode::LIVE === $model->getSetting('mode')) {
            \XLite\Core\Database::getRepo('XLite\Model\Config')->createOption(
                array(
                    'category' => 'CDev\Paypal',
                    'name'     => 'show_admin_welcome',
                    'value'    => 'N',
                )
            );
        }
    }
}
