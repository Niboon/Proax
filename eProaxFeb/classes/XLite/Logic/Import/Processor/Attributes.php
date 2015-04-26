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

namespace XLite\Logic\Import\Processor;

/**
 * Attributes import processor
 */
class Attributes extends \XLite\Logic\Import\Processor\AProcessor
{
    /**
     * Get title
     *
     * @return string
     */
    static public function getTitle()
    {
        return static::t('Attributes imported');
    }

    /**
     * Get repository
     *
     * @return \XLite\Model\Repo\ARepo
     */
    protected function getRepository()
    {
        return \XLite\Core\Database::getRepo('XLite\Model\Attribute');
    }

    /**
     * Assemble maodel conditions
     *
     * @param array $data Data
     *
     * @return array
     */
    protected function assembleModelConditions(array $data)
    {
        $conditions = parent::assembleModelConditions($data);

        if (
            !empty($conditions['productClass'])
            && is_object($conditions['productClass'])
            && !$conditions['productClass']->isPersistent()
        ) {
            $conditions['productClass'] = $conditions['productClass']->getName();
        }

        return $conditions;
    }

    // {{{ Columns

    /**
     * Define columns
     *
     * @return array
     */
    protected function defineColumns()
    {
        return array(
            'position'        => array(),
            'product'         => array(
                static::COLUMN_IS_KEY          => true,
            ),
            'name'            => array(
                static::COLUMN_IS_KEY          => true,
                static::COLUMN_IS_MULTILINGUAL => true,
            ),
            'class'    => array(
                static::COLUMN_IS_KEY          => true,
                static::COLUMN_IS_MULTILINGUAL => true,
                static::COLUMN_PROPERTY        => 'productClass',
            ),
            'group'  => array(
                static::COLUMN_IS_KEY          => true,
                static::COLUMN_IS_MULTILINGUAL => true,
                static::COLUMN_PROPERTY        => 'attributeGroup',
            ),
            'options'         => array(
                static::COLUMN_IS_MULTILINGUAL => true,
                static::COLUMN_IS_MULTIPLE     => true,
            ),
            'type'            => array(),
        );
    }

    // }}}

    // {{{ Verification

    /**
     * Get messages
     *
     * @return array
     */
    public static function getMessages()
    {
        return parent::getMessages()
            + array(
                'ATTR-POSITION-FMT' => 'Wrong position format',
                'ATTR-GROUP-FMT'    => 'The "{{value}}" group is not created',
                'ATTR-TYPE-FMT'     => 'Wrong type format',
                'ATTR-NAME-FMT'     => 'The name is empty',
            );
    }

    /**
     * Get error texts
     *
     * @return array
     */
    public static function getErrorTexts()
    {
        return parent::getErrorTexts()
            + array(
                'ATTR-GROUP-FMT'    => 'New group will be created',
            );
    }

    /**
     * Verify 'position' value
     *
     * @param mixed $value  Value
     * @param array $column Column info
     *
     * @return void
     */
    protected function verifyPosition($value, array $column)
    {
        if (!$this->verifyValueAsEmpty($value) && !$this->verifyValueAsUinteger($value)) {
            $this->addWarning('ATTR-POSITION-FMT', array('column' => $column, 'value' => $value));
        }
    }

    /**
     * Verify 'type' value
     *
     * @param mixed $value  Value
     * @param array $column Column info
     *
     * @return void
     */
    protected function verifyType($value, array $column)
    {
        if ($this->verifyValueAsEmpty($value) || !\XLite\Model\Attribute::getTypes($value)) {
            $this->addError('ATTR-TYPE-FMT', array('column' => $column, 'value' => $value));
        }
    }

    /**
     * Verify 'group' value
     *
     * @param mixed $value  Value
     * @param array $column Column info
     *
     * @return void
     */
    protected function verifyGroup($value, array $column)
    {
        $value = $this->getDefLangValue($value);
        if (
            !$this->verifyValueAsEmpty($value)
            && 0 == \XLite\Core\Database::getRepo('XLite\Model\AttributeGroup')->findOneByName($value, true)
        ) {
            $this->addWarning('ATTR-GROUP-FMT', array('column' => $column, 'value' => $value));
        }
    }

    /**
     * Verify 'group' value
     *
     * @param mixed $value  Value
     * @param array $column Column info
     *
     * @return void
     */
    protected function verifyName($value, array $column)
    {
        $value = $this->getDefLangValue($value);
        if ($this->verifyValueAsEmpty($value)) {
            $this->addError('ATTR-NAME-FMT', array('column' => $column, 'value' => $value));
        }
    }

    /**
     * Verify 'product class' value
     *
     * @param mixed $value  Value
     * @param array $column Column info
     *
     * @return void
     */
    protected function verifyClass($value, array $column)
    {
        $value = $this->getDefLangValue($value);
        if (!$this->verifyValueAsEmpty($value) && !$this->verifyValueAsProductClass($value)) {
            $this->addWarning('GLOBAL-PRODUCT-CLASS-FMT', array('column' => $column, 'value' => $value));
        }
    }

    /**
     * Verify 'product' value
     *
     * @param mixed $value  Value
     * @param array $column Column info
     *
     * @return void
     */
    protected function verifyProduct($value, array $column)
    {
        if (!$this->verifyValueAsEmpty($value) && !$this->verifyValueAsProduct($value)) {
            $this->addWarning('GLOBAL-PRODUCT-FMT', array('column' => $column, 'value' => $value));
        }
    }

    /**
     * Verify 'options' value
     *
     * @param mixed $value  Value
     * @param array $column Column info
     *
     * @return void
     */
    protected function verifyOptions($value, array $column)
    {
    }

    // }}}

    // {{{ Normalizators

    /**
     * Normalize 'position' value
     *
     * @param mixed @value Value
     *
     * @return integer
     */
    protected function normalizePositionValue($value)
    {
        return abs(intval($value));
    }

    /**
     * Normalize 'class' value
     *
     * @param mixed @value Value
     *
     * @return \XLite\Model\ProductClass
     */
    protected function normalizeClassValue($value)
    {
        return $this->normalizeValueAsProductClass($value);
    }

    /**
     * Normalize 'group' value
     *
     * @param mixed @value Value
     *
     * @return \XLite\Model\AttributeGroup
     */
    protected function normalizeGroupValue($value)
    {
        if (!\XLite\Core\Converter::isEmptyString($this->currentRowData['class'])) {
            $className = $this->getDefLangValue($this->currentRowData['class']);
            $productClass = \XLite\Core\Database::getRepo('XLite\Model\ProductClass')->findOneByName($className);

        } else {
            $productClass = null;
        }

        return $this->normalizeValueAsAttributeGroup($value, $productClass);
    }

    /**
     * Normalize 'product' value
     *
     * @param mixed @value Value
     *
     * @return \XLite\Model\ProductClass
     */
    protected function normalizeProductValue($value)
    {
        return $this->normalizeValueAsProduct($value);
    }

    // }}}

    // {{{ Import

    /**
     * Import 'options' value
     *
     * @param \XLite\Model\Attribute $model  Attribute
     * @param array                  $value  Value
     * @param array                  $column Column info
     *
     * @return void
     */
    protected function importOptionsColumn(\XLite\Model\Attribute $model, array $value, array $column)
    {
        if ($value) {
            foreach ($value as $index => $val) {
                $option = $model->getAttributeOptions()->get($index);
                if (!$option) {
                    $option = new \XLite\Model\AttributeOption();
                    $option->setAttribute($model);
                    $model->getAttributeOptions()->add($option);

                    \XLite\Core\Database::getEM()->persist($option);
                }
                $this->updateModelTranslations($option, $val);
            }

            while (count($model->getAttributeOptions()) > count($value)) {
                $option = $model->getAttributeOptions()->last();
                \XLite\Core\Database::getRepo('\XLite\Model\AttributeOption')->delete($option, false);
                $model->getAttributeOptions()->removeElement($option);
            }
        }
    }

    /**
     * Import 'group' value
     *
     * @param \XLite\Model\Attribute $model  Attribute
     * @param string                 $value  Value
     * @param array                  $column Column info
     *
     * @return void
     */
    protected function importGroupColumn(\XLite\Model\Attribute $model, $value, array $column)
    {
        if ($value) {
            $group = $this->normalizeGroupValue($value);
            $this->updateModelTranslations($group, $value);
            $group->setProductClass($model->getProductClass());
            $model->setAttributeGroup($group);
        }
    }

    // }}}
}
