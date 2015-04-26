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

namespace XLite\Module\CDev\UserPermissions;

/**
 * User permissions module main class
 */
abstract class Main extends \XLite\Module\AModule
{
    /**
     * Author name
     *
     * @return string
     */
    public static function getAuthorName()
    {
        return 'X-Cart team';
    }

    /**
     * Get module major version
     *
     * @return string
     */
    public static function getMajorVersion()
    {
        return '5.2';
    }

    /**
     * Module version
     *
     * @return string
     */
    public static function getMinorVersion()
    {
        return '1';
    }

    /**
     * Module name
     *
     * @return string
     */
    public static function getModuleName()
    {
        return 'User permissions';
    }

    /**
     * Module description
     *
     * @return string
     */
    public static function getDescription()
    {
        return 'Allows you to restrict access to backend functions to only those employees who need them. You can define administrator roles and configure which groups of back-end functions are available to users having these roles.';
    }

    /**
     * Decorator run this method at the end of cache rebuild
     *
     * @return void
     */
    public static function runBuildCacheHandler()
    {
        parent::runBuildCacheHandler();

        $enabledRole = \XLite\Core\Database::getRepo('XLite\Model\Role')->findOneBy(array('enabled' => true));
        if (!$enabledRole) {
            $permanent = \XLite\Core\Database::getRepo('XLite\Model\Role')->getPermanentRole();
            if (!$permanent) {
                $permanent = \XLite\Core\Database::getRepo('XLite\Model\Role')->findFrame(0, 1);
                $permanent = 0 < count($permanent) ? array_shift($permanent) : null;
            }

            if ($permanent) {
                $permanent->setEnabled(true);
                \XLite\Core\Database::getEM()->flush();
            }
        }
    }

    /**
     * Get permissions which should be enabled after enabling the module
     *
     * @return array
     */
    public static function getPermissions()
    {
        return parent::getCorePermissions();
    }
}
