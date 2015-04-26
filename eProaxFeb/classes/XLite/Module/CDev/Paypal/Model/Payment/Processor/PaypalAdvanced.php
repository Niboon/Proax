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

namespace XLite\Module\CDev\Paypal\Model\Payment\Processor;

/**
 * Paypal Payments Advanced payment processor
 */
class PaypalAdvanced extends \XLite\Module\CDev\Paypal\Model\Payment\Processor\APaypal
{
    /**
     * Referral page URL 
     * 
     * @var string
     */
    protected $referralPageURL = 'https://www.paypal.com/webapps/mpp/referral/paypal-payments-advanced?partner_id=';

    /**
     * Knowledge base page URL
     *
     * @var string
     */
    protected $knowledgeBasePageURL = 'http://kb.x-cart.com/pages/viewpage.action?pageId=7505725';

    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();

        $method = \XLite\Module\CDev\Paypal\Main::getPaymentMethod(
            \XLite\Module\CDev\Paypal\Main::PP_METHOD_PPA
        );

        $this->api->setMethod($method);
    }

    /**
     * Get the list of merchant countries where this payment processor can work
     *
     * @return array
     */
    public function getAllowedMerchantCountries()
    {
        return array('US', 'CA');
    }

    /**
     * Get allowed currencies
     * https://developer.paypal.com/webapps/developer/docs/classic/payflow/integration-guide/#paypal-currency-codes
     * https://developer.paypal.com/webapps/developer/docs/classic/paypal-payments-pro/integration-guide/WPWebsitePaymentsPro/#id25a6cc16-bbc4-4070-a575-9fad358f2b95__idd1ca306a-3829-4f55-930e-b295702a3e91
     *
     * @param \XLite\Model\Payment\Method $method Payment method
     *
     * @return array
     */
    protected function getAllowedCurrencies(\XLite\Model\Payment\Method $method)
    {
        return array_merge(
            parent::getAllowedCurrencies($method),
            array(
                'AUD', 'CAD', 'CZK', 'DKK', 'EUR',
                'HKD', 'HUF', 'JPY', 'NOK', 'NZD',
                'PLN', 'GBP', 'SGD', 'SEK', 'CHF',
                'USD',
            )
        );
    }

    /**
     * Return array of parameters for 'CAPTURE' request
     *
     * @param \XLite\Model\Payment\BackendTransaction $transaction Transaction
     *
     * @return array
     */
    protected function getCaptureRequestParams(\XLite\Model\Payment\BackendTransaction $transaction)
    {
        $params = parent::getCaptureRequestParams($transaction);

        $params['CAPTURECOMPLETE'] = 'Y';

        return $params;
    }
}
