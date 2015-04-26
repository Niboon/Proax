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

namespace XLite\Module\CDev\ProductAdvisor\Model\Repo;

/**
 * The ProductStats model repository
 */
class ProductStats extends \XLite\Model\Repo\ARepo
{
    // {{{ findStats

    /**
     * Find statistics data for specified arrays of viewed product IDs and ordered product IDs
     * Returns result in format array('A-B', 'C-D', ...) where A,C - viewed product ID, B,D - ordered product ID
     * 
     * @param array $viewedProductIds  Viewed product IDs
     * @param array $orderedProductIds Ordered product IDs
     *  
     * @return array
     */
    public function findStats($viewedProductIds, $orderedProductIds)
    {
        return $this->defineFindStatsQuery($viewedProductIds, $orderedProductIds)->getResult();
    }


    /**
     * Prepare query builder
     *
     * @param array $viewedProductIds  Viewed product IDs
     * @param array $orderedProductIds Ordered product IDs
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    protected function defineFindStatsQuery($viewedProductIds, $orderedProductIds)
    {
        $qb = $this->createQueryBuilder('ps');

        if (1 < count($viewedProductIds)) {
            $qb->innerJoin(
                'ps.viewed_product',
                'vp',
                'WITH',
                'vp.product_id IN (' . implode(',', $viewedProductIds) . ')'
            );

        } else {
            $qb->innerJoin('ps.viewed_product', 'vp', 'WITH', 'vp.product_id = :viewedProductId')
                ->setParameter('viewedProductId', array_pop($viewedProductIds));
        }

        if (1 < count($orderedProductIds)) {
            $qb->innerJoin(
                'ps.bought_product',
                'bp',
                'WITH',
                'bp.product_id IN (' . implode(',', $orderedProductIds) . ')'
            );

        } else {
            $qb->innerJoin('ps.bought_product', 'bp', 'WITH', 'bp.product_id = :orderedProductId')
                ->setParameter('orderedProductId', array_pop($orderedProductIds));
        }

        return $qb;
    }

    // }}}

    // {{{ updateStats

    /**
     * Updates statistics for specified arrays of viewed product IDs and ordered product IDs
     * 
     * @param array $data Statistics records gathered from database for updating
     *  
     * @return void
     */
    public function updateStats($data)
    {
        foreach ($data as $row) {
            $row->setCount($row->getCount() + 1);
            \XLite\Core\Database::getEM()->persist($row);
        }

        \XLite\Core\Database::getEM()->flush();
    }

    // }}}
}
