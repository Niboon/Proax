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
 * Attributes repository
 */
class Attribute extends \XLite\Model\Repo\Base\I18n
{
    /**
     * Allowable search params
     */
    const SEARCH_PRODUCT          = 'product';
    const SEARCH_PRODUCT_CLASS    = 'productClass';
    const SEARCH_ATTRIBUTE_GROUP  = 'attributeGroup';
    const SEARCH_TYPE             = 'type';
    const SEARCH_NAME             = 'name';
    const SEARCH_LIMIT            = 'limit';

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
        $queryBuilder = $this->createQueryBuilder('a');
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
        $qb->select('COUNT(DISTINCT a.id)');

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
        return $qb->addOrderBy('a.position', 'ASC')->getResult();
    }

    /**
     * Find multiple attributes
     *
     * @param \XLite\Model\Product $product Product
     * @param array                $ids     Array of Ids
     *
     * @return array
     */
    public function findMultipleAttributes(\XLite\Model\Product $product, $ids)
    {
        return $ids
            ? $this->definefindMultipleAttributesQuery($product, $ids)->getResult()
            : array();
    }

    /**
     * Define query for findMultipleAttributes() method
     * 
     * @param \XLite\Model\Product $product Product
     * @param array                $ids     Attribute ID list
     *  
     * @return \XLite\Model\QueryBuilder\AQueryBuilder
     */
    protected function definefindMultipleAttributesQuery(\XLite\Model\Product $product, array $ids)
    {
        $qb = $this->createQueryBuilder('a');

        return $qb->leftJoin('a.attribute_properties', 'ap', 'WITH', 'ap.product = :product')
            ->addSelect('ap.position')
            ->andWhere('a.id IN (' . $qb->getInCondition($ids, 'arr') . ')')
            ->addGroupBy('a.id')
            ->setParameter('product', $product);
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
            static::SEARCH_PRODUCT,
            static::SEARCH_PRODUCT_CLASS,
            static::SEARCH_ATTRIBUTE_GROUP,
            static::SEARCH_TYPE,
            static::SEARCH_NAME,
            static::SEARCH_LIMIT,
        );
    }

    /**
     * Prepare certain search condition
     *
     * @param \Doctrine\ORM\QueryBuilder $queryBuilder Query builder to prepare
     * @param mixed                      $value        Condition OPTIONAL
     *
     * @return void
     */
    protected function prepareCndProduct(\Doctrine\ORM\QueryBuilder $queryBuilder, $value = null)
    {
        if ($value) {
            $queryBuilder->andWhere('a.product = :attributeProduct')
                ->setParameter('attributeProduct', $value);

        } else {
            $queryBuilder->andWhere('a.product is null');
        }
    }

    /**
     * Prepare certain search condition
     *
     * @param \Doctrine\ORM\QueryBuilder $queryBuilder Query builder to prepare
     * @param mixed                      $value        Condition OPTIONAL
     *
     * @return void
     */
    protected function prepareCndProductClass(\Doctrine\ORM\QueryBuilder $queryBuilder, $value = null)
    {
        if (is_null($value)) {
            $queryBuilder->andWhere('a.productClass is null');

        } elseif (is_object($value) && 'Doctrine\ORM\PersistentCollection' != get_class($value)) {
            $queryBuilder->andWhere('a.productClass = :productClass')
                ->setParameter('productClass', $value);

        } elseif ($value) {

            $ids = array();
            foreach ($value as $id) {
                if ($id) {
                    $ids[] = is_object($id) ? $id->getId() : $id;
                }
            }

            if ($ids) {
                $keys = \XLite\Core\Database::buildInCondition($queryBuilder, $ids, 'pcid');
                $queryBuilder->linkInner('a.productClass')
                    ->andWhere('productClass.id IN (' . implode(', ', $keys) . ')');
            }
        }
    }

    /**
     * Prepare certain search condition
     *
     * @param \Doctrine\ORM\QueryBuilder $queryBuilder Query builder to prepare
     * @param mixed                      $value        Condition OPTIONAL
     *
     * @return void
     */
    protected function prepareCndAttributeGroup(\Doctrine\ORM\QueryBuilder $queryBuilder, $value = null)
    {
        if ($value) {
            $queryBuilder->andWhere('a.attributeGroup = :attributeGroup')
                ->setParameter('attributeGroup', $value);

        } else {
            $queryBuilder->andWhere('a.attributeGroup is null');
        }
    }

    /**
     * Prepare certain search condition
     *
     * @param \Doctrine\ORM\QueryBuilder $queryBuilder Query builder to prepare
     * @param mixed                      $value        Condition OPTIONAL
     *
     * @return void
     */
    protected function prepareCndType(\Doctrine\ORM\QueryBuilder $queryBuilder, $value = null)
    {
        if ($value) {
            if (is_array($value)) {
                $queryBuilder->andWhere('a.type IN (\'' . implode("','", $value) . '\')');

            } else {
                $queryBuilder->andWhere('a.type = :type')
                    ->setParameter('type', $value);
            }
        }
    }

    /**
     * Prepare certain search condition
     *
     * @param \Doctrine\ORM\QueryBuilder $queryBuilder Query builder to prepare
     * @param mixed                      $value        Condition OPTIONAL
     *
     * @return void
     */
    protected function prepareCndName(\Doctrine\ORM\QueryBuilder $queryBuilder, $value = null)
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
        $queryBuilder->setFrameResults($value);
    }

    // }}}

    // {{{ Export routines

    /**
     * Define query builder for COUNT query
     *
     * @return \XLite\Model\QueryBuilder\AQueryBuilder
     */
    protected function defineCountForExportQuery()
    {
        $qb = $this->createPureQueryBuilder();

        return $qb->select(
            'COUNT(DISTINCT ' . $qb->getMainAlias() . '.' . $this->getPrimaryKeyField() . ')'
        );
    }

    /**
     * Define export iterator query builder
     *
     * @param integer $position Position
     *
     * @return \XLite\Model\QueryBuilder\AQueryBuilder
     */
    protected function defineExportIteratorQueryBuilder($position)
    {
        return $this->createPureQueryBuilder()
            ->setFirstResult($position)
            ->setMaxResults(1000000000);
    }

    // }}}

    /**
     * Generate attribute values
     *
     * @param \XLite\Model\Product $product         Product
     * @param boolean              $useProductClass Use product class OPTIONAL
     *
     * @return void
     */
    public function generateAttributeValues(\XLite\Model\Product $product, $useProductClass = null)
    {
        $cnd = new \XLite\Core\CommonCell;
        $cnd->productClass = $useProductClass ? $product->getProductClass() : null;
        $cnd->product = null;
        $cnd->type = array(
            \XLite\Model\Attribute::TYPE_CHECKBOX,
            \XLite\Model\Attribute::TYPE_SELECT,
        );
        foreach ($this->search($cnd) as $a) {
            $a->addToNewProduct($product);
        }
    }

    /**
     * Get identifiers list for specified query builder object
     *
     * @param \Doctrine\ORM\QueryBuilder $qb    Query builder
     * @param string                     $name  Name
     * @param mixed                      $value Value
     *
     * @return void
     */
    protected function addImportCondition(\Doctrine\ORM\QueryBuilder $qb, $name, $value)
    {
        if ('productClass' == $name && is_string($value)) {
            $alias = $qb->getMainAlias();
            $qb->linkInner($alias . '.productClass')
                ->linkInner('productClass.translations', 'productClassTranslations')
                ->andWhere('productClassTranslations.name = :productClass')
                ->setParameter('productClass', $value);

        } else {
            $result = parent::addImportCondition($qb, $name, $value);
        }
    }
}
