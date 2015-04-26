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
 * AddonsListInstalled
 */
class AddonsListInstalled extends \XLite\Controller\Admin\Base\AddonsList
{
    /**
     * Define and set handler attributes; initialize handler
     *
     * @param array $params Handler params OPTIONAL
     *
     * @return void
     */
    public function __construct(array $params = array())
    {
        parent::__construct($params);

        if (\XLite\Core\Session::getInstance()->returnURL) {
            $this->setReturnURL(\XLite\Core\Session::getInstance()->returnURL);
            \XLite\Core\Session::getInstance()->returnURL = '';

            $this->redirect();
        }
    }

    /**
     * Return the current page title (for the content area)
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->isRecentlyInstalledPage()
            ? static::t('Recently installed modules')
            : static::t('Installed Modules');
    }

    /**
     * Substring search getter
     *
     * @return string
     */
    public function getSubstring()
    {
        return \XLite\Core\Request::getInstance()->substring;
    }

    /**
     * The recently installed page flag
     *
     * @return boolean
     */
    public function isRecentlyInstalledPage()
    {
        return isset(\XLite\Core\Request::getInstance()->recent)
            && (count(static::getRecentlyInstalledModuleList()) > 0);
    }

    // {{{ Short-name methods

    /**
     * Return module identificator
     *
     * @return integer
     */
    protected function getModuleId()
    {
        return \XLite\Core\Request::getInstance()->moduleId;
    }

    /**
     * Search for module
     *
     * @return \XLite\Model\Module|void
     */
    protected function getModule()
    {
        return \XLite\Core\Database::getRepo('\XLite\Model\Module')->find($this->getModuleId());
    }

    /**
     * Search for modules
     *
     * @param string $cellName Request cell name
     *
     * @return array
     */
    protected function getModules($cellName)
    {
        $modules = array();

        foreach (((array) \XLite\Core\Request::getInstance()->$cellName) as $id => $value) {
            $modules[] = \XLite\Core\Database::getRepo('\XLite\Model\Module')->find(intval($id));
        }

        return array_filter($modules);
    }

    // }}}

    // Action handlers

    /**
     * Enable module
     *
     * :TODO: TO REMOVE?
     *
     * @return void
     */
    protected function doActionEnable()
    {
        $module = $this->getModule();

        if ($module) {
            // Update data in DB
            // :TODO: this action should be performed via ModulesManager
            $module->setEnabled(true);
            $module->getRepository()->update($module);

            // Flag to rebuild cache
            \XLite::setCleanUpCacheFlag(true);
        }
    }

    /**
     * Define the actions with no secure token
     *
     * @return array
     */
    public static function defineFreeFormIdActions()
    {
        return array_merge(parent::defineFreeFormIdActions(), array('pack'));
    }

    /**
     * Pack module into PHAR module file
     *
     * @return void
     */
    protected function doActionPack()
    {
        if (LC_DEVELOPER_MODE) {
            $module = $this->getModule();

            if ($module) {
                if ($module->getEnabled()) {
                    \Includes\Utils\PHARManager::packModule(new \XLite\Core\Pack\Module($module));

                } else {
                    \XLite\Core\TopMessage::addError('Only enabled modules can be packed');
                }

            } else {
                \XLite\Core\TopMessage::addError('Module with ID X is not found', array('id' => $this->getModuleId()));
            }

        } else {
            \XLite\Core\TopMessage::addError(
                'Module packing is available in the DEVELOPER mode only. Check etc/config.php file'
            );
        }
    }

    /**
     * Uninstall module
     *
     * @return void
     */
    protected function doActionUninstall()
    {
        $module = $this->getModule();
        if ($module && $module->canUninstall()) {
            if ($module->getEnabled()) {
                $module->setEnabled(false);
                $module->callModuleMethod('callDisableEvent');
            }

            if (!defined('LC_MODULE_CONTROL')) {
                define('LC_MODULE_CONTROL', true);
            }
            $result = $this->uninstallModule($module);

            if ($result) {
                // To restore previous state
                \XLite\Core\Marketplace::getInstance()->saveAddonsList(0);
                // Flag to rebuild cache
                \XLite::setCleanUpCacheFlag(true);
            }
        }
    }

    /**
     * Switch module
     *
     * @return void
     */
    protected function doActionSwitch()
    {
        $changed = false;
        $deleted = false;
        $data    = (array) \XLite\Core\Request::getInstance()->switch;
        $modules = array();
        $firstModule = null;

        $switchModules = $this->getModules('switch');
        $switchModulesKeys = array();

        $excludedModules = array();
        $excludedEnableModules = array();

        // Correct modules list
        foreach ($switchModules as $key => $module) {

            $toDelete  = false;
            $toDisable = false;
            $toEnable  = false;

            $switchModulesKeys[] = $module->getModuleId();

            if (!empty($data[$module->getModuleId()]['delete'])) {
                $toDelete = true;

            } else {
                $old = !empty($data[$module->getModuleId()]['old']);
                $new = !empty($data[$module->getModuleId()]['new']);
                $toDisable = (!$new && $old != $new);
                $toEnable  = ($new && $old != $new);
            }

            if ($toDelete || $toDisable) {
                $dependentModules = $module->getDependentModules();
                if ($dependentModules) {

                    foreach ($dependentModules as $dep) {

                        $depModule = \XLite\Core\Database::getRepo('XLite\Model\Module')->getModuleInstalled($dep);

                        if ($depModule) {
                            $depDelete = !empty($data[$depModule->getModuleId()]['delete']);
                            $depDisable = empty($data[$depModule->getModuleId()]['new']);

                            if (
                                ($toDelete && !$depDelete)
                                || ($toDisable && !$depDelete && !$depDisable)
                            ) {
                                // Remove current module from the actions list if it has active dependent modules
                                $excludedModules[] = $module->getModuleName();
                                unset($data[$module->getModuleId()]);
                                unset($switchModules[$key]);
                                break;
                            }
                        }
                    }
                }

            } elseif ($toEnable) {
                // Get the list of modules which current module depends on
                $list = $this->getAllDisabledModuleDependencies($module);

                if ($list) {
                    foreach ($list as $m) {
                        if (
                            empty($data[$m->getModuleId()])
                            || (
                                empty($data[$m->getModuleId()]['delete'])
                                && empty($data[$m->getModuleId()]['new'])
                            )
                        ) {
                            $data[$m->getModuleId()] = array(
                                'old' => 0,
                                'new' => 1,
                            );
                            $additionalSwitchModules[$m->getModuleId()] = $m;
                        }
                    }

                } elseif (false === $list) {
                    // Remove current module from the actions list as it can't be enabled
                    $excludedEnableModules[] = $module->getModuleName();
                    unset($data[$module->getModuleId()]);
                }
            }
        }

        if ($excludedModules) {
            \XLite\Core\TopMessage::addWarning(
                'The following selected modules cannot be disabled or uninstalled as they have dependent modules',
                array('list' => implode(', ', $excludedModules))
            );

            // Selection has excluded modules - this is a critical case, break an entire operation
            return;
        }

        if ($excludedEnableModules) {
            \XLite\Core\TopMessage::addWarning(
                'The following selected modules cannot be enabled as they depend on disabled modules which cannot be enabled',
                array('list' => implode(', ', $excludedEnableModules))
            );

            // Selection has excluded modules - this is a critical case, break an entire operation
            return;
        }

        if (!empty($additionalSwitchModules)) {
            // Extend $switchModules list by additional modules
            foreach ($additionalSwitchModules as $k => $am) {
                if (!in_array($k, $switchModulesKeys)) {
                    $switchModules[] = $am;
                }
            }
        }

        foreach ($switchModules as $module) {

            if (!empty($data[$module->getModuleId()]['delete'])) {
                $old = $new = null;
                $delete = true;

            } else {
                $old = !empty($data[$module->getModuleId()]['old']);
                $new = !empty($data[$module->getModuleId()]['new']);
                $delete = false;
            }

            if ($delete) {

                // Uninstall module

                if ($module->getEnabled()) {
                    $module->setEnabled(false);
                    $module->callModuleMethod('callDisableEvent');
                }

                if (!defined('LC_MODULE_CONTROL')) {
                    define('LC_MODULE_CONTROL', true);
                }

                if ($this->uninstallModule($module)) {
                    $deleted = true;
                }

            } elseif ($old !== $new) {

                // Change module status

                $module->setEnabled($new);

                // Call disable event to make some module specific changes
                if ($old) {
                    $module->callModuleMethod('callDisableEvent');
                } elseif (is_null($firstModule)) {
                    $firstModule = $module;
                }

                $modules[] = $module;
                $changed = true;
            }
        }

        // Flag to rebuild cache
        if ($changed) {
            // We redirect the admin to the modules page on the activated module anchor
            // The first module in a batch which is available now
            \XLite\Core\Session::getInstance()->returnURL = $firstModule
                ? $this->getModulePageURL($firstModule)
                : (\XLite\Core\Request::getInstance()->return ?: '');

            \XLite\Core\Database::getRepo('\XLite\Model\Module')->updateInBatch($modules);
        }

        if ($deleted) {
            // Refresh marketplace modules cache
            \XLite\Core\Marketplace::getInstance()->saveAddonsList(0);
        }

        if ($changed || $deleted) {
            // Flag to rebuild classes cache
            \XLite::setCleanUpCacheFlag(true);
        }
    }

    /**
     * Get list of all not active module dependencies
     * Returns false if module can not be enabled
     *
     * @param \XLite\Model\Module $module Module model
     *
     * @return array|boolean
     */
    protected function getAllDisabledModuleDependencies($module)
    {
        $list = array();
        $canEnable = true;

        foreach ($module->getDependencyModules(true) as $dep) {

            if (!$this->canEnable($dep)) {
                $canEnable = false;
                break;

            } else {

                $list[$dep->getActualName()] = $dep;

                $deps = $this->getAllDisabledModuleDependencies($dep);

                if (false === $deps) {
                    $canEnable = false;
                    break;

                } else {
                    $list = array_merge($list, $deps);
                }
            }
        }

        return $canEnable ? $list : false;
    }

    /**
     * Alias for canEnable() method
     *
     * @param \XLite\Model\Module $module Module model
     *
     * @return boolean
     */
    protected function canEnable($module)
    {
        return $module->canEnable(true);
    }

    /**
     * Module page URL getter
     *
     * @param \XLite\Model\Module $module Module model
     *
     * @return string
     */
    protected function getModulePageURL(\XLite\Model\Module $module)
    {
        $pageId = \XLite\Core\Database::getRepo('XLite\Model\Module')->getInstalledPageId(
            $module->getAuthor(),
            $module->getName(),
            \XLite\View\Pager\Admin\Module\Manage::getInstance()->getItemsPerPage()
        );

        $params = array(
            'clearCnd' => 1,
            \XLite\View\Pager\APager::PARAM_PAGE_ID => $pageId,
        );

        return $this->buildURL('addons_list_installed', '', $params) . '#' . $module->getName();
    }

    /**
     * Perform some actions before redirect
     *
     * @param string $action Performed action
     *
     * @return void
     */
    protected function actionPostprocess($action)
    {
        parent::actionPostprocess($action);

        $this->setReturnURL($this->buildURL('addons_list_installed'));
    }

    // }}}
}
