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

namespace XLite\Module\XC\ProductFilter\Model\QueryBuilder;

/**
 * Product query builder
 */
abstract class Product extends \XLite\Model\QueryBuilder\Product implements \XLite\Base\IDecorator
{
    /**
     * Assign attribute condition
     *
     * @param \XLite\Model\Attribute $attribute Attribute
     * @param mixed                  $value     Value
     *
     * @return void
     */
    public function assignAttributeCondition(\XLite\Model\Attribute $attribute, $value)
    {
        $result = null;

        $alias = 'av' . $attribute->getId();
        $getConditionFunc = 'getCondition'
            . $attribute->getTypes($attribute->getType(), true);

        $where = $this->{$getConditionFunc}($attribute, $value, $alias);

        if ($where) {
            if (is_array($where)) {
                foreach ($where as $w) {
                    $this->andWhere($w);
                }

            } else {
                $this->andWhere($where);
            }

            $attr = 'attribute' . $attribute->getId();
            $this->leftJoin(
                'p.attributeValue' . $attribute->getType(),
                $alias,
                'WITH',
                $alias . '.attribute = :' . $attr
            );
            $this->setParameter($attr, $attribute);

            if ($attribute::TYPE_SELECT == $attribute->getType()) {
                $this->leftJoin($alias . '.attribute_option', $alias . 'o');
            }
        }
    }

    // {{{ Attribute condition getters

    /**
     * Return condition for text
     *
     * @param \XLite\Model\Attribute $attribute Attribute
     * @param mixed                  $value     Condition data
     * @param string                 $alias     Alias
     *
     * @return string
     */
    protected function getConditionText(\XLite\Model\Attribute $attribute, $value, $alias)
    {
        return '';
    }

    /**
     * Return condition for select
     *
     * @param \XLite\Model\Attribute $attribute Attribute
     * @param mixed                  $value     Condition data
     * @param string                 $alias     Alias
     *
     * @return string
     */
    protected function getConditionSelect(\XLite\Model\Attribute $attribute, $value, $alias)
    {
        $where = '';
        if (
            $value
            && is_array($value)
        ) {
            foreach ($value as $k => $v) {
                if (!is_numeric($v)) {
                    unset($value[$k]);
                }
            }
            if ($value) {
                $where = $alias . 'o.id IN (' . implode(',', $value) . ')';
            }
        }

        return $where;
    }

    /**
     * Return condition for checkbox
     *
     * @param \XLite\Model\Attribute $attribute Attribute
     * @param mixed                  $value     Condition data
     * @param string                 $alias     Alias
     *
     * @return string
     */
    protected function getConditionCheckbox(\XLite\Model\Attribute $attribute, $value, $alias)
    {
        return $value ? $alias . '.value = true' : '';
    }

    // }}}
}
