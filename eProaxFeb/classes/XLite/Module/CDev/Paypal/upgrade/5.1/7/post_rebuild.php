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
    // Loading data to the database from yaml file
    $yamlFile = __DIR__ . LC_DS . 'post_rebuild.yaml';

    if (\Includes\Utils\FileManager::isFileReadable($yamlFile)) {
        \XLite\Core\Database::getInstance()->loadFixturesFromYaml($yamlFile);
    }

    $paymentMethods = \XLite\Core\Database::getRepo('\XLite\Model\Payment\Method')->findBy(
        array('moduleName' => 'CDev_Paypal')
    );

    foreach ($paymentMethods as $paymentMethod) {

        if (
            'PayflowLink' == $paymentMethod->getServiceName()
            || 'PaypalAdvanced' == $paymentMethod->getServiceName()
            || 'ExpressCheckout' == $paymentMethod->getServiceName()
        ) {
            $setting = new \XLite\Model\Payment\MethodSetting();

            $setting->setName('buyNowEnabled');
            $setting->setValue('1');
            $setting->setPaymentMethod($paymentMethod);
            $setting->persist();

            $paymentMethod->addSettings($setting);
        }

        if ('ExpressCheckout' == $paymentMethod->getServiceName()) {

            foreach ($paymentMethod->getSettings() as $setting) {
                if ('api_type' == $setting->getName()) {
                    $setting->setValue('payflow' == $setting->getValue() ? 'api' : 'email');
                }
            }

            $settings = array(
                'api_solution' => 'payflow',
                'api_username' => '',
                'api_password' => '',
                'auth_method'  => 'signature',
                'signature'    => '',
                'certificate'  => '',
                'merchantId'   => '',
            );

            foreach ($settings as $name => $value) {
                $setting = new \XLite\Model\Payment\MethodSetting();

                $setting->setName($name);
                $setting->setValue($value);
                $setting->setPaymentMethod($paymentMethod);
                $setting->persist();

                $paymentMethod->addSettings($setting);
            }
        }
    }

    \XLite\Core\Database::getRepo('XLite\Model\Config')->createOption(
        array(
            'category' => 'CDev\Paypal',
            'name'     => 'show_admin_welcome',
            'value'    => 'N',
        )
    );

    \XLite\Core\Database::getEM()->flush();
};
