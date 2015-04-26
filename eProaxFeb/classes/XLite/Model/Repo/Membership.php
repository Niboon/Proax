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
 * Membership repository
 */
class Membership extends \XLite\Model\Repo\Base\I18n
{
    /**
     * Allowable search params
     */
    const CND_LIMIT   = 'limit';

    /**
     * Repository type
     *
     * @var string
     */
    protected $type = self::TYPE_SECONDARY;

    /**
     * Default 'order by' field name
     *
     * @var string
     */
    protected $defaultOrderBy = 'position';

    // {{{ Search

    /**
     * Common search
     *
     * @param \XLite\Core\CommonCell $cnd       Search condition OPTIONAL
     * @param boolean                $countOnly Return items list or only its size OPTIONAL
     *
     * @return \Doctrine\ORM\PersistentCollection|integer
     */
    public function search(\XLite\Core\CommonCell $cnd = null, $countOnly = false)
    {
        $queryBuilder = $this->createQueryBuilder('m');
        $this->currentSearchCnd = $cnd;

        if ($this->currentSearchCnd) {
            foreach ($this->currentSearchCnd as $key => $value) {
                $this->callSearchConditionHandler($value, $key, $queryBuilder, $countOnly);
            }
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
        $qb->select('COUNT(DISTINCT m.membership_id)');

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
        return $qb->addOrderBy('m.position', 'ASC')->getResult();
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
            static::CND_LIMIT,
        );
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

    // {{{ defineCacheCells

    /**
     * Define cache cells
     *
     * @return array
     */
    protected function defineCacheCells()
    {
        $list = parent::defineCacheCells();

        $list['all'] = array();

        $list['enabled'] = array(
            self::ATTRS_CACHE_CELL => array('enabled'),
        );

        return $list;
    }

    // }}}

    // {{{ findAllMemberships

    /**
     * Find all languages
     *
     * @return array
     */
    public function findAllMemberships()
    {
        return $this->defineAllMembershipsQuery()->getResult();
    }

    /**
     * Define query builder for findAllMemberships()
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    protected function defineAllMembershipsQuery()
    {
        return $this->createQueryBuilder();
    }

    // }}}

    // {{{ findActiveMemberships

    /**
     * Find all enabled languages
     *
     * @return array
     */
    public function findActiveMemberships()
    {
        return $this->defineActiveMembershipsQuery()->getResult();
    }

    /**
     * Define query builder for findActiveMemberships()
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    protected function defineActiveMembershipsQuery()
    {
        return $this->createQueryBuilder()
            ->where('m.enabled = :true')
            ->setParameter('true', true);
    }

    // }}}

    // {{{ findOneByName

    /**
     * Find membership by name (any language)
     *
     * @param string  $name       Name
     * @param boolean $onlyActive Search only in enabled mebmerships OPTIONAL
     * @param boolean $countOnly  Count only OPTIONAL
     *
     * @return \XLite\Model\Membership|void
     */
    public function findOneByName($name, $onlyActive = true, $countOnly = false)
    {
        return $countOnly
            ? count($this->defineOneByNameQuery($name, $onlyActive)->getResult())
            : $this->defineOneByNameQuery($name, $onlyActive)->getSingleResult();
    }

    /**
     * Define query builder for findOneByName() method
     *
     * @param string  $name       Name
     * @param boolean $onlyActive Search only in enabled mebmerships
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    protected function defineOneByNameQuery($name, $onlyActive)
    {
        $qb = $this->createQueryBuilder()
            ->andWhere('translations.name = :name')
            ->setParameter('name', $name)
            ->setMaxResults(1);

        if ($onlyActive) {
            $qb->andWhere('m.enabled = :true');
            $qb->setParameter('true', true);
        }

        return $qb;
    }

    // }}}

    /**
     * Insert single entity
     *
     * @param \XLite\Model\AEntity|array $entity Data to insert OPTIONAL
     *
     * @return void
     */
    protected function performInsert($entity = null)
    {
        $entity = parent::performInsert($entity);

        if ($entity && !\XLite\Core\Database::getRepo('XLite\Model\Product')->getBlockQuickDataFlag()) {
            \XLite\Core\QuickData::getInstance()->updateMembershipData($entity);
        }

        return $entity;
    }
}
