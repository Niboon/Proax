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

namespace XLite\Module\CDev\Bestsellers\Model\Repo;

/**
 * The "OrderItem" model repository
 */
class Product extends \XLite\Model\Repo\Product implements \XLite\Base\IDecorator
{
    /**
     * Defines bestsellers products collection
     *
     * @param \XLite\Core\CommonCell $cnd   Search condition
     * @param integer                $count Number of products to get OPTIONAL
     * @param integer                $cat   Category identificator OPTIONAL
     *
     * @return array
     */
    public function findBestsellers(\XLite\Core\CommonCell $cnd, $count = 0, $cat = 0)
    {
        return $this->defineBestsellersQuery($cnd, $count, $cat)->getOnlyEntities();
    }

    /**
     * Prepare certain search condition
     *
     * @param \Doctrine\ORM\QueryBuilder $queryBuilder Query builder to prepare
     * @param array                      $value        Condition data
     * @param boolean                    $countOnly    "Count only" flag. Do not need to add "order by" clauses if only count is needed.
     *
     * @return void
     */
    protected function prepareCndOrderBy(\Doctrine\ORM\QueryBuilder $queryBuilder, array $value, $countOnly)
    {
        list($sort, $order) = $this->getSortOrderValue($value);
        if ('r.bought' == $sort) {
            if (!$countOnly) {
                $queryBuilder->linkLeft('p.order_items')
                    ->linkLeft('order_items.order')
                    ->linkLeft('order.paymentStatus')
                    ->andWhere('(paymentStatus.id IS NULL OR ' . $queryBuilder->expr()->in('paymentStatus.code', \XLite\Model\Order\Status\Payment::getPaidStatuses()) . ')')
                    ->addSelect('sum(order_items.amount) as product_amount')
                    ->addOrderBy('product_amount', $order);
            }

            $value = array('translations.name', $order);
        }

        parent::prepareCndOrderBy($queryBuilder, $value, $countOnly);
    }


    /**
     * Prepares query builder object to get bestsell products
     *
     * @param \XLite\Core\CommonCell $cnd   Search condition
     * @param integer                $count Number of products to get
     * @param integer                $cat   Category identificator
     *
     * @return \Doctrine\ORM\QueryBuilder Query builder object
     */
    protected function defineBestsellersQuery(\XLite\Core\CommonCell $cnd, $count, $cat)
    {
        list($sort, $order) = $cnd->{self::P_ORDER_BY};

        $qb = $this->createQueryBuilder()
            ->linkInner('p.order_items', 'o')
            ->linkInner('o.order', 'ord')
            ->linkInner('ord.paymentStatus', 'ps')
            ->addSelect('sum(o.amount) as product_amount');

        $qb->andWhere($qb->expr()->in('ps.code', \XLite\Model\Order\Status\Payment::getPaidStatuses()))
            ->groupBy('o.object')
            ->addOrderBy('product_amount', 'desc')
            ->addOrderBy($sort, $order);

        if (0 < $count) {
            $qb->setMaxResults($count);
        }

        if (0 < $cat) {
            $qb->linkLeft('p.categoryProducts', 'cp')
                ->linkLeft('cp.category', 'c');
            \XLite\Core\Database::getRepo('XLite\Model\Category')->addSubTreeCondition($qb, $cat);
        }

        return \XLite\Core\Database::getRepo('XLite\Model\Product')->assignExternalEnabledCondition($qb, 'p');
    }
}
