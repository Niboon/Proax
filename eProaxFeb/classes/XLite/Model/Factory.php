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

namespace XLite\Model;

/**
 * ____description____
 * TODO[SINGLETON] - must extends the Base\Singleton
 * NOTE - check the "factory.<name>" tags in templates
 */
class Factory extends \XLite\Base
{
    /**
     * Create object instance and pass arguments to it contructor (if needed)
     *
     * @param string $class Class name
     * @param array  $args  Constructor arguments OPTIONAL
     *
     * @return \XLite\Base
     */
    public static function create($class, array $args = array())
    {
        $handler = new \ReflectionClass($class);

        return self::isSingleton($handler) ? self::getSingleton($class) : self::createObject($handler, $args);
    }


    /**
     * Check if class is a singleton
     * FIXME - must be revised or removed
     *
     * @param \ReflectionClass $handler Class descriptor
     *
     * @return void
     */
    protected static function isSingleton(\ReflectionClass $handler)
    {
        return $handler->getConstructor()->isProtected();
    }

    /**
     * Return a singleton refernce
     *
     * @param string $class Class name
     *
     * @return \XLite\Base
     */
    protected static function getSingleton($class)
    {
        return call_user_func(array($class, 'getInstance'));
    }

    /**
     * Create new object
     *
     * @param \ReflectionClass $handler Class descriptor
     * @param array            $args    Constructor params OPTIONAL
     *
     * @return \XLite\Base
     */
    protected static function createObject(\ReflectionClass $handler, array $args = array())
    {
        return $handler->hasMethod('__construct') ? $handler->newInstanceArgs($args) : $handler->newInstance();
    }


    /**
     * Create object instance
     *
     * @param string $name Class name
     *
     * @return \XLite\Base
     */
    public function __get($name)
    {
        return self::create($name);
    }
}
