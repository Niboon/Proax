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

namespace XLite\Module\XC\ProductVariants\Logic\Import\Processor;

/**
 * Products
 */
abstract class Products extends \XLite\Logic\Import\Processor\Products implements \XLite\Base\IDecorator
{
    const VARIANT_PREFIX = 'variant';

    /**
     * Product variants
     *
     * @var array
     */
    protected $variants = array();

    /**
     * Product variants attributes
     *
     * @var array
     */
    protected $variantsAttributes = array();

    /**
     * Define columns
     *
     * @return array
     */
    protected function defineColumns()
    {
        $columns = parent::defineColumns();

        $columns += array(
            static::VARIANT_PREFIX . 'SKU'      => array(
                static::COLUMN_IS_MULTIROW => true
            ),
            static::VARIANT_PREFIX . 'Price'    => array(
                static::COLUMN_IS_MULTIROW => true
            ),
            static::VARIANT_PREFIX . 'Quantity' => array(
                static::COLUMN_IS_MULTIROW => true
            ),
            static::VARIANT_PREFIX . 'Weight'   => array(
                static::COLUMN_IS_MULTIROW => true
            ),
        );

        return $columns;
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
                'VARIANT-PRICE-FMT'       => 'Wrong variant price format',
                'VARIANT-QUANTITY-FMT'    => 'Wrong variant quantity format',
                'VARIANT-PRODUCT-SKU-FMT' => 'SKU is already assigned to variant',
                'VARIANT-WEIGHT-FMT'      => 'Wrong variant weight format',
            );
    }

    /**
     * Verify 'SKU' value
     *
     * @param mixed $value  Value
     * @param array $column Column info
     *
     * @return void
     */
    protected function verifySku($value, array $column)
    {
        parent::verifySku($value, $column);

        if (!$this->verifyValueAsEmpty($value)) {

            $entity = \XLite\Core\Database::getRepo('XLite\Module\XC\ProductVariants\Model\ProductVariant')
                ->findOneBySku($value);

            if ($entity) {
                $this->addError('VARIANT-PRODUCT-SKU-FMT', array('column' => $column, 'value' => $value));
            }
        }
    }

    /**
     * Verify 'variantSKU' value
     *
     * @param mixed $value  Value
     * @param array $column Column info
     *
     * @return void
     */
    protected function verifyVariantSKU($value, array $column)
    {
    }

    /**
     * Verify 'variantPrice' value
     *
     * @param mixed $value  Value
     * @param array $column Column info
     *
     * @return void
     */
    protected function verifyVariantPrice($value, array $column)
    {
        if (!$this->verifyValueAsEmpty($value)) {
            foreach ($value as $val) {
                if (!$this->verifyValueAsFloat($val)) {
                    $this->addWarning('VARIANT-PRICE-FMT', array('column' => $column, 'value' => $val));
                }
            }
        }
    }

    /**
     * Verify 'variantQuantity' value
     *
     * @param mixed $value  Value
     * @param array $column Column info
     *
     * @return void
     */
    protected function verifyVariantQuantity($value, array $column)
    {
        if (!$this->verifyValueAsEmpty($value)) {
            foreach ($value as $val) {
                if (!$this->verifyValueAsFloat($val)) {
                    $this->addWarning('VARIANT-QUANTITY-FMT', array('column' => $column, 'value' => $val));
                }
            }
        }
    }

    /**
     * Verify 'variantWeight' value
     *
     * @param mixed $value  Value
     * @param array $column Column info
     *
     * @return void
     */
    protected function verifyVariantWeight($value, array $column)
    {
        if (!$this->verifyValueAsEmpty($value)) {
            foreach ($value as $val) {
                if (!$this->verifyValueAsFloat($val)) {
                    $this->addWarning('VARIANT-WEIGHT-FMT', array('column' => $column, 'value' => $val));
                }
            }
        }
    }

    // }}}

    // {{{ Import

    /**
     * Import data
     *
     * @param array $data Row set Data
     *
     * @return boolean
     */
    protected function importData(array $data)
    {
        $this->variants = $this->variantsAttributes = array();

        return parent::importData($data);
    }

    /**
     * Import 'attributes' value
     *
     * @param \XLite\Model\Product $model  Product
     * @param array                $value  Value
     * @param array                $column Column info
     *
     * @return void
     */
    protected function importAttributesColumn(\XLite\Model\Product $model, array $value, array $column)
    {
        parent::importAttributesColumn($model, $value, $column);

        if ($this->multAttributes) {
            \XLite\Core\Database::getEM()->flush();

            $variantsAttributes = array();
            foreach ($this->multAttributes as $id => $values) {
                foreach ($values as $k => $v) {
                    if (1 == count($v)) {
                        $variantsAttributes[$k][$id] = array_shift($v);

                    } else {
                        unset($this->multAttributes[$id]);
                        break;
                    }
                }
            }

            if ($variantsAttributes) {
                $tmp = array();
                foreach ($variantsAttributes as $k => $v) {
                    $tmp[$k] = implode('::', $v);
                }
                if (count($tmp) == count($variantsAttributes)) {
                    foreach ($variantsAttributes as $rowIndex => $values) {
                        foreach ($values as $id => $value) {
                            if (!isset($this->variantsAttributes[$id])) {
                                $this->variantsAttributes[$id] = \XLite\Core\Database::getRepo('XLite\Model\Attribute')->find($id);
                            }
                            $attribute = $this->variantsAttributes[$id];

                            $repo = \XLite\Core\Database::getRepo($attribute->getAttributeValueClass($attribute->getType()));
                            if ($attribute::TYPE_CHECKBOX == $attribute->getType()) {
                                $values[$id] = $repo->findOneBy(
                                    array(
                                        'attribute' => $attribute,
                                        'product'   => $model,
                                        'value'     => $this->normalizeValueAsBoolean($value),
                                    )
                                );

                            } else {
                                $attributeOption = \XLite\Core\Database::getRepo('XLite\Model\AttributeOption')
                                   ->findOneByNameAndAttribute($value, $attribute);
                                $values[$id] = $repo->findOneBy(
                                    array(
                                        'attribute'        => $attribute,
                                        'product'          => $model,
                                        'attribute_option' => $attributeOption,
                                    )
                                );
                            }

                        }

                        $variant = $model->getVariantByAttributeValues($values);

                        if (!$variant) {
                            $variant = new \XLite\Module\XC\ProductVariants\Model\ProductVariant();
                            foreach ($values as $attributeValue) {
                                $method = 'addAttributeValue' . $attributeValue->getAttribute()->getType();
                                $variant->$method($attributeValue);
                                $attributeValue->addVariants($variant);
                            }
                            $variant->setProduct($model);
                            $model->addVariants($variant);
                            \XLite\Core\Database::getEM()->persist($variant);
                        }

                        $this->variants[$rowIndex] = $variant;
                    }
                }

                foreach ($model->getVariantsAttributes() as $va) {
                    $model->getVariantsAttributes()->removeElement($va);
                    $va->getVariantsProducts()->removeElement($model);
                }

                foreach ($this->variantsAttributes as $va) {
                    $model->addVariantsAttributes($va);
                    $va->addVariantsProducts($model);
                }

            }
        }
    }

    /**
     * Import 'variantSKU' value
     *
     * @param \XLite\Model\Product $model  Product
     * @param mixed                $value  Value
     * @param array                $column Column info
     *
     * @return void
     */
    protected function importVariantSKUColumn(\XLite\Model\Product $model, $value, array $column)
    {
        foreach ($this->variants as $rowIndex => $variant) {
            $variant->setSku(isset($value[$rowIndex]) ? $value[$rowIndex] : '');
        }
    }

    /**
     * Import 'variantPrice' value
     *
     * @param \XLite\Model\Product $model  Product
     * @param mixed                $value  Value
     * @param array                $column Column info
     *
     * @return void
     */
    protected function importVariantPriceColumn(\XLite\Model\Product $model, $value, array $column)
    {
        foreach ($this->variants as $rowIndex => $variant) {
            $variant->setPrice($this->normalizeValueAsFloat(isset($value[$rowIndex]) ? $value[$rowIndex] : 0));
            $variant->setDefaultPrice(!isset($value[$rowIndex]));
        }
    }

    /**
     * Import 'variantQuantity' value
     *
     * @param \XLite\Model\Product $model  Product
     * @param mixed                $value  Value
     * @param array                $column Column info
     *
     * @return void
     */
    protected function importVariantQuantityColumn(\XLite\Model\Product $model, $value, array $column)
    {
        foreach ($this->variants as $rowIndex => $variant) {
            $variant->setAmount($this->normalizeValueAsUinteger(isset($value[$rowIndex]) ? $value[$rowIndex] : 0));
            $variant->setDefaultAmount(!isset($value[$rowIndex]));
        }
    }

    /**
     * Import 'variantWeight' value
     *
     * @param \XLite\Model\Product $model  Product
     * @param mixed                $value  Value
     * @param array                $column Column info
     *
     * @return void
     */
    protected function importVariantWeightColumn(\XLite\Model\Product $model, $value, array $column)
    {
        foreach ($this->variants as $rowIndex => $variant) {
            $variant->setWeight($this->normalizeValueAsFloat(isset($value[$rowIndex]) ? $value[$rowIndex] : 0));
            $variant->setDefaultWeight(!isset($value[$rowIndex]));
        }
    }

    // }}}
}
