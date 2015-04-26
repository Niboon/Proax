<?php
// vim: set ts=4 sw=4 sts=4 et:
/**
 * User: proaxit
 * Date: 2015-02-27
 * Time: 1:47 PM
 */

namespace XLite\Module\ProaxIT\CustomPricing\Controller\Customer;

/**
 * \XLite\Controller\Customer\Cart
 */
abstract class Cart extends \XLite\Controller\Customer\Cart implements \XLite\Base\IDecorator
{

    protected function addItem($item)
    {
        if ($this->checkPrice($item)) {
            $result = parent::addItem($item);
        } else {
            $this->isValid = false;
            $result = false;
            \XLite\Core\TopMessage::addWarning(\XLite\Core\Translation::lbl('Product: {{productName}} is unavailable for purchase online.
                Please send us a price quote request instead by clicking the Request Price Quote button',
                array(
                    'productName'     => $item->getProduct()->getName()
                )));
        }

        return $result;
    }

    /**
     * Disable redirect to cart after 'Add-to-cart' action if quantity is invalid
     *
     * @return void
     */
    protected function setURLToReturn()
    {
        if ($this->getCart()->getItemsWithWrongAmounts()) {
            \XLite\Core\Config::getInstance()->General->redirect_to_cart = false;
        } else {
            parent::setURLToReturn();
        }
    }

    /**
     * Show message about wrong product amount
     *
     * @param \XLite\Model\OrderItem $item Order item
     *
     * @return void
     */
    protected function processInvalidAmountError(\XLite\Model\OrderItem $item)
    {
        if ($this->checkPrice($item)) {
            parent::processInvalidAmountError($item);
        } else {
                \XLite\Core\TopMessage::addWarning( \XLite\Core\Translation::lbl('Product: {{productName}} is unavailable for purchase online.
                Please remove the product from your cart and click on the product page to send us a quotation request.',
                array(
                    'productName'     => $item->getProduct()->getName()
                ))
            );
        }
    }

    /**
     * @param \XLite\Model\OrderItem $item
     * @return bool
     */
    protected function checkPrice(\XLite\Model\OrderItem $item)
    {
        return $item->getProduct()->getClearPrice() != 0;
    }

}