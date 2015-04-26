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

namespace XLite\Module\XC\MultiCurrency\Core\RateProvider;

/**
 * Cache decorator
 */
class GoogleFinance extends \XLite\Module\XC\MultiCurrency\Core\RateProvider\ARateProvider
{
    /**
     * URL to post request for rate
     *
     * @var string
     */
    protected $url = 'http://www.google.com/finance/converter';

    /**
     * Get currency conversion rate
     *
     * @param string $from Source currency code (alpha-3)
     * @param string $to   Destination currency code (alpha-3)
     *
     * @return float
     */
    public function getRate($from, $to)
    {
        $result = null;

        $data = array(
            'a'    => 1,
            'from' => $from,
            'to'   => $to,
        );

        $postData = array();

        foreach ($data as $k => $v) {
            $postData[] = "$k=$v";
        }

        $request = new \XLite\Core\HTTP\Request($this->url . '?' . implode('&', $postData));

        $request->verb = 'GET';

        $response = $request->sendRequest();

        if (!empty($response->body)) {
            $rate = $this->parseResponse($from, $to, $response->body);

            if ($rate) {
                $result = doubleval($rate);
            }
        }

        return $result;
    }


    /**
     * Parse server response
     *
     * @param string $from     Source currency code (alpha-3)
     * @param string $to       Destination currency code (alpha-3)
     * @param string $response Server response
     *
     * @return string
     */
    protected function parseResponse($from, $to, $response)
    {
        $result = null;

        $pattern = sprintf('1 %s =.*([\d.]+) %s', $from, $to);

        if (preg_match('/' . $pattern . '/SsU', $response, $match)) {
            $result = $match[1];
        }

        return $result;
    }
}