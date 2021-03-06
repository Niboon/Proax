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

namespace XLite\Module\XC\MultiCurrency\View\FormField\Select;

use \XLite\Module\XC\MultiCurrency\Core\Task\UpdateRates;

/**
 * Rate provider select class
 */
class RateUpdateInterval extends \XLite\View\FormField\Select\Regular
{
    /**
     * Return field value
     *
     * @return mixed
     */
    public function getValue()
    {
        return \XLite\Core\Config::getInstance()->XC->MultiCurrency->updateInterval;
    }

    /**
     * Return default options list
     *
     * @return array
     */
    protected function getDefaultOptions()
    {
        return array(
            UpdateRates::INT_1_MIN      => static::t('1 minute'),
            UpdateRates::INT_10_MIN     => static::t('10 minutes'),
            UpdateRates::INT_15_MIN     => static::t('15 minutes'),
            UpdateRates::INT_30_MIN     => static::t('30 minutes'),
            UpdateRates::INT_1_HOUR     => static::t('1 hour'),
            UpdateRates::INT_2_HOURS    => static::t('2 hours'),
            UpdateRates::INT_4_HOURS    => static::t('4 hours'),
            UpdateRates::INT_6_HOURS    => static::t('6 hours'),
            UpdateRates::INT_12_HOURS   => static::t('12 hours'),
            UpdateRates::INT_1_DAY      => static::t('1 day'),
        );
    }
}