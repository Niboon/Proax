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

namespace XLite\Module\CDev\Paypal\View\Button;

/**
 * Sign up button
 */
class SignUp extends \XLite\View\Button\SimpleLink
{
    /**
     * Get a list of CSS files required to display the widget properly
     *
     * @return array
     */
    public function getCSSFiles()
    {
        $list = parent::getCSSFiles();
        $list[] = 'modules/CDev/Paypal/settings/signup.css';

        return $list;
    }

    /**
     * Get a list of JavaScript files required to display the widget properly
     *
     * @return array
     */
    public function getJSFiles()
    {
        $list = parent::getJSFiles();

        $api = \XLite\Module\CDev\Paypal\Main::getRESTAPIInstance();
        if ($api->isInContextSignUpAvailable()) {
            $list[] = 'modules/CDev/Paypal/settings/signup.js';
        }

        return $list;
    }

    /**
     * Get CSS class name
     *
     * @return string
     */
    protected function getClass()
    {
        return parent::getClass() . 'btn regular-button pp-signup';
    }

    /**
     * Defines the default location path
     *
     * @return string
     */
    protected function getDefaultLocation()
    {
        $api = \XLite\Module\CDev\Paypal\Main::getRESTAPIInstance();
        $method = \XLite\Module\CDev\Paypal\Main::getPaymentMethod(\XLite\Module\CDev\Paypal\Main::PP_METHOD_EC);

        return $api->isInContextSignUpAvailable()
            ? $method->getReferralPageURL($method)
            : $this->buildURL('paypal_settings', '', array('method_id' => $method->getMethodId()));
    }

    /**
     * Get default attributes
     *
     * @return array
     */
    protected function getDefaultAttributes()
    {
        $params = array(
            'target'             => 'PPFrame',
            'data-paypal-button' => 'true',
        );

        $api = \XLite\Module\CDev\Paypal\Main::getRESTAPIInstance();

        return $api->isInContextSignUpAvailable()
            ? $params
            : array();
    }
}
