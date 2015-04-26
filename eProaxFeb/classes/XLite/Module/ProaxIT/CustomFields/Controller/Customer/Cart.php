<?php
// vim: set ts=4 sw=4 sts=4 et:
/**
 * User: proaxit
 * Date: 2015-01-27
 * Time: 1:51 PM
 */

namespace XLite\Module\ProaxIT\CustomFields\Controller\Customer;

/**
 * \XLite\Controller\Customer\Cart
 */
abstract class Cart extends \XLite\Controller\Customer\Cart implements \XLite\Base\IDecorator
{

    protected function addItem($item)
    {

        $packSize = $item->getProduct()->getPackSize();
        if ($this->checkPackSize($item, $packSize)) {
            $result = parent::addItem($item);
        } else {
            $this->isValid = false;
            $result = false;
            \XLite\Core\TopMessage::addWarning('You can only add product ' . $item->getName() . ' in increments of ' . $packSize .
                '\'s (i.e. ' . $packSize . ', '. 2*$packSize .', ' . 3*$packSize . ', etc).');
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
        if ($this->checkPackSize($item, $item->getProduct()->getPackSize())) {
            parent::processInvalidAmountError($item);
        } else {
                \XLite\Core\TopMessage::addWarning(
                'You tried to buy an invalid quantity of "{{product}}" product {{description}}. Please adjust the product quantity to increments of {{packSize}}.',
                array(
                    'product'     => $item->getProduct()->getName(),
                    'description' => $item->getExtendedDescription(),
                    'packSize'    => $item->getProduct()->getPackSize()
                )
            );
        }
    }

    /**
     * Check if quantity is in increments of the pack size
     *
     * @param $item
     * @param $packSize
     * @return bool
     */
    protected function checkPackSize($item, $packSize)
    {
        return $item->getAmount() % $packSize == 0;
    }

}