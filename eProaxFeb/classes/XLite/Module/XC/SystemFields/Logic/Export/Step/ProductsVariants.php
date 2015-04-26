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
 * @copyright Copyright (c) 2011-2014 Qualiteam software Ltd <info@x-cart.com>. All rights reserved
 * @license   http://www.x-cart.com/license-agreement.html X-Cart 5 License Agreement
 * @link      http://www.x-cart.com/
 */

namespace XLite\Module\XC\SystemFields\Logic\Export\Step;

/**
 * Products
 *
 * @LC_Dependencies("XC\ProductVariants")
 */
abstract class ProductsVariants extends \XLite\Logic\Export\Step\Products implements \XLite\Base\IDecorator
{
    /**
     * Define columns
     *
     * @return array
     */
    protected function defineColumns()
    {
        $columns = parent::defineColumns();

        $columns += array(
            static::VARIANT_PREFIX . \XLite\Module\XC\SystemFields\Logic\Field::UPC_ISBN    => array(static::COLUMN_MULTIPLE => true),
            static::VARIANT_PREFIX . \XLite\Module\XC\SystemFields\Logic\Field::MNF_VENDOR  => array(static::COLUMN_MULTIPLE => true),
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

    /**
     * Get column value for 'variantUpcIsbn' column
     *
     * @param array   $dataset Dataset
     * @param string  $name    Column name
     * @param integer $i       Subcolumn index
     *
     * @return string
     */
    protected function getVariantUpcIsbnColumnValue(array $dataset, $name, $i)
    {
        return empty($dataset['variant'])
            ? ''
            : $this->getColumnValueByName($dataset['variant'], \XLite\Module\XC\SystemFields\Logic\Field::UPC_ISBN);
    }

    /**
     * Get column value for 'variantMnfVendor' column
     *
     * @param array   $dataset Dataset
     * @param string  $name    Column name
     * @param integer $i       Subcolumn index
     *
     * @return string
     */
    protected function getVariantMnfVendorColumnValue(array $dataset, $name, $i)
    {
        return empty($dataset['variant'])
            ? ''
            : $this->getColumnValueByName($dataset['variant'], \XLite\Module\XC\SystemFields\Logic\Field::MNF_VENDOR);
    }
}
