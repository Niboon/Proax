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

namespace XLite\Module\CDev\SimpleCMS\Controller\Admin;

/**
 * Logo and favicon changes
 *
 */
class LogoFavicon extends \XLite\Controller\Admin\Settings
{
    /**
     * Name of the configuration category
     */
    const LOGO_FAVICON_CATEGORY = 'CDev\SimpleCMS';

    /**
     * Page identification
     *
     * @var string
     */
    public $page = self::LOGO_FAVICON_CATEGORY;

    /**
     * Title of the page
     *
     * @return string
     */
    public function getTitle()
    {
        return static::t('Look & Feel');
    }

    /**
     * Get tab names
     *
     * @return array
     */
    public function getPages()
    {
        $list = parent::getPages();
        $list[static::LOGO_FAVICON_CATEGORY] = static::t('Logo & Favicon');

        return $list;
    }
}
