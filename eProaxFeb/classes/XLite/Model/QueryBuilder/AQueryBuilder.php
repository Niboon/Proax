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

namespace XLite\Model\QueryBuilder;

/**
 * Abstract query builder
 */
abstract class AQueryBuilder extends \Doctrine\ORM\QueryBuilder implements \Countable
{

    /**
     * Linked joins
     *
     * @var array
     */
    protected $joins = array();

    /**
     * Data storage
     *
     * @var   array
     */
    protected $dataStorage = array();

    /**
     * Flag to define is inventory is already joined to prevent double joining (error)
     *
     * @var boolean
     */
    protected $hasJoinedInventory = false;

    /**
     * Join inventory procedure
     *
     * @return \XLite\Model\QueryBuilder\Product
     */
    public function innerJoinInventory()
    {
        if (!$this->hasJoinedInventory) {
            $this->innerJoin('p.inventory', 'i');
            $this->hasJoinedInventory = true;
        }

        return $this;
    }

    // {{{ Result helpers

    /**
     * Get result
     *
     * @return array
     */
    public function getResult()
    {
        return $this->getQuery()->getResult();
    }

    /**
     * Get result as object array.
     *
     * @return array
     */
    public function getObjectResult()
    {
        $result = array();

        foreach ($this->getResult() as $idx => $item) {
            $result[$idx] = is_object($item) ? $item : $item[0];
        }

        return $result;
    }

    /**
     * Get result as array
     *
     * @return array
     */
    public function getArrayResult()
    {
        return $this->getQuery()->getArrayResult();
    }

    /**
     * Get single result
     *
     * @return \XLite\Model\AEntity|void
     */
    public function getSingleResult()
    {
        try {
            $entity = $this->setMaxResults(1)->getQuery()->getSingleResult();

        } catch (\Doctrine\ORM\NonUniqueResultException $exception) {
            $entity = null;

        } catch (\Doctrine\ORM\NoResultException $exception) {
            $entity = null;
        }

        return $entity;
    }

    /**
     * Get single scalar result
     *
     * @return mixed
     */
    public function getSingleScalarResult()
    {
        try {
            $scalar = $this->setMaxResults(1)->getQuery()->getSingleScalarResult();

        } catch (\Doctrine\ORM\NonUniqueResultException $exception) {
            $scalar = null;

        } catch (\Doctrine\ORM\NoResultException $exception) {
            $scalar = null;
        }

        return $scalar;
    }

    /**
     * Get iterator
     *
     * @return \Iterator
     */
    public function iterate()
    {
        return $this->getQuery()->iterate();
    }

    /**
     * Execute query
     *
     * @return mixed
     */
    public function execute()
    {
        return $this->getQuery()->execute();
    }

    /**
     * Get only entities
     *
     * @return array
     */
    public function getOnlyEntities()
    {
        $result = array();

        foreach ($this->getResult() as $entity) {

            if (is_array($entity)) {
                $entity = $entity[0];
            }

            $result[] = $entity;
        }

        return $result;
    }

    /**
     * Get count
     *
     * @return integer
     */
    public function count()
    {
        return intval($this->selectCount()->getSingleScalarResult());
    }

    // }}}

    // {{{ Query builder helpers

    /**
     * Get Query builder main alias
     *
     * @return string
     */
    public function getMainAlias()
    {
        $from = $this->getDQLPart('from');
        $from = explode(' ', array_shift($from), 2);

        return isset($from[1]) ? $from[1] : $from[0];
    }


    /**
     * Link association as inner join
     *
     * @param string $join  The relationship to join
     * @param string $alias The alias of the join OPTIONAL
     *
     * @return \XLite\Model\QueryBuilder\AQueryBuilder
     */
    public function linkInner($join, $alias = null)
    {
        if (!$alias) {
            list($main, $alias) = explode('.', $join, 2);
        }

        if (!in_array($alias, $this->joins)) {
            $this->innerJoin($join, $alias);
            $this->joins[] = $alias;
        }

        return $this;
    }

    /**
     * Link association as left join
     *
     * @param string $join  The relationship to join
     * @param string $alias The alias of the join OPTIONAL
     *
     * @return \XLite\Model\QueryBuilder\AQueryBuilder
     */
    public function linkLeft($join, $alias = null)
    {
        if (!$alias) {
            list($main, $alias) = explode('.', $join, 2);
        }

        if (!in_array($alias, $this->joins)) {
            $this->leftJoin($join, $alias);
            $this->joins[] = $alias;
        }

        return $this;
    }

    /**
     * Get IN () condition
     *
     * @param mixed  $data   Data
     * @param string $prefix Parameter prefix OPTIONAL
     *
     * @return string
     */
    public function getInCondition($data, $prefix = 'id')
    {
        if (is_scalar($data)) {
            $data = array($data);

        } elseif (is_object($data)) {
            if ($data instanceOf \Iterator) {
                $tmp = array();
                foreach ($data as $value) {
                    $tmp[] = $value;
                }
                $data = $tmp;
            }
        }

        $keys = \XLite\Core\Database::buildInCondition($this, $data, $prefix);

        return implode(', ', $keys);
    }

    /**
     * Map 'AND' conditions list
     *
     * @param array  $conditions Conditions list (key => value list)
     * @param string $alias      Model alias OPTIONAL
     *
     * @return \XLite\Model\QueryBuilder\AQueryBuilder
     */
    public function mapAndConditions(array $conditions, $alias = null)
    {
        $alias = $alias ?: $this->getMainAlias();

        foreach ($conditions as $name => $value) {
            $parts = explode('.', $name, 2);
            if (!isset($parts[1])) {
                $name = $alias . '.' . $name;
            }
            $this->bindAndCondition($name, $value);
        }

        return $this;
    }

    /**
     * Select count
     *
     * @return \XLite\Model\QueryBuilder\AQueryBuilder
     */
    public function selectCount()
    {
        return $this->select('COUNT(' . $this->getMainAlias() . ')')
            ->setMaxResults(1);
    }

    /**
     * Set frame results
     *
     * @param integer|array $start Start position
     * @param integer       $limit Length
     *
     * @return \XLite\Model\QueryBuilder\AQueryBuilder
     */
    public function setFrameResults($start = 0, $limit = 0)
    {
        if (is_array($start)) {
            $limit = isset($start[1]) ? $start[1] : 0;
            $start = $start[0];
        }

        $start = max(0, intval($start));
        $limit = max(0, intval($limit));

        if (0 < $start) {
            $this->setFirstResult($start);
        }

        if (0 < $limit) {
            $this->setMaxResults($limit);
        }

        return $this;
    }

    /**
     * Bind AND condition
     *
     * @param string $name  Name
     * @param mixed  $value Value
     * @param string $type  Condition type OPTIONAL
     *
     * @return \XLite\Model\QueryBuilder\AQueryBuilder
     */
    public function bindAndCondition($name, $value, $type = '=')
    {
        $placeholder = str_replace('.', '_', $name);

        return $this->andWhere($name . ' ' . $type . ' :' . $placeholder)
            ->setParameter($placeholder, $value);
    }

    /**
     * Bind OR condition
     *
     * @param string $name  Name
     * @param mixed  $value Value
     * @param string $type  Condition type OPTIONAL
     *
     * @return \XLite\Model\QueryBuilder\AQueryBuilder
     */
    public function bindOrCondition($name, $value, $type = '=')
    {
        $placeholder = str_replace('.', '_', $name);

        return $this->orWhere($name . ' ' . $type . ' :' . $placeholder)
            ->setParameter($placeholder, $value);
    }

    /**
     * Bind macro date
     *
     * @param string  $field Field name
     * @param integer $start Start date OPTIONAL
     * @param integer $end   End adte OPTIONAL
     *
     * @return \XLite\Model\QueryBuilder\AQueryBuilder
     */
    public function bindMacroDate($field, $start = null, $end = null)
    {
        $suffix = str_replace('.', '_', $field);

        if (is_int($start)) {
            $this->andWhere($field . ' >= :startDate' . $suffix)
                ->setParameter('startDate' . $suffix, $start);
        }

        if (is_int($end)) {
            $this->andWhere($field . ' <= :endDate' . $suffix)
                ->setParameter('endDate' . $suffix, $end);
        }

        return $this;
    }

    // }}}

    // {{{ Data storage

    /**
     * Get data cell
     *
     * @param string $name Cell name
     *
     * @return mixed
     */
    public function getDataCell($name)
    {
        return isset($this->dataStorage[$name]) ? $this->dataStorage[$name] : null;
    }

    /**
     * Set data cell
     *
     * @param string $name  Cwll name
     * @param mixed  $value Value
     *
     * @return \XLite\Model\QueyBuilder\AQueryBuilder
     */
    public function setDataStorage($name, $value)
    {
        $this->dataStorage[$name] = $value;

        return $this;
    }

    // }}}

}
