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
class State extends \XLite\Model\Repo\ARepo
{
    /**
     * Allowable search params
     */
    const P_ORDER_BY        = 'orderBy';
    const P_LIMIT           = 'limit';
    const P_SUBSTRING       = 'substring';
    const P_COUNTRY_CODE    = 'countryCode';

    /**
     * Repository type
     *
     * @var string
     */
    protected $type = self::TYPE_SERVICE;

    /**
     * Default 'order by' field name
     *
     * @var string
     */
    protected $defaultOrderBy = 'state';

    /**
     * Alternative record identifiers
     *
     * @var array
     */
    protected $alternativeIdentifier = array(
        array('code', 'country_code'),
    );

    /**
     * Get dump 'Other' state
     *
     * @param string $customState Custom state name OPTIONAL
     *
     * @return \XLite\Model\State
     */
    public function getOtherState($customState = null)
    {
        $state = new \XLite\Model\State();
        $state->state = isset($customState) ? $customState : 'Other';
        $state->state_id = -1;

        return $state;
    }

    /**
     * Check - is state id of dump 'Other' state or not
     *
     * @param integer $stateId State id
     *
     * @return boolean
     */
    public function isOtherStateId($stateId)
    {
        return -1 == $stateId;
    }

    /**
     * Get state code by state id
     *
     * @param integer $stateId State id
     *
     * @return string|void
     */
    public function getCodeById($stateId)
    {
        $result = $this->getFromCache('codes', array('state_id' => $stateId));

        if (!isset($result)) {
            $entity = $this->defineGetCodeByIdQuery($stateId)->getSingleResult();
            $result = $entity ? $entity->getCode() : '';

            $this->saveToCache($result, 'codes', array('state_id' => $stateId));
        }

        return $result;
    }

    /**
     * Find state by id (dump 'Other' state included)
     *
     * @param integer $stateId     State id
     * @param string  $customState Custom state name if state is dump 'Other' state OPTIONAL
     *
     * @return \XLite\Model\State
     */
    public function findById($stateId, $customState = '')
    {
        return $this->isOtherStateId($stateId)
            ? $this->getOtherState($customState)
            : $this->findOneByStateId($stateId);
    }

    /**
     * Find state by id
     *
     * @param integer $stateId State id
     *
     * @return \XLite\Model\State
     */
    public function findOneByStateId($stateId)
    {
        return $this->defineOneByStateIdQuery($stateId)->getSingleResult();
    }

    /**
     * Find all states
     *
     * @return array
     */
    public function findAllStates()
    {
        $data = $this->getFromCache('all');

        if (!isset($data)) {
            $data = $this->defineAllStatesQuery()->getResult();
            $this->saveToCache($data, 'all');
        }

        return $data;
    }

    /**
     * Find states by country code
     *
     * @param string $countryCode Country code
     *
     * @return \XLite\Model\State|void
     */
    public function findByCountryCode($countryCode)
    {
        $country = \XLite\Core\Database::getRepo('XLite\Model\Country')->find($countryCode);

        return $country ? $this->defineByCountryQuery($country)->getResult() : array();
    }

    /**
     * Find states by country code and state code
     *
     * @param string $countryCode Country code
     * @param string $code        State code
     *
     * @return \XLite\Model\State|void
     */
    public function findOneByCountryAndCode($countryCode, $code)
    {
        return $this->defineOneByCountryAndCodeQuery($countryCode, $code)->getSingleResult();
    }

    /**
     * Find one by record
     *
     * @param array                $data   Record
     * @param \XLite\Model\AEntity $parent Parent model OPTIONAL
     *
     * @return \XLite\Model\AEntity
     */
    public function findOneByRecord(array $data, \XLite\Model\AEntity $parent = null)
    {
        if (!empty($data['country_code']) && !empty($data['code'])) {
            $result = $this->findOneByCountryAndCode($data['country_code'], $data['code']);

        } elseif ($parent && $parent instanceOf \XLite\Model\Country) {
            $result = $this->findOneByCountryAndCode($parent->getCode(), $data['code']);

        } elseif (!empty($data['code']) && !empty($data['country']) && is_array($data['country']) && !empty($data['country']['code'])) {
            $result = $this->findOneByCountryAndCode($data['country']['code'], $data['code']);

        } else {
            $result = parent::findOneByRecord($data, $parent);
        }

        return $result;
    }

    /**
     * Define query builder for getCodeById() method
     *
     * @param integer $stateId State id
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    protected function defineGetCodeByIdQuery($stateId)
    {
        return $this->createQueryBuilder()
            ->where('s.state_id = :id')
            ->setMaxResults(1)
            ->setParameter('id', $stateId);
    }

    /**
     * Define query builder for findOneByStateId()
     *
     * @param integer $stateId State id
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    protected function defineOneByStateIdQuery($stateId)
    {
        return $this->createQueryBuilder()
            ->addSelect('c')
            ->leftJoin('s.country', 'c')
            ->andWhere('s.state_id = :id')
            ->setParameter('id', $stateId)
            ->setMaxResults(1);
    }

    /**
     * Define query builder for findAllStates()
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    protected function defineAllStatesQuery()
    {
        return $this->createQueryBuilder()
            ->addSelect('c')
            ->leftJoin('s.country', 'c');
    }

    /**
     * Define query for findByCountryCode() method
     *
     * @param \XLite\Model\Country $country Country
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    protected function defineByCountryQuery(\XLite\Model\Country $country)
    {
        return $this->createQueryBuilder()
            ->andWhere('s.country = :country')
            ->setParameter('country', $country);
    }

    /**
     * Define query for findOneByCountryAndCode() method
     *
     * @param string $countryCode Country code
     * @param string $code        State code
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    protected function defineOneByCountryAndCodeQuery($countryCode, $code)
    {
        return $this->createQueryBuilder()
            ->innerJoin('s.country', 'country')
            ->andWhere('country.code = :country AND s.code = :code')
            ->setParameter('country', $countryCode)
            ->setParameter('code', $code);
    }

    // {{{ Cache

    /**
     * Define cache cells
     *
     * @return array
     */
    protected function defineCacheCells()
    {
        $list = parent::defineCacheCells();

        $list['all'] = array(
            self::RELATION_CACHE_CELL => array('\XLite\Model\Country'),
        );

        $list['codes'] = array(
            self::ATTRS_CACHE_CELL => array('state_id'),
        );

        return $list;
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
        $queryBuilder = $this->createPureQueryBuilder();
        $this->currentSearchCnd = $cnd;

        foreach ($this->currentSearchCnd as $key => $value) {
            if ($key != self::P_LIMIT || !$countOnly) {
                $this->callSearchConditionHandler($value, $key, $queryBuilder);
            }
        }

        if ($countOnly) {
            // We remove all order-by clauses since it is not used for count-only mode
            $queryBuilder->select('COUNT(s.state_id)')->orderBy('s.state_id');
            $result = intval($queryBuilder->getSingleScalarResult());

        } else {
            $result = $queryBuilder->getOnlyEntities();
        }

        return $result;
    }

    /**
     * Return list of handling search params
     *
     * @return array
     */
    protected function getHandlingSearchParams()
    {
        return array(
            static::P_ORDER_BY,
            static::P_LIMIT,
            static::P_SUBSTRING,
            static::P_COUNTRY_CODE,
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
     * @param \XLite\Model\Profile       $value        Profile
     *
     * @return void
     */
    protected function prepareCndCountryCode(\Doctrine\ORM\QueryBuilder $queryBuilder, $value)
    {
        if (!empty($value)) {

            $queryBuilder->innerJoin('s.country', 'c')
                ->andWhere('c.code = :countryCode')
                ->setParameter('countryCode', $value);
        }
    }


    /**
     * Prepare certain search condition
     *
     * @param \Doctrine\ORM\QueryBuilder $queryBuilder Query builder to prepare
     * @param \XLite\Model\Profile       $value        Profile
     *
     * @return void
     */
    protected function prepareCndSubstring(\Doctrine\ORM\QueryBuilder $queryBuilder, $value)
    {
        if (!empty($value)) {

            $queryBuilder->andWhere('s.state LIKE :substring')
                ->setParameter('substring', '%' . $value . '%');
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
    protected function prepareCndOrderBy(\Doctrine\ORM\QueryBuilder $queryBuilder, array $value)
    {
        list($sort, $order) = $this->getSortOrderValue($value);

        if (!is_array($sort)) {
            $sort = array($sort);
            $order = array($order);
        }

        foreach ($sort as $key => $sortItem) {
            $queryBuilder->addOrderBy($sortItem, $order[$key]);
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

    // }}}
}
