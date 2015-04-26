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
 * Langauge labels repository
 */
class LanguageLabel extends \XLite\Model\Repo\Base\I18n
{
    /**
     * Allowable search params
     */
    const P_ORDER_BY        = 'orderBy';
    const P_LIMIT           = 'limit';
    const SEARCH_SUBSTRING  = 'substring';
    const SEARCH_CODES      = 'codes';
    const ORDER_FIRST_BY_IDS = 'orderFirstByIds';

    /**
     * Repository type
     *
     * @var string
     */
    protected $type = self::TYPE_SERVICE;

    /**
     * currentSearchCnd
     *
     * @var \XLite\Core\CommonCell
     */
    protected $currentSearchCnd = null;

    /**
     * Alternative record identifiers
     *
     * @var array
     */
    protected $alternativeIdentifier = array(
        array('name'),
    );

    /**
     * Language codes where to search translation
     *
     * @var array
     */
    protected $searchCodes = array();

    // {{{ Cache routines

    /**
     * Define cache cells
     *
     * @return array
     */
    protected function defineCacheCells()
    {
        $list = parent::defineCacheCells();
        $list['all_by_code'] = array();

        return $list;
    }

    // }}}

    // {{{ Search labels by code

    /**
     * Find labels by language code
     *
     * @param string  $code  Language code OPTIONAL
     * @param boolean $count Flag: return count results if true
     *
     * @return array
     */
    public function findLabelsByCode($code = null, $count = false)
    {
        if (!isset($code)) {
            $code = \XLite\Core\Session::getInstance()->getLanguage()->getCode();
        }

        $data = $this->getFromCache('all_by_code', array('code' => $code));

        if (!isset($data)) {
            $data = $this->defineLabelsByCodeQuery($code)->getResult();
            $data = $this->postprocessLabelsByCode($data, $code);
            $this->saveToCache($data, 'all_by_code', array('code' => $code));
        }

        return $count ? count($data) : $data;
    }

    /**
     * Define query builder for findLabelsByCode()
     *
     * @param string $code Language code OPTIONAL
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    protected function defineLabelsByCodeQuery($code)
    {
        return $this->createQueryBuilder();
    }

    /**
     * Postprocess for findLabelsByCode()
     *
     * @param array  $data Language labels
     * @param string $code Language code
     *
     * @return array
     */
    protected function postprocessLabelsByCode(array $data, $code)
    {
        $result = array();

        foreach ($data as $row) {
            $translation = $row->getLabelTranslation($code);

            if (isset($translation)) {
                $result[$row->getName()] = $translation->getLabel();
            }
        }

        ksort($result);

        return $result;
    }

    // }}}

    // {{{ Find labels translated to the specific language

    /**
     * Find labels by language code
     *
     * @param string  $code  Language code OPTIONAL
     * @param boolean $count Flag: return count results if true
     *
     * @return array
     */
    public function findLabelsTranslatedToCode($code)
    {
        $result = array();

        $qb = $this->createQueryBuilder();
        $data = $this->defineLabelsTranslatedToCodeQuery($qb, $code)->getResult();

        if ($data) {
            foreach ($data as $row) {
                $result[$row->getName()] = $row->getTranslation($code)->label;
            }
        }

        return $result;
    }

    /**
     * Define query for 'countByCode()' method
     *
     * @param \Doctrine\ORM\QueryBuilder $qb   Query builder
     * @param string                     $code Code
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    protected function defineLabelsTranslatedToCodeQuery($qb, $code)
    {
        return $qb->innerJoin('l.translations', 'lt')
            ->andWhere('lt.code = :code')
            ->setParameter('code', $code);
    }

    // }}}

    // {{{ countByName

    /**
     * Count labels by name
     *
     * @param string $name Name
     *
     * @return integer
     */
    public function countByName($name)
    {
        return $this->defineCountByNameQuery($name)->count();
    }

    /**
     * Define query for 'countByName()' method
     *
     * @param string $name Name
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    protected function defineCountByNameQuery($name)
    {
        return $this->defineCountQuery()
            ->andWhere('l.name LIKE :name')
            ->setParameter('name', '%' . $name . '%');
    }

    // }}}

    // {{{ countByCode

    /**
     * Count labels by code
     *
     * @param string $code Code
     *
     * @return integer
     */
    public function countByCode($code)
    {
        return $this->defineCountByCodeQuery($code)->count();
    }

    /**
     * Define query for 'countByCode()' method
     *
     * @param string $code Code
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    protected function defineCountByCodeQuery($code)
    {
        return $this->defineCountQuery()
            ->innerJoin('l.translations', 'lt')
            ->andWhere('lt.code = :code')
            ->setParameter('code', $code);
    }

    // }}}

    // {{{ findLikeName

    /**
     * Find lables by name pattern with data frame
     *
     * @param string  $name  Name pattern
     * @param integer $start Start offset OPTIONAL
     * @param integer $limit Frame length OPTIONAL
     *
     * @return array
     */
    public function findLikeName($name, $start = 0, $limit = 0)
    {
        return $this->defineLikeNameQuery($name, $start, $limit)->getResult();
    }

    /**
     * Define query for 'findLikeName()' method
     *
     * @param string  $name  Name
     * @param integer $start Start offset
     * @param integer $limit Frame length
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    protected function defineLikeNameQuery($name, $start, $limit)
    {
        return $this->createPureQueryBuilder()
            ->bindAndCondition('l.name', $name, 'LIKE')
            ->setFrameResults($start, $limit);
    }

    // }}}

    /**
     * Convert entity to parameters list for 'all_by_code' cache cell
     *
     * @param \XLite\Model\AEntity $entity Entity
     *
     * @return array
     */
    protected function convertRecordToParamsAllByCode(\XLite\Model\AEntity $entity)
    {
        return array('*');
    }

    // {{{

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

        if (!empty($cnd->{static::SEARCH_CODES})) {
            $this->searchCodes = $cnd->{static::SEARCH_CODES};
        }

        foreach ($this->currentSearchCnd as $key => $value) {
            if (!in_array($key, array(self::P_LIMIT, static::ORDER_FIRST_BY_IDS)) || !$countOnly) {
                $this->callSearchConditionHandler($value, $key, $queryBuilder);
            }
        }

        if ($countOnly) {
            // We remove all order-by clauses since it is not used for count-only mode
            $queryBuilder->select('COUNT(l.label_id)')->orderBy('l.label_id');
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
            static::SEARCH_SUBSTRING,
            static::ORDER_FIRST_BY_IDS,
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
    protected function prepareCndSubstring(\Doctrine\ORM\QueryBuilder $queryBuilder, $value)
    {
        if (!empty($value)) {

            $queryBuilder->leftJoin('l.translations', 'lt');

            $or = new \Doctrine\ORM\Query\Expr\Orx();

            // Use non-standard Doctrine function CAST(expr AS CHAR)
            $or->add('CastChar(l.name) LIKE :substring');

            $and = new \Doctrine\ORM\Query\Expr\Andx();
            $and->add('lt.label LIKE :substring');
            $and->add($queryBuilder->expr()->in('lt.code', $this->searchCodes));

            $or->add($and);

            $queryBuilder->andWhere($or)
                ->setParameter('substring', '%' . $value . '%');
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
    protected function prepareCndOrderFirstByIds(\Doctrine\ORM\QueryBuilder $queryBuilder, $value)
    {
        // Use non-standard Doctrine function FIN_IN_SET(needle, haystack)
        $queryBuilder->addSelect('FindInSet(l.label_id, :label_ids) new_index')
            ->setParameter('label_ids', implode(',', $value));

        $queryBuilder->addOrderBy('new_index', 'DESC');
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

    // {{{ Delete translations

    /**
     * Delete all translations of labels to the specific language
     *
     * @param string $code Language code
     *
     * @return boolean
     */
    public function deleteTranslations($code)
    {
        $qb = $this->createQueryBuilder();
        $data = $this->defineLabelsTranslatedToCodeQuery($qb, $code)->getResult();

        if ($data) {

            $toDelete = array();

            foreach ($data as $row) {
                $toDelete[] = $row->getTranslation($code);
            }

            if (!empty($toDelete)) {
                \XLite\Core\Database::getRepo('XLite\Model\LanguageLabel')->deleteInBatch($toDelete);
            }
        }

        return true;
    }
}
