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
class CheckoutFailed extends \XLite\Controller\Customer\CheckoutFailed implements \XLite\Base\IDecorator
{
    /**
     * Preprocessor for no-action run
     *
     * @return void
     */
    protected function doNoAction()
    {
        if (\XLite\Core\Session::getInstance()->inContextRedirect) {
            unset(\XLite\Core\Session::getInstance()->inContextRedirect);

            echo <<<HTML
            <html><head></head><body>
<script type="text/javascript">

(function(d, s, id){
  var js, ref = d.getElementsByTagName(s)[0];
  if (!d.getElementById(id)){
    js = d.createElement(s); js.id = id; js.async = true;
    js.src = "//www.paypalobjects.com/js/external/paypal.v1.js";
    ref.parentNode.insertBefore(js, ref);
  }
}(document, "script", "paypal-js"));

</script>
            </body></html>
HTML;
            exit;

        } elseif (\XLite\Core\Session::getInstance()->cancelUrl) {
            $this->setReturnURL(\XLite\Core\Session::getInstance()->cancelUrl);

            unset(\XLite\Core\Session::getInstance()->cancelUrl);
        } else {
            parent::doNoAction();
        }
    }
}
