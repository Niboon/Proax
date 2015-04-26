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

namespace XLite\Module\XC\ProductVariants\Controller\Admin;

/**
 * Product variants page controller (Order section)
 */
class Order extends \XLite\Controller\Admin\Order implements \XLite\Base\IDecorator
{

    /**
     * Prepare order item before price calculation
     *
     * @param \XLite\Model\OrderItem $item       Order item
     * @param array                  $attributes Attributes
     *
     * @return void
     */
    protected function prepareItemBeforePriceCalculation(\XLite\Model\OrderItem $item, array $attributes)
    {
        parent::prepareItemBeforePriceCalculation($item, $attributes);

        if ($item && $item->getProduct()->mustHaveVariants()) {
            $variant = $item->getProduct()->getVariantByAttributeValuesIds($attributes);
            if ($variant) {
                $oldVariant = $item->getVariant();
                if (!$oldVariant || $oldVariant->getId() != $variant->getId()) {
                    \XLite\Core\Request::getInstance()->oldAmount = null;
                }
                $item->setVariant($variant);
            }
        }

        return $item;
    }

    /**
     * Assemble recalculate item event
     *
     * @param \XLite\Model\OrderItem $item Order item
     *
     * @return array
     */
    protected function assembleRecalculateItemEvent(\XLite\Model\OrderItem $item)
    {
        $data = parent::assembleRecalculateItemEvent($item);

        if ($item->getVariant()) {
            $data['sku'] = $item->getVariant()->getSku()
                ? $item->getVariant()->getSku()
                : null;
        }

        return $data;
    }
}
