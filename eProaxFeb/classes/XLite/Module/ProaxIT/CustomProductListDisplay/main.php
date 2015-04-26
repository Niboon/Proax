<?php
namespace XLite\Module\ProaxIT\CustomProductListDisplay;


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
     * Module name
     *
     * @return string
     */
    public static function getModuleName()
    {
        return 'Custom Product List Display';
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
        return 0;
    }

    /**
     * Module description
     *
     * @return string
     */
    public static function getDescription()
    {
        return 'Switches the default product result display to a list instead of a grid. Also introduces a fix to the size of items in the grid view so that the grids are equally sized while maintaining the aspect ratios';
    }

    /**
     * Decorator runs this method right after rebuilding the classes cache
     * Remove the current grid view image thumbnail template to allow for a custom replacement template
     *
     * @return void
     */
    public static function runBuildCacheHandler()
    {
        parent::runBuildCacheHandler();

            \XLite\Core\Layout::getInstance()->removeTemplateFromList('product/quantity_box/parts/quantity_box.tpl','itemsList.product.grid.customer.info.photo');
    }
}