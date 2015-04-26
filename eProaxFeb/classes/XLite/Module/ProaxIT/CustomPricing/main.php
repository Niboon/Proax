<?php
namespace XLite\Module\ProaxIT\CustomPricing;


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
        return 'Custom Pricing';
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
        return 'Add custom pricing for different customers using their customer id through SQL stored procedures.
        Customer_id and Preferred Shipping fields are also added into the Profile table through this module.';
    }
}