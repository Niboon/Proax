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

namespace XLite\Model\Payment\Base;

/**
 * Abstract credit card, web-based processor
 */
abstract class WebBased extends \XLite\Model\Payment\Base\CreditCard
{
    /**
     * Form method (only for web-based processor)
     */
    const FORM_METHOD_POST = 'post';
    const FORM_METHOD_GET  = 'get';


    /**
     * Get redirect form URL
     *
     * @return string
     */
    abstract protected function getFormURL();

    /**
     * Get redirect form fields list
     *
     * @return array
     */
    abstract protected function getFormFields();


    /**
     * Get return request owner transaction or null
     *
     * @return \XLite\Model\Payment\Transaction|void
     */
    public function getReturnOwnerTransaction()
    {
        return null;
    }

    /**
     * Do custom redirect after customer's return
     *
     * @return void
     */
    public function doCustomReturnRedirect()
    {
    }

    /**
     * Do initial payment
     *
     * @return string Status code
     */
    protected function doInitialPayment()
    {
        $method     = $this->getFormMethod();
        $url        = $this->getFormURL();
        $formFields = $this->getFormFields();
        $body       = $this->assembleFormBody($formFields);

        $this->logRedirect($formFields);

        if ($body) {
            $page = <<<HTML
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en" dir="ltr">
<head>
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
</head>
<body onload="javascript: document.getElementById('form').submit();">
  <form method="$method" id="form" name="payment_form" action="$url">
    <fieldset style="display: none;">
$body
    </fieldset>
    <noscript>
      If you are not redirected within 3 seconds, please <input type="submit" value="press here" />.
    </noscript>
  </form>
</body>
</html>
HTML;
            print ($page);
            $status = static::PROLONGATION;

        } else {
            $this->writeEmptyBodyErrorMessage();
            $status = static::FAILED;
            $this->transaction->setNote('Payment is failed');
        }

        return $status;
    }

    /**
     * Write empty body error message 
     * 
     * @return void
     */
    protected function writeEmptyBodyErrorMessage()
    {
        $this->setDetail(
            'webbased_data_error',
            'Payment processor \'' . get_called_class() . '\' did not assemble form data successfull.'
        );
    }

    /**
     * Get form method
     *
     * @return string
     */
    protected function getFormMethod()
    {
        return self::FORM_METHOD_POST;
    }

    /**
     * Assemble form body (field set)
     *
     * @param array $formFields Form fields list
     *
     * @return string HTML
     */
    protected function assembleFormBody(array $formFields)
    {
        $inputs = array();

        foreach ($formFields as $name => $value) {
            $inputs[] = '<input type="hidden" name="' . htmlspecialchars($name)
                . '" value="' . htmlspecialchars($value) . '" />';
        }

        $body = $inputs
            ? '      ' . implode("\n" . '      ', $inputs)
            : '';

        return $body;
    }

    /**
     * Log redirect form
     *
     * @param array $list Form fields list
     *
     * @return void
     */
    protected function logRedirect(array $list)
    {
        \XLite\Logger::getInstance()->log(
            $this->transaction->getPaymentMethod()->getServiceName() . ' payment gateway : redirect' . PHP_EOL
            . 'Method: ' . $this->getFormMethod() . PHP_EOL
            . 'URL: ' . $this->getFormURL() . PHP_EOL
            . 'Data: ' . var_export($list, true),
            LOG_DEBUG
        );
    }
}
