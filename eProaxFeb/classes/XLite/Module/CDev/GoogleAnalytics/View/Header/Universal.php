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

namespace XLite\Module\CDev\GoogleAnalytics\View\Header;

/**
 * Header declaration (Universal)
 *
 * @ListChild (list="head")
 */
class Universal extends \XLite\Module\CDev\GoogleAnalytics\View\Header\AHeader
{
    /**
     * Return widget default template
     *
     * @return string
     */
    protected function getDefaultTemplate()
    {
        return 'modules/CDev/GoogleAnalytics/header/universal.tpl';
    }

    /**
     * Get GA options list
     *
     * @return array
     */
    protected function getGAOptions()
    {
        $str = "'create', '"
            . \XLite\Core\Config::getInstance()->CDev->GoogleAnalytics->ga_account
            . "', 'auto'";

        if (2 == \XLite\Core\Config::getInstance()->CDev->GoogleAnalytics->ga_tracking_type) {
            $str .= ', {cookieDomain: \'.\' + self.location.host.replace(/^[^\.]+\./, \'\')}';

        } elseif (3 == \XLite\Core\Config::getInstance()->CDev->GoogleAnalytics->ga_tracking_type) {
            $str .= ", {'allowLinker': true}";
        }

        $list = array($str);
        $list[] = "'send', 'pageview'";

        $controller = \XLite::getController();

        if ($controller instanceof \XLite\Controller\Customer\CheckoutSuccess) {
            $orders = \XLite\Core\Session::getInstance()->gaProcessedOrders;
            if (!is_array($orders)) {
                $orders = array();
            }

            $order = $this->getOrder();

            if (
                !in_array($order->getOrderId(), $orders)
                && $order->getProfile()
            ) {

                $bAddress = $order->getProfile()->getBillingAddress();
                $city = $bAddress ? $bAddress->getCity() : '';
                $state = ($bAddress && $bAddress->getState()) ? $bAddress->getState()->getState() : '';
                $country = ($bAddress && $bAddress->getCountry()) ? $bAddress->getCountry()->getCountry() : '';

                $tax = $order->getSurchargeSumByType(\XLite\Model\Base\Surcharge::TYPE_TAX);
                $shipping = $order->getSurchargeSumByType(\XLite\Model\Base\Surcharge::TYPE_SHIPPING);

                $list[] = "'require', 'ecommerce'";
                $list[] = "'ecommerce:addTransaction', {"
                    . "'id': '" . $order->getOrderNumber() . "', "
                    . "'affiliation': '" . $this->escapeJavascript(\XLite\Core\Config::getInstance()->Company->company_name) . "', "
                    . "'revenue': '" . $order->getTotal() . "', "
                    . "'tax': '" . $tax . "', "
                    . "'shipping': '" . $shipping . "'}";

                foreach ($order->getItems() as $item) {

                    $list[] = "'ecommerce:addItem', {"
                        . "'id': '" . $order->getOrderNumber() . "', "
                        . "'sku': '" . $this->escapeJavascript($item->getSku()) . "', "
                        . "'name': '" . $this->escapeJavascript($item->getName()) . "', "
                        . "'price': '" . $item->getPrice() . "', "
                        . "'quantity': '" . $item->getAmount() . "'}";
                }

                $list[] = "'ecommerce:send'";

                $orders[] = $order->getOrderId();
                \XLite\Core\Session::getInstance()->gaProcessedOrders = $orders;
            }
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
            && $this->useUniversalAnalytics();
    }
}
