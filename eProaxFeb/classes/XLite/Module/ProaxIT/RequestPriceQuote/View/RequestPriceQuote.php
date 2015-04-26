<?php
// vim: set ts=4 sw=4 sts=4 et:

namespace XLite\Module\ProaxIT\RequestPriceQuote\View;

/**
 * Request price quote widget
 *
 * @ListChild (list="center", zone="customer")
 */
class RequestPriceQuote extends \XLite\View\SimpleDialog
{
    const PARAM_SKU = 'sku';
    /**
     * Return list of allowed targets
     *
     * @return array
     */
    public static function getAllowedTargets()
    {
        $list = parent::getAllowedTargets();
        $list[] = 'request_price_quote';
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
     * Return widget default template
     *
     * @return string
     */
    protected function getDefaultTemplate()
    {
        return 'modules/ProaxIT/RequestPriceQuote/request_price_quote/body.tpl';
    }

    /**
     * Return widget body
     *
     * @return string
     */
    protected function getBody()
    {
        return 'modules/ProaxIT/RequestPriceQuote/request_price_quote/body.tpl';
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
            self::PARAM_SKU => new \XLite\Model\WidgetParam\String('SKU',0),
        );
    }

    /**
     * Return captcha
     *
     * @return string
     */
    protected function getCaptcha()
    {
        $config = \XLite\Core\Config::getInstance()->ProaxIT->RequestPriceQuote;
        $result = '';

        if (
            $config->recaptcha_private_key
            && $config->recaptcha_public_key
        ) {
            require_once LC_DIR_MODULES . '/ProaxIT/RequestPriceQuote/recaptcha/recaptchalib.php';
            $result = recaptcha_get_html($config->recaptcha_public_key);
        }

        return $result;
    }
}
