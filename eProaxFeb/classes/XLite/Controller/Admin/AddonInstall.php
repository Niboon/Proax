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
 * AddonInstall
 */
class AddonInstall extends \XLite\Controller\Admin\AAdmin
{
    /**
     * Delimiter for licenses in the popup window textarea
     */
    const LICENSE_DELIMITER = '==============';

    protected $licenseCache = null;

    /**
     * Controller constructor
     *
     * @param array $params
     */
    public function __construct(array $params = array())
    {
        parent::__construct($params);
        if ('view_license' === $this->getAction()) {
            $this->getLicense();
        }
    }

    // {{{ Public methods for viewers

    /**
     * Defines the modules identificators for installation
     *
     * @return array
     */
    public function getModuleIds()
    {
        return explode(',', \XLite\Core\Request::getInstance()->{\XLite\View\Button\Addon\SelectInstallationType::PARAM_MODULEIDS});
    }

    /**
     * Return title
     *
     * @return string
     */
    public function getTitle()
    {
        return 'view_license' === $this->getAction()
            ? static::t('Modules license agreements')
            : static::t('Updates are available');
    }

    /**
     * Return LICENSE text for the modules
     *
     * @return string
     */
    public function getLicense()
    {
        if (is_null($this->licenseCache)) {
            $result = array();
            $error = false;
            foreach ($this->getModuleIds() as $id) {
                $module = $this->getModule($id);
                $info = \XLite\Core\Marketplace::getInstance()->getAddonInfo($module->getMarketplaceID());
                if ($info) {
                    // Do not display the empty license
                    // If the info is correct the empty license means that the admin is already agree with the license
                    if (!empty($info[\XLite\Core\Marketplace::FIELD_LICENSE])) {
                        $result[] .= static::t('{{module}} license agreement', array('module' => $module->getModuleName())) . ":\r\n"
                            . $info[\XLite\Core\Marketplace::FIELD_LICENSE] . "\r\n\r\n";
                    }

                } else {
                    $error = true;
                    $this->showError(__FUNCTION__, static::t('License is not received'));
                }
            }

            // Since this action is performed in popup
            // If the result is empty but there is no error then we redirect the admin to the installation of the modules
            if (empty($result) && !$error) {
                $this->setReturnURL(
                    $this->hasCoreUpdate()
                        ? $this->buildURL(
                            'addon_install',
                            'select_installation_type',
                            array(
                                'widget' => '\XLite\View\ModulesManager\InstallationType',
                                'moduleIds' => implode(',', $this->getModuleIds()),
                            )
                        )
                        : $this->buildURL(
                            'upgrade',
                            'install_addon_force',
                            array(
                                'agree'     => 'Y',
                                'moduleIds' => $this->getModuleIds(),
                            )
                        )
                );

                $this->doRedirect();
            }

            $this->licenseCache = implode(static::LICENSE_DELIMITER . "\r\n", $result);
        }

        return $this->licenseCache;
    }

    // }}}

    /**
     * Check if the core has update (but not upgrade) available
     *
     * @return boolean
     */
    protected function hasCoreUpdate()
    {
        $update = \XLite\Core\Marketplace::getInstance()->checkForUpdates();

        return !empty($update)
            && (
                $update[\XLite\Core\Marketplace::FIELD_ARE_UPDATES_AVAILABLE]
                && !$update[\XLite\Core\Marketplace::FIELD_IS_UPGRADE_AVAILABLE]
            );
    }

    // {{{ Short-name methods

    /**
     * Search for module
     *
     * @param integer $id
     *
     * @return \XLite\Model\Module|void
     */
    protected function getModule($id)
    {
        return \XLite\Core\Database::getRepo('\XLite\Model\Module')->find($id);
    }

    // }}}

    // {{{ Action handlers

    /**
     * doActionViewLicense
     *
     * @return void
     */
    protected function doActionViewLicense()
    {
    }

    /**
     * doActionSelectInstallationType
     *
     * @return void
     */
    protected function doActionSelectInstallationType()
    {
    }

    /**
     * Define the actions with no secure token
     *
     * @return array
     */
    public static function defineFreeFormIdActions()
    {
        return array_merge(parent::defineFreeFormIdActions(), array('view_license'));
    }

    // }}}
}
