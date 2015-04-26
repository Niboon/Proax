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

namespace XLite\Module\CDev\SimpleCMS\View;

/**
 * Logo
 */
abstract class AView extends \XLite\View\AView implements \XLite\Base\IDecorator
{
    /**
     * Return theme common files
     *
     * @param boolean $adminZone Admin zone flag OPTIONAL
     *
     * @return array
     */
    protected function getThemeFiles($adminZone = null)
    {
        $list = parent::getThemeFiles($adminZone);
        $list[static::RESOURCE_CSS][] = 'modules/CDev/SimpleCMS/page/style.css';

        return $list;
    }

    /**
     * Return favicon resource path
     *
     * @return string
     */
    protected function getFavicon()
    {
        $url = \XLite\Core\Config::getInstance()->CDev->SimpleCMS->favicon;

        return $url ?: parent::getFavicon();
    }

    /**
     * Flag if the favicon is displayed in the customer area
     *
     * If the custom favicon is defined then the favicon will be displayed
     *
     * @return boolean
     */
    protected function displayFavicon()
    {
        $url = \XLite\Core\Config::getInstance()->CDev->SimpleCMS->favicon;

        return !empty($url);
    }

    /**
     * Get invoice logo
     *
     * @return string
     */
    public function getInvoiceLogo()
    {
        $url = \XLite\Core\Config::getInstance()->CDev->SimpleCMS->logo;

        return $url ?: parent::getInvoiceLogo();
    }
}
