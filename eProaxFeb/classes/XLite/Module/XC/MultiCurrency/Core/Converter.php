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

namespace XLite\Module\XC\MultiCurrency\Core;

use XLite\Module\XC\MultiCurrency\Core\MultiCurrency;

/**
 * Misceleneous conversion routines
 */
class Converter extends \XLite\Core\Converter implements \XLite\Base\IDecorator
{
    /**
     * Selected multi currency
     *
     * @var \XLite\Module\XC\MultiCurrency\Model\ActiveCurrency
     */
    protected $selectedMultiCurrency = null;

    /**
     * Convert price to selected currency according to rate
     *
     * @param float $value Value
     *
     * @return float
     */
    public function convertToSelectedMultiCurrency($value)
    {
        if (
            MultiCurrency::getInstance()->hasMultipleCurrencies()
            && !$this->getSelectedMultiCurrency()->isDefaultCurrency()
        ) {
            $value = MultiCurrency::getInstance()->convertValueByRate(
                $value,
                $this->getSelectedMultiCurrency()->getRate()
            );
        }

        return $value;
    }

    /**
     * Get selected currency
     *
     * @return \XLite\Module\XC\MultiCurrency\Model\ActiveCurrency
     */
    protected function getSelectedMultiCurrency()
    {
        if (is_null($this->selectedMultiCurrency)) {
            $this->selectedMultiCurrency = MultiCurrency::getInstance()->getSelectedMultiCurrency();
        }

        return $this->selectedMultiCurrency;
    }
}