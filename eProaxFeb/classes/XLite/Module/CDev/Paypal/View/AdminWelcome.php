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

namespace XLite\Module\CDev\Paypal\View;

/**
 * Paypal banner
 *
 * @ListChild (list="dashboard-center", zone="admin", weight="20")
 */
class AdminWelcome extends \XLite\View\Dialog
{
    /**
     * Add widget specific CSS files
     *
     * @return array
     */
    public function getCSSFiles()
    {
        $list = parent::getCSSFiles();
        $list[] = 'main/style.css';
        $list[] = $this->getDir() . '/style.css';

        return $list;
    }

    /**
     * Return templates directory name
     *
     * @return string
     */
    protected function getDir()
    {
        return 'modules/CDev/Paypal/welcome';
    }

    /**
     * Check if widget is visible
     *
     * @return boolean
     */
    protected function isVisible()
    {
        $config = \XLite\Core\Config::getInstance()->CDev->Paypal;

        return parent::isVisible()
            && $this->isRootAccess()
            && 1 != \XLite\Core\Session::getInstance()->hide_welcome_block_paypal
            && 'Y' == $config->show_admin_welcome;
    }

    /**
     * Check if the current admin user has the root access
     *
     * @return boolean
     */
    protected function isRootAccess()
    {
        return \XLite\Core\Auth::getInstance()->isPermissionAllowed(\XLite\Model\Role\Permission::ROOT_ACCESS);
    }

    /**
     * Get box class
     *
     * @return string
     */
    protected function getBoxClass()
    {
        return 'admin-welcome paypal';
    }

    /**
     * Returns paupal email
     *
     * @return string
     */
    protected function getPaypalEmail()
    {
        $method = \XLite\Module\CDev\Paypal\Main::getPaymentMethod(\XLite\Module\CDev\Paypal\Main::PP_METHOD_EC);

        return $method->getSetting('email');
    }
}
