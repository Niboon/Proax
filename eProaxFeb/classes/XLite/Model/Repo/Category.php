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
 * Category repository class
 */
class Category extends \XLite\Model\Repo\Base\I18n
{
    /**
     * Allowable search params
     */
    const SEARCH_PARENT = 'parent';
    const SEARCH_LIMIT  = 'limit';

    const ROOT_LPOS = 1;

    /**
     * Maximum value of the "rpos" field in all records
     *
     * @var integer
     */
    protected $maxRightPos;

    /**
     * Flush unit-of-work changes after every record loading
     *
     * @var boolean
     */
    protected $flushAfterLoading = true;

    /**
     * Root category
     *
     * @var \XLite\Model\Category
     */
    protected static $rootCategory;

    /**
     * Return the reserved ID of root category
     *
     * @param boolean $override Override flag OPTIONAL
     *
     * @return \XLite\Model\Category
     */
    public function getRootCategory($override = false)
    {
        if ($override || !isset(static::$rootCategory)) {
            static::$rootCategory = $this->findOneByLpos(static::ROOT_LPOS) ?: false;
        }

        return static::$rootCategory ?: null;
    }

    /**
     * Return the reserved ID of root category
     *
     * @return integer
     */
    public function getRootCategoryId()
    {
        $category = $this->getRootCategory();

        return $category ? $category->getCategoryId() : null;
    }

    /**
     * Return the category enabled condition
     *
     * @return boolean
     */
    public function getEnabledCondition()
    {
        return !\XLite::isAdminZone();
    }

    /**
     * Return the category membership condition
     *
     * @return boolean
     */
    public function getMembershipCondition()
    {
        return !\XLite::isAdminZone();
    }

    /**
     * Create a new QueryBuilder instance that is prepopulated for this entity name
     *
     * @param string  $alias       Table alias OPTIONAL
     * @param string  $indexBy     The index for the from. OPTIONAL
     * @param string  $code        Language code OPTIONAL
     * @param boolean $excludeRoot Do not include root category into the search result OPTIONAL
     *
     * @return \XLite\Model\QueryBuilder\AQueryBuilder
     */
    public function createQueryBuilder($alias = null, $indexBy = null, $code = null, $excludeRoot = true)
    {
        $queryBuilder = parent::createQueryBuilder($alias, $indexBy, $code);

        return $this->initializeQueryBuilder($queryBuilder, $alias, $excludeRoot);
    }

    /**
     * Initialize the query builder (to prevent the use of language query)
     *
     * @param \Doctrine\ORM\QueryBuilder $queryBuilder Query builder to use
     * @param string                     $alias        Table alias OPTIONAL
     * @param boolean                    $excludeRoot  Do not include root category into the search result OPTIONAL
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    protected function initializeQueryBuilder($queryBuilder, $alias = null, $excludeRoot = true)
    {
        $this->addEnabledCondition($queryBuilder, $alias);
        $this->addOrderByCondition($queryBuilder, $alias);
        $this->addMembershipCondition($queryBuilder, $alias);

        if ($excludeRoot) {
            $this->addExcludeRootCondition($queryBuilder, $alias);
        }

        return $queryBuilder;
    }

    /**
     * find() with cache
     *
     * @param integer $categoryId Category ID
     *
     * @return \XLite\Model\Category
     */
    public function getCategory($categoryId)
    {
        return $this->find($this->prepareCategoryId($categoryId));
    }

    /**
     * Return full list of categories
     *
     * @param integer $rootId ID of the subtree root OPTIONAL
     *
     * @return array
     */
    public function getCategories($rootId = null)
    {
        return $this->defineFullTreeQuery($rootId)->getResult();
    }

    /**
     * Return full list of categories
     *
     * @param integer $rootId ID of the subtree root OPTIONAL
     *
     * @return array
     */
    public function getCategoriesPlainList($rootId = null)
    {
        $rootId = $rootId ?: $this->getRootCategoryId();

        return $this->getCategoriesPlainListChild($rootId);
    }

    /**
     * Return list of subcategories (one level)
     *
     * @param integer $rootId ID of the subtree root
     *
     * @return array
     */
    public function getSubcategories($rootId)
    {
        return $this->defineSubcategoriesQuery($rootId)->getResult();
    }

    /**
     * Return list of categories on the same level
     *
     * @param \XLite\Model\Category $category Category
     * @param boolean               $hasSelf  Flag to include itself OPTIONAL
     *
     * @return array
     */
    public function getSiblings(\XLite\Model\Category $category, $hasSelf = false)
    {
        return $this->defineSiblingsQuery($category, $hasSelf)->getResult();
    }

    /**
     * Return categories subtree
     *
     * @param integer $categoryId Category Id
     *
     * @return array
     */
    public function getSubtree($categoryId)
    {
        return $category = $this->getCategory($categoryId)
            ? $this->defineSubtreeQuery($categoryId)->getResult()
            : array();
    }

    /**
     * Get categories path from root to the specified category
     *
     * @param integer $categoryId Category Id
     *
     * @return array
     */
    public function getCategoryPath($categoryId)
    {
        return $category = $this->getCategory($categoryId)
            ? $this->defineCategoryPathQuery($categoryId)->getResult()
            : array();
    }

    /**
     * Return the array of the category path
     *
     * @param integer $categoryId Category Id
     *
     * @return array
     */
    public function getCategoryNamePath($categoryId)
    {
        return array_map(array($this, 'getCategoryName'), $this->getCategoryPath($categoryId));
    }

    /**
     * The method is used as a callback in the "$this->getCategoryNamePath()" method
     *
     * @param \XLite\Model\Category $category Category
     *
     * @return string
     */
    public function getCategoryName(\XLite\Model\Category $category)
    {
        return $category->getName();
    }

    /**
     * Get depth of the category path
     *
     * @param integer $categoryId Category Id
     *
     * @return integer
     */
    public function getCategoryDepth($categoryId)
    {
        return $category = $this->getCategory($categoryId)
            ? $this->defineCategoryDepthQuery($categoryId)->getSingleScalarResult()
            : 0;
    }

    /**
     * Get categories list by product ID
     *
     * @param integer $productId Product ID
     *
     * @return \Doctrine\ORM\PersistentCollection
     */
    public function findAllByProductId($productId)
    {
        return $this->defineSearchByProductIdQuery($productId)->getResult();
    }

    /**
     * Find one by path
     *
     * @param array $path Path
     *
     * @return \XLite\Model\Category
     */
    public function findOneByPath(array $path)
    {
        $result = $this->getRootCategory();

        if (!empty($path)) {
            do {
                $name = array_shift($path);
                $result = $this->createQueryBuilder()
                    ->andWhere('c.parent = :parent')
                    ->andWhere('translations.name = :name')
                    ->setParameter('parent', $result)
                    ->setParameter('name', $name)
                    ->getSingleResult();

            } while ($result && $path);
        }

        return $result;
    }

    /**
     * Get plan list for tree
     *
     * @param integer $categoryId Category id OPTIONAL
     *
     * @return array
     */
    public function getPlanListForTree($categoryId = null)
    {
        $categoryId = $categoryId ?: $this->getRootCategoryId();

        $list = array();
        foreach ($this->getChildsPlainListForTree($categoryId) as $category) {
            $list[] = array(
                'category_id'  => $category['category_id'],
                'depth'        => $category['depth'],
                'translations' => $category['translations'],
            );
            if ($category['rpos'] > $category['lpos'] + 1) {
                $list = array_merge($list, $this->getPlanListForTree($category['category_id']));
            }
        }

        return $list;
    }

    /**
     * Get children plain list for tree
     *
     * @param integer $categoryId Category id
     *
     * @return array
     */
    public function getChildsPlainListForTree($categoryId)
    {
        return $this->defineChildsPlainListForTreeQuery($categoryId)->getArrayResult();
    }

    /**
     * Add the conditions for the current subtree
     *
     * NOTE: function is public since it's needed to the Product model repository
     *
     * @param \Doctrine\ORM\QueryBuilder $queryBuilder Query builder to modify
     * @param integer                    $categoryId   Current category ID
     * @param string                     $field        Name of the field to use OPTIONAL
     * @param integer                    $lpos         Left position OPTIONAL
     * @param integer                    $rpos         Right position OPTIONAL
     *
     * @return boolean
     */
    public function addSubTreeCondition(
        \Doctrine\ORM\QueryBuilder $queryBuilder,
        $categoryId,
        $field = 'lpos',
        $lpos = null,
        $rpos = null
    ) {
        $category = $this->getCategory($categoryId);

        if ($category) {
            $lpos = $lpos ?: $category->getLpos();
            $rpos = $rpos ?: $category->getRpos();

            $queryBuilder->andWhere($queryBuilder->expr()->between('c.' . $field, $lpos, $rpos));
        }

        return isset($category);
    }


    /**
     * Define query for getChildsPlainListForTree()
     *
     * @param integer $categoryId Category id
     *
     * @return \XLite\Model\QueryBuilder\AQueryBuilder
     */
    protected function defineChildsPlainListForTreeQuery($categoryId)
    {
        return $this->createPureQueryBuilder()
            ->select('c')
            ->addSelect('translations')
            ->linkInner('c.translations')
            ->linkInner('c.parent')
            ->andWhere('parent.category_id = :cid')
            ->setParameter('cid', $categoryId);
    }

    /**
     * Define the Doctrine query
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    protected function defineMaxRightPosQuery()
    {
        return $this->createPureQueryBuilder()
            ->select('MAX(c.rpos)')
            ->groupBy('c.category_id')
            ->setMaxResults(1);
    }

    /**
     * Define the Doctrine query
     *
     * @param integer $categoryId Category Id
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    protected function defineFullTreeQuery($categoryId)
    {
        $queryBuilder = $this->createQueryBuilder()
            ->addSelect('translations');

        $this->addSubTreeCondition($queryBuilder, $categoryId ?: $this->getRootCategoryId());

        return $queryBuilder;
    }

    /**
     * Get categories plain list (child)
     *
     * @param integer $categoryId Category id
     *
     * @return array
     */
    protected function getCategoriesPlainListChild($categoryId)
    {
        $list = array();

        foreach ($this->defineSubcategoriesQuery($categoryId)->getArrayResult() as $category) {
            $list[] = $category;
            if ($category['rpos'] > $category['lpos'] + 1) {
                $list = array_merge($list, $this->getCategoriesPlainListChild($category['category_id']));
            }
        }

        return $list;
    }

    /**
     * Define the Doctrine query
     *
     * @param integer $categoryId Category Id
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    protected function defineSubcategoriesQuery($categoryId)
    {
        $queryBuilder = $this->initializeQueryBuilder($this->createPureQueryBuilder());

        if ($categoryId) {
            $queryBuilder->innerJoin('c.parent', 'cparent')
                ->andWhere('cparent.category_id = :parentId')
                ->setParameter('parentId', $categoryId);

        } else {
            $queryBuilder->andWhere('c.parent IS NULL');
        }

        return $queryBuilder;
    }

    /**
     * Define the Doctrine query
     *
     * @param \XLite\Model\Category $category Category
     * @param boolean               $hasSelf  Flag to include itself OPTIONAL
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    protected function defineSiblingsQuery(\XLite\Model\Category $category, $hasSelf = false)
    {
        $result = $this->defineSubcategoriesQuery($category->getParentId());

        if (!$hasSelf) {
            $result->andWhere('c.category_id <> :category_id')
                ->setParameter('category_id', $category->getCategoryId());
        }

        return $result;
    }

    /**
     * Define the Doctrine query
     *
     * @param integer $categoryId Category Id
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    protected function defineSubtreeQuery($categoryId)
    {
        return $this->defineFullTreeQuery($categoryId)
            ->andWhere('c.category_id <> :category_id')
            ->setParameter('category_id', $categoryId);
    }

    /**
     * Define the Doctrine query
     *
     * @param integer $categoryId Category Id
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    protected function defineCategoryPathQuery($categoryId)
    {
        $queryBuilder = $this->createQueryBuilder();
        $category = $this->getCategory($categoryId);

        if ($category) {
            $this->addSubTreeCondition($queryBuilder, $categoryId, 'lpos', 1, $category->getLpos());

            $this->addSubTreeCondition(
                $queryBuilder,
                $categoryId,
                'rpos',
                $category->getRpos(),
                $this->getMaxRightPos()
            );

            $queryBuilder->orderBy('c.lpos', 'ASC');

        } else {
            // :TODO: - throw exception
        }

        return $queryBuilder;
    }

    /**
     * Define the Doctrine query
     *
     * @param integer $categoryId Category Id
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    protected function defineCategoryDepthQuery($categoryId)
    {
        return $this->defineCategoryPathQuery($categoryId)
            ->select('COUNT(c.category_id) - 1')
            ->setMaxResults(1);
    }

    /**
     * Define the Doctrine query
     *
     * @param integer $productId Product Id
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    protected function defineSearchByProductIdQuery($productId)
    {
        return $this->createQueryBuilder()
            ->innerJoin('c.categoryProducts', 'cp')
            ->innerJoin('cp.product', 'product')
            ->andWhere('product.product_id = :productId')
            ->setParameter('productId', $productId)
            ->addOrderBy('cp.orderby', 'ASC');
    }

    /**
     * Define the Doctrine query
     *
     * @param string  $index        Field name
     * @param integer $relatedIndex Related index value
     * @param integer $offset       Increment OPTIONAL
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    protected function defineUpdateIndexQuery($index, $relatedIndex, $offset = 2)
    {
        $expr = new \Doctrine\ORM\Query\Expr();

        return $this->createPureQueryBuilder('c', false)
            ->update($this->_entityName, 'c')
            ->set('c.' . $index, 'c.' . $index . ' + :offset')
            ->andWhere($expr->gt('c.' . $index, ':relatedIndex'))
            ->setParameter('offset', $offset)
            ->setParameter('relatedIndex', $relatedIndex);
    }

    /**
     * Adds additional condition to the query for checking if category is enabled
     *
     * @param \Doctrine\ORM\QueryBuilder $queryBuilder Query builder object
     * @param string                     $alias        Entity alias OPTIONAL
     *
     * @return void
     */
    protected function addEnabledCondition(\Doctrine\ORM\QueryBuilder $queryBuilder, $alias = null)
    {
        if ($this->getEnabledCondition()) {
            $queryBuilder->andWhere(($alias ?: $this->getDefaultAlias()) . '.enabled = :enabled')
                ->setParameter('enabled', true);
        }
    }

    /**
     * Adds additional condition to the query for checking if category is enabled
     *
     * @param \Doctrine\ORM\QueryBuilder $queryBuilder Query builder object
     * @param string                     $alias        Entity alias OPTIONAL
     *
     * @return void
     */
    protected function addMembershipCondition(\Doctrine\ORM\QueryBuilder $queryBuilder, $alias = null)
    {
        if ($this->getMembershipCondition()) {
            $alias = $alias ?: $this->getDefaultAlias();
            $membership = \XLite\Core\Auth::getInstance()->getMembershipId();

            if ($membership) {
                $queryBuilder->leftJoin($alias . '.memberships', 'membership')
                    ->andWhere('membership.membership_id = :membershipId OR membership.membership_id IS NULL')
                    ->setParameter('membershipId', \XLite\Core\Auth::getInstance()->getMembershipId());

            } else {
                $queryBuilder->leftJoin($alias . '.memberships', 'membership')
                    ->andWhere('membership.membership_id IS NULL');
            }
        }
    }

    /**
     * Adds additional condition to the query to order categories
     *
     * @param \Doctrine\ORM\QueryBuilder $queryBuilder Query builder object
     * @param string                     $alias        Entity alias OPTIONAL
     *
     * @return void
     */
    protected function addOrderByCondition(\Doctrine\ORM\QueryBuilder $queryBuilder, $alias = null)
    {
        $queryBuilder
        // We need POS ordering since POS and LPOS are orderings inside the same one level.
        // LPOS is formed by the system (by adding into the level)
        // POS  is formed manually by admin and must have priority
            ->addOrderBy(($alias ?: $this->getDefaultAlias()) . '.pos', 'ASC')
            ->addOrderBy(($alias ?: $this->getDefaultAlias()) . '.category_id', 'ASC');
    }

    /**
     * Adds additional condition to the query to order categories
     *
     * @param \Doctrine\ORM\QueryBuilder $queryBuilder Query builder object
     * @param string                     $alias        Entity alias OPTIONAL
     *
     * @return void
     */
    protected function addExcludeRootCondition(\Doctrine\ORM\QueryBuilder $queryBuilder, $alias = null)
    {
        $alias = $alias ?: $this->getDefaultAlias();

        $queryBuilder->andWhere($alias . '.category_id <> :rootId')
            ->setParameter('rootId', $this->getRootCategoryId());
    }

    /**
     * Return maximum index in the "nested set" tree
     *
     * @return integer
     */
    protected function getMaxRightPos()
    {
        if (!isset($this->maxRightPos)) {
            $this->maxRightPos = $this->defineMaxRightPosQuery()->getSingleScalarResult();
        }

        return $this->maxRightPos;
    }

    /**
     * Prepare data for a new category node
     *
     * @param \XLite\Model\Category $entity Category object
     * @param \XLite\Model\Category $parent Parent category object OPTIONAL
     *
     * @return void
     */
    protected function prepareNewCategoryData(\XLite\Model\Category $entity, \XLite\Model\Category $parent = null)
    {
        if (!isset($parent)) {
            $parent = $this->getCategory($entity->getParentId());
        }

        if (isset($parent)) {
            $entity->setLpos($parent->getLpos() + 1);
            $entity->setRpos($parent->getLpos() + 2);
            $entity->setDepth($parent->getDepth() + 1);

        } else {
            // :TODO: - rework - add support last root category
            $entity->setLpos(1);
            $entity->setRpos(2);
        }

        $entity->setParent($parent);
    }

    /**
     * Prepare data for a the "updateQuickFlags()" method
     *
     * @param integer $scAll     The "subcategories_count_all" flag value
     * @param integer $scEnabled The "subcategories_count_enabled" flag value
     *
     * @return array
     */
    protected function prepareQuickFlags($scAll, $scEnabled)
    {
        return array(
            'subcategories_count_all'     => $scAll,
            'subcategories_count_enabled' => $scEnabled,
        );
    }

    /**
     * Prepare passed ID
     * NOTE: see E:0038835 (external BT)
     *
     * @param mixed $categoryId Category ID
     *
     * @return integer|void
     */
    protected function prepareCategoryId($categoryId)
    {
        return abs(intval($categoryId)) ?: null;
    }

    /**
     * Update quick flags for a category
     *
     * @param \XLite\Model\Category $entity Category
     * @param array                 $flags  Flags to set
     *
     * @return void
     */
    protected function updateQuickFlags(\XLite\Model\Category $entity, array $flags)
    {
        $quickFlags = $entity->getQuickFlags();

        if (!isset($quickFlags)) {
            $quickFlags = new \XLite\Model\Category\QuickFlags();
            $quickFlags->setCategory($entity);
            $entity->setQuickFlags($quickFlags);
        }

        foreach ($flags as $name => $delta) {
            $name = \Includes\Utils\Converter::convertToPascalCase($name);
            $quickFlags->{'set' . $name}($quickFlags->{'get' . $name}() + $delta);
        }
    }

    // {{{ Methods to manage entities

    /**
     * Remove all subcategories
     *
     * @param integer $categoryId Main category
     *
     * @return void
     */
    public function deleteSubcategories($categoryId)
    {
        $this->deleteInBatch($this->getSubtree($categoryId));
    }

    /**
     * Insert single entity
     *
     * @param \XLite\Model\Category|array $entity Data to insert OPTIONAL
     *
     * @return \XLite\Model\Category
     */
    protected function performInsert($entity = null)
    {
        /** @var \XLite\Model\Category $entity */
        $entity   = parent::performInsert($entity);
        $parentID = $entity->getParentId();

        if (empty($parentID)) {
            // Insert root category
            $this->prepareNewCategoryData($entity);

        } else {
            // Get parent for non-root category
            $parent = $this->getCategory($parentID);

            if ($parent) {
                // Reload parent category from database to get correct value of indexes
                // after batch update in previous call of performInsert on import
                \XLite\Core\Database::getEM()->refresh($parent);

                // Update indexes in the nested set
                $this->defineUpdateIndexQuery('lpos', $parent->getLpos())->execute();
                $this->defineUpdateIndexQuery('rpos', $parent->getLpos())->execute();

                // Create record in DB
                $this->prepareNewCategoryData($entity, $parent);

            } else {
                \Includes\ErrorHandler::fireError(__METHOD__ . ': category #' . $parentID . ' not found');
            }
        }

        // Update quick flags
        if (isset($parent) && null == $entity->getCategoryId()) {
            $this->updateQuickFlags($parent, $this->prepareQuickFlags(1, $entity->getEnabled() ? 1 : -1));
        }

        return $entity;
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
        if (isset($data['enabled']) && $entity->getParent() && ($entity->getEnabled() xor ((bool) $data['enabled']))) {
            $this->updateQuickFlags(
                $entity->getParent(),
                $this->prepareQuickFlags(0, ($entity->getEnabled() ? -1 : 1))
            );
        }

        parent::performUpdate($entity, $data);
    }

    /**
     * Delete single entity
     *
     * @param \XLite\Model\AEntity $entity Entity to detach
     *
     * @return void
     */
    protected function performDelete(\XLite\Model\AEntity $entity)
    {
        // Update quick flags
        if ($entity->getParent()) {
            $this->updateQuickFlags($entity->getParent(), $this->prepareQuickFlags(-1, $entity->getEnabled() ? -1 : 0));
        }

        // Root category cannot be removed. Only its subtree
        $onlySubtree = ($entity->getCategoryId() == $this->getRootCategoryId());

        // Calculate some variables
        $right = $entity->getRpos() - ($onlySubtree ? 1 : 0);
        $width = $entity->getRpos() - $entity->getLpos() - ($onlySubtree ? 1 : -1);

        // Update indexes in the nested set.
        // FIXME: must not use execute()
        $this->defineUpdateIndexQuery('lpos', $right, -$width)->execute();
        $this->defineUpdateIndexQuery('rpos', $right, -$width)->execute();

        if ($onlySubtree) {
            $this->deleteInBatch($this->getSubtree($entity->getCategoryId()), false);

        } else {
            parent::performDelete($entity);
        }
    }

    // }}}

    /**
     * Load raw fixture
     *
     * @param \XLite\Model\AEntity $entity  Entity
     * @param array                $record  Record
     * @param array                $regular Regular fields info OPTIONAL
     * @param array                $assocs  Associations info OPTIONAL
     *
     * @return void
     */
    public function loadRawFixture(
        \XLite\Model\AEntity $entity,
        array $record,
        array $regular = array(),
        array $assocs = array()
    ) {
        /** @var \XLite\Model\Category $entity */
        $this->performInsert($entity);

        parent::loadRawFixture($entity, $record, $regular, $assocs);
    }

    /**
     * Assemble associations from record
     *
     * @param array $record Record
     * @param array $assocs Associations info OPTIONAL
     *
     * @return array
     */
    protected function assembleAssociationsFromRecord(array $record, array $assocs = array())
    {
        if (!isset($record['quickFlags'])) {
            $record['quickFlags'] = array();
        }

        return parent::assembleAssociationsFromRecord($record, $assocs);
    }

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
        $qb->select('COUNT(DISTINCT c.category_id)');

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
            static::SEARCH_PARENT,
            static::SEARCH_LIMIT,
        );
    }

    // {{{ Export routines

    /**
     * Define query builder for COUNT query
     *
     * @return \XLite\Model\QueryBuilder\AQueryBuilder
     */
    protected function defineCountForExportQuery()
    {
        $qb = $this->createPureQueryBuilder();
        $this->addSubTreeCondition($qb, $this->getRootCategoryId());

        return $qb->select(
            'COUNT(DISTINCT ' . $qb->getMainAlias() . '.' . $this->getPrimaryKeyField() . ')'
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

    /**
     * Prepare certain search condition
     *
     * @param \Doctrine\ORM\QueryBuilder $queryBuilder Query builder to prepare
     * @param mixed                      $value        Condition data
     *
     * @return void
     */
    protected function prepareCndParent(\Doctrine\ORM\QueryBuilder $queryBuilder, $value)
    {
        if ($value && !is_object($value)) {
            $value = \XLite\Core\Database::getRepo('XLite\Model\Category')->find(intval($value));
        }

        if ($value) {
            $queryBuilder->andWhere('c.parent = :parent')
                ->setParameter('parent', $value);
        }
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
        $queryBuilder = parent::defineExportIteratorQueryBuilder($position);

        $this->addSubTreeCondition($queryBuilder, $this->getRootCategoryId());

        return $queryBuilder;
    }

    // }}}

    // {{{ Correct categories structure methods

    /**
     * Correct categories structure: lpos, rpos and depth fields
     *
     * @return void
     */
    public function correctCategoriesStructure()
    {
        $all = $this->defineFindAllSortedQueryBuilder()->getResult();

        if (!empty($all)) {
            $allCategories = array();
            $byKey = array();

            foreach ($all as $category) {
                $byKey[intval($category->getParentId())][] = array(
                    'category_id' => $category->getCategoryId(),
                    'parent_id'   => $category->getParentId(),
                    'lpos'        => 0,
                    'rpos'        => 0,
                    'depth'       => 0,
                    'pos'         => $category->getPos(),
                    'enabled'     => $category->getEnabled(),
                    'subcats'     => 0,
                    'subcats_enabled' => 0,
                    'total_subcats' => 0,
                );
            }

            ksort($byKey);

            foreach ($byKey as $children) {
                foreach ($children as $c) {
                    $allCategories[] = $c;
                }
            }

            unset($byKey);

            $allCategories[0]['lpos'] = 1;
            $allCategories[0]['rpos'] = 2;
            $allCategories[0]['depth'] = -1;

            $this->correctCategoryData($allCategories);

            $categories = array();
            foreach ($allCategories as $c) {
                $categories[$c['category_id']] = $c;
            }

            foreach ($all as $category) {
                $catId = $category->getCategoryId();
                $data = array(
                    'lpos' => $categories[$catId]['lpos'],
                    'rpos' => $categories[$catId]['rpos'],
                    'depth' => $categories[$catId]['depth'],
                );
                $this->update($category, $data);
                $qf = $category->getQuickFlags();
                if (!$qf) {
                    $qf = new \XLite\Model\Category\QuickFlags();
                    $qf->setCategory($category);
                    $category->setQuickFlags($qf);
                }
                $qf->setSubcategoriesCountAll($categories[$catId]['subcats']);
                $qf->setSubcategoriesCountEnabled($categories[$catId]['subcats_enabled']);
            }
        }
    }

    /**
     * Recursively calculate lpos, rpos and depth
     *
     * @param array   &$categories Categories data array
     * @param integer $currentId   Current categories array index OPTIONAL
     *
     * @return void
     */
    protected function correctCategoryData(&$categories, $currentId = 0)
    {
        $current = $categories[$currentId];

        $idx = $current['lpos'] + 1;

        foreach ($categories as $i => $c) {
            if ($c['parent_id'] == $current['category_id']) {
                $categories[$i]['depth'] = $current['depth'] + 1;
                $categories[$i]['lpos'] = $idx;
                $categories[$i]['rpos'] = $idx + 1;

                $this->correctCategoryData($categories, $i);

                $categories[$currentId]['rpos'] = $categories[$i]['rpos'] + 1;

                $categories[$currentId]['subcats']++;

                if ($c['enabled']) {
                    $categories[$currentId]['subcats_enabled']++;
                }

                $categories[$currentId]['total_subcats'] += (1 + $categories[$i]['total_subcats']);

                $idx += (2 + 2 * $categories[$i]['total_subcats']);
            }
        }
    }

    /**
     * Define specific query builder
     *
     * @return \XLite\Model\QueryBuilder\AQueryBuilder
     */
    protected function defineFindAllSortedQueryBuilder()
    {
        return parent::createQueryBuilder('c')
            ->addOrderBy('c.category_id');
    }

    // }}}

    // {{{ Product in category

    /**
     * Check if product present in category
     *
     * @param \XLite\Model\Category|integer $category Category
     * @param \XLite\Model\Product|integer  $product  Product
     *
     * @return boolean
     */
    public function hasProduct($category, $product)
    {
        return (bool) $this->defineHasProduct($category, $product)->getSingleScalarResult();
    }

    /**
     * Define the Doctrine query
     *
     * @param \XLite\Model\Category|integer $category Category
     * @param \XLite\Model\Product|integer  $product  Product
     *
     * @return \XLite\Model\QueryBuilder\AQueryBuilder
     */
    protected function defineHasProduct($category, $product)
    {
        return $this->createQueryBuilder()
            ->andWhere('c.category_id = :categoryId')
            ->setParameter('categoryId', is_object($category) ? $category->getCategoryId() : $category)
            ->innerJoin('c.categoryProducts', 'cp')
            ->andWhere('cp.product = :product')
            ->setParameter('product', $product)
            ->selectCount();
    }


    // }}}
}
