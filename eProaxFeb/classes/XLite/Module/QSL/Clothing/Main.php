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

namespace XLite\Module\QSL\Clothing;

/**
 * Module description
 *
 * @package XLite
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
        return 'Qualiteam';
    }

    /**
     * Module name
     *
     * @return string
     */
    public static function getModuleName()
    {
        return 'Template #42 "Clothing"';
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
        return '4';
    }

    /**
     * Module description
     *
     * @return string
     */
    public static function getDescription()
    {
        return 'The "Clothing" X-Cart Design Template (#42). This template is good for the following themes:  Accessories, Clothing, Jewelry, Watches, Wedding';
    }

    /**
     * Determines if we need to show settings form link
     *
     * @return boolean
     */
    public static function showSettingsForm()
    {
        return false;
    }


    /**
     * Register the module skins.
     *
     * @return array
     */
    public static function getSkins()
    {
        return array(
            \XLite::CUSTOMER_INTERFACE => array(
                'Clothing/customer',
            ),
        );
    }


    /**
     *
     * @return array
     */
    protected static function moveTemplatesInLists()
    {
        $templates_list = array();

        $templates_list = array(

            'layout/header.bar.search.tpl' => array(
                array('layout.header.bar', 'customer'),
                array('layout.header', 400, 'customer'),
            ),

            'layout/header.right.tpl' => array(
                array('layout.header', 'customer'),
                array('layout.header', 300, 'customer'),
            ),

            'layout/header.bar.tpl' => array(
                array('layout.header', 'customer'),
                array('layout.header', 200, 'customer'),
            ),

            'mini_cart/horizontal/parts/item.price.tpl' => array(
                array('minicart.horizontal.item', 'customer'),
                array('minicart.horizontal.item', 400, 'customer'),
            ),
            
            'layout/main.footer.tpl' => array(
                array('layout.main', 'customer'),
                array('layout.bottom_menu', 50, 'customer'),
            ),

        );

        return $templates_list;
    }

    /**
     *
     * @return array
     */
     /*protected static function moveClassesInLists()
     {
         return array(
             'XLite\CDev\' => array(
                 array('layout.header.right', 'customer'),
                 array('sidebar.first', 10, 'customer'),
             ),
         );
     }*/


    /**
     * Decorator run this method at the end of cache rebuild
     *
     * @return void
     */
    public static function runBuildCacheHandler()
    {
        parent::runBuildCacheHandler();

        if (\XLite\Core\Database::getRepo("XLite\Model\Module")->findOneBy(array("name"=>"Bestsellers", "author"=>"CDev", "enabled"=> true))) {

            \XLite\Core\Layout::getInstance()->removeClassFromList(
                'XLite\Module\CDev\Bestsellers\View\Bestsellers',
                'center.bottom',
                \XLite\Model\ViewList::INTERFACE_CUSTOMER
            );

            \XLite\Core\Layout::getInstance()->addClassToList(
                'XLite\Module\CDev\Bestsellers\View\Bestsellers',
                'center.bottom',
                array(
                    'zone'   => \XLite\Model\ViewList::INTERFACE_CUSTOMER,
                    'weight' => 80,
                )
            );
        }


        \XLite\Core\Layout::getInstance()->removeClassFromList(
            'XLite\View\Category',
            'center',
            \XLite\Model\ViewList::INTERFACE_CUSTOMER
        );

        \XLite\Core\Layout::getInstance()->addClassToList(
            'XLite\View\Category',
            'center.bottom',
            array(
                'zone'   => \XLite\Model\ViewList::INTERFACE_CUSTOMER,
                'weight' => 79,
            )
        );

        \XLite\Core\Layout::getInstance()->addTemplateToList(
            'items_list/product/parts/common.product-thumbnail.tpl',
            'product.rotator.item',
            array(
                'zone'   => \XLite\Model\ViewList::INTERFACE_CUSTOMER,
                'weight' => 1,
            )
        );
        
        \XLite\Core\Layout::getInstance()->addTemplateToList(
            'items_list/product/parts/common.product-name.tpl',
            'product.rotator.item',
            array(
                'zone'   => \XLite\Model\ViewList::INTERFACE_CUSTOMER,
                'weight' => 20,
            )
        );

        \XLite\Core\Layout::getInstance()->addTemplateToList(
            'items_list/product/parts/common.product-thumbnail.tpl',
            'product.simple.list.item',
            array(
                'zone'   => \XLite\Model\ViewList::INTERFACE_CUSTOMER,
                'weight' => 10,
            )
        );

        \XLite\Core\Layout::getInstance()->addTemplateToList(
            'items_list/product/parts/common.product-name.tpl',
            'product.simple.list.item',
            array(
                'zone'   => \XLite\Model\ViewList::INTERFACE_CUSTOMER,
                'weight' => 20,
            )
        );

        \XLite\Core\Layout::getInstance()->addTemplateToList(
            'items_list/product/parts/common.product-price.tpl',
            'product.simple.list.item',
            array(
                'zone'   => \XLite\Model\ViewList::INTERFACE_CUSTOMER,
                'weight' => 30,
            )
        );

        if (\XLite\Core\Database::getRepo("XLite\Model\Module")->findOneBy(array("name"=>"ProductAdvisor", "author"=>"CDev", "enabled"=> true))) {

            \XLite\Core\Layout::getInstance()->removeClassFromList(
                'XLite\Module\CDev\ProductAdvisor\View\RecentlyViewed',
                'center.bottom',
                \XLite\Model\ViewList::INTERFACE_CUSTOMER
            );

            \XLite\Core\Layout::getInstance()->addClassToList(
                'XLite\Module\CDev\ProductAdvisor\View\RecentlyViewed',
                'center.bottom',
                array(
                    'zone'   => \XLite\Model\ViewList::INTERFACE_CUSTOMER,
                    'weight' => 950,
                )
            );
        }
    }
}
