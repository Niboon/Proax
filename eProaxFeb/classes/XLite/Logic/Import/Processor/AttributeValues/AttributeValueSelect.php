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

namespace XLite\Logic\Import\Processor\AttributeValues;

/**
 * Product attributes values import processor
 */
class AttributeValueSelect extends \XLite\Logic\Import\Processor\AttributeValues\AAttributeValue
{
    /**
     * Attribute type
     *
     * @var string
     */
    protected $attributeType = 'S';


    /**
     * Get title
     *
     * @return string
     */
    static public function getTitle()
    {
        return static::t('Product attributes values (Plain text) has been imported');
    }

    /**
     * Get repository
     *
     * @return \XLite\Model\Repo\ARepo
     */
    protected function getRepository()
    {
        return \XLite\Core\Database::getRepo('XLite\Model\AttributeValue\AttributeValueSelect');
    }

    /**
     * Get attribute value data
     *
     * @param array                  $data      Import row data
     * @param \XLite\Model\Attribute $attribute Attribute object
     *
     * @return array
     */
    protected function getAttributeValueData($data, $attribute)
    {
        $option = \XLite\Core\Database::getRepo('XLite\Model\AttributeOption')->findOneByNameAndAttribute($data['value'], $attribute);

        if (!$option) {
            $option = new \XLite\Model\AttributeOption();
            $option->setAttribute($attribute);
            $option->setName($data['value']);
            \XLite\Core\Database::getEM()->persist($option);
        }

        return array(
            'attribute_option' => $option,
        );
    }

    /**
     * Import 'priceModifier' value
     *
     * @param \XLite\Model\AttributeValue\AAttributeValue $model Attribute value object
     * @param mixed                                       $value  Value
     * @param array                                       $column Column info
     *
     * @return void
     */
    protected function importPriceModifierColumn($model, $value, array $column)
    {
        $model->setModifier($value, 'price');
    }

    /**
     * Import 'weightModifier' value
     *
     * @param \XLite\Model\AttributeValue\AAttributeValue $model Attribute value object
     * @param mixed                                       $value  Value
     * @param array                                       $column Column info
     *
     * @return void
     */
    protected function importWeightModifierColumn($model, $value, array $column)
    {
        $model->setModifier($value, 'weight');
    }
}
