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

namespace XLite\Module\CDev\Catalog;

/**
 * Catalog module main class
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
        return 'Catalog';
    }

    /**
     * Module description
     *
     * @return string
     */
    public static function getDescription()
    {
        return 'Disables checkout and makes your website a plain catalog'
            . ' of products which customers can browse but can\'t buy online.';
    }

    /**
     * Decorator run this method at the end of cache rebuild
     *
     * @return void
     */
    public static function runBuildCacheHandler()
    {
        parent::runBuildCacheHandler();

        \XLite\Core\Layout::getInstance()->removeTemplateFromLists('product/details/parts/common.add-button.tpl');
        \XLite\Core\Layout::getInstance()->removeTemplateFromLists('product/details/parts/common.qty.tpl');
        \XLite\Core\Layout::getInstance()->removeTemplateFromLists('items_list/product/parts/common.drag-n-drop-handle.tpl');
        \XLite\Core\Layout::getInstance()->removeTemplateFromLists('items_list/product/parts/common.button-add2cart.tpl');
        \XLite\Core\Layout::getInstance()->removeTemplateFromLists('items_list/product/parts/list.button-add2cart.tpl');
        \XLite\Core\Layout::getInstance()->removeTemplateFromLists('items_list/product/parts/table.captions.add2cart-button.tpl');
        \XLite\Core\Layout::getInstance()->removeTemplateFromLists('modules/CDev/ProductAdvisor/items_list/product/parts/common.button-add2cart.tpl');
        \XLite\Core\Layout::getInstance()->removeTemplateFromLists('product/details/parts/header.add.tpl');
    }

}
