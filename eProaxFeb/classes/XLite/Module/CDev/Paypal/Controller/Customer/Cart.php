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

namespace XLite\Module\CDev\Paypal\Controller\Customer;

/**
 * Checkout controller
 */
class Cart extends \XLite\Controller\Customer\Cart implements \XLite\Base\IDecorator
{
    /**
     * Product status in cart
     *
     * @var boolean
     */
    protected $isAddedSuccessfully = false;

    /**
     * Process 'Add item' success
     *
     * @return void
     */
    protected function processAddItemSuccess()
    {
        // todo: rewrite add2cartPopup top message block
        // parent::processAddItemSuccess();

        if (\XLite\Module\CDev\Paypal\Main::isExpressCheckoutEnabled()) {
            if (!\XLite\Core\Request::getInstance()->expressCheckout) {
                \XLite\Core\TopMessage::addInfo(new \XLite\Module\CDev\Paypal\View\Button\TopMessage\ExpressCheckout());
            }
        } else {
            parent::processAddItemSuccess();
        }
    }

    /**
     * URL to return after product is added
     *
     * @return string
     */
    protected function setURLToReturn()
    {
        if (\XLite\Core\Request::getInstance()->expressCheckout) {
            $params = array(
                'cancelUrl' => \XLite\Core\Request::getInstance()->cancelUrl,
            );

            if (\XLite\Core\Request::getInstance()->inContext) {
                $params['inContext'] = true;
            }

            $this->setReturnURL($this->buildURL('checkout', 'start_express_checkout', $params));

        } else {
            parent::setURLToReturn();
        }
    }
}
