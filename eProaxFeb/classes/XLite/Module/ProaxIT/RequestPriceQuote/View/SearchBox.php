<?php
// vim: set ts=4 sw=4 sts=4 et:

namespace XLite\Module\ProaxIT\RequestPriceQuote\View;

/**
 * Add Request Price Handler to Instant Search Box
 */

class SearchBox extends \XLite\View\Form\Product\Search\Customer\Simple implements \XLite\Base\IDecorator
{
    /**
     * getJSFiles
     *
     * @return array
     */
    public function getJSFiles()
    {
        $list = parent::getJSFiles();

        $list[] = '/modules/ProaxIT/RequestPriceQuote/request_price_quote/js/popupPriceQuote.js';
        $list[] = '/modules/ProaxIT/RequestPriceQuote/request_price_quote/js/request-price-quote-popup.js';

        return $list;
    }

    /**
     * Get a list of CSS files required to display the widget properly
     *
     * @return array
     */
    public function getCSSFiles()
    {
        $list = parent::getCSSFiles();

        $list[] = 'modules/ProaxIT/RequestPriceQuote/request_price_quote/css/style.css';

        return $list;
    }
}
