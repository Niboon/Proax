<?php
//// vim: set ts=4 sw=4 sts=4 et:
//
///**
// * X-Cart
// *
// * NOTICE OF LICENSE
// *
// * This source file is subject to the software license agreement
// * that is bundled with this package in the file LICENSE.txt.
// * It is also available through the world-wide-web at this URL:
// * http://www.x-cart.com/license-agreement.html
// * If you did not receive a copy of the license and are unable to
// * obtain it through the world-wide-web, please send an email
// * to licensing@x-cart.com so we can send you a copy immediately.
// *
// * DISCLAIMER
// *
// * Do not modify this file if you wish to upgrade X-Cart to newer versions
// * in the future. If you wish to customize X-Cart for your needs please
// * refer to http://www.x-cart.com/ for more information.
// *
// * @category  X-Cart 5
// * @author    Qualiteam software Ltd <info@x-cart.com>
// * @copyright Copyright (c) 2011-2015 Qualiteam software Ltd <info@x-cart.com>. All rights reserved
// * @license   http://www.x-cart.com/license-agreement.html X-Cart 5 License Agreement
// * @link      http://www.x-cart.com/
// */
//
//namespace XLite\Module\ProaxIT\QuickOrderWidget\Controller\Customer;
//
//class Cart extends \XLite\Controller\Customer\Cart implements \XLite\Base\IDecorator
//{
//    /**
//     * Add product to cart
//     *
//     * @return void
//     */
//    protected function doActionQuickadd()
//    {
//        // Add product to the cart and set a top message (if needed)
//        $item = $this->getItemFromSKU();
//
//        if ($item && $this->addItem($item)) {
//            $this->processAddItemSuccess();
//
//        } else {
//            $this->processAddItemError();
//        }
//
//        // Update cart
//        $this->updateCart();
//
//        // Set return URL
//        $this->setURLToReturn();
//    }
//
//
//    /**
//     * Get (and create) current cart item.
//     * Order item is changed according \XLite\Core\Request
//     * (according customer request to add some specific features to item in cart. for example - options/variants/offers and so on)
//     *
//     * @return \XLite\Model\OrderItem
//     */
//    protected function getItemFromSKU()
//    {
//        return $this->prepareOrderItem(
//            $this->getProductFromSKU(),
//            $this->isSetCurrentAmount() ? $this->getCurrentAmount() : null
//        );
//    }
//
//    /**
//     * Return current product class for further adding to cart
//     *
//     * @return \XLite\Model\Product
//     */
//    protected function getProductFromSKU()
//    {
//        if (is_null($this->product)) {
//            $this->product = \XLite\Core\Database::getRepo('XLite\Model\Product')->find($this->getCurrentProductId());
//        }
//
//        return ($this->product && $this->product->isAvailable()) ? $this->product : null;
//    }
//
//    /**
//     * Add products from the order to cart
//     *
//     * @return void
//     */
//    protected function doActionAddOrder()
//    {
//        $order = null;
//
//        if (\XLite\Core\Request::getInstance()->order_id) {
//            $order = \XLite\Core\Database::getRepo('\XLite\Model\Order')
//                ->find(intval(\XLite\Core\Request::getInstance()->order_id));
//
//        } elseif (\XLite\Core\Request::getInstance()->order_number) {
//            $order = \XLite\Core\Database::getRepo('\XLite\Model\Order')
//                ->findOneByOrderNumber(\XLite\Core\Request::getInstance()->order_number);
//        }
//
//        if (
//            $order
//            && (
//                $order->getProfile()->getAnonymous()
//                || (
//                    \XLite\Core\Auth::getInstance()->isLogged()
//                    && \XLite\Core\Auth::getInstance()->getProfile()->getProfileId() == $order->getOrigProfile()->getProfileId()
//                )
//            )
//        ) {
//            $this->addedOrder = $order;
//
//            foreach ($order->getItems() as $item) {
//                if ($item->isValidToClone()) {
//                    $this->addItem($item->cloneEntity());
//                }
//            }
//
//            $this->updateCart();
//        }
//
//        $this->setReturnURL($this->getURL());
//    }
//
//    // TODO: refactoring
//
//    /**
//     * 'delete' action
//     *
//     * @return void
//     */
//    protected function doActionDelete()
//    {
//        $item = $this->getCart()->getItemByItemId(\XLite\Core\Request::getInstance()->cart_id);
//
//        if ($item) {
//            $this->getCart()->getItems()->removeElement($item);
//            \XLite\Core\Database::getEM()->remove($item);
//            $this->updateCart();
//            \XLite\Core\TopMessage::addInfo('Item has been deleted from cart');
//        } else {
//            $this->valid = false;
//
//            \XLite\Core\TopMessage::addError(
//                'Item has not been deleted from cart'
//            );
//        }
//    }
//
//    /**
//     * Update cart
//     *
//     * @return void
//     */
//    protected function doActionUpdate()
//    {
//        // Update quantity
//        $cartId = \XLite\Core\Request::getInstance()->cart_id;
//        $amount = \XLite\Core\Request::getInstance()->amount;
//
//        if (!is_array($amount)) {
//            $amount = isset(\XLite\Core\Request::getInstance()->cart_id)
//                ? array($cartId => $amount)
//                : array();
//        } elseif (isset($cartId)) {
//            $amount = isset($amount[$cartId])
//                ? array($cartId => $amount[$cartId])
//                : array();
//        }
//
//        $result = false;
//        $warningText = '';
//
//        foreach ($amount as $id => $quantity) {
//            $item = $this->getCart()->getItemByItemId($id);
//
//            if ($warningText === '') {
//                $warningText = $item->getAmountWarning($quantity);
//            }
//
//            if ($item) {
//                $item->setAmount($quantity);
//                $result = true;
//            }
//        }
//
//        // Update shipping method
//        if (isset(\XLite\Core\Request::getInstance()->shipping)) {
//            $this->getCart()->setShippingId(\XLite\Core\Request::getInstance()->shipping);
//
//            $result = true;
//        }
//
//        if ($warningText !== '') {
//            \XLite\Core\TopMessage::addWarning($warningText);
//        }
//
//        if ($result) {
//            $this->updateCart();
//        }
//    }
//
//    /**
//     * 'checkout' action
//     *
//     * @return void
//     */
//    protected function doActionCheckout()
//    {
//        $this->doActionUpdate();
//
//        // switch to checkout dialog
//        $this->setReturnURL($this->buildURL('checkout'));
//    }
//
//    /**
//     * Clear cart
//     *
//     * @return void
//     */
//    protected function doActionClear()
//    {
//        if (!$this->getCart()->isEmpty()) {
//
//            // Clear cart
//            $this->getCart()->clear();
//
//            // Update cart properties
//            $this->updateCart();
//
//            \XLite\Core\TopMessage::addInfo('Item has been deleted from cart');
//        }
//
//        $this->setReturnURL($this->buildURL('cart'));
//    }
//
//    /**
//     * Just update the cart if no action is defined
//     *
//     * @return void
//     */
//    protected function doNoAction()
//    {
//        $this->updateCart();
//    }
//}
