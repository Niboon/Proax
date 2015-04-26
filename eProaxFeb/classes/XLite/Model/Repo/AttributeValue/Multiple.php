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

namespace XLite\Model\Repo\AttributeValue;

/**
 * Multiple attribute values repository
 */
abstract class Multiple extends \XLite\Model\Repo\AttributeValue\AAttributeValue
{
    /**
     * Get modifier types by product 
     * 
     * @param \XLite\Model\Product $product Product
     *  
     * @return array
     */
    public function getModifierTypesByProduct(\XLite\Model\Product $product)
    {
        $price = $this->createQueryBuilder('av')
            ->select('a.id')
            ->innerJoin('av.attribute', 'a')
            ->andWhere('av.product = :product AND (a.productClass IS NULL OR a.productClass = :productClass) AND av.priceModifier != 0')
            ->setParameter('product', $product)
            ->setParameter('productClass', $product->getProductClass())
            ->addGroupBy('a.id')
            ->setMaxResults(1)
            ->getResult();

        $weight = $this->createQueryBuilder('av')
            ->select('a.id')
            ->innerJoin('av.attribute', 'a')
            ->andWhere('av.product = :product AND (a.productClass IS NULL OR a.productClass = :productClass) AND av.weightModifier != 0')
            ->setParameter('product', $product)
            ->setParameter('productClass', $product->getProductClass())
            ->addGroupBy('a.id')
            ->setMaxResults(1)
            ->getResult();

        return array(
            'price'  => !empty($price),
            'weight' => !empty($weight),
        );
    }

}
