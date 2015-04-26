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
 * @copyright Copyright (c) 2011-2015 Qualiteam software Ltd <info@x-cart.com>. All rights reserved
 * @license   http://www.x-cart.com/license-agreement.html X-Cart 5 License Agreement
 * @link      http://www.x-cart.com/
 */

namespace XLite\Module\CDev\GoogleAnalytics\View;

/**
 * Additional bloc for Checkout success page
 *
 * @ListChild (list="center")
 */
class CheckoutSuccess extends \XLite\View\AView
{
    /**
     * Return list of allowed targets
     *
     * @return array
     */
    public static function getAllowedTargets()
    {
        $list = parent::getAllowedTargets();

        $list[] = 'checkoutSuccess';

        return $list;
    }

    /**
     * Return widget default template
     *
     * @return string
     */
    protected function getDefaultTemplate()
    {
        return 'modules/CDev/GoogleAnalytics/drupal.tpl';
    }

    /**
     * Get account id from Drupal module
     *
     * @return string
     */
    protected function getAccount()
    {
        return variable_get('googleanalytics_account', '');
    }

    /**
     * Get commands for _gat
     *
     * @return void
     */
    protected function getGatCommands()
    {
        $list = array();

        $orders = \XLite\Core\Session::getInstance()->gaProcessedOrders;
        if (!is_array($orders)) {
            $orders = array();
        }

        $order = $this->getOrder();
        if (!in_array($order->getOrderId(), $orders)) {

            $bAddress = $order->getProfile()->getBillingAddress();
            $city = $bAddress ? $bAddress->getCity() : '';
            $state = ($bAddress && $bAddress->getState()) ? $bAddress->getState()->getState() : '';
            $country = ($bAddress && $bAddress->getCountry()) ? $bAddress->getCountry()->getCountry() : '';

            $tax = $order->getSurchargeSumByType('TAX');
            $shipping = $order->getSurchargeSumByType('SHIPPING');

            $list[] = '\'_addTrans\', '
                . '\'' . $order->getOrderId() . '\', '
                . '\'' . $this->escapeJavascript(\XLite\Core\Config::getInstance()->Company->company_name) . '\', '
                . '\'' . $order->getTotal() . '\', '
                . '\'' . $tax . '\', '
                . '\'' . $shipping . '\', '
                . '\'' . $this->escapeJavascript($city) . '\', '
                . '\'' . $this->escapeJavascript($state) . '\', '
                . '\'' . $this->escapeJavascript($country) . '\'';

            foreach ($order->getItems() as $item) {

                $product = $item->getProduct();
                $category = $product ? $product->getCategory() : null;
                if ($category && $category->getCategoryId()) {
                    $categories = \XLite\Core\Database::getRepo('XLite\Model\Category')
                        ->getCategoryPath($category->getCategoryId());
                    $category = array();
                    foreach ($categories as $cat) {
                        $category[] = $cat->getName();
                    }

                    $category = implode(' / ', $category);

                } else {
                    $category = '';
                }

                $list[] = '\'_addItem\', '
                    . '\'' . $order->getOrderId() . '\', '
                    . '\'' . $this->escapeJavascript($item->getSku()) . '\', '
                    . '\'' . $this->escapeJavascript($item->getName()) . '\', '
                    . '\'' . $this->escapeJavascript($category) . '\', '
                    . '\'' . $item->getPrice() . '\', '
                    . '\'' . $item->getAmount() . '\'';
            }

            $list[] = '\'_trackTrans\'';

            $orders[] = $order->getOrderId();
            \XLite\Core\Session::getInstance()->gaProcessedOrders = $orders;
        }

        return $list;
    }

    /**
     * Check if widget is visible
     *
     * @return boolean
     */
    protected function isVisible()
    {
        return parent::isVisible()
            && !$this->useUniversalAnalytics()
            && $this->isDisplayDrupal();
    }

    /**
     * Display widget as Drupal-specific
     *
     * @return boolean
     */
    protected function isDisplayDrupal()
    {
        return \XLite\Core\Operator::isClassExists('\XLite\Module\CDev\DrupalConnector\Handler')
            && \XLite\Module\CDev\DrupalConnector\Handler::getInstance()->checkCurrentCMS()
            && function_exists('googleanalytics_help')
            && $this->getAccount();
    }

    /**
     * Escape string for Javascript
     *
     * @param string $string String
     *
     * @return string
     */
    protected function escapeJavascript($string)
    {
        return strtr(
            $string,
            array(
                '\\' => '\\\\',
                '\'' => '\\\'',
                '"'  => '\\"',
                "\r" => '\\r',
                "\n" => '\\n',
                '</' =>'<\/'
            )
        );
    }
}
