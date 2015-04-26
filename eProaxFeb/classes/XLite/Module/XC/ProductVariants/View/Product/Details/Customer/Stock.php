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

namespace XLite\Module\XC\ProductVariants\View\Product\Details\Customer;

/**
 * Stock
 */
class Stock extends \XLite\View\Product\Details\Customer\Stock implements \XLite\Base\IDecorator
{
    /**
     * Return available amount
     *
     * @return integer
     */
    protected function getAvailableAmount()
    {
        return $this->getProductVariant() && !$this->getProductVariant()->getDefaultAmount()
            ? $this->getProductVariant()->getAvailableAmount()
            : parent::getAvailableAmount();
    }

    /**
     * Check - 'items available' label is visible or not
     *
     * @return boolean
     */
    protected function isInStock()
    {
        $result = parent::isInStock();
        $variant = $this->getProductVariant();

        if ($variant) {
            if (!$variant->getDefaultAmount()) {
                $result = !$this->getProductVariant()->isOutOfStock();
            }

        } elseif ($this->getProduct()->mustHaveVariants()) {
            $result = false;
        }

        return $result;
    }

    /**
     * Return 'Out of stock' message
     *
     * @return string
     */
    protected function getOutOfStockMessage()
    {
        return $this->getProduct()->mustHaveVariants()
            ? static::t($this->getProductVariant() ? 'This item is out of stock' : 'This item is not available')
            : parent::getOutOfStockMessage();
    }
}
