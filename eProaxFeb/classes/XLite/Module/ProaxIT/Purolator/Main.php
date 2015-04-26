<?php
// vim: set ts=4 sw=4 sts=4 et:

namespace XLite\Module\ProaxIT\Purolator;

/**
 * Main module class 
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
     * Module name
     *
     * @return string
     */
    public static function getModuleName()
    {
        return 'Purolator';
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
        return '0';
    }

    /**
     * Module description
     *
     * @return string
     */
    public static function getDescription()
    {
        return 'Gets shipping quotes for Purolator delivery methods.';
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
     * Return link to settings form
     *
     * @return string
     */
    public static function getSettingsForm()
    {
        return \XLite\Core\Converter::buildURL('purolator');
    }

    /**
     * Perform some actions at startup
     *
     * @return string
     */
    public static function init()
    {
        parent::init();

        // Register Purolator shipping processor
        \XLite\Model\Shipping::getInstance()->registerProcessor(
            '\XLite\Module\ProaxIT\Purolator\Model\Shipping\Processor\Purolator'
        );
    }

    /**
     * Return true if module should work in strict mode
     * (strict mode enables the logging of errors like 'The module is not configured')
     *
     * @return boolean
     */
    public static function isStrictMode()
    {
        return false;
    }
}
