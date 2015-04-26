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

namespace XLite\Core;

/**
 * Events subsystem
 */
class Event extends \XLite\Base\Singleton
{
    /**
     * Events list
     *
     * @var array
     */
    protected $events = array();

    /**
     * Trigger invalidElement event
     *
     * @param string $name    Element name
     * @param string $message Error message
     *
     * @return void
     */
    public static function invalidElement($name, $message)
    {
        self::__callStatic('invalidElement', array(array('name' => $name, 'message' => $message)));
    }

    /**
     * Trigger invalidForm event
     *
     * @param string $name    Form name
     * @param string $message Error message
     *
     * @return void
     */
    public static function invalidForm($name, $message)
    {
        self::__callStatic('invalidForm', array(array('name' => $name, 'message' => $message)));
    }

    /**
     * Short event creation
     *
     * @param string $name      Event name
     * @param array  $arguments Event arguments
     *
     * @return void
     */
    public static function __callStatic($name, array $arguments)
    {
        static::getInstance()->trigger(
            $name,
            0 < count($arguments) ? array_shift($arguments) : array()
        );
    }

    /**
     * Trigger event
     *
     * @param string $name      Event name
     * @param array  $arguments Event arguments OPTIONAL
     *
     * @return void
     */
    public function trigger($name, array $arguments = array())
    {
        $this->events[] = array(
            'name'      => $name,
            'arguments' => $arguments,
        );
    }

    /**
     * Exclude event
     * 
     * @param string $name Event name
     *  
     * @return void
     */
    public function exclude($name)
    {
        foreach ($this->events as $i => $event) {
            if ($event['name'] == $name) {
                unset($this->events[$i]);
            }
        }

        $this->events = array_values($this->events);
    }

    /**
     * Display events
     *
     * @return void
     */
    public function display()
    {
        foreach ($this->events as $event) {
            header('event-' . $event['name'] . ': ' . json_encode($event['arguments']));
        }
    }

    /**
     * Clear list
     *
     * @return void
     */
    public function clear()
    {
        $this->events = array();
    }
}
