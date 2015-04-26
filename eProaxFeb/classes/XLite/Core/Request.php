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
 * Request
 */
class Request extends \XLite\Base\Singleton
{
    /**
     * Current method
     */
    const METHOD_CLI = 'cli';

    /**
     * Cureent request method
     *
     * @var string
     */
    protected $requestMethod = null;

    /**
     * Request data
     *
     * @var array
     */
    protected $data = array();

    /**
     * Cache for mobile device flag
     *
     * @var null|boolean
     */
    protected static $isMobileDeviceFlag = null;

    /**
     * Drag-n-drop-cart feature is turned off for the mobile devices
     *
     * @return boolean
     */
    public static function isDragDropCartFlag()
    {
        return !static::isMobileDevice();
    }

    /**
     * Detect Mobile version
     *
     * @return boolean
     */
    public static function isMobileDevice()
    {
        if (is_null(static::$isMobileDeviceFlag)) {
            static::$isMobileDeviceFlag = static::detectMobileDevice();
        }

        return static::$isMobileDeviceFlag;
    }

    /**
     * Defines if the device is a tablet
     *
     * @return boolean
     */
    public static function isTablet()
    {
        return \XLite\Core\MobileDetect::getInstance()->isTablet();
    }

    /**
     * Detect if browser is mobile device
     *
     * @return boolean
     */
    public static function detectMobileDevice()
    {
        return \XLite\Core\MobileDetect::getInstance()->isMobile();
    }

    /**
     * Map request data
     *
     * @param array $data Custom data OPTIONAL
     *
     * @return void
     */
    public function mapRequest(array $data = array())
    {
        if (empty($data)) {
            if ($this->isCLI()) {
                for ($i = 1; count($_SERVER['argv']) > $i; $i++) {
                    $pair = explode('=', $_SERVER['argv'][$i], 2);
                    $data[preg_replace('/^-+/Ss', '', $pair[0])] = isset($pair[1]) ? trim($pair[1]) : true;
                }

            } else {
                $data = array_merge($this->getGetData(false), $this->getPostData(false), $this->getCookieData(false));
            }
        }

        $this->data = array_replace_recursive($this->data, $this->prepare($data));
    }

    /**
     * Return all data
     *
     * @return array
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * Return data from the $_GET global variable
     *
     * @param boolean $prepare Flag OPTIONAL
     *
     * @return array
     */
    public function getGetData($prepare = true)
    {
        return $prepare ? $this->prepare($_GET) : $_GET;
    }

    /**
     * Return data from the $_POST global variable
     *
     * @param boolean $prepare Flag OPTIONAL
     *
     * @return array
     */
    public function getPostData($prepare = true)
    {
        return $prepare ? $this->prepare($_POST) : $_POST;
    }

    /**
     * Return data from the $_COOKIE global variable
     *
     * @param boolean $prepare Flag OPTIONAL
     *
     * @return array
     */
    public function getCookieData($prepare = true)
    {
        return $prepare ? $this->prepare($_COOKIE) : $_COOKIE;
    }

    /**
     * Return data from the $_SERVER global variable
     *
     * @param boolean $prepare Flag OPTIONAL
     *
     * @return array
     */
    public function getServerData($prepare = true)
    {
        return $prepare ? $this->prepare($_SERVER) : $_SERVER;
    }

    /**
     * Return current request method
     *
     * @return string
     */
    public function getRequestMethod()
    {
        return $this->requestMethod;
    }

    /**
     * Set request method
     *
     * @param string $method New request method
     *
     * @return void
     */
    public function setRequestMethod($method)
    {
        $this->requestMethod = $method;
    }

    /**
     * Check if current request method is "GET"
     *
     * @return boolean
     */
    public function isGet()
    {
        return 'GET' === $this->requestMethod;
    }

    /**
     * Check if current request method is "POST"
     *
     * @return boolean
     */
    public function isPost()
    {
        return 'POST' === $this->requestMethod;
    }

    /**
     * Check if current request method is "HEAD"
     *
     * @return boolean
     */
    public function isHead()
    {
        return 'HEAD' === $this->requestMethod;
    }

    /**
     * Check - is AJAX request or not
     *
     * @return boolean
     */
    public function isAJAX()
    {
        return isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest';
    }

    /**
     * Check for secure connection
     *
     * @return boolean
     */
    public function isHTTPS()
    {
        return \XLite\Core\URLManager::isHTTPS();
    }

    /**
     * Check - is command line interface or not
     *
     * @return boolean
     */
    public function isCLI()
    {
        return 'cli' == PHP_SAPI;
    }

    /**
     * Getter
     *
     * @param string $name Property name
     *
     * @return mixed
     */
    public function __get($name)
    {
        return isset($this->data[$name]) ? $this->data[$name] : null;
    }

    /**
     * Setter
     *
     * @param string $name  Property name
     * @param mixed  $value Property value
     *
     * @return void
     */
    public function __set($name, $value)
    {
        $this->data[$name] = $this->prepare($value);
    }

    /**
     * Check property accessability
     *
     * @param string $name Property name
     *
     * @return boolean
     */
    public function __isset($name)
    {
        return isset($this->data[$name]);
    }


    /**
     * Constructor
     *
     * @return void
     */
    protected function __construct()
    {
        $this->requestMethod = isset($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : self::METHOD_CLI;
        $this->mapRequest();
    }

    /**
     * Unescape single value
     *
     * @param string $value Value to sanitize
     *
     * @return string
     */
    protected function doUnescapeSingle($value)
    {
        return stripslashes($value);
    }

    /**
     * Remove automatically added escaping
     *
     * @param mixed $data Data to sanitize
     *
     * @return mixed
     */
    protected function doUnescape($data)
    {
        return is_array($data)
            ? array_map(array($this, __FUNCTION__), $data)
            : $this->doUnescapeSingle($data);
    }

    /**
     * Normalize request data
     *
     * @param mixed $request Request data
     *
     * @return mixed
     */
    protected function normalizeRequestData($request)
    {
        if (ini_get('magic_quotes_gpc')) {
            $request = $this->doUnescape($request);
        }

        return $request;
    }

    /**
     * Wrapper for sanitize()
     *
     * @param mixed $data Data to sanitize
     *
     * @return mixed
     */
    protected function prepare($data)
    {
        if (is_array($data)) {
            if (isset($data['target']) && !$this->checkControlArgument($data['target'], 'Target')) {
                $data['target'] = \XLite::TARGET_404;
                $data['action'] = null;
            }

            if (isset($data['action']) && !$this->checkControlArgument($data['action'], 'Action')) {
                unset($data['action']);
            }
        }

        return $this->normalizeRequestData($data);
    }

    /**
     * Check control argument (like target)
     *
     * @param mixed  $value Argument value
     * @param string $name  Argument name
     *
     * @return boolean
     */
    protected function checkControlArgument($value, $name)
    {
        $result = true;

        if (!is_string($value)) {
            \XLite\Logger::getInstance()->log($name . ' has a wrong type');
            $result = false;

        } elseif (!preg_match('/^[a-z0-9_]*$/Ssi', $value)) {
            \XLite\Logger::getInstance()->log($name . ' has a wrong format');
            $result = false;
        }

        return $result;
    }

    // {{{ Cookie

    /**
     * Set cookie 
     * 
     * @param string  $name  Name
     * @param string  $value Value
     * @param integer $ttl   TTL OPTIONAL
     *  
     * @return boolean
     */
    public function setCookie($name, $value, $ttl = 0)
    {
        $result = true;

        $options = \XLite::getInstance()->getOptions('host_details');

        $ttl = $ttl != 0 ? \XLite\Core\Converter::time() + $ttl : 0;

        $httpDomain = $this->getCookieDomain(false);
        $result = @setcookie(
            $name,
            $value,
            $ttl,
            $this->getCookiePath(false),
            $httpDomain,
            false,
            true
        ) && $result;

        $httpsDomain = $this->getCookieDomain(true);
        if ($httpDomain != $httpsDomain) {
            $result = @setcookie(
                $name,
                $value,
                $ttl,
                $this->getCookiePath(true),
                $httpsDomain,
                true,
                true
            ) && $result;
        }

        return $result;
    }

    public function unsetCookie($name)
    {
        if (isset($_COOKIE[$name])) {
            unset($_COOKIE[$name]);
        }

        $httpDomain = $this->getCookieDomain(false);
        @setcookie(
            $name,
            null,
            -1,
            $this->getCookiePath(false),
            $httpDomain,
            false,
            true
        );

        $httpsDomain = $this->getCookieDomain(true);
        if ($httpDomain != $httpsDomain) {
            $result = @setcookie(
                $name,
                null,
                -1,
                $this->getCookiePath(true),
                $httpsDomain,
                true,
                true
            );
        }
    }

    /**
     * Get host / domain for Set-Cookie
     *
     * @param boolean $secure Secure protocol or not OPTIONAL
     *
     * @return string
     */
    protected function getCookieDomain($secure = false)
    {
        $url = $this->getCookieURL($secure);

        return false === strstr($url['host'], '.') ? false : $url['host'];
    }

    /**
     * Get URL path for Set-Cookie
     *
     * @param boolean $secure Secure protocol or not OPTIONAL
     *
     * @return string
     */
    protected function getCookiePath($secure = false)
    {
        $url = $this->getCookieURL($secure);

        return isset($url['path']) ? $url['path'] : '/';
    }

    /**
     * Get parsed URL for Set-Cookie
     *
     * @param boolean $secure Secure protocol or not OPTIONAL
     *
     * @return array
     */
    protected function getCookieURL($secure = false)
    {
        $url = $secure
            ? 'http://' .  \XLite::getInstance()->getOptions(array('host_details', 'http_host'))
            : 'https://' . \XLite::getInstance()->getOptions(array('host_details', 'https_host'));

        $url .= \XLite::getInstance()->getOptions(array('host_details', 'web_dir'));

        return parse_url($url);
    }

    // }}}

    // {{{ Bot detected

    /**
     * Cookie cell for robot mark 
     */
    const COOKIE_ROBOT = 'is_robot';

    /**
     * Bot flag 
     * 
     * @var   boolean
     */
    protected $botFlag;

    /**
     * Bot signatures 
     * 
     * @var array
     */
    protected $botSignatures = array (
        'X-Cart info'    => array(
            'X-Cart info',
            'X-Cart Catalog Generator',
        ),
        'Google'        => array(
            'Googlebot',
            'Mediapartners-Google',
            'Googlebot-Mobile',
            'Googlebot-Image',
            'Adsbot-Google',
        ),
        'Yahoo'            => array(
            'Slurp',
            'YahooSeeker',
        ),
        'Microsoft'        => array(
            'MSNBot',
            'MSNBot-Media',
            'MSNBot-NewsBlogs',
            'MSNBot-Products',
            'MSNBot-Academic',
            'bingbot',
            'adidxbot',
        ),
        'Ask'            => array(
            'Teoma',
        ),
        'Baidu'          => array(
            'baiduspider',
        ),
        'dotnetdotcom.org' => array(
            'dotbot',
        ),
        'Qihoo' => array(
            'Qihoo',
        ),
        'Tencent' => array(
            'Sosospider',
        ),
        'Entireweb.com' => array(
            'Speedy Spider',
        ),
        'WuKong' => array(
            'WukongSpider',
        ),
        'NHN Corporation' => array(
            'Yeti',
        ),
        'NetEase Inc' => array(
           'YodaoBot',
        ),
    );

    /**
     * Check - current user is bot or not
     * 
     * @return boolean
     */
    public function isBot()
    {
        if (!isset($this->botFlag)) {
            $this->botFlag = false;

            if (!$this->isCLI() && !empty($_SERVER['HTTP_USER_AGENT'])) {
                if (isset($_COOKIE[static::COOKIE_ROBOT])) {
                    if (substr($_COOKIE[static::COOKIE_ROBOT], 0, -1) == hash('md4', $_SERVER['HTTP_USER_AGENT'])) {
                    $this->botFlag = (bool)substr($_COOKIE[static::COOKIE_ROBOT], -1);

                    } else {
                        $this->unsetCookie(static::COOKIE_ROBOT);
                    }

                } else {
                    $this->botFlag = $this->detectBot();
                    if ($this->botFlag) {
                        $this->setCookie(static::COOKIE_ROBOT, hash('md4', $_SERVER['HTTP_USER_AGENT']) . intval($this->botFlag));
                    }
                }
            }
        }

        return $this->botFlag;
    }

    /**
     * Detect bot 
     * 
     * @return boolean
     */
    protected function detectBot()
    {
        $result = false;

        foreach ($this->botSignatures as $name => $signatures) {
            foreach ($signatures as $signature) {
                if (false !== stristr($_SERVER['HTTP_USER_AGENT'], $signature)) {
                    $result = true;
                    break;
                }
            }
        }

        return $result;
    }

    // }}}

    // {{{ Browser detection

    /**
     * Browscap cache
     *
     * @var object
     */
    protected $browscap;

    /**
     * Get browser data 
     *
     * @param boolean $override Override cache OPTIONAL
     * 
     * @return object
     */
    public function getBrowserData($override = false)
    {
        if (
            (!isset($this->browscap) || $override)
            && !$this->isCLI()
            && !empty($_SERVER['HTTP_USER_AGENT'])
        ) {
            $this->browscap = $this->getBrowscapClient()->getBrowser($_SERVER['HTTP_USER_AGENT']);
        }

        return $this->browscap;
    }

    /**
     * Get browscap client 
     * 
     * @return \phpbrowscap\Browscap
     */
    protected function getBrowscapClient()
    {
        require_once (LC_DIR_LIB . 'Browscap.php');

        $cacheDir = LC_DIR_DATA . 'browscap';
        if (!file_exists($cacheDir)) {
            $path = $this->getBrowsCapPath();
            \Includes\Utils\FileManager::mkdirRecursive($cacheDir);
            \Includes\Utils\FileManager::copy(
                $this->getBrowsCapPath(),
                $cacheDir . LC_DS . 'browscap.ini'
            );
        }

        return new \phpbrowscap\Browscap($cacheDir);
    }

    /**
     * Get browsCap file path 
     * 
     * @return string
     */
    protected function getBrowsCapPath()
    {
        return LC_DIR_LIB . 'browscap.ini';
    }

    // }}}
}
