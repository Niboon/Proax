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

namespace XLite\Model\ListNode;

/**
 * Checkout step
 */
class CheckoutStep extends \XLite\Model\ListNode
{
    /**
     * Is checkout step passed or not
     *
     * @var boolean
     */
    protected $isPassed = false;

    /**
     * Name of the widget class for this checkout step
     *
     * @var string
     */
    protected $widgetClass = null;


    /**
     * __construct
     *
     * @param string  $key         Step mode
     * @param string  $widgetClass Step widget class name
     * @param boolean $isPassed    If step is passed or not
     *
     * @return void
     */
    public function __construct($key, $widgetClass, $isPassed)
    {
        parent::__construct($key);

        $this->isPassed    = $isPassed;
        $this->widgetClass = $widgetClass;
    }

    /**
     * isPassed
     *
     * @return boolean
     */
    public function isPassed()
    {
        return $this->isPassed;
    }

    /**
     * checkMode
     *
     * @param string $mode Current mode
     *
     * @return boolean
     */
    public function checkMode($mode)
    {
        return isset($mode) ? $this->checkKey($mode) : $this->isPassed();
    }

    /**
     * getWidgetClass
     *
     * @return string
     */
    public function getWidgetClass()
    {
        return $this->widgetClass;
    }

    /**
     * isRegularStep
     *
     * @return boolean
     */
    public function isRegularStep()
    {
        return call_user_func(array($this->getWidgetClass(), 'isRegularStep'));
    }

    /**
     * getMode
     *
     * @return string
     */
    public function getMode()
    {
        return $this->getKey();
    }

    /**
     * getTopMessage
     *
     * @return array
     */
    public function getTopMessage()
    {
        return \XLite\Model\Factory::create($this->getWidgetClass())->getTopMessage($this->isPassed());
    }
}
