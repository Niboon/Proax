<?php
// vim: set ts=4 sw=4 sts=4 et:

/**
 * X-Cart
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the software license agreement
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.x-cart.com/license-agreement.html
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to licensing@x-cart.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not modify this file if you wish to upgrade X-Cart to newer versions
 * in the future. If you wish to customize X-Cart for your needs please
 * refer to http://www.x-cart.com/ for more information.
 *
 * @category  X-Cart 5
 * @author    Qualiteam software Ltd <info@x-cart.com>
 * @copyright Copyright (c) 2011-2014 Qualiteam software Ltd <info@x-cart.com>. All rights reserved
 * @license   http://www.x-cart.com/license-agreement.html X-Cart 5 License Agreement
 * @link      http://www.x-cart.com/
 */

namespace XLite\Module\ProaxIT\RequestPriceQuote\View\Button;

/**
 * Product selection in popup
 */
class PopupPriceQuote extends \XLite\View\Button\APopupButton
{
    const PARAM_SKU = 'sku';

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
     * getCSSFiles
     *
     * @return array
     */
    public function getCSSFiles()
    {
        $list = parent::getCSSFiles();

        $list[] = '/modules/ProaxIT/RequestPriceQuote/request_price_quote/css/hideQty.css';
        $list[] = '/modules/ProaxIT/RequestPriceQuote/request_price_quote/css/style.css';

        return $list;
    }

    /**
     * Defines the widgets from which the CSS/JS files must be taken
     *
     * @return array
     */
    protected function getWidgets()
    {
        return array(
            $this->getSelectorViewClass(),
            '\XLite\Module\ProaxIT\RequestPriceQuote\Form\RequestPriceQuote',
        );
    }

    /**
     * Return URL parameters to use in AJAX popup
     *
     * @return array
     */
    protected function prepareURLParams()
    {
        return array(
            'target'        => $this->getSelectorTarget(),
            'widget'        => $this->getSelectorViewClass(),
            'sku'           => $this->getParam(self::PARAM_SKU)
        );
    }

    /**
     * Define widget params
     *
     * @return void
     */
    protected function defineWidgetParams()
    {
        parent::defineWidgetParams();

        $this->widgetParams += array(
            self::PARAM_SKU => new \XLite\Model\WidgetParam\String('SKU', 0),
        );
    }


    /**
     * Defines the target of the product selector
     * The main reason is to get the title for the selector from the controller
     *
     * @return string
     */
    protected function getSelectorTarget()
    {
        return 'request_price_quote';
    }

    /**
     * Defines the class name of the widget which will display the product list dialog
     *
     * @return string
     */
    protected function getSelectorViewClass()
    {
        return '\XLite\Module\ProaxIT\RequestPriceQuote\View\RequestPriceQuote';
    }

    /**
     * Return CSS classes
     *
     * @return string
     */
    protected function getClass()
    {
        return trim(parent::getClass() . ' popup-request-price-quote ');
    }

    /**
     * getDefaultLabel
     *
     * @return string
     */
    protected function getDefaultLabel()
    {
        return 'Request Price Quote';
    }
}
