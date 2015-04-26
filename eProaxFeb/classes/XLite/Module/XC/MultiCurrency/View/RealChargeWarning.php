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
 * Product price
 */
class RealChargeWarning extends \XLite\View\AView
{
    const PARAM_ORDER = 'order';

    /**
     * Get CSS files
     *
     * @return array
     */
    public function getCSSFiles()
    {
        $return = parent::getCSSFiles();

        $return[] = $this->getDir() . LC_DS . 'real_charge_style.css';

        return $return;
    }

    /**
     * Get order
     *
     * @return \XLite\Model\Order
     */
    protected function getOrder()
    {
        return $this->getParam(self::PARAM_ORDER);
    }

    /**
     * Define widget parameters
     *
     * @return void
     */
    protected function defineWidgetParams()
    {
        parent::defineWidgetParams();

        $this->widgetParams += array(
            self::PARAM_ORDER => new \XLite\Model\WidgetParam\Object(
                'Order',
                null,
                false,
                '\XLite\Model\Cart'
            )
        );
    }

    /**
     * Return widget default template
     *
     * @return string
     */
    protected function getDefaultTemplate()
    {
        return $this->getDir() . LC_DS . 'real_charge_warning.tpl';
    }

    /**
     * Get directory
     *
     * @return string
     */
    protected function getDir()
    {
        return 'modules' . LC_DS . 'XC' . LC_DS . 'MultiCurrency' . LC_DS . 'shopping_cart';
    }

    /**
     * Get note
     *
     * @return string
     */
    protected function getSelectedRateText()
    {
        return static::t(
            'Note: All prices billed in {{currency}}. Current exchange rate is {{exchange_rate}}.',
            array(
                'currency'      => $this->getDefaultCurrencyText(
                    MultiCurrency::getInstance()->getDefaultCurrency()
                ),
                'exchange_rate' => $this->getSelectedCurrencyRateText(
                    MultiCurrency::getInstance()->getSelectedCurrency(),
                    MultiCurrency::getInstance()->getSelectedCurrency()->getActiveCurrency()->getRate(),
                    MultiCurrency::getInstance()->getDefaultCurrency()
                )
            )
        );
    }

    /**
     * Get default currency text
     *
     * @param \XLite\Model\Currency $defaultCurrency Currency
     *
     * @return string
     */
    protected function getDefaultCurrencyText(\XLite\Model\Currency $defaultCurrency)
    {
        $prefix = $defaultCurrency->getPrefix();

        $prefix = empty($prefix) ? $defaultCurrency->getSuffix() : $prefix;
        $prefix = empty($prefix) ? '' : ' (' . $prefix . ')';

        return $defaultCurrency->getCode() . $prefix;
    }

    /**
     * Get selected currency text
     *
     * @param \XLite\Model\Currency $selectedCurrency Selected currency
     * @param float                 $rate             Rate
     * @param \XLite\Model\Currency $defaultCurrency  Default currency
     *
     * @return string
     */
    protected function getSelectedCurrencyRateText(\XLite\Model\Currency $selectedCurrency, $rate, \XLite\Model\Currency $defaultCurrency)
    {
        $rate = 1 / $rate;

        $defaultCurrency->setE(4);

        $return = $this->formatPrice(1, $selectedCurrency, false, true)
            . ' = ' . $this->formatPrice($rate, $defaultCurrency, false, true);

        return $return;
    }
}