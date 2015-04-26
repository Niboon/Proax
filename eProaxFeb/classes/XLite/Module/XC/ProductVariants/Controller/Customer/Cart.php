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

namespace XLite\Module\XC\ProductVariants\Controller\Customer;

/**
 * Cart page controller
 */
class Cart extends \XLite\Controller\Customer\Cart implements \XLite\Base\IDecorator
{
    /**
     * Correct product amount to add to cart
     *
     * @param \XLite\Model\OrderItem $item   Product to add
     * @param integer                $amount Amount of product
     *
     * @return integer
     */
    protected function correctAmountToAdd(\XLite\Model\OrderItem $item, $amount)
    {
        if ($item && $item->getProduct()->mustHaveVariants()) {
            $item->setVariant(
                $item->getProduct()->getVariantByAttributeValuesIds(
                    $item->getAttributeValuesIds()
                )
            );
        }

        return parent::correctAmountToAdd($item, $amount);
    }

    /**
     * Check if the requested amount is available for the product
     *
     * @param \XLite\Model\OrderItem $item   Order item to add
     * @param integer                $amount Amount to check OPTIONAL
     *
     * @return integer
     */
    protected function checkAmount(\XLite\Model\OrderItem $item, $amount = null)
    {
        return $item->getVariant() && !$item->getVariant()->getDefaultAmount()
            ? ($amount ?: 0) < $item->getVariant()->getAvailableAmount()
            : parent::checkAmount($item, $amount);
    }
}
