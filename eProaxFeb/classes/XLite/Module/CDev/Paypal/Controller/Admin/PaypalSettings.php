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

namespace XLite\Module\CDev\Paypal\Controller\Admin;

/**
 * Paypal settings controller
 */
class PaypalSettings extends \XLite\Controller\Admin\AAdmin
{
    /**
     * Controller parameters
     *
     * @var array
     */
    protected $params = array('method_id');

    /**
     * Paypal module string name for payment methods
     */
    const MODULE_NAME = 'CDev_Paypal';

    /**
     * Return the current page title (for the content area)
     *
     * @return string
     */
    public function getTitle()
    {
        $paymentMethod = $this->getPaymentMethod();

        return $paymentMethod
            ? $paymentMethod->getName()
            : '';
    }

    /**
     * Get method id from request
     *
     * @return integer
     */
    public function getMethodId()
    {
        return \XLite\Core\Request::getInstance()->method_id;
    }

    /**
     * Get payment method
     *
     * @return \XLite\Model\Payment\Method
     */
    public function getPaymentMethod()
    {
        $paymentMethod = $this->getMethodId()
            ? \XLite\Core\Database::getRepo('\XLite\Model\Payment\Method')->find($this->getMethodId())
            : null;

        return $paymentMethod && static::MODULE_NAME === $paymentMethod->getModuleName()
            ? $paymentMethod
            : null;
    }

    /**
     * Is In-Context Boarding SignUp available
     *
     * @return boolean
     */
    public function isInContextSignUpAvailable()
    {
        $api = \XLite\Module\CDev\Paypal\Main::getRESTAPIInstance();

        return $api->isInContextSignUpAvailable();
    }

    /**
     * Get SignUp url
     *
     * @return string
     */
    public function getSignUpUrl()
    {
        return $this->getPaymentMethod()->getReferralPageURL($this->getPaymentMethod());
    }

    /**
     * Do action 'Update'
     *
     * @return void
     */
    protected function doActionUpdate()
    {
        $this->getModelForm()->performAction('modify');
    }

    /**
     * Return class name for the controller main form
     *
     * @return string
     */
    protected function getModelFormClass()
    {
        $namespace = '\XLite\Module\CDev\Paypal\View\Model';
        $className = $this->getPaymentMethod()->getServiceName();

        return $namespace . '\\' . $className;
    }

    /**
     * doActionUpdateCredentials
     *
     * @return void
     */
    protected function doActionUpdateCredentials()
    {
        $request = \XLite\Core\Request::getInstance();
        $data = array();

        if ($request->merchantIdInPayPal) {
            $apiClient = new \XLite\Module\CDev\Paypal\Core\RESTAPI();

            $data = $apiClient->getMerchantCredentials(
                \XLite\Module\CDev\Paypal\Core\RESTAPI::PARTNER_ID,
                $request->merchantIdInPayPal
            );
        }

        $method = \XLite\Module\CDev\Paypal\Main::getPaymentMethod(\XLite\Module\CDev\Paypal\Main::PP_METHOD_EC);

        if ($data && isset($data['api_credentials']) && isset($data['api_credentials']['signature'])) {
            $credentials = $data['api_credentials']['signature'];

            $method->setSetting('api_type', 'api');
            $method->setSetting('api_solution', 'paypal');
            $method->setSetting('api_username', $credentials['api_user_name']);
            $method->setSetting('api_password', $credentials['api_password']);
            $method->setSetting('auth_method', 'signature');
            $method->setSetting('signature', $credentials['signature']);
            $method->setSetting('mode', 'live');
            $method->setSetting('merchantId', $data['merchant_id']);

            $method->update();

            \XLite\Core\Database::getRepo('XLite\Model\Config')->createOption(
                array(
                    'category' => 'CDev\Paypal',
                    'name'     => 'show_admin_welcome',
                    'value'    => 'N',
                )
            );

            \XLite\Core\TopMessage::getInstance()->addInfo(
                'Your API credentials have been successfully obtained from your PayPal account'
                . ' and saved for use by your X-Cart store.'
            );

        } else {
            \XLite\Core\TopMessage::getInstance()->addError(
                'Unfortunately, your API credentials could not be obtained from your PayPal account automatically.'
            );
        }

        $this->setReturnURL($this->buildURL('paypal_settings', '', array('method_id' => $method->getMethodId())));
    }
}
