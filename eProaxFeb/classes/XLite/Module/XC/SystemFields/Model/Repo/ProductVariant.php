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

namespace XLite\Module\XC\SystemFields\Model\Repo;

/**
 * Product variants repository
 *
 * @LC_Dependencies("XC\ProductVariants")
 */
class ProductVariant extends \XLite\Module\XC\ProductVariants\Model\Repo\ProductVariant implements \XLite\Base\IDecorator
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
        $result = parent::getModifierTypesByProduct($product);

        $upcIsbn = $this->createQueryBuilder('v')
            ->andWhere('v.product = :product AND v.upcIsbn IS NOT NULL AND v.upcIsbn != :empty')
            ->setParameter('product', $product)
            ->setParameter('empty', '')
            ->setMaxResults(1)
            ->getResult();

        $mnfVendor = $this->createQueryBuilder('v')
            ->andWhere('v.product = :product AND v.mnfVendor IS NOT NULL AND v.mnfVendor != :empty')
            ->setParameter('product', $product)
            ->setParameter('empty', '')
            ->setMaxResults(1)
            ->getResult();

        return $result + array(
            'upcIsbn'   => !empty($upcIsbn),
            'mnfVendor' => !empty($mnfVendor),
        );
    }

}
