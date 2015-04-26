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
 * Login
 */
class Login extends \XLite\Module\CDev\Paypal\Core\RESTAPI
{
    // {{{ Config

    /**
     * Check configuration
     *
     * @return boolean
     */
    public static function isConfigured()
    {
        return \Xlite\Core\Config::getInstance()->CDev->Paypal->loginClientId
            && \Xlite\Core\Config::getInstance()->CDev->Paypal->loginClientSecret;
    }

    /**
     * Is test mode
     *
     * @return boolean
     */
    public function isTestMode()
    {
        return 'live' !== \Xlite\Core\Config::getInstance()->CDev->Paypal->loginMode;
    }

    /**
     * Check request
     *
     * @return boolean
     */
    public function checkRequest()
    {
        return (bool) \XLite\Core\Request::getInstance()->code
            && 'PayPal' == \XLite\Core\Request::getInstance()->auth_provider;
    }

    /**
     * Returns scope
     *
     * @return array
     */
    protected function getScope()
    {
        return array(
            'openid', 'email', 'profile', 'address', 'phone',
            'https://uri.paypal.com/services/expresscheckout'
        );
    }

    // }}}

    // {{{ SignIn

    /**
     * Get SignIn url
     *
     * @param string $returnUrl Return url
     *
     * @return string
     */
    public function getSignInURL($returnUrl)
    {
        $url = $this->isTestMode()
            ? 'https://www.sandbox.paypal.com/webapps/auth/protocol/openidconnect'
            : 'https://www.paypal.com/webapps/auth/protocol/openidconnect';

        $params = array(
            'client_id'     => \Xlite\Core\Config::getInstance()->CDev->Paypal->loginClientId,
            'response_type' => 'code',
            'scope'         => implode(' ', $this->getScope()),
            'redirect_uri'  => $returnUrl
        );

        return sprintf('%s/v1/authorize?%s', $url, http_build_query($params));
    }

    // }}}

    // {{{ Token (from authorisation code)

    /**
     * Retrieve user info token
     *
     * @param string $code Code
     *
     * @return mixed
     */
    public function createFromAuthorisationCode($code)
    {
        $params = array('code' => $code);

        return $this->doRequest('createFromAuthorisationCode', $params);
    }

    /**
     * Prepare url
     *
     * @param string $url    Request url
     * @param array  $params Request params
     *
     * @return string
     * @see    https://developer.paypal.com/docs/api/#authentication--headers
     */
    protected function prepareCreateFromAuthorisationCodeUrl($url, $params)
    {
        return $url . '/v1/identity/openidconnect/tokenservice';
    }

    /**
     * Prepare body
     *
     * @param array $params Request params
     *
     * @return string
     */
    protected function prepareCreateFromAuthorisationCodeParams($params)
    {
        return $params + array('grant_type' => 'authorization_code');
    }

    // }}}

    // {{{ Token (from refresh token)

    /**
     * Retrieve user info token
     *
     * @param string $token Refresh token
     *
     * @return mixed
     */
    public function createFromRefreshToken($token)
    {
        $params = array('refresh_token' => $token);

        return $this->doRequest('createFromAuthorisationCode', $params);
    }

    /**
     * Prepare url
     *
     * @param string $url    Request url
     * @param array  $params Request params
     *
     * @return string
     * @see    https://developer.paypal.com/docs/api/#authentication--headers
     */
    protected function prepareCreateFromRefreshTokenUrl($url, $params)
    {
        return $url . '/v1/identity/openidconnect/tokenservice';
    }

    /**
     * Prepare body
     *
     * @param array $params Request params
     *
     * @return string
     */
    protected function prepareCreateFromRefreshTokenParams($params)
    {
        return $params + array(
            'grant_type' => 'refresh_token',
            'scope'      => $this->getScope()
        );
    }

    // }}}

    // {{{ User info

    /**
     * Retrieve user info
     *
     * @param string $accessToken Access token
     *
     * @return mixed
     */
    public function getUserinfo($accessToken)
    {
        $params = array($accessToken);

        return $this->doRequest('getUserinfo', $params);
    }

    /**
     * Prepare url
     *
     * @param string $url    Request url
     * @param array  $params Request params
     *
     * @return string
     * @see    https://developer.paypal.com/docs/api/#authentication--headers
     */
    protected function prepareGetUserinfoUrl($url, $params)
    {
        return $url . '/v1/identity/openidconnect/userinfo?schema=openid';
    }

    /**
     * Prepare request object
     *
     * @param \XLite\Core\HTTP\Request $request Request object
     * @param array                    $params  Request params
     *
     * @return \XLite\Core\HTTP\Request
     * @see    https://developer.paypal.com/docs/api/#authentication--headers
     */
    protected function prepareGetUserinfoRequest($request, $params)
    {
        list($accessToken) = $params;

        $request->setHeader('Authorization', sprintf('Bearer %s', $accessToken));
        $request->verb = 'GET';

        return $request;
    }

    // }}}

    // {{{ Backend request

    /**
     * Prepare request
     *
     * @param \XLite\Core\HTTP\Request $request Request
     * @param string                   $type    Request type
     * @param array                    $params  Request params
     *
     * @return \XLite\Core\HTTP\Request
     */
    protected function prepareRequest($request, $type, $params)
    {
        $request->setHeader('Accept', 'application/json');
        $request->setHeader('Accept-Language', 'en_US');
        $request->setHeader('Content-Type', 'application/x-www-form-urlencoded');

        $authorization = base64_encode(
            sprintf(
                '%s:%s',
                \Xlite\Core\Config::getInstance()->CDev->Paypal->loginClientId,
                \Xlite\Core\Config::getInstance()->CDev->Paypal->loginClientSecret
            )
        );
        $request->setHeader('Authorization', sprintf('Basic %s', $authorization));

        return parent::prepareRequest($request, $type, $params);
    }

    // }}}
}
