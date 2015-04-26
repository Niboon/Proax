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
class PaypalCredit extends \XLite\View\Model\AModel
{
    const PARAM_PAYMENT_METHOD = 'paymentMethod';

    /**
     * Schema of the default section
     *
     * @var array
     */
    protected $schemaDefault = array(
        'enabled' => array(
            self::SCHEMA_CLASS    => '\XLite\View\FormField\Select\EnabledDisabled',
            self::SCHEMA_LABEL    => 'PayPal Credit is',
            self::SCHEMA_REQUIRED => false,
        ),
        'agreement' => array(
            self::SCHEMA_CLASS    => '\XLite\View\FormField\Input\Checkbox\Simple',
            self::SCHEMA_LABEL    => 'I agree with PayPal terms & conditions',
            self::SCHEMA_REQUIRED => true,
            self::SCHEMA_DEPENDENCY => array(
                self::DEPENDENCY_SHOW => array(
                    'enabled' => array('1'),
                ),
            ),
        ),
        'email' => array(
            self::SCHEMA_CLASS    => '\XLite\View\FormField\Input\Text\Email',
            self::SCHEMA_LABEL    => 'PayPal account email',
            self::SCHEMA_REQUIRED => true,
            self::SCHEMA_DEPENDENCY => array(
                self::DEPENDENCY_SHOW => array(
                    'enabled' => array('1'),
                ),
            ),
        ),
        'bannerOnHomePage' => array(
            self::SCHEMA_CLASS    => '\XLite\Module\CDev\Paypal\View\FormField\Select\BannerOnHomePage',
            self::SCHEMA_LABEL    => 'Banner on Home page',
            self::SCHEMA_REQUIRED => false,
            self::SCHEMA_DEPENDENCY => array(
                self::DEPENDENCY_SHOW => array(
                    'enabled' => array('1'),
                ),
            ),
        ),
        'bannerOnCategoryPages' => array(
            self::SCHEMA_CLASS    => '\XLite\Module\CDev\Paypal\View\FormField\Select\BannerOnCategoryPages',
            self::SCHEMA_LABEL    => 'Banner on Category pages',
            self::SCHEMA_REQUIRED => false,
            self::SCHEMA_DEPENDENCY => array(
                self::DEPENDENCY_SHOW => array(
                    'enabled' => array('1'),
                ),
            ),
        ),
        'bannerOnProductDetailsPages' => array(
            self::SCHEMA_CLASS    => '\XLite\Module\CDev\Paypal\View\FormField\Select\BannerOnProductDetailsPages',
            self::SCHEMA_LABEL    => 'Banner on Product details pages',
            self::SCHEMA_REQUIRED => false,
            self::SCHEMA_DEPENDENCY => array(
                self::DEPENDENCY_SHOW => array(
                    'enabled' => array('1'),
                ),
            ),
        ),
        'bannerOnCartPage' => array(
            self::SCHEMA_CLASS    => '\XLite\Module\CDev\Paypal\View\FormField\Select\BannerOnCartPage',
            self::SCHEMA_LABEL    => 'Banner on Cart page',
            self::SCHEMA_REQUIRED => false,
            self::SCHEMA_DEPENDENCY => array(
                self::DEPENDENCY_SHOW => array(
                    'enabled' => array('1'),
                ),
            ),
        ),
    );

    /**
     * Return model object to use
     *
     * @return \XLite\Model\Payment\Method
     */
    public function getModelObject()
    {
        return $this->getPaymentMethod();
    }

    /**
     * Return list of form fields for section default
     *
     * @return array
     */
    protected function getFormFieldsForSectionDefault()
    {
        $publisherId = $this->getModelObjectValue('publisherId');
        if (!empty($publisherId)) {
            $this->schemaDefault['email'][static::SCHEMA_COMMENT] = static::t(
                'Your PayPal Publisher ID is X',
                array('publisherId' => $publisherId)
            );
        }

        return $this->translateSchema('default');
    }

    /**
     * Return name of web form widget class
     *
     * @return string
     */
    protected function getFormClass()
    {
        return '\XLite\Module\CDev\Paypal\View\Form\PaypalCreditSettings';
    }

    /**
     * There is no object for settings
     *
     * @return \XLite\Model\AEntity
     */
    protected function getDefaultModelObject()
    {
        return null;
    }

    /**
     * Retrieve property from the model object
     *
     * @param mixed $name Field/property name
     *
     * @return mixed
     */
    protected function getModelObjectValue($name)
    {
        $paymentMethod = $this->getParam(static::PARAM_PAYMENT_METHOD);

        $value = $paymentMethod
            ? $paymentMethod->getSetting($name)
            : null;

        if ('email' == $name && empty($value)) {
            $value = $this->getExpressCheckoutEmail();
        }

        return $value;
    }

    /**
     * Get express checkout email
     *
     * @return string
     */
    protected function getExpressCheckoutEmail()
    {
        $expressCheckout = \XLite\Core\Database::getRepo('XLite\Model\Payment\Method')
            ->findOneBy(array('service_name' => 'ExpressCheckout'));

        return $expressCheckout->getSetting('email');
    }

    /**
     * defineWidgetParams
     *
     * @return void
     */
    protected function defineWidgetParams()
    {
        parent::defineWidgetParams();

        $this->widgetParams += array(
            self::PARAM_PAYMENT_METHOD => new \XLite\Model\WidgetParam\Object('Payment method', null),
        );
    }

    /**
     * Return list of the "Button" widgets
     *
     * @return array
     */
    protected function getFormButtons()
    {
        $result = parent::getFormButtons();

        $result['submit'] = new \XLite\View\Button\Submit(
            array(
                \XLite\View\Button\AButton::PARAM_LABEL    => 'Save changes',
                \XLite\View\Button\AButton::PARAM_BTN_TYPE => 'regular-main-button',
                \XLite\View\Button\AButton::PARAM_STYLE    => 'action',
            )
        );

        return $result;
    }

    /**
     * Populate model object properties by the passed data
     *
     * @param array $data Data to set
     *
     * @return void
     */
    protected function setModelProperties(array $data)
    {
        if (isset($data['enabled']) && !$data['enabled']) {
            $data = array('enabled' => '0');
        }

        foreach ($data as $name => $value) {
            switch ($name) {
                case 'agreement':
                    $value = !empty($value);
                    break;

                case 'email':
                    $publisherId = $this->getModelObject()->getSetting('publisherId');
                    $email = $this->getModelObject()->getSetting('email');

                    if (empty($publisherId) || $email !== $value) {
                        $publisherId = \XLite\Module\CDev\Paypal\Core\PaypalCredit::getInstance()
                            ->getPublisherId($value);

                        if (empty($publisherId)) {
                            \XLite\Core\TopMessage::getInstance()->addWarning(
                                'Unable to retrieve Publisher ID for specified PayPal account. Banners are now disabled.'
                            );
                        }

                        $this->getModelObject()->setSetting('publisherId', $publisherId);
                    }
                    break;

                default:
                    break;
            }

            $this->getModelObject()->setSetting($name, $value);
        }
    }
}
