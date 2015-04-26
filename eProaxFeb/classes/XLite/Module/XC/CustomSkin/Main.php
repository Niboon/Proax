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

namespace XLite\Module\XC\CustomSkin;

/**
 * Development module that simplifies the process of implementing design changes
 *
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
     * Module name
     *
     * @return string
     */
    public static function getModuleName()
    {
        return 'Custom skin';
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
        return '2';
    }

    /**
     * Module description
     *
     * @return string
     */
    public static function getDescription()
    {
        return 'Development module that simplifies the process of implementing design changes.';
    }

    /**
     * No settings for this module
     *
     * @return boolean
     */
    public static function showSettingsForm()
	{
		return false;
	}

    /**
     * The following pathes are defined as substitutional skins:
     *
     * admin interface:     skins/custom_skin/admin/en/
     * customer interface:  skins/custom_skin/default/en/
     * mail interface:      skins/custom_skin/mail/en
     *
     * @return array
     */
    public static function getSkins()
    {
        return array(
            \XLite::ADMIN_INTERFACE     => array('custom_skin/admin'),
            \XLite::CUSTOMER_INTERFACE  => array('custom_skin/default'),
            \XLite::MAIL_INTERFACE      => array('custom_skin/mail'),
        );
    }

    /**
     * The module is defined as skin module
     *
     * @return integer|null
     */
    public static function getModuleType()
    {
        return static::MODULE_TYPE_SKIN;
    }
}
