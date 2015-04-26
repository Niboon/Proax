<?php
// vim: set ts=4 sw=4 sts=4 et:

namespace XLite\Module\ProaxIT\RequestPriceQuote\View\ItemsList\Product;

/**
 * Add Popup JS and CSS to product Search results
 */
abstract class AProduct extends \XLite\View\ItemsList\Product\AProduct implements \XLite\Base\IDecorator
{
    /**
     * getJSFiles
     *
     * @return array
     */
    public function getJSFiles()
    {
        $list = parent::getJSFiles();


        if ($key = array_search('items_list/product/products_list.js', $list)) {
            unset($list[$key]);
        }
        $list[] = '/modules/ProaxIT/RequestPriceQuote/request_price_quote/js/products_list.js';

        $list[] = '/modules/ProaxIT/RequestPriceQuote/request_price_quote/js/popupPriceQuote.js';

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
