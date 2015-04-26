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

namespace XLite\Core\EventListener;

/**
 * Abstract event listener 
 */
abstract class AEventListener extends \XLite\Base\Singleton
{
    /**
     * Errors 
     * 
     * @var array
     */
    protected $errors = array();

    /**
     * Arguments
     *
     * @var array
     */
    protected $arguments;

    /**
     * Handle event
     *
     * @param string $name      Event name
     * @param array  $arguments Event arguments OPTIONAL
     *
     * @return boolean
     */
    public static function handle($name, array $arguments = array())
    {
        return static::checkEvent($name, $arguments) ? static::getInstance()->handleEvent($name, $arguments) : false;
    }

    /**
     * Check event
     *
     * @param string $name      Event name
     * @param array  $arguments Event arguments OPTIONAL
     *
     * @return boolean
     */
    public static function checkEvent($name, array $arguments)
    {
        return true;
    }

    /**
     * Handle event (internal, after checking)
     *
     * @param string $name      Event name
     * @param array  $arguments Event arguments OPTIONAL
     *
     * @return boolean
     */
    public function handleEvent($name, array $arguments)
    {
        $this->arguments = $arguments;
    }

    /**
     * Get errors 
     * 
     * @return array
     */
    public function getErrors()
    {
        return $this->errors;
    }

}

