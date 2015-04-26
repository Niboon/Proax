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

namespace XLite\Module\CDev\Sale\Model\Repo;

/**
 * The Product model repository extension
 */
class Product extends \XLite\Model\Repo\Product implements \XLite\Base\IDecorator
{
    /**
     * Allowable search params
     */
    const P_PARTICIPATE_SALE = 'participateSale';

    /**
     * Name of the calculated field - percent value.
     */
    const PERCENT_CALCULATED_FIELD = 'percentValueCalculated';


    // {{{ Search functionallity extension

    /**
     * Add "participate sale" flag to the list of handling search params
     *
     * @return array
     */
    protected function getHandlingSearchParams()
    {
        $params = parent::getHandlingSearchParams();

        $params[] = self::P_PARTICIPATE_SALE;

        return $params;
    }

    /**
     * Prepare certain search condition
     *
     * @param \Doctrine\ORM\QueryBuilder $queryBuilder Query builder to prepare
     * @param array                      $value        Condition data
     * @param boolean                    $countOnly    Count only flag
     *
     * @return void
     */
    protected function prepareCndParticipateSale(\Doctrine\ORM\QueryBuilder $queryBuilder, $value, $countOnly)
    {
        $cnd = new \Doctrine\ORM\Query\Expr\Orx();

        $pricePercentCnd = new \Doctrine\ORM\Query\Expr\Andx();

        $pricePercentCnd->add('p.discountType = :discountTypePercent');
        $pricePercentCnd->add('p.salePriceValue > 0');

        $priceAbsoluteCnd = new \Doctrine\ORM\Query\Expr\Andx();

        $priceAbsoluteCnd->add('p.discountType = :discountTypePrice');
        $priceAbsoluteCnd->add('p.price > p.salePriceValue');

        $cnd->add($pricePercentCnd);
        $cnd->add($priceAbsoluteCnd);

        if (!$countOnly) {
            $queryBuilder->addSelect(
                'if(p.discountType = :discountTypePercent, p.salePriceValue, 100 - 100 * p.salePriceValue / p.price) ' . static::PERCENT_CALCULATED_FIELD
            );
        }

        $queryBuilder->andWhere('p.participateSale = :participateSale')
            ->andWhere($cnd)
            ->setParameter('participateSale', $value)
            ->setParameter('discountTypePercent', \XLite\Module\CDev\Sale\Model\Product::SALE_DISCOUNT_TYPE_PERCENT)
            ->setParameter('discountTypePercent', \XLite\Module\CDev\Sale\Model\Product::SALE_DISCOUNT_TYPE_PERCENT)
            ->setParameter('discountTypePrice', \XLite\Module\CDev\Sale\Model\Product::SALE_DISCOUNT_TYPE_PRICE);
    }
}
