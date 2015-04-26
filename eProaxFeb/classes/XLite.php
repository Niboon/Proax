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

/**
 * Application singleton
 *
 * TODO: to revise
 * TODO[SINGLETON]: lowest priority
 */
class XLite extends \XLite\Base
{
    /**
     * Endpoints
     */
    const CART_SELF  = 'cart.php';
    const ADMIN_SELF = 'admin.php';

    /**
     * This target will be used if the "target" params is not passed in the request
     */
    const TARGET_DEFAULT = 'main';
    const TARGET_404     = 'page_not_found';

    /**
     * Interfaces codes
     */
    const ADMIN_INTERFACE    = 'admin';
    const CUSTOMER_INTERFACE = 'customer';
    const CONSOLE_INTERFACE  = 'console';
    const MAIL_INTERFACE     = 'mail';
    const COMMON_INTERFACE   = 'common';

    /**
     * Default shop currency code (840 - US Dollar)
     */
    const SHOP_CURRENCY_DEFAULT = 840;

    /**
     * Temporary variable name for latest cache building time
     */
    const CACHE_TIMESTAMP = 'cache_build_timestamp';

    /**
     * Trial period TTL (days)
     */
    const TRIAL_PERIOD = '30';

    /**
     * Session variable name for show trial notice flag.
     */
    const SHOW_TRIAL_NOTICE = 'showTrialNotice';

    /**
     * Producer site URL
     */
    const PRODUCER_SITE_URL = 'http://www.x-cart.com/';

    /**
     * Name of the form id
     */
    const FORM_ID = 'xcart_form_id';

    /**
     * Current area flag
     *
     * @var boolean
     */
    protected static $adminZone = false;

    /**
     * URL type flag
     *
     * @var boolean
     */
    protected static $cleanURL = false;

    /**
     * Called controller
     *
     * @var \XLite\Controller\AController
     */
    protected static $controller = null;

    /**
     * Flag; determines if we need to cleanup (and, as a result, to rebuild) classes and templates cache
     *
     * @var boolean
     */
    protected static $isNeedToCleanupCache = false;

    /**
     * Current currency
     *
     * @var \XLite\Model\Currency
     */
    protected $currentCurrency;

    /**
     * Check is admin interface
     *
     * @return boolean
     */
    public static function isAdminZone()
    {
        return static::$adminZone;
    }

    /**
     * Check is cache building
     *
     * @return boolean
     */
    public static function isCacheBuilding()
    {
        return defined('LC_CACHE_BUILDING') && constant('LC_CACHE_BUILDING');
    }

    /**
     * Check if clean URL used
     *
     * @return boolean
     */
    public static function isCleanURL()
    {
        return static::$cleanURL;
    }

    /**
     * Ability to provoke cache cleanup (or to prevent it)
     *
     * @param boolean $flag If it's needed to cleanup cache or not
     *
     * @return void
     */
    public static function setCleanUpCacheFlag($flag)
    {
        static::$isNeedToCleanupCache = (true === $flag);
    }

    /**
     * Get controller
     *
     * @return \XLite\Controller\AController
     */
    public static function getController()
    {
        if (!isset(static::$controller)) {
            $class = static::getControllerClass();
            if (!$class) {
                \XLite\Core\Request::getInstance()->target = static::TARGET_DEFAULT;
                \XLite\Logger::logCustom('access', 'Controller class ' . $class . ' not found!');
                \XLite\Core\Request::getInstance()->target = static::TARGET_404;
                $class = static::getControllerClass();
            }

            static::$controller = new $class(\XLite\Core\Request::getInstance()->getData());
            static::$controller->init();
        }

        return static::$controller;
    }

    /**
     * Set controller
     * FIXME - to delete
     *
     * @param mixed $controller Controller OPTIONAL
     *
     * @return void
     */
    public static function setController($controller = null)
    {
        if (is_null($controller) || $controller instanceof \XLite\Controller\AController) {
            static::$controller = $controller;
        }
    }

    /**
     * Defines the installation language code
     *
     * @return string
     */
    public static function getInstallationLng()
    {
        return \Includes\Utils\ConfigParser::getInstallationLng();
    }

    /**
     * Return X-Cart 5 license of the core
     *
     * @return \XLite\Model\ModuleKey | null
     */
    public static function getXCNLicense()
    {
        $result = null;

        // Get list of all registered X-Cart 5 license keys
        $licenseKeys = \XLite\Core\Database::getRepo('XLite\Model\ModuleKey')
            ->findBy(array('keyType' => \XLite\Model\ModuleKey::KEY_TYPE_XCN));

        // Select only the license key with non empty 'XCN plan'
        if ($licenseKeys) {
            foreach ($licenseKeys as $key) {
                if (intval($key->getXcnPlan()) != 0) {
                    $result = $key;
                    break;
                }
            }
        }

        return $result;
    }

    /**
     * Return X-Cart 5 license of the core
     *
     * @return string
     */
    public static function getXCNLicenseKey()
    {
        $license = static::getXCNLicense();

        return $license ? $license->getKeyValue() : '';
    }

    /**
     * Return X-Cart 5 license type of core
     *
     * @return integer
     */
    public static function getXCNLicenseType()
    {
        $license = static::getXCNLicense();

        return $license ? intval($license->getXcnPlan()) : 0;
    }

    /**
     * Get number of days left before trial period will expire
     *
     * @return integer
     */
    public static function getTrialPeriodLeft($returnDays = true)
    {
        $startTime = \XLite\Core\Config::getInstance()->Version->timestamp;

        $endTime = $startTime + 86400 * \XLite::TRIAL_PERIOD;

        return $returnDays
            ? intval(($endTime - time()) / 86400)
            : $endTime - time();
    }

    /**
     * Check if trial period is expired
     *
     * @return boolean
     */
    public static function isTrialPeriodExpired()
    {
        return !static::getXCNLicense() && 0 >= static::getTrialPeriodLeft(false);
    }

    /**
     * Return true if registered Free license
     *
     * @param \XLite\Model\ModuleKey $license License key OPTIONAL
     *
     * @return boolean
     */
    public static function isFreeLicense($license = null)
    {
        $result = false;

        $key = $license ?: static::getXCNLicense();

        if ($key) {

            if (2 == $key->getXcnPlan()) {
                $result = true;

            } else {
                $keyData = $key->getKeyData();
                $result = isset($keyData['editionName']) && 'Free' == $keyData['editionName'];
            }
        }

        return $result;
    }

    /**
     * Return affiliate ID
     *
     * @return string
     */
    public static function getAffiliateId()
    {
        return \Includes\Utils\ConfigParser::getOptions(array('affiliate', 'id'));
    }

    /**
     * Return affiliate URL
     *
     * @param string $url Url part to add OPTIONAL
     *
     * @return string
     */
    public static function getXCartURL($url = '')
    {
        $affiliateId = static::getAffiliateId();

        if (empty($url)) {
            $url = static::PRODUCER_SITE_URL;
        }

        $url .= (strpos($url, '?') ? '&' : '?') . 'sl=' . static::getInstallationLng();

        return $affiliateId
            ? 'http://www.x-cart.com/aff/?aff_id=' . $affiliateId . '&url=' . urlencode($url)
            : $url;
    }

    /**
     * Return current target
     *
     * @return string
     */
    protected static function getTarget()
    {
        if (empty(\XLite\Core\Request::getInstance()->target)) {
            \XLite\Core\Request::getInstance()->target = static::dispatchRequest();
        }

        return \XLite\Core\Request::getInstance()->target;
    }

    /**
     * Assemble and get controller class name
     *
     * @return string
     */
    protected static function getControllerClass()
    {
        return \XLite\Core\Converter::getControllerClass(static::getTarget());
    }

    /**
     * Return specified (or the whole list) options
     *
     * @param mixed $names List (or single value) of option names OPTIONAL
     *
     * @return mixed
     */
    public function getOptions($names = null)
    {
        return \XLite\Core\ConfigParser::getOptions($names);
    }

    /**
     * Clean up classes cache (if needed)
     *
     * @return void
     */
    public function __destruct()
    {
        if (static::$isNeedToCleanupCache) {
            \Includes\Decorator\Utils\CacheManager::cleanupCacheIndicators();
        }
    }

    /**
     * Return current endpoint script
     *
     * @return string
     */
    public function getScript()
    {
        return static::isAdminZone() ? static::ADMIN_SELF : static::CART_SELF;
    }

    /**
     * Return full URL for the resource
     *
     * @param string  $url      Url part to add OPTIONAL
     * @param boolean $isSecure Use HTTP or HTTPS OPTIONAL
     * @param array   $params   Optional URL params OPTIONAL
     *
     * @return string
     */
    public function getShopURL($url = '', $isSecure = null, array $params = array())
    {
        return \XLite\Core\URLManager::getShopURL($url, $isSecure, $params);
    }

    /**
     * Return instance of the abstract factory sigleton
     *
     * @return \XLite\Model\Factory
     */
    public function getFactory()
    {
        return \XLite\Model\Factory::getInstance();
    }

    /**
     * Call application die (general routine)
     *
     * @param string $message Error message
     *
     * @return void
     */
    public function doGlobalDie($message)
    {
        $this->doDie($message);
    }

    /**
     * Initialize all active modules
     *
     * @return void
     */
    public function initModules()
    {
        \Includes\Utils\ModulesManager::initModules();
    }

    /**
     * Update module registry
     *
     * @return void
     */
    public function updateModuleRegistry()
    {
        $calculatedHash = \XLite\Core\Database::getRepo('XLite\Model\Module')->calculateEnabledModulesRegistryHash();

        if (\Includes\Utils\ModulesManager::getEnabledStructureHash() != $calculatedHash) {

            \XLite\Core\Database::getRepo('XLite\Model\Module')->addEnabledModulesToRegistry();
            \Includes\Utils\ModulesManager::saveEnabledStructureHash($calculatedHash);
        }
    }

    /**
     * Perform an action and redirect
     *
     * @return boolean
     */
    public function runController()
    {
        return $this->getController()->handleRequest();
    }

    /**
     * Return viewer object
     *
     * @return \XLite\View\Controller|void
     */
    public function getViewer()
    {
        $this->runController();

        $viewer = $this->getController()->getViewer();
        $viewer->init();

        return $viewer;
    }

    /**
     * Process request
     *
     * @return \XLite
     */
    public function processRequest()
    {
        $this->runController();

        $this->getController()->processRequest();

        return $this;
    }

    /**
     * Run application
     *
     * @param boolean $adminZone Admin interface flag OPTIONAL
     *
     * @return \XLite
     */
    public function run($adminZone = false)
    {
        // Set current area
        static::$adminZone = (bool)$adminZone;

        // Clear some data
        static::clearDataOnStartup();

        // Initialize logger
        \XLite\Logger::getInstance();

        // Initialize modules
        $this->initModules();

        if (\XLite\Core\Request::getInstance()->isCLI()) {

            // Set skin for console interface
            \XLite\Core\Layout::getInstance()->setConsoleSkin();

        } elseif (true === static::$adminZone) {

            // Set skin for admin interface
            \XLite\Core\Layout::getInstance()->setAdminSkin();
        }

        return $this;
    }

    /**
     * Get current currency
     *
     * @return \XLite\Model\Currency
     */
    public function getCurrency()
    {
        if (!isset($this->currentCurrency)) {
            $this->currentCurrency = \XLite\Core\Database::getRepo('XLite\Model\Currency')
                ->find(\XLite\Core\Config::getInstance()->General->shop_currency ?: static::SHOP_CURRENCY_DEFAULT);
        }

        return $this->currentCurrency;
    }

    /**
     * Return current action
     *
     * @return mixed
     */
    protected function getAction()
    {
        return \XLite\Core\Request::getInstance()->action;
    }

    /**
     * Clear some data
     *
     * @return void
     */
    protected function clearDataOnStartup()
    {
        static::$controller = null;
        \XLite\Model\CachingFactory::clearCache();
    }

    // {{{ Clean URLs support

    /**
     * Dispatch request
     *
     * @return string
     */
    protected static function dispatchRequest()
    {
        $result = static::TARGET_DEFAULT;

        if (LC_USE_CLEAN_URLS && isset(\XLite\Core\Request::getInstance()->url)) {
            $result = static::getTargetByCleanURL();
        }

        return $result;
    }

    /**
     * Return target by clean URL
     *
     * @return void
     */
    protected static function getTargetByCleanURL()
    {
        $tmp = \XLite\Core\Request::getInstance();
        list($target, $params) = \XLite\Core\Converter::parseCleanUrl($tmp->url, $tmp->last, $tmp->rest, $tmp->ext);

        if (!empty($target)) {
            $tmp->mapRequest($params);

            static::$cleanURL = true;
        }

        return $target;
    }

    // }}}

    // {{{ Form Id

    /**
     * Create the form id for the widgets
     *
     * @return string
     */
    final public static function getFormId($createNewFormId = true)
    {
        return \XLite\Core\Session::getInstance()->createFormId($createNewFormId);
    }

    // }}}

    // {{{ Application versions

    /**
     * Get application version
     *
     * @return string
     */
    final public function getVersion()
    {
        return \Includes\Utils\Converter::composeVersion($this->getMajorVersion(), $this->getMinorVersion());
    }

    /**
     * Get application major version
     *
     * @return string
     */
    final public function getMajorVersion()
    {
        return '5.1';
    }

    /**
     * Get application minor version
     *
     * @return string
     */
    final public function getMinorVersion()
    {
        return '10';
    }

    /**
     * Compare a version with the major core version
     *
     * @param string $version  Version to compare
     * @param string $operator Comparison operator
     *
     * @return boolean
     */
    final public function checkVersion($version, $operator)
    {
        return version_compare($this->getMajorVersion(), $version, $operator);
    }

    /**
     * Compare a version with the minor core version
     *
     * @param string $version  Version to compare
     * @param string $operator Comparison operator
     *
     * @return boolean
     */
    final public function checkMinorVersion($version, $operator)
    {
        return version_compare($this->getMinorVersion(), $version, $operator);
    }

    // }}}
}
