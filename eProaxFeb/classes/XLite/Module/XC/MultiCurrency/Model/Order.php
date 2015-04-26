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

namespace XLite\Module\XC\MultiCurrency\Model;

use XLite\Module\XC\MultiCurrency\Core\MultiCurrency;

/**
 * Class represents an order
 */
class Order extends \XLite\Model\Order implements \XLite\Base\IDecorator
{
    /**
     * Selected multi currency
     *
     * @var \XLite\Model\Currency
     *
     * @OneToOne (targetEntity="XLite\Model\Currency")
     * @JoinColumn (name="multi_currency_id", referencedColumnName="currency_id")
     */
    protected $selectedMultiCurrency = null;

    /**
     * Selected multi currency rate
     *
     * @var float
     *
     * @Column (type="decimal", precision=14, scale=4)
     */
    protected $selectedMultiCurrencyRate = 1.0;

    /**
     * Since Doctrine lifecycle callbacks do not allow to modify associations, we've added this method
     *
     * @param string $type Type of current operation
     *
     * @return void
     */
    public function prepareEntityBeforeCommit($type)
    {
        if (
            static::ACTION_UPDATE == $type
            && !isset($this->selectedMultiCurrency)
        ) {
            $selectedCurrency = MultiCurrency::getInstance()->getSelectedMultiCurrency();

            if (
                isset($selectedCurrency)
                && !$selectedCurrency->isDefaultCurrency()
            ) {
                $this->setSelectedMultiCurrency($selectedCurrency->getCurrency());
                $this->setSelectedMultiCurrencyRate($selectedCurrency->getRate());
            }
        }

        parent::prepareEntityBeforeCommit($type);
    }

    /**
     * Check if order is multi currency order (display currency is different from charge currency)
     *
     * @return boolean
     */
    public function isMultiCurrencyOrder()
    {
        $return = false;

        $orderCurrency = $this->getCurrency();
        $selectedCurrency = $this->getSelectedMultiCurrency();

        if (
            isset($selectedCurrency)
            && $orderCurrency->getCode() != $selectedCurrency->getCode()
        ) {
            $return = true;
        }

        return $return;
    }
}