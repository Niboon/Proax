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

namespace XLite\Controller\Admin;

/**
 * OrderItem model selector controller
 */
class ModelOrderItemSelector extends \XLite\Controller\Admin\ModelProductSelector
{
    /**
     * Cached order item
     *
     * @var \XLite\Model\OrderItem
     */
    protected $orderItem = null;

    /**
     * Define specific data structure which will be sent in the triggering event (model.selected)
     *
     * @param mixed $item Model item
     *
     * @return string
     */
    protected function defineDataItem($item)
    {
        $data = parent::defineDataItem($item);

        $orderItem = new \XLite\Model\OrderItem();
        $orderItem->setProduct($item);

        if ($item->hasEditableAttributes()) {
            $orderItem->setAttributeValues($item->prepareAttributeValues());
        }

        $orderItem = $this->postprocessOrderItem($orderItem);

        $orderItem->setItemNetPrice(null);
        $orderItem->setPrice(null);
        $orderItem->calculate();
        $orderItem->renew();

        $data['clear_price'] = $orderItem->getClearPrice();
        $data['selected_price'] = $orderItem->getDisplayPrice();

        $data['max_qty'] = $orderItem->getProductAvailableAmount();
        $data['server_price_control'] = $orderItem->isPriceControlledServer();

        if ($item->hasEditableAttributes()) {
            // SKU may differ after attributes selection
            $data['selected_sku'] = $orderItem->getSku();
            $data['presentation'] = $this->formatItem($orderItem);

            $data['clear_price'] = $orderItem->getClearPrice();
            $data['server_price_control'] = $orderItem->isPriceControlledServer();

            if ($data['server_price_control']) {
                $data['selected_price'] = $orderItem->getDisplayPrice();
            }

            $widget = new \XLite\View\OrderItemAttributes(
                array(
                    'orderItem' => $orderItem,
                    'idx'       => \XLite\Core\Request::getInstance()->idx ?: $orderItem->getItemId(),
                )
            );
            $widget->init();

            $data['selected_attributes'] = $widget->getContent();

            $widget = new \XLite\View\InvoiceAttributeValues(
                array(
                    'item'             => $orderItem,
                    'displayVariative' => 1,
                )
            );

            $widget->init();

            $data['attributes_widget'] = $widget->getContent();
        }

        $this->orderItem = $orderItem;

        return $data;
    }

    /**
     * Do additional modifications with order item and return this
     *
     * @param \XLite\Model\OrderItem $orderItem Order item entity
     *
     * @return \XLite\Model\OrderItem
     */
    protected function postprocessOrderItem(\XLite\Model\OrderItem $orderItem)
    {
        return $orderItem;
    }

    /**
     * Format the value for the method: $this->getJSONData()
     *
     * @param mixed   &$item
     * @param integer $index
     *
     * @return void
     */
    public function prepareItem(&$item, $index)
    {
        parent::prepareItem($item, $index);

        $item['text_presentation'] = $item['data']['presentation'];
    }
}
