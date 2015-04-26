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

namespace XLite\Module\XC\Upselling\Model\Repo;

/**
 * Upselling Product repository
 */
class UpsellingProduct extends \XLite\Model\Repo\ARepo
{
    // {{{ Search

    const SEARCH_LIMIT = 'limit';
    const SEARCH_PARENT_PRODUCT_ID = 'parentProductId';
    const SEARCH_EXCL_PRODUCT_ID   = 'excludingProductId';
    const SEARCH_DATE              = 'date';

    /**
     * Default 'order by' field name
     *
     * @var string
     */
    protected $defaultOrderBy = 'orderBy';


    /**
     * Get upselling products list
     *
     * @param integer $productId Product ID
     *
     * @return array(\XLite\Module\XC\Upselling\Model\UpsellingProduct) Objects
     */
    public function getUpsellingProducts($productId)
    {
        return $this->findByParentProductId($productId);
    }

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
        $queryBuilder = $this->createQueryBuilder('f');
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
        $qb->select('COUNT(DISTINCT f.id)');

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
     * Find by type
     *
     * @param integer $productId Product ID
     *
     * @return array
     */
    protected function findByParentProductId($productId)
    {
        $cnd = new \XLite\Core\CommonCell();
        $cnd->{static::SEARCH_PARENT_PRODUCT_ID} = $productId;

        return $this->search($cnd);
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
            static::SEARCH_PARENT_PRODUCT_ID,
            static::SEARCH_LIMIT,
            static::SEARCH_EXCL_PRODUCT_ID,
            static::SEARCH_DATE,
        );
    }

    /**
     * Prepare certain search condition
     *
     * @param \Doctrine\ORM\QueryBuilder $qb    Query builder to prepare
     * @param string                     $value Condition data
     *
     * @return void
     */
    protected function prepareCndParentProductId(\Doctrine\ORM\QueryBuilder $qb, $value)
    {
        $f = $this->getMainAlias($qb);
        $qb = $qb->innerJoin($f . '.product', 'p')
            ->andWhere($f . '.parentProduct = :parentProductId')
            ->setParameter('parentProductId', $value);

        return \XLite\Core\Database::getRepo('XLite\Model\Product')->assignExternalEnabledCondition($qb, 'p');
    }

    /**
     * Prepare certain search condition
     *
     * @param \Doctrine\ORM\QueryBuilder $queryBuilder Query builder to prepare
     * @param array                      $value        Condition data
     *
     * @return void
     */
    protected function prepareCndExcludingProductId(\Doctrine\ORM\QueryBuilder $queryBuilder, $value)
    {
        if (is_array($value) && 1 < count($value)) {
            $queryBuilder->andWhere('p.product_id NOT IN (' . implode(',', $value) . ')');

        } else {
            $queryBuilder->andWhere('p.product_id != :productId')
                ->setParameter('productId', is_array($value) ? array_pop($value) : $value);
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
    protected function prepareCndDate(\Doctrine\ORM\QueryBuilder $queryBuilder, $value)
    {
        $queryBuilder->andWhere('p.arrivalDate < :date')
            ->setParameter('date', $value);
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

    /**
     * Add the association link for the upsell product
     *
     * @param \XLite\Module\XC\Upselling\Model\UpsellingProduct $link Related product link
     *
     * @return void
     */
    public function addBidirectionalLink($link)
    {
        $this->changeBidirectionalLink($link, true);
    }

    /**
     * Delete the association link for the upsell product
     *
     * @param \XLite\Module\XC\Upselling\Model\UpsellingProduct $link Related product link
     *
     * @return void
     */
    public function deleteBidirectionalLink($link)
    {
        $this->changeBidirectionalLink($link, false);
    }

    /**
     * Change the association link for the upsell product
     * This routine is used only inside the model
     *
     * @param \XLite\Module\XC\Upselling\Model\UpsellingProduct $link             Related product link
     * @param boolean                                           $newBidirectional Bi-directional flag
     *
     * @return void
     */
    protected function changeBidirectionalLink($link, $newBidirectional)
    {
        $data = array(
            'parentProduct' => $link->getProduct(),
            'product'       => $link->getParentProduct(),
        );
        $aLink = $this->findOneBy($data);
        $aLink ? \XLite\Core\Database::getEM()->remove($aLink) : null;

        if ($newBidirectional) {
            // Need to add link
            $this->insert($data);
        }
    }
}
