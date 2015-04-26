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
 * Measure
 */
class Measure extends \XLite\Controller\Admin\AAdmin
{
    /**
     * Measure action
     *
     * @return void
     */
    protected function doActionMeasure()
    {
        if (!\XLite\Core\Config::getInstance()->Internal->probe_key) {
            $key = md5(strval(microtime(true) * 1000000) . uniqid(true));
            \XLite\Core\Database::getRepo('XLite\Model\Config')->createOption(
                array(
                    'category' => 'Internal',
                    'name'     => 'probe_key',
                    'value'    => $key,
                )
            );
            \XLite\Core\Config::getInstance()->Internal->probe_key = $key;
        }

        $this->requestProbe();

        $this->redirect(\XLite\Core\Converter::buildURL());
    }

    /**
     * Request probe script
     *
     * @return void
     */
    protected function requestProbe()
    {
        $url = \XLite::getInstance()->getShopURL(
            \XLite\Core\Converter::buildURL(
                '',
                '',
                array('key' => \XLite\Core\Config::getInstance()->Internal->probe_key),
                'probe.php'
            )
        );

        set_time_limit(0);

        $request = new \XLite\Core\HTTP\Request($url);
        $response = $request->sendRequest();

        if (200 != $response->code) {
            \XLite\Core\TopMessage::addError('Measuring productivity in manual mode failed.');
        }
    }
}
