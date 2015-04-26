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

namespace XLite\Module\CDev\Paypal\Core;

/**
 * Google auth provider
 */
class PaypalAuthProvider extends \XLite\Module\CDev\SocialLogin\Core\AAuthProvider
{
    /**
     * Unique auth provider name
     */
    const PROVIDER_NAME = 'paypal';

    /**
     * Url to which user will be redirected
     */
    const AUTH_REQUEST_URL = '';

    /**
     * Data to gain access to
     */
    const AUTH_REQUEST_SCOPE = '';

    /**
     * Url to get access token
     */
    const TOKEN_REQUEST_URL = '';

    /**
     * Url to access user profile information
     */
    const PROFILE_REQUEST_URL = '';

    /**
     * Get OAuth 2.0 client ID
     *
     * @return string
     */
    protected function getClientId()
    {
        return \Xlite\Core\Config::getInstance()->CDev->Paypal->loginClientId;
    }

    /**
     * Get OAuth 2.0 client secret
     *
     * @return string
     */
    protected function getClientSecret()
    {
        return \Xlite\Core\Config::getInstance()->CDev->Paypal->loginClientSecret;
    }
}
