<?php
/**
 * User: proaxit
 * Date: 2015-02-27
 * Time: 1:47 PM
 */

namespace XLite\Module\ProaxIT\CustomPricing\Model;

/**
 * Order item
 */
class OrderItem extends \XLite\Model\OrderItem implements \XLite\Base\IDecorator
{

    /**
     * Define the warning if price is zero
     *
     * @param integer $amount
     *
     * @return string
     */
    public function getAmountWarning($amount)
    {
        $amountWarning = parent::getAmountWarning($amount);
        if ($amountWarning == '') {
            $price = $this->getProduct()->getClearPrice();

            if ($price == 0) {
                $amountWarning = \XLite\Core\Translation::lbl('Product: {{productName}} is unavailable for purchase online.
                Please send us a price quote request instead by clicking the Request Price Quote button',
                    array(
                    'productName' => $this->getName(),
                ));
            }
        }

        return $amountWarning;
    }

    /**
     * Check if item has a wrong amount
     *
     * @return boolean
     */
    public function hasWrongAmount()
    {
        $price = $this->getProduct()->getClearPrice();

        return parent::hasWrongAmount() || ($price == 0);
    }

}