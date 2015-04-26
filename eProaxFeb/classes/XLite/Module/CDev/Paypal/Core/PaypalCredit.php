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
 * PaypalCredit
 */
class PaypalCredit extends \XLite\Base\Singleton
{
    /**
     * API settings
     */
    const CLIENT_KEY = '6e5ed40774ca7b82a7e5c61ec86533e3f1f65386';
    const SHARED_SECRET = '034105372bfb99f86c5b5a6c5efc6df02349f0d9';
    const END_POINT = 'https://api.financing.paypal.com/finapi/v1/publishers/';
    const BN_CODE = 'XCart_Cart';

    /**
     * Get publisher id
     *
     * @param string $email Email
     *
     * @return string
     */
    public function getPublisherId($email)
    {
        $publisherId = null;

        $sellerName = \XLite\Core\Config::getInstance()->Company->company_name;

        $data = array(
            'sellerName' => $sellerName,
            'emailAddress' => $email,
            'bnCode' => static::BN_CODE,
        );

        $request = new \XLite\Core\HTTP\Request(static::END_POINT);

        if (function_exists('curl_version')) {
            $request->setAdditionalOption(\CURLOPT_SSLVERSION, 1);
            $curlVersion = curl_version();

            if (
                $curlVersion
                && $curlVersion['ssl_version']
                && 0 !== strpos($curlVersion['ssl_version'], 'NSS')
            ) {
                $request->setAdditionalOption(\CURLOPT_SSL_CIPHER_LIST, 'TLSv1');
            }
        }

        $request->body = json_encode($data);
        $request->verb = 'POST';

        $timeStamp = LC_START_TIME . '000';
        $authorization = 'FPA ' . static::CLIENT_KEY;
        $authorization .= ':' . sha1(static::SHARED_SECRET . $timeStamp);
        $authorization .= ':' . $timeStamp;

        $request->setHeader('Authorization', $authorization);
        $request->setHeader('Accept', 'application/json');
        $request->setHeader('Content-Type', 'application/json');

        $response = $request->sendRequest();

        \XLite\Module\CDev\Paypal\Main::addLog('getPublisherId', $response->body);

        if (201 == $response->code) {
            $responseData = json_decode($response->body, true);

            if ($responseData && isset($responseData['publisherId'])) {
                $publisherId = $responseData['publisherId'];
            }
        }

        return $publisherId;
    }
}
