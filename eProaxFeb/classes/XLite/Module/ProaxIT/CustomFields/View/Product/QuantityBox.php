<?php
// vim: set ts=4 sw=4 sts=4 et:


namespace XLite\Module\ProaxIT\CustomFields\View\Product;
use XLite\Module\CDev\ProductAdvisor\Model\Product;

/**
 * Quantity box
 */
class QuantityBox extends \XLite\View\Product\QuantityBox implements \XLite\Base\IDecorator
{
    /**
     * Register CSS files
     *
     * @return array
     */
    public function getCSSFiles()
    {
        $list = parent::getCSSFiles();
        $list[] = '/modules/ProaxIT/CustomFields/css/style.css';

        return $list;
    }

    /**
     * Register JS files
     *
     * @return array
     */
    public function getJSFiles()
    {
        $list = parent::getJSFiles();
        $list[] =  '/modules/ProaxIT/CustomFields/js/script.js';
        return $list;
    }


    /**
     * Return minimum quantity
     *
     * @return integer
     */
    protected function getMinQuantity()
    {
        $product = $this->getProduct();
        $packSize = $product ? $product->getPackSize() : null;
        return $packSize ? : parent::getMinQuantity();
    }


    /**
     * Return array for dropdown quantities
     *
     * @return array
     */
    protected function getOptionsArray($product=null)
    {
        $packSize = $product ? $product->getPackSize() : $this->getMinQuantity();
        $arr = array();
        for ($i=1; $i<1001; $i++) {
            $arr[] = $i * $packSize;
        }
        return $arr;
    }

    /**
     * Return if pack quantities should be shown as a dropdown list
     *
     * @return bool
     */
    protected function isInPacks()
    {
        $packSize = $this->getMinQuantity();
        return $packSize > 1;
    }

    /**
     * Value for Selected Option
     *
     * @return integer
     */
    protected function getSelectedValue()
    {
        return $this->getParam(self::PARAM_FIELD_VALUE) ? : $this->getMinQuantity();
    }

    /**
     * Checks if the quantity in cart is a multiple of the product's pack size
     *
     * @return bool
     */
    protected function showIncorrectIncrementOption()
    {
        return $this->getSelectedValue() % $this->getMinQuantity() != 0;
    }
}