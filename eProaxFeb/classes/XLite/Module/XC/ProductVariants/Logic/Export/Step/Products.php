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

namespace XLite\Module\XC\ProductVariants\Logic\Export\Step;

/**
 * Products
 */
abstract class Products extends \XLite\Logic\Export\Step\Products implements \XLite\Base\IDecorator
{
    const VARIANT_PREFIX = 'variant';

    // {{{ Data

    /**
     * Get model datasets
     *
     * @param \XLite\Model\AEntity $model Model
     *
     * @return array
     */
    protected function getModelDatasets(\XLite\Model\AEntity $model)
    {
        return $this->distributeDatasetModel(
            parent::getModelDatasets($model),
            'variant',
            $model->getVariants()
        );
    }

    // }}}

    // {{{ Columns

    /**
     * Define columns
     *
     * @return array
     */
    protected function defineColumns()
    {
        $columns = parent::defineColumns();

        $columns += array(
            static::VARIANT_PREFIX . 'SKU'      => array(static::COLUMN_MULTIPLE => true),
            static::VARIANT_PREFIX . 'Price'    => array(static::COLUMN_MULTIPLE => true),
            static::VARIANT_PREFIX . 'Quantity' => array(static::COLUMN_MULTIPLE => true),
            static::VARIANT_PREFIX . 'Weight'   => array(static::COLUMN_MULTIPLE => true),
        );

        return $columns;
    }

    /**
     * Get attribute column data
     *
     * @param \XLite\Model\Attribute $attribute Attribute object
     *
     * @return array
     */
    protected function getAttributeColumn($attribute)
    {
        $column = parent::getAttributeColumn($attribute);

        $column[static::COLUMN_MULTIPLE] = true;

        return $column;
    }

    // }}}

    // {{{ Getters and formatters

    /**
     * Get column value for 'variantSKU' column
     *
     * @param array   $dataset Dataset
     * @param string  $name    Column name
     * @param integer $i       Subcolumn index
     *
     * @return string
     */
    protected function getVariantSKUColumnValue(array $dataset, $name, $i)
    {
        return empty($dataset['variant'])
            ? ''
            : $this->getColumnValueByName($dataset['variant'], 'sku');
    }

    /**
     * Get column value for 'variantPrice' column
     *
     * @param array   $dataset Dataset
     * @param string  $name    Column name
     * @param integer $i       Subcolumn index
     *
     * @return string
     */
    protected function getVariantPriceColumnValue(array $dataset, $name, $i)
    {
        return empty($dataset['variant']) || $this->getColumnValueByName($dataset['variant'], 'defaultPrice')
            ? ''
            : $this->getColumnValueByName($dataset['variant'], 'price');
    }

    /**
     * Get column value for 'variantQuantity' column
     *
     * @param array   $dataset Dataset
     * @param string  $name    Column name
     * @param integer $i       Subcolumn index
     *
     * @return string
     */
    protected function getVariantQuantityColumnValue(array $dataset, $name, $i)
    {
        return empty($dataset['variant']) || $this->getColumnValueByName($dataset['variant'], 'defaultAmount')
            ? ''
            : $this->getColumnValueByName($dataset['variant'], 'amount');
    }

    /**
     * Get column value for 'variantWeight' column
     *
     * @param array   $dataset Dataset
     * @param string  $name    Column name
     * @param integer $i       Subcolumn index
     *
     * @return string
     */
    protected function getVariantWeightColumnValue(array $dataset, $name, $i)
    {
        return empty($dataset['variant']) || $this->getColumnValueByName($dataset['variant'], 'defaultWeight')
            ? ''
            : $this->getColumnValueByName($dataset['variant'], 'weight');
    }

    /**
     * Get column value for abstract 'attribute' column
     *
     * @param array   $dataset Dataset
     * @param string  $name    Column name
     * @param integer $i       Subcolumn index
     *
     * @return string
     */
    protected function getAttributeColumnValue(array $dataset, $name, $i)
    {
        $columns = $this->getColumns();
        $column = $columns[$name];
        $attribute = isset($dataset['attribute']) && $dataset['attribute']
            ? $dataset['attribute']
            : \XLite\Core\Database::getRepo('XLite\Model\Attribute')->find($column['attributeId']);

        return isset($dataset['variant']) && $dataset['variant'] && $attribute->isVariable($dataset['model'])
            ? $dataset['variant']->getAttributeValue($attribute)->asString()
            : ($i ? '' : parent::getAttributeColumnValue($dataset, $name, $i));
    }

    // }}}
}
