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

namespace XLite\Base;

/**
 * SuperClass
 */
abstract class SuperClass
{
    /**
     * Default store language
     *
     * @var string
     */
    protected static $defaultLanguage;
    
    /**
     * Timestamp of the user timezone
     *
     * @var integer
     */
    protected static $userTime;

    /**
     * So called static constructor
     *
     * @return void
     */
    public static function __constructStatic()
    {
        static::$defaultLanguage = \Includes\Utils\ConfigParser::getOptions(array('language', 'default'));
    }

    /**
     * Set default language
     *
     * @param string $code Language code
     *
     * @return void
     */
    public static function setDefaultLanguage($code)
    {
        static::$defaultLanguage = $code;
    }

    /**
     * Getter
     *
     * @return string
     */
    public static function getDefaultLanguage()
    {
        return static::$defaultLanguage;
    }

    /**
     * Return converted into user time current timestamp
     * 
     * @return integer
     */
    public static function getUserTime()
    {
        if (!isset(static::$userTime)) {
            static::$userTime = \XLite\Core\Converter::convertTimeToUser();
        }
        return static::$userTime;
    }

    /**
     * Language label translation short method
     *
     * @param string $name      Label name
     * @param array  $arguments Substitution arguments OPTIONAL
     * @param string $code      Language code OPTIONAL
     *
     * @return string
     */
    protected static function t($name, array $arguments = array(), $code = null)
    {
        return \XLite\Core\Translation::getInstance()->translate($name, $arguments, $code);
    }

    /**
     * Protected constructor.
     * It's not possible to instantiate a derived class (using the "new" operator)
     * until that child class is not implemented public constructor
     *
     * @return void
     */
    protected function __construct()
    {
    }

    /**
     * Stop script execution
     *
     * :FIXME: - must be static
     *
     * @param string $message Text to display
     *
     * @return void
     */
    protected function doDie($message)
    {
        if (!($this instanceof \XLite\Logger)) {
            \XLite\Logger::getInstance()->log($message, LOG_ERR);
        }

        if (
            $this instanceof XLite
            || \XLite::getInstance()->getOptions(array('log_details', 'suppress_errors'))
        ) {
            $message = 'Internal error. Contact the site administrator.';
        }

        die ($message);
    }
}
