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

namespace XLite\Model\Repo;

/**
 * The "order_item" model repository
 */
class OrderItem extends \XLite\Model\Repo\ARepo
{
    /**
     * Allowable search params
     */
    const P_ORDER = 'order';
    const P_LIMIT = 'limit';

    // {{{ Functions to grab top selling products data

    /**
     * Get top sellers depending on certain condition
     *
     * @param \XLite\Core\CommonCell $cnd       Conditions
     * @param boolean                $countOnly Count only flag OPTIONAL
     *
     * @return \Doctrine\ORM\PersistentCollection|integer
     */
    public function getTopSellers(\XLite\Core\CommonCell $cnd, $countOnly = false)
    {
        $result = $this->prepareTopSellersCondition($cnd)->getResult();

        return $countOnly ? count($result) : $result;
    }

    /**
     * Returns the top sellers count (used on the dashboard)
     *
     * @param integer $currencyId Currency Id
     *
     * @return integer
     */
    public function getTopSellersCount($currencyId)
    {
        return $this->getTopSellers($this->prepareTopSellersCountCnd($currencyId), true);
    }

    /**
     * Has top sellers
     *
     * @param integer $currencyId Currency Id OPTIONAL
     *
     * @return boolean
     */
    public function hasTopSellers($currencyId = null)
    {
        $cnd = $this->prepareTopSellersCountCnd($currencyId);
        $cnd->limit = 1;

        return 0 < $this->getTopSellers($cnd, true);
    }

    /**
     * Prepare the top sellers count condition
     *
     * @param integer $currencyId Currency Id OPTIONAL
     *
     * @return \XLite\Core\CommonCell
     */
    protected function prepareTopSellersCountCnd($currencyId = null)
    {
        $cnd = new \XLite\Core\CommonCell();

        if (isset($currencyId)) {
            $cnd->currency = $currencyId;
        }

        return $cnd;
    }

    /**
     * Prepare top sellers search condition
     *
     * @param \XLite\Core\CommonCell $cnd Conditions
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    protected function prepareTopSellersCondition(\XLite\Core\CommonCell $cnd)
    {
        list($start, $end) = $cnd->date;

        $qb = $this->createQueryBuilder();

        $qb->addSelect('SUM(o.amount) as cnt')
            ->innerJoin('o.order', 'o1')
            ->innerJoin('o1.paymentStatus', 'ps')
            ->addSelect('o1.date')
            ->andWhere($qb->expr()->in('ps.code', \XLite\Model\Order\Status\Payment::getPaidStatuses()))
            ->setMaxResults($cnd->limit)
            ->addGroupBy('o.sku')
            ->addOrderBy('cnt', 'desc')
            ->addOrderBy('o.name', 'asc');

        if ($cnd->currency) {
            $qb->innerJoin('o1.currency', 'currency', 'WITH', 'currency.currency_id = :currency_id')
                ->setParameter('currency_id', $cnd->currency);
        }

        if (0 < $start) {
            $qb->andWhere('o1.date >= :start')
                ->setParameter('start', $start);
        }

        if (0 < $end) {
            $qb->andWhere('o1.date < :end')
                ->setParameter('end', $end);
        }

        return $qb;
    }

    // }}}

    // {{{ Search

    /**
     * Common search
     *
     * @param \XLite\Core\CommonCell $cnd       Search condition
     * @param boolean                $countOnly Return items list or only its size OPTIONAL
     *
     * @return \Doctrine\ORM\PersistentCollection|integer
     */
    public function search(\XLite\Core\CommonCell $cnd, $countOnly = false)
    {
        $queryBuilder = $this->createQueryBuilder();
        $this->currentSearchCnd = $cnd;

        foreach ($this->currentSearchCnd as $key => $value) {
            if (self::P_LIMIT != $key || !$countOnly) {
                $this->callSearchConditionHandler($value, $key, $queryBuilder);
            }
        }

        $result = $queryBuilder->getOnlyEntities();

        return $countOnly ? count($result) : $result;
    }

    /**
     * Return list of handling search params
     *
     * @return array
     */
    protected function getHandlingSearchParams()
    {
        return array(
            static::P_ORDER,
            static::P_LIMIT,
        );
    }

    /**
     * Call corresponded method to handle a serch condition
     *
     * @param mixed                      $value        Condition data
     * @param string                     $key          Condition name
     * @param \Doctrine\ORM\QueryBuilder $queryBuilder Query builder to prepare
     *
     * @return void
     */
    protected function callSearchConditionHandler($value, $key, \Doctrine\ORM\QueryBuilder $queryBuilder)
    {
        if ($this->isSearchParamHasHandler($key)) {
            $methodName = 'prepareCnd' . ucfirst($key);
            // $methodName is assembled from 'prepareCnd' + key
            $this->$methodName($queryBuilder, $value);

        } else {
            // TODO - add logging here
        }
    }

    /**
     * Check if param can be used for search
     *
     * @param string $param Name of param to check
     *
     * @return boolean
     */
    protected function isSearchParamHasHandler($param)
    {
        return in_array($param, $this->getHandlingSearchParams());
    }

    /**
     * Prepare certain search condition
     *
     * @param \Doctrine\ORM\QueryBuilder $queryBuilder Query builder to prepare
     * @param integer                    $value        Condition data
     *
     * @return void
     */
    protected function prepareCndOrder(\Doctrine\ORM\QueryBuilder $queryBuilder, $value)
    {
        if (!empty($value)) {
            $queryBuilder->andWhere('o.order = :order')
                ->setParameter('order', $value);
        }
    }

    /**
     * Prepare certain search condition
     *
     * @param \Doctrine\ORM\QueryBuilder $queryBuilder Query builder to prepare
     * @param array                      $value        Condition data
     *
     * @return void
     */
    protected function prepareCndLimit(\Doctrine\ORM\QueryBuilder $queryBuilder, array $value)
    {
        $queryBuilder->setFrameResults($value);
    }
}
