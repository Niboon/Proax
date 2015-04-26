<?php
// vim: set ts=4 sw=4 sts=4 et:

namespace XLite\Module\ProaxIT\QuickOrderWidget\Controller\Customer;

/**
 * Request price quote controller
 */
class QuickOrderWidget extends \XLite\Controller\Customer\ACustomer
{

    /**
     * Return the current page title (for the content area)
     *
     * @return string
     */
    public function getTitle()
    {
        return 'Quick Order';
    }


    /**
     * Common method to determine current location
     *
     * @return string
     */
    protected function getLocation()
    {
        return $this->getTitle();
    }

    /**
     * Send message
     *
     * @return void
     */
    protected function doActionAdd()
    {
        $data = \XLite\Core\Request::getInstance()->getData();
        $config = \XLite\Core\Config::getInstance()->ProaxIT->QuickOrderWidget;
        $numberOfFields = $config->number_of_fields ? : 10;

        for ($c = 0; $c < $numberOfFields; $c ++) {
            unset($data['sku' . $c]);
            unset($data['quantity' . $c]);
        }

        \XLite\Core\TopMessage::addInfo('You products have been added to the cart');

        \XLite\Core\Session::getInstance()->quick_order_widget = $data;
    }

//    /**
//     * Return value of data
//     *
//     * @param string $field Field
//     *
//     * @return string
//     */
//    public function getValue($field)
//    {
//        $data = \XLite\Core\Session::getInstance()->request_price_quote;
//        $value = $data && isset($data[$field]) ? $data[$field] : '';
//
//        if (!$value) {
//            $auth = \XLite\Core\Auth::getInstance();
//            switch ($field) {
//                case 'name': {
//                    if (
//                        $auth->isLogged()
//                        && 0 < $auth->getProfile()->getAddresses()->count()
//                    ) {
//                        return $auth->getProfile()->getAddresses()->first()->getName();
//                    } else {
//                        return '';
//                    }
//                }
//                case 'email': {
//                    if ($auth->isLogged()) {
//                        return $auth->getProfile()->getLogin();
//                    } else {
//                        return '';
//                    }
//                }
//                case 'customerId': {
//                    $customerId = 'N/A';
//                    if ($auth->isLogged()) {
//                        $customerId = $auth->getProfile()->getCustomerId();
//                    }
//                    return $customerId;
//                }
//                case 'sku': {
//                    $popupData = \XLite\Core\Request::getInstance()->getData();
//                    if ($data['sku']) {
//                        $sku = $data['sku'];
//                    } elseif ($popupData['sku']) {
//                        $sku = $popupData['sku'];
//                    } elseif ($this->getParam('sku')) {
//                        $sku = $this->getParam('sku');
//                    } else {
//                        $sku = '[Enter Product SKU Here]';
//                    }
//                    return $sku;
//                }
//                default: {
//                    return '';
//                }
//            }
//        } else {
//            return $value;
//        }
//
//    }
}
