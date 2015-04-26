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
 * Paypal Credit (Merchant API) payment processor
 */
class PaypalCreditMerchantAPI extends \XLite\Module\CDev\Paypal\Model\Payment\Processor\ExpressCheckoutMerchantAPI
{
    /**
     * Knowledge base page URL
     *
     * @var string
     */
    protected $knowledgeBasePageURL = 'https://www.paypal.com/webapps/mpp/promotional-financing';

    /**
     * Get payment method row checkout template
     *
     * @param \XLite\Model\Payment\Method $method Payment method
     *
     * @return string
     */
    public function getCheckoutTemplate(\XLite\Model\Payment\Method $method)
    {
        return 'modules/CDev/Paypal/checkout/paypalCredit.tpl';
    }

    /**
     * Get the list of merchant countries where this payment processor can work
     *
     * @return array
     */
    public function getAllowedMerchantCountries()
    {
        return array('US');
    }

    /**
     * Add Paypal Credit parameters to SET_EXPRESS_CHECKOUT request
     *
     * @return array
     */
    protected function getSetExpressCheckoutRequestParams()
    {
        $postData = parent::getSetExpressCheckoutRequestParams();

        $postData['SOLUTIONTYPE'] = 'Sole';
        $postData['USERSELECTEDFUNDINGSOURCE'] = 'BML';
        $postData['LANDINGPAGE'] = 'Billing';

        return $postData;
    }

    /**
     * Get setting value by name
     *
     * @param string $name Name
     *
     * @return mixed
     */
    protected function getSetting($name)
    {
        return $this->getExpressCheckoutMethod()->getSetting($name);
    }

    /**
     * Get express checkout method
     *
     * @return \XLite\Model\Payment\Method
     */
    protected function getExpressCheckoutMethod()
    {
        return \XLite\Core\Database::getRepo('XLite\Model\Payment\Method')
            ->findOneBy(array('service_name' => \XLite\Module\CDev\Paypal\Main::PP_METHOD_EC));
    }
}
