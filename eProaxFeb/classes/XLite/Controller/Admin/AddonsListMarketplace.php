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
 * AddonsListMarketplace
 */
class AddonsListMarketplace extends \XLite\Controller\Admin\Base\AddonsList
{
    /**
     * Cache of landing page availability
     *
     * @var null | boolean
     */
    protected $landingPageAvail = null;

    /**
     * Define the actions with no secure token
     *
     * @return array
     */
    public static function defineFreeFormIdActions()
    {
        $list = parent::defineFreeFormIdActions();
        $list[] = 'clear_cache';
        $list[] = 'set_install';
        $list[] = 'unset_install';

        return $list;
    }

    /**
     * Clean the modules-to-install list. It is used right before the installation starts.
     *
     * @return void
     */
    public static function cleanModulesToInstall()
    {
        \XLite\Core\Session::getInstance()->modulesToInstall = array();
    }

    /**
     * Return the current page title (for the content area)
     *
     * @return string
     */
    public function getTitle()
    {
        return static::t('Modules Marketplace');
    }

    /**
     * The landing page flag
     *
     * @return boolean
     */
    public function isLandingPage()
    {
        if (is_null($this->landingPageAvail)) {
            $landingPageAvail = $this->isMarketplaceAccessible()
                ? \XLite\Core\Marketplace::getInstance()->isAvailableLanding()
                : null;

            // Landing page is unavailable if no modules are set on the landing page
            $isLandingModules = (bool) \XLite\Core\Database::getRepo('XLite\Model\Module')
                ->findOneBy(array('isLanding' => true));

            $this->landingPageAvail = isset($landingPageAvail[\XLite\Core\Marketplace::FIELD_LANDING])
                ? (bool)$landingPageAvail[\XLite\Core\Marketplace::FIELD_LANDING] && $isLandingModules
                : false;
        }

        return $this->landingPageAvail && isset(\XLite\Core\Request::getInstance()->landing);
    }

    /**
     * Get the module id list from the session
     *
     * @return array
     */
    public function getModulesToInstall()
    {
        \XLite\Core\Session::getInstance()->modulesToInstall = (!\XLite\Core\Session::getInstance()->modulesToInstall)
            ? array()
            : array_filter(
                \XLite\Core\Session::getInstance()->modulesToInstall,
                array($this, 'checkModulesToInstall')
            );

        return \XLite\Core\Session::getInstance()->modulesToInstall;
    }

    /**
     * Simple check if module id is valid (if there is any module in DB with such moduleId)
     * It is used in self::getModulesToInstall() method
     *
     * @see self::getModulesToInstall()
     *
     * @param integer|string $moduleId Module identificator
     *
     * @return boolean
     */
    public function checkModulesToInstall($moduleId)
    {
        return (bool)\XLite\Core\Database::getRepo('XLite\Model\Module')->find($moduleId);
    }

    /**
     * Verifies if the module is selected to install
     *
     * @param integer $moduleId
     *
     * @return boolean
     */
    public function isModuleSelected($moduleId)
    {
        return isset(\XLite\Core\Session::getInstance()->modulesToInstall[$moduleId]);
    }

    /**
     * Returns the number of selected modules
     *
     * @return integer
     */
    public function countModulesSelected()
    {
        return count(\XLite\Core\Session::getInstance()->modulesToInstall);
    }

    /**
     * Checks if the modules selected list is not empty
     *
     * @return boolean
     */
    public function hasModulesSelected()
    {
        return $this->countModulesSelected() > 0;
    }

    /**
     * Empty tag is provided for default landing page
     *
     * @return string
     */
    public function getTagValue()
    {
        return '';
    }

    /**
     * Return module full name
     *
     * @param integer $id
     *
     * @return string
     */
    public function getModuleName($id)
    {
        return \XLite\Core\Database::getRepo('XLite\Model\Module')->find($id)->getModuleName();
    }

    /**
     * Clear marketplace cache
     *
     * @return void
     */
    protected function doActionClearCache()
    {
        \XLite\Core\Marketplace::getInstance()->clearActionCache();

        $params = \XLite\Core\Request::getInstance()->landing
            ? array('landing' => 1)
            : array();

        $this->setReturnURL($this->buildURL('addons_list_marketplace', '', $params));
    }

    /**
     * Store the module id for installation
     *
     * @return void
     */
    protected function doActionSetInstall()
    {
        $this->storeModuleToInstall(\XLite\Core\Request::getInstance()->id);
        exit(0);
    }

    /**
     * Remove the module id for the installation
     *
     * @return void
     */
    protected function doActionUnsetInstall()
    {
        $this->removeModuleToInstall(\XLite\Core\Request::getInstance()->id);
        exit(0);
    }

    /**
     * Store the module id into the session for the further installation
     *
     * @param integer $id
     *
     * @return void
     */
    protected function storeModuleToInstall($id)
    {
        if (!\XLite\Core\Session::getInstance()->modulesToInstall) {
            \XLite\Core\Session::getInstance()->modulesToInstall = array();
        }

        \XLite\Core\Session::getInstance()->modulesToInstall =
            \XLite\Core\Session::getInstance()->modulesToInstall + array($id => $id);
    }

    /**
     * Remove the module id from the installation list
     *
     * @param integer $id
     *
     * @return void
     */
    protected function removeModuleToInstall($id)
    {
        if (!\XLite\Core\Session::getInstance()->modulesToInstall) {
            \XLite\Core\Session::getInstance()->modulesToInstall = array();
        }

        if (isset(\XLite\Core\Session::getInstance()->modulesToInstall[$id])) {
            $modulesToInstall = \XLite\Core\Session::getInstance()->modulesToInstall;
            unset($modulesToInstall[$id]);
            \XLite\Core\Session::getInstance()->modulesToInstall = $modulesToInstall;
        }
    }
}
