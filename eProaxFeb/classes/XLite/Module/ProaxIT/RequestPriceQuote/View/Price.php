<?php
// vim: set ts=4 sw=4 sts=4 et:


namespace XLite\Module\ProaxIT\RequestPriceQuote\View;

/**
 * Contact for Price View for when Price is Zero
 */
class Price extends \XLite\View\Price implements \XLite\Base\IDecorator
{
    /**
     * Return widget default template
     *
     * @return string
     */
    protected function getDefaultTemplate()
    {
        return '/modules/ProaxIT/RequestPriceQuote/request_price_quote/price_plain.tpl';
    }

    /**
     * Register CSS files
     *
     * @return array
     */
    public function getCSSFiles()
    {
        $list = parent::getCSSFiles();
        $list[] = '/modules/ProaxIT/RequestPriceQuote/request_price_quote/css/style.css';
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
        $list[] =  '/modules/ProaxIT/RequestPriceQuote/request_price_quote/js/price.js';
        return $list;
    }
}