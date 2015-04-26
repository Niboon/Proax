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

namespace XLite\Module\XC\ProductVariants\View\ItemsList\Model;

/**
 * Order items list
 */
class OrderItem extends \XLite\View\ItemsList\Model\OrderItem implements \XLite\Base\IDecorator
{
    /**
     * Do some actions before save order items
     *
     * @param boolean                $isUpdated True if action item is updated
     * @param \XLite\Model\OrderItem $entity    OrderItem entity
     *
     * @return void
     */
    protected function postprocessOrderItems($isUpdated = false, $entity = null)
    {
        foreach ($this->getOrder()->getItems() as $item) {

            if ($item->getProduct()->mustHaveVariants()) {
                $variant = $item->getProduct()->getVariantByAttributeValuesIds(
                    $item->getAttributeValuesIds()
                );

                if ($variant) {
                    $item->setVariant($variant);
                    $item->setSku($variant->getDisplaySku());
                }
            }
        }

        parent::postprocessOrderItems($isUpdated, $entity);
    }

    /**
     * Change product quantity in stock if needed
     *
     * @param \XLite\Model\OrderItem $entity Order item entity
     *
     * @return void
     */
    protected function changeItemAmountInStock($entity)
    {
        if ($entity->getVariant()) {

            $oldVariant = $this->orderItemsData[$entity->getItemId()]['variant'];
            $newVariant = $entity->getVariant();

            if ($this->isItemDataChangedVariant($oldVariant, $newVariant)) {

                // Return old variant amount to stock
                if (!$oldVariant->getDefaultAmount()) {
                    $oldVariant->changeAmount($this->orderItemsData[$entity->getItemId()]['amount']);

                } else {
                    $entity->getProduct()->getInventory()->changeAmount($this->orderItemsData[$entity->getItemId()]['amount']);
                }

                // Get new variant amount from stock
                if (!$newVariant->getDefaultAmount()) {
                    $newVariant->changeAmount(-1 * $entity->getAmount());

                } else {
                    $entity->getProduct()->getInventory()->changeAmount(-1 * $entity->getAmount());
                }

            } else {
                parent::changeItemAmountInStock($entity);
            }

        } else {
            parent::changeItemAmountInStock($entity);
        }
    }

    /**
     * Add 'variant' to the order items data fields list
     *
     * @return array
     */
    protected function getOrderItemsDataFields()
    {
        $result = parent::getOrderItemsDataFields();
        $result[] = 'variant';

        return $result;
    }

    /**
     * Check order item and return true if this is valid
     *
     * @param \XLite\Model\OrderItem $entity Order item entity
     *
     * @return boolean
     */
    protected function isValidEntity($entity)
    {
        $result = parent::isValidEntity($entity);

        if (
            $result
            && (
                $entity->getProduct()->mustHaveVariants()
                || $entity->getVariant()
            )
        ) {
            $variant = $entity->getProduct()->getVariantByAttributeValuesIds($entity->getAttributeValuesIds());
            $result = $variant
                && $entity->getVariant()
                && $variant->getId() == $entity->getVariant()->getId();
        }

        return $result;
    }

    /**
     * Return true is variants are different
     *
     * @param \XLite\Module\XC\ProductVariants\Model\ProductVariant $old Old product variant
     * param \XLite\Module\XC\ProductVariants\Model\ProductVariant $new New product variant
     *
     * @return boolean
     */
    protected function isItemDataChangedVariant($old, $new)
    {
        return $old && $new && $old->getId() != $new->getId();
    }
}
