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

return function()
{
    $paymentMethods = \XLite\Core\Database::getRepo('\XLite\Model\Payment\Method')->findBy(
        array('moduleName' => 'CDev_Paypal')
    );

    foreach ($paymentMethods as $paymentMethod) {
        // 0. rename PaypalWPSUS to PaypalWPS
        if ('PaypalWPSUS' == $paymentMethod->getServiceName()) {
            $paymentMethod->setServiceName('PaypalWPS');
        }

        // 1. rename 'test' setting to 'mode', correct value (Y - test, N - live) (EC, AP, PF)
        $APIType = 'merchant';

        if (
            'PayflowLink' == $paymentMethod->getServiceName()
            || 'PaypalAdvanced' == $paymentMethod->getServiceName()
            || 'ExpressCheckout' == $paymentMethod->getServiceName()
        ) {
            foreach ($paymentMethod->getSettings() as $setting) {
                if ('test' == $setting->getName()) {
                    $setting->setName('mode');
                    $setting->setValue($setting->getValue() == 'Y' ? 'test' : 'live');
                }
            }

            if ($paymentMethod->isEnabled()) {
                $APIType = 'payflow';
            }
        }

        if ('ExpressCheckout' == $paymentMethod->getServiceName()) {
            // 2. add api_type setting for EC - 'set payflow' if AP or PF is enabled
            $setting = new \XLite\Model\Payment\MethodSetting();

            $setting->setName('api_type');
            $setting->setValue($APIType);
            $setting->setPaymentMethod($paymentMethod);
            $setting->persist();

            $paymentMethod->addSettings($setting);

            // 3. add email setting for EC
            $setting = new \XLite\Model\Payment\MethodSetting();

            $setting->setName('email');
            $setting->setPaymentMethod($paymentMethod);
            $setting->persist();

            $paymentMethod->addSettings($setting);
        }
    }

    \XLite\Core\Database::getEM()->flush();
};
