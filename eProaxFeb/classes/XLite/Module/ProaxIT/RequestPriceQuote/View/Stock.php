<?php
// vim: set ts=4 sw=4 sts=4 et:


namespace XLite\Module\ProaxIT\RequestPriceQuote\View;

/**
 * Display Available Stock Quantity
 */
class Stock extends \XLite\View\Product\Details\Customer\Widget
{
    /**
     * Return widget default template
     *
     * @return string
     */
    protected function getDefaultTemplate()
    {
        return '/modules/ProaxIT/RequestPriceQuote/request_price_quote/stock_plain.tpl';
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
        $list[] =  '/modules/ProaxIT/RequestPriceQuote/request_price_quote/js/stock.js';
        return $list;
    }

    /**
     * Return the specific widget service name to make it visible as specific CSS class
     *
     * @return null|string
     */
    public function getFingerprint()
    {
        return 'widget-fingerprint-product-stock';
    }

    /**
     * @param  \XLite\Model\Product $product
     */
    public function getStockPreview(\XLite\Model\Product $product)
    {
        $amount = $product->inventory->amount;
        if ($amount == 0) {
            $stockPreview = 'Not';
        } elseif ($amount < 10) {
            $stockPreview = '';
        } elseif ($amount < 50) {
            $stockPreview = '10+';
        } elseif ($amount < 100) {
            $stockPreview = '50+';
        } elseif ($amount < 1000) {
            $stockPreview = '100+';
        } else {
            $stockPreview = '1000+';
        }

        return $stockPreview;
    }

}