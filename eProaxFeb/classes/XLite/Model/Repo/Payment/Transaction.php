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

namespace XLite\Model\Repo\Payment;

/**
 * Transaction repository 
 */
class Transaction extends \XLite\Model\Repo\ARepo
{

    const SEARCH_ORDER     = 'order';
    const SEARCH_PUBLIC_ID = 'public_id';
    const SEARCH_DATE      = 'date';
    const SEARCH_STATUS    = 'status';
    const SEARCH_VALUE     = 'value';
    const SEARCH_ORDERBY   = 'orderBy';
    const SEARCH_LIMIT     = 'limit';

    /**
     * Find transaction by data cell 
     * 
     * @param string $name  Name
     * @param string $value Value
     *  
     * @return \XLite\Model\Payment\Transaction
     */
    public function findOneByCell($name, $value)
    {
        return $this->defineFindOneByCellQuey($name, $value)->getSingleResult();
    }

    /**
     * Define query for findOneByCell() method
     * 
     * @param string $name  Name
     * @param string $value Value
     *  
     * @return \XLite\Model\QueryBuilder\AQueryBuilder
     */
    protected function defineFindOneByCellQuey($name, $value)
    {
        return $this->createQueryBuilder('p')
            ->linkInner('p.data')
            ->andWHere('data.name = :name AND data.value = :value')
            ->setParameter('name', $name)
            ->setParameter('value', $value)
            ->setMaxResults(1);
    }

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
        $queryBuilder = $this->createQueryBuilder('t')
            ->linkInner('t.order', 'ordr');
        $this->currentSearchCnd = $cnd;

        foreach ($this->currentSearchCnd as $key => $value) {
            $this->callSearchConditionHandler($value, $key, $queryBuilder, $countOnly);
        }

        return $countOnly
            ? $this->searchCount($queryBuilder)
            : $this->searchResult($queryBuilder);
    }

    /**
     * Search count only routine.
     *
     * @param \Doctrine\ORM\QueryBuilder $qb Query builder routine
     *
     * @return \Doctrine\ORM\PersistentCollection|integer
     */
    public function searchCount(\Doctrine\ORM\QueryBuilder $qb)
    {
        $qb->select('COUNT(DISTINCT t.transaction_id)');

        return intval($qb->getSingleScalarResult());
    }

    /**
     * Search result routine.
     *
     * @param \Doctrine\ORM\QueryBuilder $qb Query builder routine
     *
     * @return \Doctrine\ORM\PersistentCollection|integer
     */
    public function searchResult(\Doctrine\ORM\QueryBuilder $qb)
    {
        return $qb->getResult();
    }

    /**
     * Call corresponded method to handle a search condition
     *
     * @param mixed                      $value        Condition data
     * @param string                     $key          Condition name
     * @param \Doctrine\ORM\QueryBuilder $queryBuilder Query builder to prepare
     * @param boolean                    $countOnly    Count only flag
     *
     * @return void
     */
    protected function callSearchConditionHandler($value, $key, \Doctrine\ORM\QueryBuilder $queryBuilder, $countOnly)
    {
        if ($this->isSearchParamHasHandler($key)) {
            $this->{'prepareCnd' . \XLite\Core\Converter::convertToCamelCase($key)}($queryBuilder, $value, $countOnly);
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
     * Return list of handling search params
     *
     * @return array
     */
    protected function getHandlingSearchParams()
    {
        return array(
            static::SEARCH_ORDER,
            static::SEARCH_PUBLIC_ID,
            static::SEARCH_DATE,
            static::SEARCH_STATUS,
            static::SEARCH_VALUE,
            static::SEARCH_ORDERBY,
            static::SEARCH_LIMIT,
        );
    }

    /**
     * Prepare certain search condition
     *
     * @param \Doctrine\ORM\QueryBuilder $queryBuilder Query builder to prepare
     * @param mixed                      $value        Condition data
     * @param boolean                    $countOnly    "Count only" flag. Do not need to add "order by" clauses if only count is needed.
     *
     * @return void
     */
    protected function prepareCndOrder(\Doctrine\ORM\QueryBuilder $queryBuilder, $value, $countOnly)
    {
        if ($value) {
            if (is_object($value)) {
                $queryBuilder->andWhere('t.order = :order')
                    ->setParameter('order', $value);

            } else {
                $queryBuilder->andWhere('ordr.orderNumber = :orderNumber')
                    ->setParameter('orderNumber', $value);
            }
        }
    }

    /**
     * Prepare certain search condition
     *
     * @param \Doctrine\ORM\QueryBuilder $queryBuilder Query builder to prepare
     * @param array|string               $value        Condition data
     * @param boolean                    $countOnly    "Count only" flag. Do not need to add "order by" clauses if only count is needed.
     *
     * @return void
     */
    protected function prepareCndPublicId(\Doctrine\ORM\QueryBuilder $queryBuilder, $value, $countOnly)
    {
        if ($value) {
            $queryBuilder->andWhere('t.public_id LIKE :public_id')
                ->setParameter('public_id', '%' . $value . '%');
        }
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
    protected function prepareCndDate(\Doctrine\ORM\QueryBuilder $queryBuilder, $value, $countOnly)
    {
        if ($value) {
            $value = \XLite\View\FormField\Input\Text\DateRange::convertToArray($value);

            if (!empty($value[0])) {
                $queryBuilder->andWhere('t.date > :date0')
                    ->setParameter('date0', $value[0]);
            }

            if (!empty($value[1])) {
                $queryBuilder->andWhere('t.date < :date1')
                    ->setParameter('date1', $value[1]);
            }
        }
    }

    /**
     * Prepare certain search condition
     *
     * @param \Doctrine\ORM\QueryBuilder $queryBuilder Query builder to prepare
     * @param array|string               $value        Condition data
     * @param boolean                    $countOnly    "Count only" flag. Do not need to add "order by" clauses if only count is needed.
     *
     * @return void
     */
    protected function prepareCndStatus(\Doctrine\ORM\QueryBuilder $queryBuilder, $value, $countOnly)
    {
        if ($value) {
            if (is_array($value)) {
                $queryBuilder->andWhere($queryBuilder->expr()->in('t.status', $value));

            } else {
                $queryBuilder->andWhere('t.status = :status')
                    ->setParameter('status', $value);
            }
        }
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
    protected function prepareCndValue(\Doctrine\ORM\QueryBuilder $queryBuilder, $value, $countOnly)
    {
        if ($value && is_array($value)) {
            if (!empty($value[0])) {
                $queryBuilder->andWhere('t.value > :value0')
                    ->setParameter('value0', $value[0]);
            }

            if (!empty($value[1])) {
                $queryBuilder->andWhere('t.value < :value1')
                    ->setParameter('value1', $value[1]);
            }
        }
    }

    /**
     * Prepare certain search condition
     *
     * @param \Doctrine\ORM\QueryBuilder $queryBuilder Query builder to prepare
     * @param array|string               $value        Condition data
     * @param boolean                    $countOnly    "Count only" flag. Do not need to add "order by" clauses if only count is needed.
     *
     * @return void
     */
    protected function prepareCndOrderBy(\Doctrine\ORM\QueryBuilder $queryBuilder, $value, $countOnly)
    {
        if (!$countOnly) {
            if (is_string($value)) {
                list($sort, $order) = explode(' ', $value, 2);

            } else {
                list($sort, $order) = $value;
            }

            if ($sort) {
                $queryBuilder->addOrderBy($sort, $order);
            }
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

    // }}}

}
