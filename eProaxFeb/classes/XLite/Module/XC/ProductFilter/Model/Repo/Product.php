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

namespace XLite\Module\XC\ProductFilter\Model\Repo;

/**
 * The "product" model repository
 *
 */
abstract class Product extends \XLite\Model\Repo\Product implements \XLite\Base\IDecorator
{
    /**
     * Allowable search params
     */
    const P_ATTRIBUTE = 'attribute';
    const P_FILTER    = 'filter';
    const P_IN_STOCK  = 'inStock';

    /**
     * Find filtered product classes
     *
     * @param \XLite\Core\CommonCell $cnd Search condition
     *
     * @return array
     */
    public function findFilteredProductClasses(\XLite\Core\CommonCell $cnd)
    {
        $result = array();

        $data = $this->createQueryBuilderCnd($cnd)
            ->innerJoin('p.productClass', 'class')
            ->innerJoin('class.attributes', 'attr')
            ->andWhere('attr.type IN (\'' . implode('\',\'', \XLite\Model\Attribute::getFilteredTypes()) . '\')')
            ->andWhere('p.productClass is not null')
            ->addSelect('COUNT(DISTINCT p.product_id)')
            ->GroupBy('p.productClass')
            ->getSingleResult();

        if (
            $data
            && $data[1] == $this->search($cnd, true)
        ) {
            $result[] = $data[0]->getProductClass();
        }

        return $result;
    }

    /**
     * Find min price
     *
     * @param \XLite\Core\CommonCell $cnd Search condition
     *
     * @return float
     */
    public function findMinPrice(\XLite\Core\CommonCell $cnd)
    {
        $queryBuilder = $this->createQueryBuilderCnd($cnd);
        $field = \XLite::isAdminZone()
            ? 'p.price'
            : $this->getCalculatedField($queryBuilder, 'price');

        return $queryBuilder->select('MIN(' . $field . ')')
            ->orderBy('p.product_id')
            ->setMaxResults(1)
            ->getSingleScalarResult();
    }

    /**
     * Find max price
     *
     * @param \XLite\Core\CommonCell $cnd Search condition
     *
     * @return float
     */
    public function findMaxPrice(\XLite\Core\CommonCell $cnd)
    {
        $queryBuilder = $this->createQueryBuilderCnd($cnd);
        $field = \XLite::isAdminZone()
            ? 'p.price'
            : $this->getCalculatedField($queryBuilder, 'price');

        return $queryBuilder->select('MAX(' . $field . ')')
            ->orderBy('p.product_id')
            ->setMaxResults(1)
            ->getSingleScalarResult();
    }

    /**
     * Return list of handling search params
     *
     * @return array
     */
    protected function getHandlingSearchParams()
    {
        $list = parent::getHandlingSearchParams();

        $list[] = static::P_ATTRIBUTE;
        $list[] = static::P_FILTER;
        $list[] = static::P_IN_STOCK;

        return $list;
    }

    /**
     * Prepare filter search condition
     *
     * @param \Doctrine\ORM\QueryBuilder $queryBuilder Query builder to prepare
     * @param array                      $value        Condition data
     * @param boolean                    $countOnly    Return items list or only its size OPTIONAL
     *
     * @return void
     */
    protected function prepareCndFilter(\Doctrine\ORM\QueryBuilder $queryBuilder, $value, $countOnly)
    {
        if (is_array($value) && $value) {
            foreach ($value as $key => $val) {
                $this->callSearchConditionHandler($val, $key, $queryBuilder, $countOnly);
            }
        }
    }

    /**
     * Prepare attribute search condition
     *
     * @param \XLite\Model\QueryBuilder\AQueryBuilder $queryBuilder Query builder to prepare
     * @param array                                   $value        Condition data
     *
     * @return void
     */
    protected function prepareCndAttribute(\XLite\Model\QueryBuilder\AQueryBuilder $queryBuilder, $value)
    {
        if (is_array($value) && !empty($value)) {
            $classes = array();
            $attributes = \XLite\Core\Database::getRepo('XLite\Model\Attribute')->findByIds(array_keys($value));
            if ($attributes) {
                foreach ($attributes as $attribute) {
                    if (isset($value[$attribute->getId()])) {
                        $queryBuilder->assignAttributeCondition($attribute, $value[$attribute->getId()]);
                    }
                }
            }
        }
    }

    /**
     * Prepare certain search condition
     *
     * @param \Doctrine\ORM\QueryBuilder $queryBuilder Query builder to prepare
     * @param string                     $value        Condition data
     *
     * @return void
     */
    protected function prepareCndInStock(\Doctrine\ORM\QueryBuilder $queryBuilder, $value)
    {
        if ($value) {
            $queryBuilder->innerJoinInventory()
                ->andWhere('i.amount > :zero OR i.enabled = 0')
                ->setParameter('zero', 0)
                ->andWhere('p.arrivalDate < :now')
                ->setParameter('now', \XLite\Base\SuperClass::getUserTime());
        }
    }

    /**
     * Create a new QueryBuilder instance with condition
     *
     * @param \XLite\Core\CommonCell $cnd       Search condition
     * @param boolean                $countOnly Return items list or only its size OPTIONAL
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    protected function createQueryBuilderCnd(\XLite\Core\CommonCell $cnd, $countOnly = false)
    {
        $queryBuilder = $this->createQueryBuilder();
        $this->currentSearchCnd = $cnd;

        foreach ($this->currentSearchCnd as $key => $value) {
            $this->callSearchConditionHandler($value, $key, $queryBuilder, $countOnly);
        }

        return $queryBuilder;
    }
}
