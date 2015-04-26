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

namespace XLite\Core;

/**
 * Abstract URL manager
 */
abstract class URLManager extends \Includes\Utils\URLManager
{
    /**
     * Results cache for https check requests
     *
     * @var array
     */
    protected static $requestCache = array();

    /**
     * Check if store is accessible via secure protocol
     *
     * @param string  $url      URL to validate
     * @param boolean $checkSSL Check validity of SSL certificate OPTIONAL
     *
     * @return boolean
     */
    public static function isSecureURLAccessible($url, $checkSSL = false)
    {
        $key = sprintf('%d-%s', intval($checkSSL), $url);

        if (!isset(self::$requestCache[$key])) {

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_HEADER, false);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 15);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, $checkSSL);

            curl_exec($ch);

            $info = curl_getinfo($ch);

            /* FIXME: left for debug purposes. Remove this code later
            \XLite\Logger::logCustom(
                'DEBUG',
                array(
                    'checkSSL' => $checkSSL,
                    'HTTP response code' => var_export($info, true),
                )
            );
             */

            curl_close($ch);

            self::$requestCache[$key] = in_array($info['http_code'], array(200, 301, 302));
        }

        return self::$requestCache[$key];
    }

    /**
     * Return true if specified URL belongs to the allowed domain name
     *
     * @param string  $url    URL
     * @param boolean $strict URL can be relative or just with params (if strict = false) OPTIONAL
     *
     * @return boolean
     */
    public static function isValidDomain($url, $strict = true)
    {
        $result = false;

        $domain = parse_url($url, PHP_URL_HOST);

        if ($domain) {
            $result = in_array($domain, static::getAllowedDomains());

        } elseif (!$strict) {
            $result = true;
        }

        return $result;
    }

    /**
     * Get allowed domains 
     * 
     * @return array
     */
    public static function getAllowedDomains()
    {
        return array_unique(array_merge(static::getShopDomains(), static::getTrustedDomains()));
    }

    /**
     * Get array of trusted domains
     *
     * @return array
     */
    public static function getTrustedDomains()
    {
        $result = array();

        $trustedURLs = \Includes\Utils\ConfigParser::getOptions(array('other', 'trusted_domains'));

        if (!empty($trustedURLs)) {
            $result = array_map('trim', explode(',', $trustedURLs));
        }

        return $result;
    }

    /**
     * Get options
     *
     * @param mixed $option Option
     *
     * @return mixed
     */
    protected static function getOptions($option)
    {
        return \XLite::getInstance()->getOptions($option);
    }

}
