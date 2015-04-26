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

namespace XLite\Module\XC\SystemFields\View\ItemsList\Model;

/**
 * Product variants items list
 *
 * @LC_Dependencies("XC\ProductVariants")
 *
 */
class ProductVariant extends \XLite\Module\XC\ProductVariants\View\ItemsList\Model\ProductVariant
    implements \XLite\Base\IDecorator
{
    /**
     * Define columns structure
     *
     * @return array
     */
    protected function defineColumns()
    {
        $columns = parent::defineColumns();

        $step = 100;

        if (!empty($columns)) {
            $lastColumn = end($columns);

            if (isset($lastColumn['orderBy'])) {
                $step = $lastColumn['orderBy'] + 100;
            }
        }

        if (
            \XLite\Module\XC\SystemFields\Logic\Field::getInstance()
                ->isVisibleField(\XLite\Module\XC\SystemFields\Logic\Field::UPC_ISBN)
        ) {
            $columns['upcIsbn'] = array(
                static::COLUMN_NAME      => static::t('UPC/ISBN'),
                static::COLUMN_SUBHEADER => static::t('Default') . ': ' . $this->getProduct()->getUpcIsbn(),
                static::COLUMN_CLASS     => 'XLite\Module\XC\SystemFields\View\FormField\Inline\Input\Text\UpcIsbn',
                static::COLUMN_EDIT_ONLY => true,
                static::COLUMN_PARAMS    => array(
                    'attributes' => array(
                        'placeholder' => static::t('Default UPC/ISBN'),
                    )
                ),
                static::COLUMN_ORDERBY  => $step,
            );

            $step += 100;
        }

        if (
        \XLite\Module\XC\SystemFields\Logic\Field::getInstance()
            ->isVisibleField(\XLite\Module\XC\SystemFields\Logic\Field::MNF_VENDOR)
        ) {
            $columns['mnfVendor'] = array(
                static::COLUMN_NAME      => static::t('Mnf#/Vendor#'),
                static::COLUMN_SUBHEADER => static::t('Default') . ': ' . $this->getProduct()->getMnfVendor(),
                static::COLUMN_CLASS     => 'XLite\Module\XC\SystemFields\View\FormField\Inline\Input\Text\MnfVendor',
                static::COLUMN_EDIT_ONLY => true,
                static::COLUMN_PARAMS    => array(
                    'attributes' => array(
                        'placeholder' => static::t('Default Mnf/Vendor'),
                    )
                ),
                static::COLUMN_ORDERBY  => $step,
            );
        }

        return $columns;
    }
}
