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

namespace XLite\Module\CDev\MarketPrice\Logic\Import\Processor;

/**
 * Products
 */
abstract class Products extends \XLite\Logic\Import\Processor\Products implements \XLite\Base\IDecorator
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
            'PRODUCT-MARKET-PRICE-FMT' => 'Wrong price format',
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

        $columns['marketPrice'] = array();

        return $columns;
    }

    /**
     * Normalize 'marketPrice' value
     *
     * @param mixed $value Value
     *
     * @return float
     */
    protected function normalizeMarketPriceValue($value)
    {
        return $this->normalizeValueAsFloat($value);
    }

    /**
     * Verify 'marketPrice' value
     *
     * @param mixed $value  Value
     * @param array $column Column info
     *
     * @return void
     */
    protected function verifyMarketPrice($value, array $column)
    {
        if (!$this->verifyValueAsEmpty($value) && !$this->verifyValueAsFloat($value)) {
            $this->addWarning('PRODUCT-MARKET-PRICE-FMT', array('column' => $column, 'value' => $value));
        }
    }

    /**
     * Import 'marketPrice' value
     *
     * @param \XLite\Model\Product $model  Product
     * @param string               $value  Value
     * @param array                $column Column info
     *
     * @return void
     */
    protected function importMarketPriceColumn(\XLite\Model\Product $model, $value, array $column)
    {
        if ($value) {
            $model->setMarketPrice(floatval($value));
        }
    }
}
