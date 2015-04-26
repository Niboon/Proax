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
 * Country repository
 */
class Country extends \XLite\Model\Repo\Base\I18n
{
    /**
     * Allowable search params
     */
    const P_SUBSTRING  = 'substring' ;
    const P_HAS_STATES = 'hasStates' ;
    const P_ORDER_BY   = 'orderBy';
    const P_LIMIT      = 'limit';

    /**
     * Repository type
     *
     * @var string
     */
    protected $type = self::TYPE_SERVICE;

    /**
     * Alternative record identifiers
     *
     * @var array
     */
    protected $alternativeIdentifier = array(
        array('code'),
    );

    // {{{ defineCacheCells

    /**
     * Define cache cells
     *
     * @return array
     */
    protected function defineCacheCells()
    {
        $list = parent::defineCacheCells();

        $list['all'] = array(
            self::RELATION_CACHE_CELL => array(
                '\XLite\Model\State',
            ),
        );
        $list['enabled'] = array(
            self::RELATION_CACHE_CELL => array(
                '\XLite\Model\State',
            ),
        );
        $list['states'] = array(
            self::RELATION_CACHE_CELL => array(
                '\XLite\Model\State',
            ),
        );

        return $list;
    }

    // }}}

    // {{{ findAllEnabled

    /**
     * Find all enabled countries
     *
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function findAllEnabled()
    {
        $data = $this->getFromCache('enabled');
        if (!isset($data)) {
            $data = $this->defineAllEnabledQuery()->getResult();
            foreach ($data as $c) {
                $c->getCountry();
            }
            $this->saveToCache($data, 'enabled');
        }

        return $data;
    }

    /**
     * Define query builder for findAllEnabled()
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    protected function defineAllEnabledQuery()
    {
        return $this->createQueryBuilder()
            ->addSelect('s')
            ->leftJoin('c.states', 's')
            ->andWhere('c.enabled = :enable')
            ->addOrderBy('translations.country', 'ASC')
            ->setParameter('enable', true);
    }

    // }}}

    // {{{

    /**
     * Find all countries
     *
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function findAllCountries()
    {
        $data = $this->getFromCache('all');
        if (!isset($data)) {
            $data = $this->defineAllCountriesQuery()->getResult();
            foreach ($data as $c) {
                $c->getCountry();
            }
            $this->saveToCache($data, 'all');
        }

        return $data;
    }

    /**
     * Define query builder for findAllCountries()
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    protected function defineAllCountriesQuery()
    {
        return $this->createQueryBuilder()
            ->addSelect('s')
            ->addOrderBy('translations.country', 'ASC')
            ->leftJoin('c.states', 's');
    }

    // }}}

    // {{{ findCountriesStates

    /**
     * Get hash array (key - enabled country code, value - empty array)
     *
     * @return array
     */
    public function findCountriesStates()
    {
        $data = $this->getFromCache('states');

        if (!isset($data)) {

            $data = $this->defineCountriesStatesQuery()->getResult();
            $data = $this->postprocessCountriesStates($data);

            $this->saveToCache($data, 'states');
        }

        return $data;
    }

    /**
     * Define query builder for findCountriesStates()
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    protected function defineCountriesStatesQuery()
    {
        return $this->createQueryBuilder()
            ->addSelect('s')
            ->leftJoin('c.states', 's')
            ->where('c.enabled = :enabled')
            ->addOrderBy('s.state', 'ASC')
            ->setParameter('enabled', true);
    }

    /**
     * Postprocess enabled dump countries
     *
     * @param array $data Countries
     *
     * @return array
     */
    protected function postprocessCountriesStates(array $data)
    {
        $result = array();

        foreach ($data as $row) {
            if (0 < count($row->getStates())) {
                $code = $row->getCode();
                $result[$code] = array();

                foreach ($row->getStates() as $state) {
                    $result[$code][] = array(
                        'name' => $state->getState(),
                        'key'  => $state->getStateId()
                    );
                }
            }
        }

        return $result;
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
        $queryBuilder = $this->createQueryBuilder('c');

        foreach ($cnd as $key => $value) {
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
        $qb->select('COUNT(c.code)');

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
     *
     * @return void
     */
    protected function callSearchConditionHandler($value, $key, \Doctrine\ORM\QueryBuilder $queryBuilder, $countOnly)
    {
        if ($this->isSearchParamHasHandler($key)) {
            $this->{'prepareCnd' . ucfirst($key)}($queryBuilder, $value, $countOnly);

        } else {
            // TODO - add logging here
        }
    }

    /**
     * Return list of handling search params
     *
     * @return array
     */
    protected function getHandlingSearchParams()
    {
        return array(
            self::P_SUBSTRING,
            self::P_HAS_STATES,
            self::P_ORDER_BY,
            self::P_LIMIT,
        );
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
     * @param array                      $value        Condition data
     * @param boolean                    $countOnly    "Count only" flag. Do not need to add "order by" clauses if only count is needed.
     *
     * @return void
     */
    protected function prepareCndSubstring(\Doctrine\ORM\QueryBuilder $queryBuilder, $value, $countOnly)
    {
        $or = new \Doctrine\ORM\Query\Expr\Orx();

        $or->add('translations.country LIKE :pattern')
            ->add('c.code = :pattern2');

        return $queryBuilder->andWhere($or)
            ->setParameter('pattern', '%' . $value . '%')
            ->setParameter('pattern2', $value);
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
    protected function prepareCndHasStates(\Doctrine\ORM\QueryBuilder $queryBuilder, $value, $countOnly)
    {
        return $queryBuilder->innerJoin('c.states', 's')
            ->addOrderBy('translations.country', 'ASC');
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
        if (!$countOnly) {
            list($sort, $order) = $this->getSortOrderValue($value);
            $queryBuilder->addOrderBy($sort, $order);
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
