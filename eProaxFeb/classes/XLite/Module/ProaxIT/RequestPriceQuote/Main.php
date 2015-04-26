<?php
// vim: set ts=4 sw=4 sts=4 et:

namespace XLite\Module\ProaxIT\RequestPriceQuote;

/**
 * Request price quote module main class
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
        return 'Niboon Tangnirunkul';
    }

    /**
     * Module version
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
        return '0';
    }

    /**
     * Module name
     *
     * @return string
     */
    public static function getModuleName()
    {
        return 'Request Price Quote';
    }

    /**
     * Module description
     *
     * @return string
     */
    public static function getDescription()
    {
        return 'Introduces the ability to hide the prices for certain products and show a popup button for customers to
        send an email to request a price quote for the product.';
    }

    /**
     * Determines if we need to show settings form link
     *
     * @return boolean
     */
    public static function showSettingsForm()
    {
        return true;
    }

    /**
     * Decorator run this method at the end of cache rebuild
     * Remove default add to cart button for integration with price quote request logic
     *
     * @return void
     */
    public static function runBuildCacheHandler()
    {
        parent::runBuildCacheHandler();

        \XLite\Core\Layout::getInstance()->removeTemplateFromLists('product/details/parts/common.add-button.tpl');
        \XLite\Core\Layout::getInstance()->removeTemplateFromLists('items_list/product/parts/common.drag-n-drop-handle.tpl');
        \XLite\Core\Layout::getInstance()->removeTemplateFromLists('items_list/product/parts/common.button-add2cart.tpl');
        \XLite\Core\Layout::getInstance()->removeTemplateFromLists('items_list/product/parts/list.button-add2cart.tpl');
        \XLite\Core\Layout::getInstance()->removeTemplateFromLists('modules/CDev/ProductAdvisor/items_list/product/parts/common.button-add2cart.tpl');
        \XLite\Core\Layout::getInstance()->removeTemplateFromLists('modules/CDev/InstantSearch/product/add-to-cart.buy.tpl');
        \XLite\Core\Layout::getInstance()->removeTemplateFromLists('modules/CDev/InstantSearch/product/price.tpl');
        \XLite\Core\Layout::getInstance()->removeTemplateFromLists('modules/CDev/InstantSearch/product/add-to-cart.qty.tpl');
    }

}
