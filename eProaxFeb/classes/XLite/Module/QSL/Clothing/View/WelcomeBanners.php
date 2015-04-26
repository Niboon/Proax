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
 * @copyright Copyright (c) 2011-2014 Qualiteam software Ltd <info@x-cart.com>. All rights reserved
 * @license   http://www.x-cart.com/license-agreement.html X-Cart 5 License Agreement
 * @link      http://www.x-cart.com/
 */

namespace XLite\Module\QSL\Clothing\View;

/**
 * Welcome Static Banners widget
 *
 * @ListChild (list="layout.main.center", zone="customer", weight="20")
 */
class WelcomeBanners extends \XLite\View\AView
{
    /**
     * Determines if some module is enabled
     *
     * @return boolean
     */
    public function isModuleEnabled($moduleName, $moduleAuthor = 'CDev')
    {
        $module = \XLite\Core\Database::getRepo('XLite\Model\Module')
            ->findOneBy(array('author' => $moduleAuthor, 'name' => $moduleName));
        return $module && $module->getEnabled();
    }

    /**
     * Check if the module Banner System is avaiable in the store
     *
     * @return boolean
     */
    protected function isBannerSystem()
    {
        return $this->isModuleEnabled('Banner', 'QSL');
    }



    /**
     * Check if it is a Home page
     *
     * @return boolean
     */
    protected function isWelcomeBanner()
    {
        return ('main' == $this->getTarget() && !\XLite\Module\QSL\Clothing\View\WelcomeBanners::isBannerSystem())
            ? true
            : false;
    }


    /**
     * Return list of targets allowed for this widget
     *
     * @return array
     */
    public static function getAllowedTargets()
    {

        $result[] = 'main';

        return $result;

    }

    /**
     * Return widget default template
     *
     * @return string
     */
    protected function getDefaultTemplate()
    {
        return 'modules/QSL/Clothing/welcome.banners.tpl';
    }
}