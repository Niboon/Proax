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

namespace XLite\Module\XC\MultiCurrency\Core\Task;

use XLite\Module\XC\MultiCurrency\Core\MultiCurrency;

/**
 * Scheduled task that sends automatic cart reminders.
 */
class UpdateRates extends \XLite\Core\Task\Base\Periodic
{
    const INT_1_MIN     = 60;
    const INT_5_MIN     = 300;
    const INT_10_MIN    = 600;
    const INT_15_MIN    = 900;
    const INT_30_MIN    = 1800;
    const INT_1_HOUR    = 3600;
    const INT_2_HOURS   = 7200;
    const INT_4_HOURS   = 14400;
    const INT_6_HOURS   = 21600;
    const INT_12_HOURS  = 43200;
    const INT_1_DAY     = 86400;

    /**
     * Get title
     *
     * @return string
     */
    public function getTitle()
    {
        return static::t('Rates update');
    }

    /**
     * Run step
     *
     * @return void
     */
    protected function runStep()
    {
        MultiCurrency::getInstance()->updateRates();
    }

    /**
     * Get period (seconds)
     *
     * @return integer
     */
    protected function getPeriod()
    {
        return \XLite\Core\Config::getInstance()->XC->MultiCurrency->updateInterval;
    }
}