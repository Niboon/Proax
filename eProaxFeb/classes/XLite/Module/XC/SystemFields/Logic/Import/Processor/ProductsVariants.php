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

namespace XLite\Module\XC\SystemFields\Logic\Import\Processor;

/**
 * Products
 *
 * @LC_Dependencies("XC\ProductVariants", "XC\SystemFields")
 */
abstract class ProductsVariants extends \XLite\Logic\Import\Processor\Products implements \XLite\Base\IDecorator
{
    /**
     * Get messages
     *
     * @return array
     */
    public static function getMessages()
    {
        return parent::getMessages()
        + array(
            'VARIANT-UPC-ISBN-FMT' => 'Wrong variant UPC/ISBN format',
            'VARIANT-MNF-VENDOR-FMT' => 'Wrong variant Mnf#/Vendor# format',
        );
    }

    /**
     * Define columns
     *
     * @return array
     */
    protected function defineColumns()
    {
        $columns = parent::defineColumns();

        $columns += array(
            'variant' . \XLite\Module\XC\SystemFields\Logic\Field::UPC_ISBN     => array(
                static::COLUMN_IS_MULTIROW => true
            ),
            'variant' . \XLite\Module\XC\SystemFields\Logic\Field::MNF_VENDOR   => array(
                static::COLUMN_IS_MULTIROW => true
            ),
        );

        return $columns;
    }

    /**
     * Verify 'variantUpcIsbn' value
     *
     * @param mixed $value  Value
     * @param array $column Column info
     *
     * @return void
     */
    protected function verifyVariantUpcIsbn($value, array $column)
    {
    }

    /**
     * Verify 'variantMnfVendor' value
     *
     * @param mixed $value  Value
     * @param array $column Column info
     *
     * @return void
     */
    protected function verifyVariantMnfVendor($value, array $column)
    {
    }

    /**
     * Import 'variantUpcIsbn' value
     *
     * @param \XLite\Model\Product $model  Product
     * @param mixed                $value  Value
     * @param array                $column Column info
     *
     * @return void
     */
    protected function importVariantUpcIsbnColumn(\XLite\Model\Product $model, $value, array $column)
    {
        foreach ($this->variants as $rowIndex => $variant) {
            $variant->setUpcIsbn(isset($value[$rowIndex]) ? $value[$rowIndex] : '');
        }
    }


    /**
     * Import 'variantMnfVendor' value
     *
     * @param \XLite\Model\Product $model  Product
     * @param mixed                $value  Value
     * @param array                $column Column info
     *
     * @return void
     */
    protected function importVariantMnfVendorColumn(\XLite\Model\Product $model, $value, array $column)
    {
        foreach ($this->variants as $rowIndex => $variant) {
            $variant->setMnfVendor(isset($value[$rowIndex]) ? $value[$rowIndex] : '');
        }
    }
}
