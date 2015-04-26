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

namespace XLite\Module\XC\News\Model\Repo;

/**
 * News messages repository
 */
class NewsMessage extends \XLite\Model\Repo\Base\I18n
{

    /**
     * Alternative record identifiers
     *
     * @var   array
     */
    protected $alternativeIdentifier = array(
        array('cleanURL'),
    );

    /**
     * Find product by clean URL
     *
     * @param string $url Clean URL
     *
     * @return \XLite\Module\XC\News\Model\NewsMessage
     */
    public function findOneByCleanURL($url)
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.cleanURL = :url')
            ->setParameter('url', $url)
            ->setMaxResults(1)
            ->getSingleResult();
    }

    // {{{ Search

    const SEARCH_NAME    = 'name';
    const SEARCH_ORDERBY = 'orderBy';
    const P_LIMIT        = 'limit';
    const SEARCH_ENABLED =  'enabled';

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
        $queryBuilder = $this->createQueryBuilder('n');
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
        $qb->select('COUNT(DISTINCT n.id)');

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
            $this->{'prepareCnd' . ucfirst($key)}($queryBuilder, $value, $countOnly);
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
            static::SEARCH_NAME,
            static::SEARCH_ORDERBY,
            static::P_LIMIT,
            static::SEARCH_ENABLED,
        );
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
    protected function prepareCndName(\Doctrine\ORM\QueryBuilder $queryBuilder, $value, $countOnly)
    {
        if ($value) {
            $queryBuilder->andWhere('translations.name = :name')
                ->setParameter('name', $value);
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
        call_user_func_array(array($queryBuilder, 'setFrameResults'), array_merge($value));
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
     * @param array|string               $value        Condition data
     * @param boolean                    $countOnly    "Count only" flag. Do not need to add "order by" clauses if only count is needed.
     *
     * @return void
     */
    protected function prepareCndEnabled(\Doctrine\ORM\QueryBuilder $queryBuilder, $value, $countOnly)
    {
        if ($value) {
            $queryBuilder->andWhere('n.enabled = :true AND n.date < :time')
                ->setParameter('true', true)
                ->setParameter('time', \XLite\Core\Converter::time());
        }
    }

   /**
    * Prepare next and previous news
    *
    * @param \XLite\Module\XC\News\Model\NewsMessage $model Model to prepare
    *
    * @return array
    */
    public function findSiblingsByNews(\XLite\Module\XC\News\Model\NewsMessage $model)
    {
        return array(
            $this->createQueryBuilder()->andWhere('n.date < :date')->setParameter('date', $model->getDate())->setMaxResults(1)->getSingleResult(),
            $this->createQueryBuilder()->andWhere('n.date > :date')->setParameter('date', $model->getDate())->setMaxResults(1)->getSingleResult()
        );
    }

}
