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

namespace XLite\Module\XC\MultiCurrency\Core;

/**
 * Cache decorator
 */
class CurrencyRate extends \XLite\Base
{
    const PROVIDER_NONE             = 'none';
    const PROVIDER_WEBSERVICE_X     = 'webx';
    const PROVIDER_GOOGLE_FINANCE   = 'google';

    /**
     * Rate provider
     *
     * @var \XLite\Module\XC\MultiCurrency\Core\RateProvider\ARateProvider
     */
    protected $rateProvider = null;

    /**
     * Get currency conversion rate
     *
     * @param string $to Destination currency code (alpha-3)
     *
     * @return float
     */
    public function getRate($to)
    {
        $currency = \XLite\Core\Database::getRepo('XLite\Model\Currency')->find(
            \XLite\Core\Config::getInstance()->General->shop_currency
        );

        $from = $currency->getCode();

        return $this->getRateProvider()->getRate($from, $to);
    }

    /**
     * Protected constructor.
     * It's not possible to instantiate a derived class (using the "new" operator)
     * until that child class is not implemented public constructor
     *
     * @return void
     */
    protected function __construct()
    {
        parent::__construct();

        if (static::PROVIDER_WEBSERVICE_X == \XLite\Core\Config::getInstance()->XC->MultiCurrency->rateProvider) {
            $this->rateProvider = new \XLite\Module\XC\MultiCurrency\Core\RateProvider\WebserviceX();
        } else {
            $this->rateProvider = new \XLite\Module\XC\MultiCurrency\Core\RateProvider\GoogleFinance();
        }
    }

    /**
     * Get rate provider
     *
     * @return \XLite\Module\XC\MultiCurrency\Core\RateProvider\ARateProvider
     */
    protected function getRateProvider()
    {
        return $this->rateProvider;
    }
}