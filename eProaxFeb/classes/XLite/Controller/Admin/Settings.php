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

namespace XLite\Controller\Admin;

/**
 * Settings
 * todo: FULL REFACTOR!!!
 */
class Settings extends \XLite\Controller\Admin\AAdmin
{
    /**
     * Clean URL article url
     */
    const CLEAN_URL_ARTICLE_URL = 'http://kb.x-cart.com/pages/viewpage.action?pageId=7505785';

    /**
     * Values to use for $page identification
     */
    const GENERAL_PAGE      = 'General';
    const COMPANY_PAGE      = 'Company';
    const EMAIL_PAGE        = 'Email';
    const ENVIRONMENT_PAGE  = 'Environment';
    const PERFORMANCE_PAGE  = 'Performance';
    const UNITS_PAGE        = 'Units';
    const LAYOUT_PAGE       = 'Layout';

    /**
     * params
     * FIXME
     *
     * @var array
     */
    protected $params = array('target', 'page');

    /**
     * page
     * FIXME
     *
     * @var string
     */
    public $page = self::GENERAL_PAGE;

    /**
     * _waiting_list
     * FIXME
     *
     * @var mixed
     */
    public $_waiting_list = null;

    /**
     * Return the current page title (for the content area)
     *
     * @return string
     */
    public function getTitle()
    {
        $list = $this->getPages();

        return isset($list[$this->page])
            ? $list[$this->page]
            : '';
    }

    /**
     * Check if current page is accessible
     *
     * @return boolean
     */
    public function checkAccess()
    {
        $list = $this->getPages();

        /**
         * Settings controller is available directly if the $page request variable is provided
         * if the $page is omitted, the controller must be the subclass of Settings main one.
         *
         * The inner $page variable must be in the getPages() array
         */
        return parent::checkAccess()
            && isset($list[$this->page])
            && (
                ($this instanceof \XLite\Controller\Admin\Settings && isset(\XLite\Core\Request::getInstance()->page))
                || is_subclass_of($this, '\XLite\Controller\Admin\Settings')
            );
    }

    // {{{ Pages

    /**
     * Get tab names
     *
     * @return array
     */
    public function getPages()
    {
        $list = parent::getPages();
        $list[static::GENERAL_PAGE]     = static::t('Cart & checkout');
        $list[static::COMPANY_PAGE]     = static::t('Store info');
        $list[static::EMAIL_PAGE]       = static::t('Email settings');
        $list[static::ENVIRONMENT_PAGE] = static::t('Environment');

        return $list;
    }

    /**
     * Get pages templates
     *
     * @return array
     */
    protected function getPageTemplates()
    {
        $list = parent::getPageTemplates();

        foreach ($this->getPages() as $name => $title) {
            $list[$name] = 'settings/base.tpl';
        }

        $list[static::ENVIRONMENT_PAGE] = 'settings/summary/body.tpl';

        return $list;
    }

    // }}}

    // {{{ Other

    /**
     * Get options for current tab (category)
     *
     * @return array
     */
    public function getOptions()
    {
        return \XLite\Core\Database::getRepo('\XLite\Model\Config')->findByCategoryAndVisible($this->page);
    }

    /**
     * getModelFormClass
     *
     * @return string
     */
    protected function getModelFormClass()
    {
        return '\XLite\View\Model\Settings';
    }

    // }}}

    // {{{ Additional methods

    /**
     * Defines if the clean URL is enabled in the store
     *
     * @return boolean
     */
    public function isCleanURLEnabled()
    {
        return LC_USE_CLEAN_URLS;
    }

    /**
     * Defines if the clean urls can be enabled on the current server environment
     *
     * @return boolean
     */
    public function canEnableCleanURL()
    {
        $urlToCheck = \XLite::getInstance()->getShopURL() . \XLite::CLEAN_URL_CHECK_QUERY;
        $request = new \XLite\Core\HTTP\Request($urlToCheck);
        $request->setAdditionalOption(\CURLOPT_SSL_VERIFYPEER, false);
        $request->setAdditionalOption(\CURLOPT_SSL_VERIFYHOST, false);
        $response = $request->sendRequest();

        return !$this->isCleanURLEnabled()
            && $response
            && in_array($response->code, array(200, 301, 302));
    }

    /**
     * Defines the article URL of setting up the clean URL functionality
     *
     * @return string
     */
    public function getCleanURLArticleURL()
    {
        return static::CLEAN_URL_ARTICLE_URL;
    }

    /**
     * Check for the GDLib extension
     *
     * @return boolean
     */
    public function isGDLibLoaded()
    {
        return extension_loaded('gd') && function_exists('gd_info');
    }

    /**
     * isOpenBasedirRestriction
     *
     * @return boolean
     */
    public function isOpenBasedirRestriction()
    {
        $res = (string) @ini_get('open_basedir');

        return ('' !== $res);
    }

    /**
     * Get translation driver identifier
     *
     * @return string
     */
    public function getTranslationDriver()
    {
        return \XLite\Core\Translation::getInstance()->getDriver()->getName();
    }

    /**
     * Returns value by request
     *
     * @param string $name Type of value
     *
     * @return string
     */
    public function get($name)
    {
        switch($name) {

            case 'phpversion':
                $return = PHP_VERSION;
                break;

            case 'os_type':
                list($osType) = explode(' ', PHP_OS);
                $return = $osType;
                break;

            case 'mysql_server':
                $return = \Includes\Utils\Database::getDbVersion();
                break;

            case 'innodb_support':
                $return = \Includes\Utils\Database::isInnoDBSupported();
                break;

            case 'root_folder':
                $return = getcwd();
                break;

            case 'web_server':
                if (isset($_SERVER['SERVER_SOFTWARE'])) {
                    $return = $_SERVER['SERVER_SOFTWARE'];

                } else {
                    $return = '';
                }
                break;

            case 'xml_parser':
                ob_start();
                phpinfo(INFO_MODULES);
                $phpInfo = ob_get_contents();
                ob_end_clean();

                if (preg_match('/EXPAT.+>([\.\d]+)/mi', $phpInfo, $m)) {
                    $return = $m[1];

                } else {
                    $return = function_exists('xml_parser_create') ? 'found' : '';
                }

                break;

            case 'gdlib':
                if (!$this->is('GDLibLoaded')) {
                    $return = '';

                } else {
                    ob_start();

                    phpinfo(INFO_MODULES);

                    $phpInfo = ob_get_contents();

                    ob_end_clean();

                    if (preg_match('/GD.+>([\.\d]+)/mi', $phpInfo, $m)) {
                        $gdVersion = $m[1];

                    } else {
                        $gdVersion = @gd_info();

                        if (is_array($gdVersion) && isset($gdVersion['GD Version'])) {
                            $gdVersion = $gdVersion['GD Version'];

                        } else {
                            $gdVersion = 'unknown';
                        }
                    }

                    $return = 'found (' . $gdVersion . ')';
                }

                break;

            case 'core_version':
                $return = \XLite::getInstance()->getVersion();
                break;

            case 'libcurl':
                if (function_exists('curl_version')) {
                    $libcurlVersion = curl_version();

                    if (is_array($libcurlVersion)) {
                        $libcurlVersion = $libcurlVersion['version'];
                    }

                    $return = $libcurlVersion;

                } else {
                    $return = false;
                }

                break;

            case 'check_files':
                $result = array();
                $files = array();

                foreach ($files as $file) {
                    $mode = $this->getExpectedFilePermission($file);
                    $modeStr = $this->getExpectedFilePermissionStr($file);
                    $res = array('file' => $file, 'error' => '');

                    if (!is_file($file)) {
                        $res['error'] = 'does_not_exist';
                        $result[] = $res;
                        continue;
                    }

                    $perm = substr(sprintf('%o', @fileperms($file)), -4);

                    if ($perm != $modeStr) {
                        if (!@chmod($file, $mode)) {
                            $res['error'] = 'cannot_chmod';
                            $result[] = $res;
                            continue;
                        }

                    } else {
                        if ($this->getComplex('xlite.suMode') != 0) {
                            if (!@chmod($file, $mode)) {
                                $res['error'] = 'wrong_owner';
                                $result[] = $res;
                                continue;
                            }
                        }
                    }

                    $result[] = $res;
                }

                $return = $result;

                break;

            case 'check_dirs':

                $result = array();

                $dirs = array(
                    'var/run',
                    'var/log',
                    'var/html',
                    'var/backup',
                    'var/tmp',
                    'catalog',
                    'images',
                    'classes/modules',
                    'skins/default/en/modules',
                    'skins/admin/en/modules',
                    'skins/default/en/images/modules',
                    'skins/admin/en/images/modules',
                    'skins/mail/en/modules',
                    'skins/mail/en/images/modules'
                );

                foreach ($dirs as $dir) {
                    $mode = $this->getDirPermission($dir);
                    $modeStr = $this->getDirPermissionStr($dir);

                    $res = array(
                        'dir'     => $dir,
                        'error'   => '',
                        'subdirs' => array(),
                    );

                    if (!is_dir($dir)) {
                        $fullPath = '';
                        $path = explode('/', $dir);

                        foreach ($path as $sub) {
                            $fullPath .= $sub . '/';

                            if (!is_dir($fullPath) && @mkdir($fullPath, $mode) !== true) {
                                break;
                            }
                        }
                    }

                    if (!is_dir($dir)) {
                        $res['error'] = 'cannot_create';
                        $result[] = $res;
                        continue;
                    }

                    $perm = substr(sprintf('%o', @fileperms($dir)), -4);

                    if ($perm != $modeStr) {
                        if (!@chmod($dir, $mode)) {
                            $res['error'] = 'cannot_chmod';
                            $result[] = $res;
                            continue;
                        }

                    } elseif (
                        ($this->getComplex('xlite.suMode') != 0 || strpos($dir, LC_DS . 'var' . LC_DS) !== false)
                        && !@chmod($dir, $mode)
                    ) {
                        $res['error'] = 'wrong_owner';
                        $result[] = $res;
                        continue;
                    }

                    $subdirs = array();

                    if ('catalog' != $dir && 'images' != $dir) {
                        $this->checkSubdirs($dir, $subdirs);
                    }

                    if (!empty($subdirs)) {
                        $res['error'] = 'cannot_chmod_subdirs';
                        $res['subdirs'] = $subdirs;
                        $result[] = $res;
                        continue;
                    }

                    $result[] = $res;
                }

                $return = $result;

                break;

            default:
                $return = parent::get($name);
        }

        return $return;
    }

    /**
     * Get directory permission
     *
     * @param string $dir Directory path
     *
     * @return integer
     */
    public function getDirPermission($dir)
    {
        global $options;

        if ($this->getComplex('xlite.suMode') == 0) {
            if (strpos($dir, LC_DS . 'var' . LC_DS) === false) {
                $mode = 0777;

            } else {
                $mode = isset($options['filesystem_permissions']['nonprivileged_permission_dir'])
                    ? base_convert($options['filesystem_permissions']['nonprivileged_permission_dir'], 8, 10)
                    : 0755;
            }

        } else {
            $mode = isset($options['filesystem_permissions']['privileged_permission_dir'])
                ? base_convert($options['filesystem_permissions']['privileged_permission_dir'], 8, 10)
                : 0711;
        }

        return $mode;
    }

    /**
     * getDirPermissionStr
     *
     * @param string $dir Directory path OPTIONAL
     *
     * @return string
     */
    public function getDirPermissionStr($dir = '')
    {
        $mode = intval($this->getDirPermission($dir));

        return strval('0' . base_convert($mode, 10, 8));
    }

    /**
     * Get expected file permission
     *
     * @param mixed $file File path OPTIONAL
     *
     * @return integer
     */
    public function getExpectedFilePermission($file = '')
    {
        global $options;

        switch ($file) {
            default:
                if ($this->getComplex('xlite.suMode') == 0) {
                    $mode = isset($options['filesystem_permissions']['nonprivileged_permission_file'])
                        ? base_convert($options['filesystem_permissions']['nonprivileged_permission_file'], 8, 10)
                        : 0644;

                } else {
                    $mode = isset($options['filesystem_permissions']['privileged_permission_file'])
                        ? base_convert($options['filesystem_permissions']['privileged_permission_file'], 8, 10)
                        : 0600;
                }
                break;
        }

        return $mode;
    }

    /**
     * Get expected file permission (string)
     *
     * @param string $file File path OPTIONAL
     *
     * @return string
     */
    public function getExpectedFilePermissionStr($file = '')
    {
        switch ($file) {
            default:
                $mode = (int)$this->getExpectedFilePermission($file);
                break;
        }

        return (string) '0' . base_convert($mode, 10, 8);
    }

    /**
     * checkSubdirs
     *
     * @param mixed $path          ____param_comment____
     * @param mixed &$subdirErrors ____param_comment____
     *
     * @return void
     */
    public function checkSubdirs($path, &$subdirErrors)
    {
        if (is_dir($path)) {
            $mode = $this->getDirPermission($path);
            $modeStr = $this->getDirPermissionStr($path);

            $dh = @opendir($path);

            while (($file = @readdir($dh)) !== false) {
                if ('.' != $file && '..' != $file) {
                    $fullpath = $path . DIRECTORY_SEPARATOR . $file;

                    if (@is_dir($fullpath)) {
                        $perm = substr(sprintf('%o', @fileperms($fullpath)), -4);

                        if ($perm != $modeStr) {
                            if (!@chmod($fullpath, $mode)) {
                                $subdirErrors[] = $fullpath;
                                continue;
                            }

                        } elseif (
                            ($this->getComplex('xlite.suMode') != 0 || strpos($fullpath, LC_DS . 'var' . LC_DS) !== false)
                            && !@chmod($fullpath, $mode)
                        ) {
                            $subdirErrors[] = $fullpath;
                            continue;
                        }

                        $this->checkSubdirs($fullpath, $subdirErrors);
                    }
                }
            }
        }
    }

    /**
     * Define the actions with no secure token
     *
     * @return array
     */
    public static function defineFreeFormIdActions()
    {
        return array_merge(parent::defineFreeFormIdActions(), array('phpinfo'));
    }

    /**
     * doActionPhpinfo
     *
     * @return void
     */
    public function doActionPhpinfo()
    {
        phpinfo();
        $this->setSuppressOutput(true);
    }

    /**
     * doActionUpdate
     *
     * @return void
     */
    public function doActionUpdate()
    {
        $this->getModelForm()->performAction('update');
    }

    /**
     * Actions to enable the clean URL functionality
     *
     * @return void
     */
    public function doActionEnableCleanUrl()
    {
        if ($this->canEnableCleanURL()) {
            $cleanURLFlag = \XLite\Core\Database::getRepo('XLite\Model\Config')->findOneBy(
                array(
                    'name'      => 'clean_url_flag',
                    'category'  => 'CleanURL'
                )
            );

            \XLite\Core\Database::getRepo('XLite\Model\Config')->update(
                $cleanURLFlag,
                array(
                    'value' => true,
                )
            );

            \XLite\Core\TopMessage::addInfo(static::t('Clean URLs are enabled'));
        }

        $this->doRedirect();
    }

    /**
     * isWin
     *
     * @return boolean
     */
    public function isWin()
    {
        return (LC_OS_CODE === 'win');
    }

    /**
     * getStateById
     *
     * @param mixed $stateId State Id
     *
     * @return \XLite\Model\State
     */
    public function getStateById($stateId)
    {
        return \XLite\Core\Database::getRepo('XLite\Model\State')->find($stateId);
    }

    /**
     * Flag to has email error
     *
     * @return string
     */
    public function hasTestEmailError()
    {
        return '' !== (string)\XLite\Core\Session::getInstance()->test_email_error;
    }

    /**
     * Return error test email sending
     *
     * @return string
     */
    public function getTestEmailError()
    {
        $error = (string)\XLite\Core\Session::getInstance()->test_email_error;

        \XLite\Core\Session::getInstance()->test_email_error = '';

        return $error;
    }

    // }}}

    // {{{ Service actions

    /**
     * Action to send test email notification
     *
     * @return void
     */
    protected function doActionTestEmail()
    {
        $request = \XLite\Core\Request::getInstance();

        $error = \XLite\Core\Mailer::sendTestEmail(
            $request->test_from_email_address,
            $request->test_to_email_address,
            $request->test_email_body
        );

        if ($error) {
            \XLite\Core\Session::getInstance()->test_email_error = $error;
            \XLite\Core\TopMessage::getInstance()->addError('Error of test e-mail sending: ' . $error);

        } else {
            \XLite\Core\TopMessage::getInstance()->add('Test e-mail have been successfully sent');
        }

        $this->setReturnURL(
            $this->buildURL('settings', '', array('page' => static::EMAIL_PAGE))
        );
    }

    // }}}
}
