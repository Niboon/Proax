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
 * @copyright Copyright (c) 2011-2014 Qualiteam software Ltd <info@x-cart.com>. All rights reserved
 * @license   http://www.x-cart.com/license-agreement.html X-Cart 5 License Agreement
 * @link      http://www.x-cart.com/
 */

/**
 * REST API client 
 */
class RESTAPIClient extends \Guzzle\Http\Client
{
    /**
     * Factory 
     *
     * @param string $url URL
     * @param string $key Access key
     *  
     * @return \RESTAPIClient
     */
    public static function factory($url, $key)
    {
        $client = new static($url);
        $client->setKey($key);

        return $client;
    }

    /**
     * Set key
     *
     * @param string $key Access key
     *
     * @return void
     */
    public function setKey($key)
    {
        $this->key = $key;
    }

    /**
     * Get key
     *
     * @return string
     */
    public function getKey()
    {
        return $this->key;
    }

    // {{{ Interfaces

    /**
     * Create a GET request for the client
     *
     * @param string|array                              $uri     Resource URI
     * @param array|Collection                          $headers HTTP headers
     * @param string|resource|array|EntityBodyInterface $body    Where to store the response entity body
     *
     * @return RequestInterface
     * @see    \Guzzle\Http\ClientInterface::createRequest()
     */
    public function get($uri = null, $headers = null, $body = null)
    {
        return parent::get($this->assembleAPIURI($uri), $headers, $body);
    }

    /**
     * Create a HEAD request for the client
     *
     * @param string|array     $uri     Resource URI
     * @param array|Collection $headers HTTP headers
     *
     * @return RequestInterface
     * @see    Guzzle\Http\ClientInterface::createRequest()
     */
    public function head($uri = null, $headers = null, array $options = array())
    {
        throw new \Exception('HEAD request not supported');
    }

    /**
     * Create a DELETE request for the client
     *
     * @param string|array     $uri     Resource URI
     * @param array|Collection $headers HTTP headers
     * @param string|resource|EntityBodyInterface $body    Body to send in the request
     *
     * @return EntityEnclosingRequestInterface
     * @see    Guzzle\Http\ClientInterface::createRequest()
     */
    public function delete($uri = null, $headers = null, $body = null, array $options = array())
    {
        return parent::delete($this->assembleAPIURI($uri), $headers, $body, $options);
    }

    /**
     * Create a PUT request for the client
     *
     * @param string|array                        $uri     Resource URI
     * @param array|Collection                    $headers HTTP headers
     * @param string|resource|EntityBodyInterface $body    Body to send in the request
     *
     * @return EntityEnclosingRequestInterface
     * @see    Guzzle\Http\ClientInterface::createRequest()
     */
    public function put($uri = null, $headers = null, $body = null, array $options = array())
    {
        if (is_array($body)) {
            $body = http_build_query(array('model' => $body));
        }

        return parent::put($this->assembleAPIURI($uri), $headers, $body, $options);
    }

    /**
     * Create a PATCH request for the client
     *
     * @param string|array                        $uri     Resource URI
     * @param array|Collection                    $headers HTTP headers
     * @param string|resource|EntityBodyInterface $body    Body to send in the request
     *
     * @return EntityEnclosingRequestInterface
     * @see    Guzzle\Http\ClientInterface::createRequest()
     */
    public function patch($uri = null, $headers = null, $body = null, array $options = array())
    {
        throw new \Exception('PATCH request not supported');
    }

    /**
     * Create a POST request for the client
     *
     * @param string|array                                $uri      Resource URI
     * @param array|Collection                            $headers  HTTP headers
     * @param array|Collection|string|EntityBodyInterface $postBody POST body. Can be a string, EntityBody, or
     *                                                    associative array of POST fields to send in the body of the
     *                                                    request.  Prefix a value in the array with the @ symbol to
     *                                                    reference a file.
     * @return EntityEnclosingRequestInterface
     * @see    Guzzle\Http\ClientInterface::createRequest()
     */
    public function post($uri = null, $headers = null, $postBody = null, array $options = array())
    {
        return parent::post($this->assembleAPIURI($uri), $headers, $postBody, $options);
    }

    /**
     * Create an OPTIONS request for the client
     *
     * @param string|array $uri Resource URI
     *
     * @return RequestInterface
     * @see    Guzzle\Http\ClientInterface::createRequest()
     */
    public function options($uri = null, array $options = array())
    {
        throw new \Exception('OPTIONS request not supported');
    }

    /**
     * Assemble full URI
     *
     * @param string $uri Short URI
     *
     * @return string
     */
    protected function assembleAPIURI($uri)
    {
        return $this->getBaseUrl() . '?target=RESTAPI&_key=' . $this->getKey() . '&_path=' . $uri;
    }

    // }}}

}

