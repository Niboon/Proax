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

namespace XLite\Module\XC\MultiCurrency\View;

use XLite\Module\XC\MultiCurrency\Core\MultiCurrency;

/**
 * Invoice widget
 */
class Invoice extends \XLite\View\Invoice implements \XLite\Base\IDecorator
{
    /**
     * Format price
     *
     * @param float                 $value        Price
     * @param \XLite\Model\Currency $currency     Currency OPTIONAL
     * @param boolean               $strictFormat Flag if the price format is strict (trailing zeroes and so on options) OPTIONAL
     *
     * @return string
     */
    protected function formatInvoicePrice($value, \XLite\Model\Currency $currency = null, $strictFormat = false)
    {
        if ($this->isMultiCurrencyOrder()) {
            $value = MultiCurrency::getInstance()->convertValueByRate(
                $value,
                $this->getOrder()->getSelectedMultiCurrencyRate()
            );

            $currency = $this->getOrder()->getSelectedMultiCurrency();
        }

        return static::formatPrice($value, $currency, $strictFormat, true);
    }

    /**
     * Format surcharge value
     *
     * @param array $surcharge Surcharge
     *
     * @return string
     */
    protected function formatSurcharge(array $surcharge)
    {
        if ($this->isMultiCurrencyOrder()) {
            $value = MultiCurrency::getInstance()->convertValueByRate(
                abs($surcharge['cost']),
                $this->getOrder()->getSelectedMultiCurrencyRate()
            );

            $return = $this->formatPrice(
                $value,
                $this->getOrder()->getSelectedMultiCurrency(),
                !\XLite::isAdminZone(),
                true
            );
        } else {
            $return = $this->formatPrice(
                abs($surcharge['cost']),
                $this->getOrder()->getCurrency(),
                !\XLite::isAdminZone(),
                true
            );
        }

        return $return;
    }

    /**
     * Check if current widget order is multi currency order (display currency is different from charge currency)
     *
     * @return boolean
     */
    protected function isMultiCurrencyOrder()
    {
        $return = false;

        $order = $this->getOrder();

        if (isset($order)) {
            $return = $order->isMultiCurrencyOrder();
        }

        return $return;
    }
}