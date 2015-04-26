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
 * Abstract admin-zone controller
 */
abstract class AAdmin extends \XLite\Controller\AController
{
    /**
     * List of recently logged in administrators
     *
     * @var array
     */
    protected $recentAdmins = null;

    /**
     * Check if current page is accessible
     *
     * @return boolean
     */
    public function checkAccess()
    {
        return ((parent::checkAccess() && $this->checkACL()) || $this->isPublicZone())
            && $this->checkFormId();
    }

    /**
     * Set if the form id is needed to make an actions
     * Form class uses this method to check if the form id should be added
     *
     * @return boolean
     */
    public static function needFormId()
    {
        return true;
    }

    /**
     * Check ACL permissions
     *
     * @return boolean
     */
    public function checkACL()
    {
        return \XLite\Core\Auth::getInstance()->isPermissionAllowed(\XLite\Model\Role\Permission::ROOT_ACCESS);
    }

    /**
     * This function called after template output
     *
     * @return void
     */
    public function postprocess()
    {
        parent::postprocess();

        if ($this->dumpStarted) {
            $this->displayPageFooter();
        }
    }

    /**
     * Check whether the title is to be displayed in the content area
     *
     * @return boolean
     */
    public function isTitleVisible()
    {
        return 'access_denied' != \XLite\Core\Request::getInstance()->target;
    }

    /**
     * Returns 'maintenance_mode' string if frontend is closed or null otherwise
     *
     * @return string
     */
    public function getCustomerZoneWarning()
    {
        return \XLite\Core\Auth::getInstance()->isClosedStorefront() ? 'maintenance_mode' : null;
    }

    /**
     * Get access level
     *
     * @return integer
     */
    public function getAccessLevel()
    {
        return \XLite\Core\Auth::getInstance()->getAdminAccessLevel();
    }

    /**
     * Handles the request to admin interface
     *
     * @return void
     */
    public function handleRequest()
    {
        // Check if user is logged in and has a right access level
        if (!\XLite\Core\Auth::getInstance()->isAuthorized($this)
            && !$this->isPublicZone()
        ) {
            \XLite\Core\Session::getInstance()->lastWorkingURL = $this->get('url');

            $this->redirect($this->buildURL('login'));

        } else {
            if (isset(\XLite\Core\Request::getInstance()->no_https)) {
                \XLite\Core\Session::getInstance()->no_https = true;
            }

            parent::handleRequest();
        }
    }

    /**
     * Define body classes
     *
     * @param array $classes Classes
     *
     * @return array
     */
    public function defineBodyClasses(array $classes)
    {
        $classes = parent::defineBodyClasses($classes);

        $auth = \XLite\Core\Auth::getInstance();
        if ($auth->isLogged()
            && $auth->getProfile()->isAdmin() == \XLite::isAdminZone()
        ) {
            $flags = \XLite\Core\Marketplace::getInstance()->checkForUpdates();
            if (is_array($flags)
                && (!empty($flags[\XLite\Core\Marketplace::FIELD_ARE_UPDATES_AVAILABLE])
                    || !empty($flags[\XLite\Core\Marketplace::FIELD_IS_UPGRADE_AVAILABLE])
                )
            ) {
                $classes[] = 'upgrade-box-visible';
            }

            if (!empty($_COOKIE['XCAdminLeftMenuCompressed'])) {
                $classes[] = 'left-menu-compressed';
            }
        }

        if ($auth->isAdmin()) {
            $classes[] = 'login-page';
        }

        if ($this->isForceChangePassword()) {
            $classes[] = 'force-change-password-section';
        }

        return $classes;
    }


    /**
     * Get recently logged in admins
     *
     * @return array
     */
    public function getRecentAdmins()
    {
        if ($this->isLogged()
            && is_null($this->recentAdmins)
        ) {
            $this->recentAdmins = \XLite\Core\Database::getRepo('XLite\Model\Profile')->findRecentAdmins();
        }

        return $this->recentAdmins;
    }

    /**
     * Check if upgrade or update is available on Marketplace.
     *
     * @return boolean
     */
    public function isUpgradeEntryAvailable()
    {
        \XLite\Upgrade\Cell::getInstance()->clear();

        return (bool) array_filter(
            \Includes\Utils\ArrayManager::getObjectsArrayFieldValues(
                \XLite\Upgrade\Cell::getInstance()->getEntries(),
                'isEnabled'
            )
        );
    }

    /**
     * Call controller action
     *
     * @return void
     */
    protected function callAction()
    {
        parent::callAction();

        if ($this->isLogged()) {
            \XLite::getInstance()->updateModuleRegistry();
            \XLite\Core\WidgetCache::getInstance()->deleteAll();
        }
    }

    /**
     * Check - is current place public or not
     *
     * @return boolean
     */
    protected function isPublicZone()
    {
        return false;
    }

    /**
     * Mark controller run thread as access denied
     *
     * @return void
     */
    protected function markAsAccessDenied()
    {
        if (\XLite\Core\Auth::getInstance()->isLogged()) {
            parent::markAsAccessDenied();

        } else {
            $this->redirect($this->buildURL('login'));
        }
    }

    /**
     * Start simplified page to display progress of some process
     *
     * @return void
     */
    protected function startDump()
    {
        parent::startDump();

        if (!isset(\XLite\Core\Request::getInstance()->mode) || 'cp' != \XLite\Core\Request::getInstance()->mode) {
            $this->displayPageHeader();
        }
    }

    /**
     * Display header of simplified page
     *
     * @param string  $title      Title OPTIONAL
     * @param boolean $scrollDown Scroll down flag OPTIONAL
     *
     * @return void
     */
    protected function displayPageHeader($title = '', $scrollDown = false)
    {
        $output = <<<OUT
<html>
<head>
    <title>$title</title>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
</head>

<body>

OUT;

        if ($scrollDown) {
            $this->dumpStarted = true;
            $output .= func_refresh_start(false);
        }

        $output .= <<<OUT

<div style='font-size: 12px;'>

OUT;

        echo ($output);
    }

    /**
     * displayPageFooter
     *
     * @return void
     */
    protected function displayPageFooter()
    {
        $urls = (array) $this->getPageReturnURL();

        foreach ($urls as $url) {
            echo ('<br />' . $url . '<br />');
        }

        $output = <<<OUT

</div>

</body>
</html>

OUT;

        echo ($output);
    }

    /**
     * getPageReturnURL
     *
     * @return array
     */
    protected function getPageReturnURL()
    {
        return array();
    }

    /**
     * Sanitize Clean URL
     *
     * @param string $cleanURL Clean URL
     *
     * @return string
     */
    protected function sanitizeCleanURL($cleanURL)
    {
        return substr(trim(preg_replace('/[^a-z0-9 \/\._-]+/Sis', '', $cleanURL)), 0, 200);
    }

    // {{{ Updates logging and error handling

    /**
     * Log upgrade error and show top message
     *
     * @param string $action  Current action
     * @param string $message Message to log and show OPTIONAL
     * @param array  $args    Arguments to subsistute OPTIONAL
     *
     * @return void
     */
    protected function showError($action, $message = null, array $args = array())
    {
        $this->valid = false;
        $this->showCommon('Error', $action, $message, $args);
    }

    /**
     * Log upgrade warning and show top message
     *
     * @param string $action  Current action
     * @param string $message Message to log and show OPTIONAL
     * @param array  $args    Arguments to subsistute OPTIONAL
     *
     * @return void
     */
    protected function showWarning($action, $message = null, array $args = array())
    {
        $this->showCommon('Warning', $action, $message, $args);
    }

    /**
     * Log upgrade info and show top message
     *
     * @param string $action  Current action
     * @param string $message Message to log and show OPTIONAL
     * @param array  $args    Arguments to subsistute OPTIONAL
     *
     * @return void
     */
    protected function showInfo($action, $message = null, array $args = array())
    {
        $this->showCommon('Info', $action, $message, $args);
    }

    /**
     * Log upgrade info and show top message
     *
     * @param string $method  Method to call
     * @param string $action  Current action
     * @param string $message Message to log and show
     * @param array  $args    Arguments to subsistute
     *
     * @return void
     */
    protected function showCommon($method, $action, $message, array $args)
    {
        if (!isset($message)) {
            $message = static::t(implode('; ', \XLite\Upgrade\Cell::getInstance()->getErrorMessages()))
                ?: static::t('unknown error');
        }

        if (isset($action) && LC_DEVELOPER_MODE) {
            $message = static::t(
                'Action X::Y, M',
                array(
                    'class'   => get_class($this),
                    'action'  => $action,
                    'message' => $message,
                )
            );
        }

        \XLite\Upgrade\Logger::getInstance()->{'log' . $method}($message, $args, true);
    }

    // }}}

    /**
     * Check - need use secure protocol or not
     *
     * @return boolean
     */
    public function needSecure()
    {
        return parent::needSecure()
            || (!$this->isHTTPS())
            && \XLite\Core\Config::getInstance()->Security->admin_security;
    }


    /**
     * Do not change it.
     * This method defines the address of the Knowledge base help article
     *
     * @return string
     */
    public function getArticleURL()
    {
        return 'http://kb.x-cart.com/pages/viewpage.action?pageId=7504187';
    }
}
