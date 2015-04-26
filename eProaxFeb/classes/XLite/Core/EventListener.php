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
 * Event listener (common) 
 */
class EventListener extends \XLite\Base\Singleton
{
    /**
     * Errors 
     * 
     * @var array
     */
    protected $errors = array();

    /**
     * Handle event
     * 
     * @param string $name      Event name
     * @param array  $arguments Event arguments OPTIONAL
     *  
     * @return boolean
     */
    public function handle($name, array $arguments = array())
    {
        $result = false;
        $this->errors = array();

        $list = $this->getListeners();

        if (isset($list[$name])) {
            $list = is_array($list[$name]) ? $list[$name] : array($list[$name]);
            foreach ($list as $class) {
                if ($class::handle($name, $arguments)) {
                    $result = true;
                }
                if ($class::getInstance()->getErrors()) {
                    $this->errors = $class::getInstance()->getErrors();
                }

            }
        }

        return $result;
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

    /**
     * Get events 
     * 
     * @return array
     */
    public function getEvents()
    {
        return array_keys($this->getListeners());
    }

    /**
     * Get listeners 
     * 
     * @return array
     */
    protected function getListeners()
    {
        return array(
            'probe'  => array('\XLite\Core\EventListener\Probe'),
            'export' => array('\XLite\Core\EventListener\Export'),
            'import' => array('\XLite\Core\EventListener\Import'),
            'imageResize' => array('\XLite\Core\EventListener\ImageResize'),
        );
    }
}

