<?php
/**
 * Created by PhpStorm.
 * User: proaxit
 * Date: 2015-02-02
 * Time: 11:52 AM
 */

namespace XLite\Module\ProaxIT\CustomFields\Model;

/**
 * Order item
 */
class OrderItem extends \XLite\Model\OrderItem implements \XLite\Base\IDecorator
{

    // Changed SKU to 40 chars, 2Feb2015 Niboon
    /**
     * Item SKU
     *
     * @var string
     *
     * @Column (type="string", length=40)
     */
    protected $sku;


    /**
     * Define the warning if amount not in increments of pack size
     *
     * @param integer $amount
     *
     * @return string
     */
    public function getAmountWarning($amount)
    {
        $amountWarning = parent::getAmountWarning($amount);
        if ($amountWarning == '') {
            $packSize = $this->getProduct()->getPackSize();

            if ($packSize > 1 && $amount % $packSize != 0) {
                $amountWarning = \XLite\Core\Translation::lbl('Product: {{productName}} has to be bought in packs of {{packSize}}.
                Please adjust the quantity to increments of {{packSize}}',
                    array(
                    'packSize' => $packSize,
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
        $packSize = $this->getProduct()->getPackSize();

        return parent::hasWrongAmount() || ($this->getAmount() % $packSize != 0);
    }

}