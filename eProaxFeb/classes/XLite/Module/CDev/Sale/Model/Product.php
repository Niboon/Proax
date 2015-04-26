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

namespace XLite\Module\CDev\Sale\Model;

/**
 * Product
 *
 */
class Product extends \XLite\Model\Product implements \XLite\Base\IDecorator
{
    /**
     * The "Discount type" field is equal to this constant if it is "Sale price"
     */
    const SALE_DISCOUNT_TYPE_PRICE   = 'sale_price';

    /**
     * The "Discount type" field is equal to this constant if it is "Percent off"
     */
    const SALE_DISCOUNT_TYPE_PERCENT = 'sale_percent';

    /**
     * Flag, if the product participates in the sale
     *
     * @var boolean
     *
     * @Column (type="boolean")
     */
    protected $participateSale = false;

    /**
     * self::SALE_DISCOUNT_TYPE_PRICE   if "sale value" is considered as "Sale price",
     * self::SALE_DISCOUNT_TYPE_PERCENT if "sale value" is considered as "Percent Off".
     *
     * @var string
     *
     * @Column (type="string", length=32, nullable=false)
     */
    protected $discountType = self::SALE_DISCOUNT_TYPE_PRICE;

    /**
     * "Sale value"
     *
     * @var float
     *
     * @Column (type="decimal", precision=14, scale=4)
     */
    protected $salePriceValue = 0;

    /**
     * Get discount type
     *
     * @return string
     */
    public function getDiscountType()
    {
        return $this->discountType ?: self::SALE_DISCOUNT_TYPE_PRICE;
    }

    /**
     * Return old net product price (before sale)
     *
     * @return float
     */
    public function getNetPriceBeforeSale()
    {
        return \XLite\Module\CDev\Sale\Logic\PriceBeforeSale::getInstance()->apply($this, 'getClearPrice', array('taxable'), 'net');
    }

    /**
     * Return old display product price (before sale)
     *
     * @return float
     */
    public function getDisplayPriceBeforeSale()
    {
        return \XLite\Module\CDev\Sale\Logic\PriceBeforeSale::getInstance()->apply($this, 'getNetPriceBeforeSale', array('taxable'), 'display');
    }
}
