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

namespace XLite\Logic\Order\Modifier;

/**
 * Abstract order modifier
 */
abstract class AModifier extends \XLite\Logic\ALogic
{

    /**
     * Mode codes
     */
    const MODE_CART  = 'cart';
    const MODE_ORDER = 'order';

    /**
     * Modifier type (see \XLite\Model\Base\Surcharge)
     *
     * @var string
     */
    protected $type;

    /**
     * Modifier unique code
     *
     * @var string
     */
    protected $code;

    /**
     * Model
     *
     * @var \XLite\Model\Order\Modifier
     */
    protected $model;

    /**
     * Order
     *
     * @var \XLite\Model\Order
     */
    protected $order;

    /**
     * Modifiers list
     *
     * @var \XLite\DataSet\Collection\OrderModifier
     */
    protected $list;

    /**
     * Surcharge identification pattern
     *
     * @var string
     */
    protected $identificationPattern;

    /**
     * Mode 
     * 
     * @var   string
     */
    protected $mode;

    /**
     * Calculate and return added surcharge or array of surcharges
     *
     * @return \XLite\Model\Order\Surcharge|array
     */
    abstract public function calculate();

    /**
     * Get surcharge information
     *
     * @param \XLite\Model\Base\Surcharge $surcharge Surcharge
     *
     * @return \XLite\DataSet\Transport\Order\Surcharge
     */
    abstract public function getSurchargeInfo(\XLite\Model\Base\Surcharge $surcharge);


    // {{{ Widget

    /**
     * Get widget class 
     * 
     * @return string
     */
    public static function getWidgetClass()
    {
        return '\XLite\View\Order\Details\Admin\Modifier';
    }

    // }}}

    /**
     * Constructor
     *
     * @param \XLite\Model\Order\Modifier $model Model
     *
     * @return void
     */
    public function __construct(\XLite\Model\Order\Modifier $model)
    {
        $this->model = $model;
    }

    /**
     * Initialize modifier
     *
     * @param \XLite\Model\Order                      $order Context
     * @param \XLite\DataSet\Collection\OrderModifier $list  Modifiers list
     *
     * @return void
     */
    public function initialize(\XLite\Model\Order $order, \XLite\DataSet\Collection\OrderModifier $list)
    {
        $this->order = $order;
        $this->list = $list;
    }

    /**
     * Preprocess internal state
     *
     * @return void
     */
    public function preprocess()
    {
    }

    /**
     * Check - can apply this modifier or not
     *
     * @return boolean
     */
    public function canApply()
    {
        return $this->type && $this->code && $this->order && $this->list && 0 < count($this->list) && $this->type;
    }

    /**
     * Get modifier type
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Get modifier unique code
     *
     * @return string
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * Get order
     *
     * @return \XLite\Model\Order
     */
    public function getOrder()
    {
        return $this->order;
    }

    /**
     * Set modifier mode 
     * 
     * @param string $mode Mode OPTIONAL
     *  
     * @return void
     */
    public function setMode($mode = null)
    {
        $this->mode = $mode;
    }

    /**
     * Get modifier mode 
     * 
     * @return string
     */
    public function getMode()
    {
        return $this->mode;
    }

    /**
     * Check - order is cart
     *
     * @return boolean
     */
    protected function isCart()
    {
        return $this->getMode() ?: $this->order instanceOf \XLite\Model\Cart;
    }

    // {{{ Surcharge operations

    /**
     * Check - modifier is specified surcharge owner or not
     *
     * @param \XLite\Model\Base\Surcharge $surcharge Surcharge
     *
     * @return boolean
     */
    public function isSurchargeOwner(\XLite\Model\Base\Surcharge $surcharge)
    {
        return ($this->identificationPattern && preg_match($this->identificationPattern, $surcharge->getCode()))
            || $surcharge->getCode() == $this->getCode();
    }

    /**
     * Add order surcharge
     *
     * @param string  $code      Surcharge code
     * @param float   $value     Value
     * @param boolean $include   Include flag OPTIONAL
     * @param boolean $available Availability flag OPTIONAL
     *
     * @return \XLite\Model\Order\Surcharge
     */
    public function addOrderSurcharge($code, $value, $include = false, $available = true)
    {
        $surcharge = new \XLite\Model\Order\Surcharge;

        $surcharge->setType($this->type);
        $surcharge->setCode($code);
        $surcharge->setValue($value);
        $surcharge->setInclude($include);
        $surcharge->setAvailable($available);
        $surcharge->setClass(get_called_class());

        $info = $this->getSurchargeInfo($surcharge);
        $surcharge->setName($info->name);

        $surcharge->setWeight(count($this->order->getSurcharges()));
        $this->order->getSurcharges()->add($surcharge);
        $surcharge->setOwner($this->order);

        return $surcharge;
    }

    /**
     * Add order item surcharge
     *
     * @param \XLite\Model\OrderItem $item      Order item
     * @param string                 $code      Surcharge code
     * @param float                  $value     Value
     * @param boolean                $include   Include flag OPTIONAL
     * @param boolean                $available Availability flag OPTIONAL
     *
     * @return \XLite\Model\OrderItem\Surcharge
     */
    protected function addOrderItemSurcharge(\XLite\Model\OrderItem $item, $code, $value, $include = false, $available = true)
    {
        $surcharge = new \XLite\Model\OrderItem\Surcharge;

        $surcharge->setType($this->type);
        $surcharge->setCode($code);
        $surcharge->setValue($value);
        $surcharge->setInclude($include);
        $surcharge->setAvailable($available);
        $surcharge->setClass(get_called_class());

        $info = $this->getSurchargeInfo($surcharge);
        $surcharge->setName($info->name);

        $item->addSurcharges($surcharge);
        $surcharge->setOwner($item);

        return $surcharge;
    }

    // }}}
}
