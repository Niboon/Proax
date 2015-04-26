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

namespace XLite\Controller\Console;

/**
 * AMQP listener controller
 */
class AMQPListener extends \XLite\Controller\Console\AConsole
{
    /**
     * Driver 
     * 
     * @var \XLite\Core\EventDriver\AMQP
     */
    protected $driver;

    /**
     * Handle message 
     * 
     * @param \AMQPMessage $message Mesasge
     * @param string       $name    Event (queue) name
     *  
     * @return boolean
     */
    public function handleMessage(\AMQPMessage $message, $name)
    {
        $result = false;
        $data = @unserialize($message->body) ?: array();

        if (\XLite\Core\EventListener::getInstance()->handle($name, $data)) {
            $result = true;
            $this->getDriver()->sendAck($message); 
        }

        return $result;
    }

    /**
     * Preprocessor for no-action
     *
     * @return void
     */
    protected function doNoAction()
    {
        $driver = $this->getDriver();
        if ($driver) {
            foreach (\XLite\Core\EventListener::getInstance()->getEvents() as $name) {
                $object = $this;
                $listener = function (\AMQPMessage $message) use ($object, $name) {
                    return $object->handleMessage($message, $name);
                };
                $driver->consume($name, $listener);
            }

            do {
                $this->wait();
            } while ($this->checkCycle());
        }
    }

    /**
     * Check wait cycle 
     * 
     * @return boolean
     */
    protected function checkCycle()
    {
        return (bool)\XLite\Core\Request::getInstance()->permanent;
    }

    /**
     * Wait
     * 
     * @return void
     */
    protected function wait()
    {
        $this->getDriver()->wait();
        if (function_exists('pcntl_signal_dispatch')) {
            pcntl_signal_dispatch();
        }
    }

    /**
     * Get driver 
     * 
     * @return \XLite\Core\EventDriver\AMQP
     */
    protected function getDriver()
    {
        if (!isset($this->driver)) {
            $this->driver = \XLite\Core\EventDriver\AMQP::isValid() ? new \XLite\Core\EventDriver\AMQP : false;
        }

        return $this->driver;
    } 
}
