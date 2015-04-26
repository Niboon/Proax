<?php
// vim: set ts=4 sw=4 sts=4 et:

namespace XLite\Module\ProaxIT\RequestPriceQuote\Model;

class Product extends \XLite\Model\Product implements \XLite\Base\IDecorator
{
    public $tmpPrice = null;

    /**
     * Whether to show price quote request button or not.
     * Custom logic to validate price for price quote showing
     *
     * @return bool
     */
    public function showPriceQuote()
    {
        if ($this->tmpPrice == null) {
            $this->tmpPrice=$this->getClearPrice();
        }
        if (floatval($this->tmpPrice) == 0) {
            //Show Button for Request Quote
            return true;
        } else {
            return false;
        }
    }

}