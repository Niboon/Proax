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

namespace XLite\Model\AttributeValue;

/**
 * Abstract multiple attribute value
 * @MappedSuperClass
 */
abstract class Multiple extends \XLite\Model\AttributeValue\AAttributeValue
{
    /**
     * Rate type codes
     */
    const TYPE_ABSOLUTE = 'a';
    const TYPE_PERCENT  = 'p';

   /**
     * Price modifier
     *
     * @var float
     *
     * @Column (type="decimal", precision=14, scale=4)
     */
    protected $priceModifier = 0.0000;

    /**
     * Price modifier type
     *
     * @var string
     *
     * @Column (type="string", options={ "fixed": true }, length=1)
     */
    protected $priceModifierType = self::TYPE_PERCENT;

   /**
     * Weight modifier
     *
     * @var float
     *
     * @Column (type="decimal", precision=14, scale=4)
     */
    protected $weightModifier = 0.0000;

    /**
     * Weight modifier type
     *
     * @var string
     *
     * @Column (type="string", options={ "fixed": true }, length=1)
     */
    protected $weightModifierType = self::TYPE_PERCENT;

    /**
     * Default flag
     *
     * @var boolean
     *
     * @Column (type="boolean")
     */
    protected $defaultValue = false;

    /**
     * Return modifiers
     *
     * @return array
     */
    static public function getModifiers()
    {
        return array(
            'price' => array(
                'title'  => 'Price',
                'symbol' => '$',
            ),
            'weight' => array(
                'title'  => 'Weight',
                'symbol' => 'w',
            ),
        );
    }

    /**
     * Format modifier
     *
     * @param float  $value Value
     * @param string $field Field
     *
     * @return string
     */
    static public function formatModifier($value, $field)
    {
        $method = 'formatModifier' . $field;

        $result = method_exists(__CLASS__, $method)
            ? static::$method($value)
            : $value;

        if (0 < $value) {
            $result = '+' . $result;
        }

        return $result;
    }

    /**
     * Format modifier price
     *
     * @param float $value Value
     *
     * @return string
     */
    static public function formatModifierPrice($value)
    {
        return \XLite\View\Price::getInstance()->formatPrice($value, null, true);
    }

    /**
     * Format modifier weight
     *
     * @param float $value Value
     *
     * @return string
     */
    static public function formatModifierWeight($value)
    {
        return $value . ' ' . \XLite\Core\Translation::translateWeightSymbol();
    }

    /**
     * Set modifier
     *
     * @param string $value Value
     * @param string $field Field
     *
     * @return void
     */
    public function setModifier($value, $field)
    {
        $method = 'set' . $field . 'Modifier';
        if (isset($value) && method_exists($this, $method)) {
            $result = $this->$method(doubleval($value));
            $method .= 'Type';
            if (method_exists($this, $method)) {
                $result = $this->$method(
                    strpos($value, '%')
                        ? static::TYPE_PERCENT
                        : static::TYPE_ABSOLUTE
                );
            }
        }
    }

    /**
     * Check is default or nor
     *
     * @return boolean
     */
    public function isDefault()
    {
        return $this->getId() == $this->getAttribute()->getDefaultAttributeValue($this->getProduct())->getId();
    }

    /**
     * Get surcharge absolute value
     *
     * @param string $field Field
     *
     * @return float
     */
    public function getAbsoluteValue($field)
    {
        $value = 0;
        if ($this->isApply()) {
            $modifier = $this->getModifierData($field);
            $value = $this->getModifierBase($field);
            $value = static::TYPE_PERCENT == $modifier['type']
                ? $value * $modifier['value'] / 100
                : $modifier['value'];
        }

        return round($value, 2);
    }

    /**
     * Get surcharge absolute value
     *
     * @param string $field Field
     *
     * @return string
     */
    public function getFormatedValue($field)
    {
        $modifier = $this->getModifierData($field);
        $value = $this->getModifierBase($field);
        $value = static::TYPE_PERCENT == $modifier['type']
            ? $value * $modifier['value'] / 100
            : $modifier['value'];
        $value = round($value, 2);

        return round($value, 2);
    }

    /**
     * Get modifier
     *
     * @param string $field Field
     *
     * @return mixed
     */
    public function getModifier($field)
    {
        $modifier = $this->getModifierData($field);

        $result = (float)$modifier['value'];
        if ($result) {
            if (0 < $result) {
                $result = '+' . $result;
            }
            if (static::TYPE_PERCENT == $modifier['type']) {
                $result .= '%';
            }

        } else {
            $result = '';
        }

        return $result;
    }

    /**
     * Check is apply or nor
     *
     * @return boolean
     */
    protected function isApply()
    {
        return true;
    }

    /**
     * Get modifier data
     *
     * @param string $field Field
     *
     * @return array
     */
    protected function getModifierData($field)
    {
        $result = array();

        $method = 'get' . $field . 'Modifier';
        $result['value'] = method_exists($this, $method)
            ? $this->$method()
            : 0;

        $method .= 'Type';
        $result['type'] = method_exists($this, $method)
            ? $this->$method()
            : static::TYPE_ABSOLUTE;

        return $result;
    }

    /**
     * Get modifier base value
     *
     * @param string  $field Field
     *
     * @return float
     */
    protected function getModifierBase($field)
    {
        $method = 'getModifierBase' . $field;

        return method_exists($this, $method)
            ? $this->{$method}()
            : 0;
    }

    /**
     * Get price-based modifier base value
     *
     * @return float
     */
    protected function getModifierBasePrice()
    {
        return $this->getProduct()->getPrice();
    }

    /**
     * Get weight-based modifier base value
     *
     * @return integer
     */
    protected function getModifierBaseWeight()
    {
        return $this->getProduct()->getWeight();
    }
}
