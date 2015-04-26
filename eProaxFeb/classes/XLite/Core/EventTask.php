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
 * Event task
 */
class EventTask extends \XLite\Base\Singleton
{
    const STATE_STANDBY     = 1;
    const STATE_IN_PROGRESS = 2;
    const STATE_FINISHED    = 3;
    const STATE_ABORTED     = 4;

    /**
     * Driver
     *
     * @var \XLite\Core\EventDriver\AEventDriver
     */
    protected $driver;

    /**
     * Call events
     *
     * @param string $name Event name
     * @param array  $args Event arguments OPTIONAL
     *
     * @return boolean
     */
    public static function __callStatic($name, array $args = array())
    {
        $result = false;

        if (in_array($name, \XLite\Core\EventListener::getInstance()->getEvents())) {
            $args = isset($args[0]) && is_array($args[0]) ? $args[0] : array();
            $driver = static::getInstance()->getDriver();
            $result = $driver ? $driver->fire($name, $args) : false;
        }

        return $result;
    }

    /**
     * Get driver
     *
     * @return \XLite\Core\EventDriver\AEventDriver
     */
    public function getDriver()
    {
        if (!isset($this->driver)) {
            $driver = \XLite::GetInstance()->getOptions(array('other', 'event_driver')) ?: 'auto';
            $driver = strtolower($driver);
            $list = $this->getDrivers();

            if ('auto' != $driver) {
                foreach ($list as $class) {
                    if (strtolower($class::getCode()) == $driver) {
                        $this->driver = new $class;
                        break;
                    }
                }
            }

            if (!$this->driver) {
                $this->driver = $list ? new $list[0] : false;
            }
        }

        return $this->driver;
    }

    /**
     * Get valid drivers
     *
     * @return array
     */
    protected function getDrivers()
    {
        $list = array();

        foreach ($this->getDriversClasses() as $class) {
            if ($class::isValid()) {
                $list[] = $class;
            }
        }

        return $list;
    }

    /**
     * Get drivers classes
     *
     * @return array
     */
    protected function getDriversClasses()
    {
        return array(
            '\XLite\Core\EventDriver\Db',
        );
    }
}
