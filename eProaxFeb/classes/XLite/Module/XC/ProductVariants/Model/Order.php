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

namespace XLite\Module\XC\ProductVariants\Model;

/**
 * Class represents an order
 *
 */
class Order extends \XLite\Model\Order implements \XLite\Base\IDecorator
{
    /**
     * Check - is item product id equal specified product id
     *
     * @param \XLite\Model\OrderItem $item      Item
     * @param integer                $productId Product id
     *
     * @return boolean
     */
    public function isItemProductIdEqual(\XLite\Model\OrderItem $item, $productId)
    {
        return parent::isItemProductIdEqual($item, $productId)
            && (
                !$item->getVariant()
                || $item->getVariant()->getDefaultAmount()
            );
    }


    /**
     * Check - is item variant id equal specified variant id
     *
     * @param \XLite\Model\OrderItem $item      Item
     * @param integer                $variantId Variant id
     *
     * @return boolean
     */
    public function isItemVariantIdEqual(\XLite\Model\OrderItem $item, $variantId)
    {
        return $item->getVariant() && $item->getVariant()->getId() == $variantId;
    }

    /**
     * Find items by variant ID
     *
     * @param integer $variantId Variant ID to use
     *
     * @return array
     */
    public function getItemsByVariantId($variantId)
    {
        $items = $this->getItems();

        return \Includes\Utils\ArrayManager::filter(
            $items,
            array($this, 'isItemVariantIdEqual'),
            $variantId
        );
    }
}
