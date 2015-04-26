<?php
namespace XLite\Module\ProaxIT\CustomFields;


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
        return 'Custom Fields';
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
        return 'Add custom fields (packSize, POQty, available, techData) to the Products. Also increases SKU field length to 40 instead of 32';
    }

    /**
     * Decorator runs this method right after rebuilding the classes cache
     * Removes default Quantity box template for a custom replacement template
     *
     * @return void
     */
    public static function runBuildCacheHandler()
    {
        parent::runBuildCacheHandler();

            \XLite\Core\Layout::getInstance()->removeTemplateFromLists('product/quantity_box/parts/quantity_box.tpl');
    }
}