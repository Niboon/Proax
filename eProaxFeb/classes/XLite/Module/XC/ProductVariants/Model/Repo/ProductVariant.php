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

namespace XLite\Module\XC\ProductVariants\Model\Repo;

/**
 * Product variants repository
 */
class ProductVariant extends \XLite\Model\Repo\ARepo
{
    /**
     * Allowable search params
     */
    const SEARCH_PRODUCT = 'product';

    const SKU_GENERATION_LIMIT = 50;

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
        $queryBuilder = $this->createQueryBuilder('v');
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
     * @return
     */
    public function searchCount(\Doctrine\ORM\QueryBuilder $qb)
    {
        $qb->select('COUNT(DISTINCT v.id)');

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
     * Generate SKU
     *
     * @param string $sku SKU
     *
     * @return string
     */
    public function assembleUniqueSKU($sku)
    {
        $i = 0;
        $qb = $this->defineGenerateSKUQuery();
        $qbp = \XLite\Core\Database::getRepo('XLite\Model\Product')->defineGenerateSKUQuery();
        $base = $sku;

        while (
            $i < static::SKU_GENERATION_LIMIT
            && (
                0 < intval($qb->setParameter('sku', $sku)->getSingleScalarResult())
                || 0 < intval($qbp->setParameter('sku', $sku)->getSingleScalarResult())
            )
        ) {
            $i++;
            $newSku = substr(uniqid($base . '-', true), 0, 32);
            if ($newSku == $sku) {
                $newSku = md5($newSku);
            }
            $sku = $newSku;
        }

        if ($i >= static::SKU_GENERATION_LIMIT) {
            $sku = md5($sku . microtime(true));
        }

        return $sku;
    }

    /**
     * Define query for generate SKU
     *
     * @return \XLite\Model\QueryBuilder\QUeryBuilder
     */
    public function defineGenerateSKUQuery()
    {
        return $this->getQueryBuilder()
            ->from($this->_entityName, 'v')
            ->select('COUNT(v.id) cnt')
            ->andWhere('v.sku = :sku');
    }

    /**
     * Get modifier types by product
     *
     * @param \XLite\Model\Product $product Product
     *
     * @return array
     */
    public function getModifierTypesByProduct(\XLite\Model\Product $product)
    {
        $price = $this->createQueryBuilder('v')
            ->andWhere('v.product = :product AND v.defaultPrice = :false')
            ->setParameter('product', $product)
            ->setParameter('false', false)
            ->setMaxResults(1)
            ->getResult();

        $quantity = $product->hasIncompleteVariantsList()
            || $this->createQueryBuilder('v')
            ->andWhere('v.product = :product AND v.defaultAmount = :false')
            ->setParameter('product', $product)
            ->setParameter('false', false)
            ->setMaxResults(1)
            ->getResult();

        $weight = $this->createQueryBuilder('v')
            ->andWhere('v.product = :product AND v.defaultWeight = :false')
            ->setParameter('product', $product)
            ->setParameter('false', false)
            ->setMaxResults(1)
            ->getResult();

        $sku = $this->createQueryBuilder('v')
            ->andWhere('v.product = :product AND v.sku IS NOT NULL AND v.sku != :empty')
            ->setParameter('product', $product)
            ->setParameter('empty', '')
            ->setMaxResults(1)
            ->getResult();

        return array(
            'price'    => !empty($price),
            'quantity' => !empty($quantity),
            'weight'   => !empty($weight),
            'sku'      => !empty($sku),
        );
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
            $queryBuilder->andWhere('v.product = :product')
                ->setParameter('product', $value);
        }
    }

    /**
     * Update single entity
     *
     * @param \XLite\Model\AEntity $entity Entity to use
     * @param array                $data   Data to save OPTIONAL
     *
     * @return void
     */
    protected function performUpdate(\XLite\Model\AEntity $entity, array $data = array())
    {
        parent::performUpdate($entity, $data);

        if ($entity->getProduct()) {
            $entity->getProduct()->updateQuickData();
        }
    }
}
