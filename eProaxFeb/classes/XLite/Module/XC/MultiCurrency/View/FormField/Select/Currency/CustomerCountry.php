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

namespace XLite\Module\XC\MultiCurrency\View\FormField\Select\Currency;

use XLite\Module\XC\MultiCurrency\Core\MultiCurrency;

/**
 * Customer currency selector
 */
class CustomerCountry extends \XLite\View\FormField\Select\ASelect
{
    /**
     * Return field value
     *
     * @return mixed
     */
    public function getValue()
    {
        return MultiCurrency::getInstance()->getSelectedCountry()->getCode();
    }

    /**
     * Return default options list
     *
     * @return array
     */
    protected function getDefaultOptions()
    {
        $return = array();

        $countries = MultiCurrency::getInstance()->getAvailableCountries();

        if (
            is_array($countries)
            && !empty($countries)
        ) {
            foreach ($countries as $country) {
                $return[$country->getCode()] = $country->getCountry();
            }
        }

        return $return;
    }

    /**
     * getDefaultLabel
     *
     * @return string
     */
    protected function getDefaultLabel()
    {
        return static::t('Country');
    }

    /**
     * Check if widget is visible
     *
     * @return boolean
     */
    protected function isVisible()
    {
        return MultiCurrency::getInstance()->hasAvailableCountries();
    }
}