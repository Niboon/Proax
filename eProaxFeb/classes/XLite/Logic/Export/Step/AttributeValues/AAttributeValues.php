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

namespace XLite\Logic\Export\Step\AttributeValues;

/**
 * Products attribute values abstract class
 */
abstract class AAttributeValues extends \XLite\Logic\Export\Step\Base\I18n
{
    // {{{ Data

    /**
     * Get filename
     *
     * @return string
     */
    protected function getFilename()
    {
        return 'product-attributes.csv';
    }

    // }}}

    // {{{ Columns

    /**
     * Build header
     *
     * @return void
     */
    protected function buildHeader()
    {
        if (!$this->generator->getOptions()->isAttrHeaderBuilt) {
            parent::buildHeader();
            $this->generator->getOptions()->isAttrHeaderBuilt = true;
        }
    }

    /**
     * Define columns
     *
     * @return array
     */
    protected function defineColumns()
    {
        $columns = array(
            'productSKU'     => array(),
            'type'           => array(),
            'name'           => array(),
            'class'          => array(static::COLUMN_GETTER => 'getClassColumnValue'),
            'group'          => array(static::COLUMN_GETTER => 'getGroupColumnValue'),
            'owner'          => array(),
            'value'          => array(),
            'default'        => array(),
            'priceModifier'  => array(),
            'weightModifier' => array(),
            'editable'       => array(), // Specific field, used only for textarea attribute type
        );

        return $columns;
    }

    // }}}

    // {{{ Getters and formatters

    /**
     * Get column value for 'productSKU' column
     *
     * @param array   $dataset Dataset
     * @param string  $name    Column name
     * @param integer $i       Subcolumn index
     *
     * @return string
     */
    protected function getProductSKUColumnValue(array $dataset, $name, $i)
    {
        $product = $dataset['model']->getProduct();

        return $product ? $product->getSku() : '';
    }

    /**
     * Get column value for 'name' column
     *
     * @param array   $dataset Dataset
     * @param string  $name    Column name
     * @param integer $i       Subcolumn index
     *
     * @return string
     */
    protected function getNameColumnValue(array $dataset, $name, $i)
    {
        return $dataset['model']->getAttribute()->getName();
    }

    /**
     * Get column value for 'sku' column
     *
     * @param array   $dataset Dataset
     * @param string  $name    Column name
     * @param integer $i       Subcolumn index
     *
     * @return string
     */
    protected function getValueColumnValue(array $dataset, $name, $i)
    {
        return $dataset['model']->asString();
    }

    /**
     * Get column value for 'type' column
     *
     * @param array   $dataset Dataset
     * @param string  $name    Column name
     * @param integer $i       Subcolumn index
     *
     * @return string
     */
    protected function getTypeColumnValue(array $dataset, $name, $i)
    {
        return $dataset['model']->getAttribute()->getType();
    }

    /**
     * Get column value for 'class' column
     *
     * @param array   $dataset Dataset
     * @param string  $name    Column name
     * @param integer $i       Subcolumn index
     *
     * @return string
     */
    protected function getClassColumnValue(array $dataset, $name, $i)
    {
        $class = $dataset['model']->getAttribute()->getProductClass();

        return $class
            ? $class->getName()
            : '';
    }

    /**
     * Get column value for 'group' column
     *
     * @param array   $dataset Dataset
     * @param string  $name    Column name
     * @param integer $i       Subcolumn index
     *
     * @return string
     */
    protected function getGroupColumnValue(array $dataset, $name, $i)
    {
        $group = $dataset['model']->getAttribute()->getAttributeGroup();

        return $group
            ? $group->getName()
            : '';
    }

    /**
     * Get column value for 'priceModifier' column
     *
     * @param array   $dataset Dataset
     * @param string  $name    Column name
     * @param integer $i       Subcolumn index
     *
     * @return string
     */
    protected function getPriceModifierColumnValue(array $dataset, $name, $i)
    {
        return $dataset['model']->getModifier('price');
    }

    /**
     * Get column value for 'weightModifier' column
     *
     * @param array   $dataset Dataset
     * @param string  $name    Column name
     * @param integer $i       Subcolumn index
     *
     * @return string
     */
    protected function getWeightModifierColumnValue(array $dataset, $name, $i)
    {
        return $dataset['model']->getModifier('weight');
    }

    /**
     * Get column value for 'weightModifier' column
     *
     * @param array   $dataset Dataset
     * @param string  $name    Column name
     * @param integer $i       Subcolumn index
     *
     * @return string
     */
    protected function getDefaultColumnValue(array $dataset, $name, $i)
    {
        return $dataset['model']->getDefault() ? 'Yes' : 'No';
    }

    /**
     * Get column value for 'weightModifier' column
     *
     * @param array   $dataset Dataset
     * @param string  $name    Column name
     * @param integer $i       Subcolumn index
     *
     * @return string
     */
    protected function getOwnerColumnValue(array $dataset, $name, $i)
    {
        return $dataset['model']->getAttribute()->getProduct() ? 'Yes' : 'No';
    }

    /**
     * Get column value for 'editable' column
     *
     * @param array   $dataset Dataset
     * @param string  $name    Column name
     * @param integer $i       Subcolumn index
     *
     * @return string
     */
    protected function getEditableColumnValue(array $dataset, $name, $i)
    {
        return (
            \XLite\Model\Attribute::TYPE_TEXT == $dataset['model']->getAttribute()->getType()
            && $dataset['model']->getEditable()
        )
        ? 'Yes'
        : '';
    }

    // }}}
}
