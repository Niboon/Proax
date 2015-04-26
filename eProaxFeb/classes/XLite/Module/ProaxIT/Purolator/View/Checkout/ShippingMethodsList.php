<?php
// vim: set ts=4 sw=4 sts=4 et:

namespace XLite\Module\ProaxIT\Purolator\View\Checkout;

/**
 * Shipping methods list
 */
abstract class ShippingMethodsList extends \XLite\View\Checkout\ShippingMethodsList implements \XLite\Base\IDecorator
{
    /**
     * Get JS files
     *
     * @return array
     */
    public function getJSFiles()
    {
        $list = parent::getJSFiles();

        $list[] = '/modules/ProaxIT/Purolator/shippingMethodChecker.js';

        return $list;
    }
}
