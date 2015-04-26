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

namespace XLite\Module\XC\MultiCurrency\View\OrderList;

use XLite\Module\XC\MultiCurrency\Core\MultiCurrency;

/**
 * Orders search widget
 */
class OrderListItem extends \XLite\View\OrderList\OrderListItem implements \XLite\Base\IDecorator
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
    protected function formatOrderPrice($value, \XLite\Model\Currency $currency = null, $strictFormat = false)
    {
        if ($this->getOrder()->isMultiCurrencyOrder()) {
            $value = MultiCurrency::getInstance()->convertValueByRate(
                $value,
                $this->getOrder()->getSelectedMultiCurrencyRate()
            );

            $currency = $this->getOrder()->getSelectedMultiCurrency();
        }

        return static::formatPrice($value, $currency, $strictFormat, true);
    }
}