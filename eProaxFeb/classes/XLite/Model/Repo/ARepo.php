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
 * Abstract repository
 */
abstract class ARepo extends \Doctrine\ORM\EntityRepository
{
    /**
     * Cache default TTL (1 year)
     */
    const CACHE_DEFAULT_TTL = 2592000;

    /**
     * Cache cell fields names
     */
    const KEY_TYPE_CACHE_CELL  = 'keyType';
    const ATTRS_CACHE_CELL     = 'attrs';
    const RELATION_CACHE_CELL  = 'relation';
    const CONVERTER_CACHE_CELL = 'converter';
    const GENERATOR_CACHE_CELL = 'generator';

    /**
     * Cache key types
     */
    const CACHE_ATTR_KEY   = 'attributesKey';
    const CACHE_HASH_KEY   = 'hashKey';
    const CACHE_CUSTOM_KEY = 'customKey';

    const DEFAULT_KEY_TYPE = self::CACHE_ATTR_KEY;
    const EMPTY_CACHE_CELL = 'all';

    /**
     * Repository type codes
     */
    const TYPE_STORE     = 'store';
    const TYPE_SECONDARY = 'secondary';
    const TYPE_SERVICE   = 'service';
    const TYPE_INTERNAL  = 'internal';

    /**
     * Default value for the "flush" param
     */
    const FLUSH_BY_DEFAULT = true;

    /**
     * Name of the prefix for the calculated field
     */
    const CALCULATED_FIELD_FLAG = 'calculatedField.';

    /**
     * Cache driver
     *
     * @var \XLite\Core\Cache\Registry
     */
    protected static $cacheDriver;

    /**
     * Cache cells (local cache)
     *
     * @var array
     */
    protected $cacheCells = null;

    /**
     * Default 'order by' field name
     *
     * @var string
     */
    protected $defaultOrderBy;

    /**
     * Default model alias
     *
     * @var string
     */
    protected $defaultAlias;

    /**
     * Alternative record identifiers
     *
     * @var array
     */
    protected $alternativeIdentifier;

    /**
     * Entity properties
     *
     * @var array
     */
    protected $entityProperties;

    /**
     * Flush unit-of-work changes after every record loading
     *
     * @var boolean
     */
    protected $flushAfterLoading = false;

    /**
     * Repository type
     *
     * @var string
     */
    protected $type = self::TYPE_STORE;

    /**
     * Query builder class
     *
     * @var string
     */
    protected $queryBuilderClass;

    /**
     * Columns' character sets definitions
     *
     * @var array
     */
    protected $columnsCharSets = array();

    /**
     * Get repository type
     *
     * @return string
     */
    public function getRepoType()
    {
        return $this->type;
    }

    /**
     * Add cache relations
     *
     * @param array $externalCells External cells
     *
     * @return void
     */
    public function addCacheRelations(array $externalCells)
    {
        $key = $this->getHashPrefix('externalCells');

        $cacheCells = \XLite\Model\Repo\ARepo::getCacheDriver()->fetch($key);
        if (!is_array($cacheCells)) {
            $cacheCells = array();
        }

        foreach ($externalCells as $model => $cells) {
            $cacheCells[$model] = isset($cacheCells[$model])
                ? array_merge($cacheCells[$model], $cells)
                : $cells;
        }

        \XLite\Model\Repo\ARepo::getCacheDriver()->save($key, $cacheCells, static::CACHE_DEFAULT_TTL);
    }

    /**
     * Get related cache cells
     *
     * @return array
     */
    public function getRelatedCacheCells()
    {
        $cacheCells = \XLite\Model\Repo\ARepo::getCacheDriver()->fetch(
            $this->getHashPrefix('externalCells')
        );

        return is_array($cacheCells) ? $cacheCells : array();
    }

    /**
     * Check - has repository any cache cells or not
     *
     * @return boolean
     */
    public function hasCacheCells()
    {
        return $this->getCacheCells();
    }

    /**
     * Delete cache by entity
     *
     * @param \XLite\Model\AEntity $entity Record
     *
     * @return void
     */
    public function deleteCacheByEntity(\XLite\Model\AEntity $entity)
    {
        foreach ($this->getCacheCells() as $name => $cell) {
            // Get cell arguments
            if ($cell[self::CONVERTER_CACHE_CELL]) {
                   $params = $this->{$cell[self::CONVERTER_CACHE_CELL]}($entity);

            } elseif (is_array($cell[self::ATTRS_CACHE_CELL])
                && $cell[self::ATTRS_CACHE_CELL]
            ) {
                $params = array();
                foreach ($cell[self::ATTRS_CACHE_CELL] as $key) {
                    $params[$key] = $entity->{'get' . \XLite\Core\Converter::convertToCamelCase($key)}();
                }

            } else {
                $params = array();
            }

            // Delete cell
            \XLite\Model\Repo\ARepo::getCacheDriver()->delete(
                $this->getCellHash($name, $cell, $params)
            );
        }

        // Delete related cache cells
        foreach ($this->getRelatedCacheCells() as $model => $cells) {
            $repo = \XLite\Core\Database::getRepo($model);
            foreach ($cells as $cell) {
                $repo->deleteCache($cell);
            }
        }
    }

    /**
     * Delete repository cache or cell cache
     *
     * @param string $name Cell name OPTIONAL
     *
     * @return void
     */
    public function deleteCache($name = '')
    {
        $prefix = $this->getHashPrefix() . '.' . $name;
        foreach (\XLite\Model\Repo\ARepo::getCacheDriver()->getIds() as $id) {
            if (0 === strpos($id, $prefix)) {
                \XLite\Model\Repo\ARepo::getCacheDriver()->delete($id);
            }
        }
    }

    /**
     * Assign default orderBy
     *
     * @param \Doctrine\ORM\QueryBuilder $queryBuilder Query builder
     * @param string                     $alias        Table short alias in query builder OPTIONAL
     *
     * @return \XLite\Model\QueryBuilder\AQueryBuilder
     */
    public function assignDefaultOrderBy(\Doctrine\ORM\QueryBuilder $queryBuilder, $alias = null)
    {
        if ($this->defaultOrderBy) {
            if (!isset($alias)) {
                $alias = $this->getMainAlias($queryBuilder);
            }

            if (is_string($this->defaultOrderBy)) {
                // One field
                $queryBuilder->addOrderBy($alias . '.' . $this->defaultOrderBy);

            } elseif (is_array($this->defaultOrderBy)) {
                // Many fields (field name => sort suffix)
                foreach ($this->defaultOrderBy as $field => $asc) {
                    $queryBuilder->addOrderBy($alias . '.' . $field, $asc ? 'ASC' : 'DESC');
                }

            }
        }

        return $queryBuilder;
    }

    /**
     * Creates a new QueryBuilder instance that is prepopulated for this entity name.
     *
     * @param string $alias   Table alias OPTIONAL
     * @param string $indexBy The index for the from.
     *
     * @return \XLite\Model\QueryBuilder\AQueryBuilder
     */
    public function createQueryBuilder($alias = null, $indexBy = null)
    {
        if (!isset($alias)) {
            $alias = $this->getDefaultAlias();
        }

        $qb = $this->getQueryBuilder()
            ->select($alias)
            ->from($this->_entityName, $alias, $indexBy);

        return $this->assignDefaultOrderBy($qb, $alias);
    }

    /**
     * Create a new QueryBuilder instance that is prepopulated for this entity name
     * NOTE: without any relative subqueries!
     *
     * @param string $alias Table alias OPTIONAL
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function createPureQueryBuilder($alias = null)
    {
        $alias = $alias ?: $this->getDefaultAlias();

        return $this->getQueryBuilder()
            ->select($alias)
            ->from($this->_entityName, $alias);
    }

    /**
     * Get default alias
     *
     * @return string
     */
    public function getDefaultAlias()
    {
        if (!isset($this->defaultAlias)) {
            $list = explode('\\', $this->_entityName);
            $this->defaultAlias = strtolower(substr(array_pop($list), 0, 1));
        }

        return $this->defaultAlias;
    }

    /**
     * Count records
     *
     * @return integer
     */
    public function count()
    {
        return $this->defineCountQuery()->count();
    }

    /**
     * Count records by conditions
     *
     * @param array $conditions Conditions
     *
     * @return integer
     */
    public function countBy(array $conditions = array())
    {
        return intval($this->defineCountByQuery($conditions)->getSingleScalarResult());
    }

    /**
     * Wrapper
     *
     * @param mixed $id Entity identifier
     *
     * @return \XLite\Model\AEntity
     */
    public function find($id)
    {
        if (is_array($id)) {
            foreach ($id as $k => $v) {
                if (!in_array($k, $this->_class->identifier, true)) {
                    unset($id[$k]);
                }
            }

            if (empty($id)) {
                unset($id);
            }
        }

        return isset($id) ? parent::find($id) : null;
    }

    /**
     * Find entities by id's list
     *
     * @param array  $ids    Id's list
     * @param string $prefix Prefix OPTIONAL
     *
     * @return array
     */
    public function findByIds(array $ids, $prefix = 'arr')
    {
        if (1 < count($this->_class->identifier)) {
            // TODO - add throw exception
        }

        $result = array();

        if ($ids) {
            $qb = $this->createQueryBuilder();
            $keys = \XLite\Core\Database::buildInCondition($qb, $ids, $prefix);
            $alias = $this->getMainAlias($qb);
            $qb->andWhere($alias . '.' . $this->_class->identifier[0] . ' IN (' . implode(', ', $keys) . ')');

            $result = $qb->getResult();
        }

        return $result;
    }

    /**
     * Find data frame
     *
     * @param integer $start Start offset OPTIONAL
     * @param integer $limit Frame length OPTIONAL
     *
     * @return array
     */
    public function findFrame($start = 0, $limit = 0)
    {
        return $this->defineFrameQuery($start, $limit)->getResult();
    }

    /**
     * Finds an entity by its primary key / identifier and return entity detached
     *
     * @param mixed $id The identifier.
     *
     * @return \XLite\Model\AEntity|void
     */
    public function findDetached($id)
    {
        $entity = parent::find($id);

        if ($entity) {
            $this->_em->detach($entity);
        }

        return $entity;
    }

    /**
     * Flushes all changes to objects that have been queued up to now to the database
     *
     * @return void
     */
    public function flushChanges()
    {
        $this->getEntityManager()->flush();
    }

    // {{{ Methods to manage entities

    /**
     * Insert entity
     *
     * @param \XLite\Model\AEntity|array $entity Entity to insert
     * @param boolean                    $flush  Flag OPTIONAL
     *
     * @return \XLite\Model\AEntity
     */
    public function insert($entity = null, $flush = self::FLUSH_BY_DEFAULT)
    {
        $entity = $this->performInsert($entity);

        if ($flush) {
            $this->flushChanges();
        }

        return $entity;
    }

    /**
     * Insert entities
     *
     * @param array   $entities List of entities to insert
     * @param boolean $flush    Flag OPTIONAL
     *
     * @return array
     */
    public function insertInBatch(array $entities, $flush = self::FLUSH_BY_DEFAULT)
    {
        $result = array();

        foreach ($entities as $entity) {
            $result[] = $this->performInsert($entity);
        }

        if ($flush) {
            $this->flushChanges();
        }

        return $result;
    }

    /**
     * Update entity
     *
     * @param \XLite\Model\AEntity $entity Entity to update
     * @param array                $data   New values for entity properties
     * @param boolean              $flush  Flag OPTIONAL
     *
     * @return void
     */
    public function update(\XLite\Model\AEntity $entity, array $data = array(), $flush = self::FLUSH_BY_DEFAULT)
    {
        $this->performUpdate($entity, $data);

        if ($flush) {
            $this->flushChanges();
        }
    }

    /**
     * Find entity by ID and update it
     *
     * @param integer $id    ID of entity to update
     * @param array   $data  New values for entity properties
     * @param boolean $flush Flag OPTIONAL
     *
     * @return void
     */
    public function updateById($id, array $data = array(), $flush = self::FLUSH_BY_DEFAULT)
    {
        $this->update($this->getById($id), $data, $flush);
    }

    /**
     * Update entities
     *
     * @param array   $entities List of entities to update
     * @param boolean $flush    Flag OPTIONAL
     *
     * @return void
     */
    public function updateInBatch(array $entities, $flush = self::FLUSH_BY_DEFAULT)
    {
        foreach ($entities as $entity) {
            $this->performUpdate($entity);
        }

        if ($flush) {
            $this->flushChanges();
        }
    }

    /**
     * Find entities by ID and update them
     *
     * @param array   $data  Array of <id => array(properties)> elements
     * @param boolean $flush Flag OPTIONAL
     *
     * @return void
     */
    public function updateInBatchById(array $data, $flush = self::FLUSH_BY_DEFAULT)
    {
        foreach ($data as $id => $properties) {
            $this->performUpdate($this->getById($id), $properties);
        }

        if ($flush) {
            $this->flushChanges();
        }
    }

    /**
     * Delete entity
     *
     * @param \XLite\Model\AEntity $entity Entity to delete
     * @param boolean              $flush  Flag OPTIONAL
     *
     * @return void
     */
    public function delete(\XLite\Model\AEntity $entity, $flush = self::FLUSH_BY_DEFAULT)
    {
        $this->performDelete($entity);

        if ($flush) {
            $this->flushChanges();
        }
    }

    /**
     * Find entity by ID and delete it
     *
     * @param integer $id    ID of entity to delete
     * @param boolean $flush Flag OPTIONAL
     *
     * @return void
     */
    public function deleteById($id, $flush = self::FLUSH_BY_DEFAULT)
    {
        $this->delete($this->getById($id), $flush);
    }

    /**
     * Delete entities
     *
     * @param array   $entities List of entities to delete
     * @param boolean $flush    Flag OPTIONAL
     *
     * @return void
     */
    public function deleteInBatch(array $entities, $flush = self::FLUSH_BY_DEFAULT)
    {
        foreach ($entities as $entity) {
            $this->performDelete($entity);
        }

        if ($flush) {
            $this->flushChanges();
        }
    }

    /**
     * Find entities by ID and delete them
     *
     * @param array   $data  Array of <id => array(properties)> elements
     * @param boolean $flush Flag OPTIONAL
     *
     * @return void
     */
    public function deleteInBatchById(array $data, $flush = self::FLUSH_BY_DEFAULT)
    {
        foreach ($data as $id => $tmp) {
            $this->performDelete($this->getById($id));
        }

        if ($flush) {
            $this->flushChanges();
        }
    }

    /**
     * Insert single entity
     *
     * @param \XLite\Model\AEntity|array $entity Data to insert OPTIONAL
     *
     * @return \XLite\Model\AEntity|array
     */
    protected function performInsert($entity = null)
    {
        if (!isset($entity) || !($entity instanceof \XLite\Model\AEntity)) {
            $entity = new $this->_entityName((array) $entity);
        }

        // Since Doctrine lifecycle callbacks do not allow
        // to modify associations, we've added this method
        $entity->prepareEntityBeforeCommit($entity::ACTION_INSERT);

        $this->getEntityManager()->persist($entity);

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
        $entity->map($data);

        // Since Doctrine lifecycle callbacks do not allow
        // to modify associations, we've added this method
        $entity->prepareEntityBeforeCommit($entity::ACTION_UPDATE);
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
        // Since Doctrine lifecycle callbacks do not allow
        // to modify associations, we've added this method
        $entity->prepareEntityBeforeCommit($entity::ACTION_DELETE);

        $this->getEntityManager()->remove($entity);
    }

    // }}}

    /**
     * Delete all records in associated table
     *
     * @return void
     */
    public function clearAll()
    {
        $this->deleteInBatch($this->findAll());
    }

    /**
     * Iterate all models
     *
     * @return \Iterator
     */
    public function iterateAll()
    {
        return $this->createPureQueryBuilder()->iterate();
    }

    /**
     * Return name of the primary key field.
     * This method is used to determine entity persistence
     *
     * @return string
     */
    public function getPrimaryKeyField()
    {
        return $this->getClassMetadata()->getSingleIdentifierFieldName();
    }

    /**
     * Return info about model field
     *
     * @param string $field Field name
     * @param string $param Data param OPTIONAL
     *
     * @return array|mixed
     */
    public function getFieldInfo($field, $param = null)
    {
        try {
            $result = $this->getClassMetadata()->getFieldMapping($field);

        } catch (\Doctrine\ORM\Mapping\MappingException $exception) {
            $result = $this->getClassMetadata()->getAssociationMapping($field);
        }

        return \Includes\Utils\ArrayManager::getIndex($result, $param, isset($param));
    }

    /**
     * Find one by record
     *
     * @param array                $data   Record
     * @param \XLite\Model\AEntity $parent Parent model OPTIONAL
     *
     * @return \XLite\Model\AEntity|void
     */
    public function findOneByRecord(array $data, \XLite\Model\AEntity $parent = null)
    {
        $identifiers = $this->collectIdentifiersByRecord($data);

        return $identifiers ? $this->findOneBy($identifiers) : null;
    }

    /**
     * Load fixtures
     *
     * @param array                $data        Data
     * @param \XLite\Model\AEntity $parent      Entity parent callback OPTIONAL
     * @param array                $parentAssoc Entity mapped propery method OPTIONAL
     *
     * @return void
     */
    public function loadFixtures(array $data, \XLite\Model\AEntity $parent = null, array $parentAssoc = array())
    {
        list($regular, $assocs) = $this->getEntityProperties();
        foreach ($data as $record) {
            $this->loadFixture($record, $regular, $assocs, $parent, $parentAssoc);
        }
    }

    /**
     * Load fixture
     *
     * @param array                $record      Record
     * @param array                $regular     Regular fields info OPTIONAL
     * @param array                $assocs      Associations info OPTIONAL
     * @param \XLite\Model\AEntity $parent      Entity parent callback OPTIONAL
     * @param array                $parentAssoc Entity mapped propery method OPTIONAL
     *
     * @return \XLite\Model\AEntity
     */
    public function loadFixture(
        array $record,
        array $regular = array(),
        array $assocs = array(),
        \XLite\Model\AEntity $parent = null,
        array $parentAssoc = array()
    ) {
        if (!$regular || !$assocs) {
            list($regular, $assocs) = $this->getEntityProperties();
        }

        // Strongly insert entity
        $insert = \XLite\Core\Database::getInstance()->getFixturesLoadingOption('insert');
        $entity = $insert ? null : $this->findOneByRecord($record, $parent);
        if (!$entity && !$insert && $parent && $parentAssoc && $parentAssoc['getter'] && !$parentAssoc['many']) {
            $entity = $parent->$parentAssoc['getter']();
        }

        // Add specified model directive
        $addModel = \XLite\Core\Database::getInstance()->getFixturesLoadingOption('addModel');
        $isAddModel = false;
        if ($addModel == $this->_class->name) {
            $isAddModel = true;

        } elseif ($addModel
            && !$entity
            && !\XLite\Core\Database::getInstance()->getFixturesLoadingOption('isAddModel')
            && !\XLite\Core\Database::getInstance()->getFixturesLoadingOption('addParent')
        ) {
            return null;
        }

        if (!$entity) {
            $class = $this->_class->name;
            $entity = new $class;
        }

        if ($parent) {
            $this->linkLoadedEntity($entity, $parent, $parentAssoc);
        }

        if ($isAddModel) {
            \XLite\Core\Database::getInstance()->setFixturesLoadingOption('isAddModel', true);
        }

        $this->loadRawFixture($entity, $record, $regular, $assocs);

        if ($isAddModel) {
            \XLite\Core\Database::getInstance()->setFixturesLoadingOption('isAddModel');
        }

        return $entity;
    }

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
    public function loadRawFixture(\XLite\Model\AEntity $entity, array $record, array $regular = array(), array $assocs = array())
    {
        if (!$regular || !$assocs) {
            list($regular, $assocs) = $this->getEntityProperties();
        }

        if (!\XLite\Core\Database::getEM()->contains($entity)) {
            $entity->map($this->assembleRegularFieldsFromRecord($record, $regular));
            $this->insert($entity, false);

        } else {
            $this->update($entity, $this->assembleRegularFieldsFromRecord($record, $regular), false);
        }

        if ($this->flushAfterLoading) {
            \XLite\Core\Database::getEM()->flush();
        }

        foreach ($this->assembleAssociationsFromRecord($record, $assocs) as $name => $value) {
            if ($assocs[$name]['many']) {
                $assocs[$name]['repo']->loadFixtures($value, $entity, $assocs[$name]);

            } else {
                $assocs[$name]['repo']->loadFixture(
                    $value,
                    array(),
                    array(),
                    $entity,
                    $assocs[$name]
                );
            }
        }
    }

    /**
     * Get entity properties
     *
     * @return array(array)
     */
    public function getEntityProperties()
    {
        if (!isset($this->entityProperties)) {
            $cmd = $this->getClassMetadata();

            $regular = array();
            foreach (array_keys($cmd->fieldMappings) as $f) {
                $fCamelCase = \XLite\Core\Converter::convertToCamelCase($f);
                $regular[$f] = array(
                    'getter' => 'get' . $fCamelCase,
                    'setter' => 'set' . $fCamelCase,
                );
            }

            $identifiers = $cmd->identifier;

            $assocs = array();
            foreach ($cmd->associationMappings as $f => $fData) {
                $isMany = $fData['type'] == \Doctrine\ORM\Mapping\ClassMetadataInfo::ONE_TO_MANY
                    || $fData['type'] == \Doctrine\ORM\Mapping\ClassMetadataInfo::MANY_TO_MANY;

                $fCamelCase = \XLite\Core\Converter::convertToCamelCase($f);
                $targetEntity = $fData['targetEntity'];

                $assoc = array(
                    'many'         => $isMany,
                    'many2many'    => \Doctrine\ORM\Mapping\ClassMetadataInfo::MANY_TO_MANY == $fData['type'],
                    'getter'       => 'get' . $fCamelCase,
                    'setter'       => ($isMany ? 'add' : 'set') . $fCamelCase,
                    'identifiers'  => array(),
                    'entityName'   => $targetEntity,
                    'mappedGetter' => null,
                    'mappedSetter' => null,
                    'repo'         => \XLite\Core\Database::getRepo($targetEntity),
                );

                foreach (\XLite\Core\Database::getEM()->getClassMetadata($targetEntity)->identifier as $ident) {
                    $identCamelCase = \XLite\Core\Converter::convertToCamelCase($ident);
                    $assoc['identifiers'][$ident] = array(
                        'getter' => 'get' . $identCamelCase,
                        'setter' => 'set' . $identCamelCase,
                    );
                }

                if ($fData['mappedBy']) {
                    $mappedCamelCase = \XLite\Core\Converter::convertToCamelCase($fData['mappedBy']);
                    $assoc['mappedGetter'] = 'get' . $mappedCamelCase;

                    if ($assoc['many2many']) {
                        $assoc['mappedSetter'] = 'add' . $mappedCamelCase;

                    } else {
                        $assoc['mappedSetter'] = 'set' . $mappedCamelCase;
                    }
                }

                $assocs[$f] = $assoc;
            }

            $this->entityProperties = array($regular, $assocs, $identifiers);
        }

        return $this->entityProperties;
    }

    /**
     * Unload fixtures
     *
     * @param array                $data        Data
     * @param \XLite\Model\AEntity $parent      Entity parent callback OPTIONAL
     * @param array                $parentAssoc Entity mapped propery method OPTIONAL
     *
     * @return integer
     */
    public function unloadFixtures(array $data, \XLite\Model\AEntity $parent = null, array $parentAssoc = array())
    {
        $result = 0;

        list($regular, $assocs) = $this->getEntityProperties();
        foreach ($data as $record) {
            $result += $this->unloadFixture($record, $regular, $assocs, $parent, $parentAssoc);
        }

        return $result;
    }

    /**
     * Unload fixture
     *
     * @param array                $record      Record
     * @param array                $regular     Regular fields info OPTIONAL
     * @param array                $assocs      Associations info OPTIONAL
     * @param \XLite\Model\AEntity $parent      Entity parent callback OPTIONAL
     * @param array                $parentAssoc Entity mapped propery method OPTIONAL
     *
     * @return \XLite\Model\AEntity
     */
    public function unloadFixture(
        array $record,
        array $regular = array(),
        array $assocs = array(),
        \XLite\Model\AEntity $parent = null,
        array $parentAssoc = array()
    ) {

        $result = 0;

        if (!$regular || !$assocs) {
            list(, $assocs) = $this->getEntityProperties();
        }

        $entity = $this->findOneByRecord($record, $parent);

        if ($entity) {
            $addModel = \XLite\Core\Database::getInstance()->getFixturesLoadingOption('addModel');

            if (!$addModel || $addModel == $this->_class->name) {
                \XLite\Core\Database::getEM()->remove($entity);
                $result++;

            } elseif ($addModel) {
                // Search target model into children list
                foreach ($this->assembleAssociationsFromRecord($record, $assocs) as $name => $value) {
                    if ($assocs[$name]['many']) {
                        $result += $assocs[$name]['repo']->unloadFixtures($value, $entity, $assocs[$name]);

                    } else {
                        $result += $assocs[$name]['repo']->unloadFixture(
                            $value,
                            array(),
                            array(),
                            $entity,
                            $assocs[$name]
                        );
                    }
                }

            }
        }

        return $result;
    }

    /**
     * Process DB schema
     *
     * @param array  $schema Schema
     * @param string $type   Schema type
     *
     * @return array
     */
    public function processSchema(array $schema, $type)
    {
        if (\XLite\Core\Database::SCHEMA_UPDATE == $type || \XLite\Core\Database::SCHEMA_CREATE == $type) {
            // Do not remove TABLES AND FOREIGN KEYS
            list($disabledTables, $disabledColumns) = \XLite\Core\Database::getInstance()
                ->getDisabledStructuresToStore();

            // Do not remove TABLES
            list($enabledTables, ) = \XLite\Core\Database::getInstance()
                ->getEnabledStructuresToStore();

            // Do not drop disabled tables and foreign keys
            foreach ($disabledTables as $i => $t) {
                $disabledTables[$i] = preg_quote($t, '/');
            }

            foreach ($enabledTables as $i => $t) {
                $enabledTables[$i] = preg_quote($t, '/');
            }

            $tablePrefix = preg_quote(\XLite\Core\Database::getInstance()->getTablePrefix(), '/');

            if ($disabledTables) {
                $schema = preg_grep(
                    '/ALTER TABLE `' . $tablePrefix . '(?:' . implode('|', $disabledTables) . ')` DROP FOREIGN KEY /Ss',
                    $schema,
                    PREG_GREP_INVERT
                );

                $schema = preg_grep(
                    '/ALTER TABLE `' . $tablePrefix . '(?:' . implode('|', $disabledTables) . ')` ADD CONSTRAINT [a-zA-Z0-9-_]+ FOREIGN KEY /Ss',
                    $schema,
                    PREG_GREP_INVERT
                );

                $schema = preg_grep(
                    '/DROP TABLE IF EXISTS `' . $tablePrefix . '(?:' . implode('|', $disabledTables + $enabledTables) . ')`/Ss',
                    $schema,
                    PREG_GREP_INVERT
                );

                // Do not drop foreign keys referenced to the disabled tables

                preg_match_all(
                    '/ALTER TABLE `([a-zA-Z0-9-_]+)` DROP FOREIGN KEY `?([a-zA-Z0-9-_]+)`?/Ss',
                    implode(';', $schema),
                    $droppedKeys
                );

                if (!empty($droppedKeys[0]) && is_array($droppedKeys[0])) {
                    $filterOnDropKeys = array();
                    $len = strlen($tablePrefix);

                    for ($i = 0; $i < count($droppedKeys[0]); $i++) {
                        $foreignKeys = \XLite\Core\Database::getEM()->getConnection()->getSchemaManager()->listTableForeignKeys($droppedKeys[1][$i]);

                        if (!empty($foreignKeys)) {
                            foreach ($foreignKeys as $fkey) {
                                if (in_array(substr($fkey->getForeignTableName(), $len), $disabledTables)) {
                                    $filterOnDropKeys[] = substr($fkey->getName(), 3); // Cut off 'FK_' from key name
                                }
                            }
                        }
                    }

                    if ($filterOnDropKeys) {
                        $filterOnDropKeys = array_unique($filterOnDropKeys);

                        $schema = preg_grep(
                            '/ALTER TABLE `[a-zA-Z0-9-_]+` DROP FOREIGN KEY FK_(?:' . implode('|', $filterOnDropKeys) . ')/Ss',
                            $schema,
                            PREG_GREP_INVERT
                        );

                        $schema = preg_grep(
                            '/DROP INDEX `IDX_(?:' . implode('|', $filterOnDropKeys) . ')` ON `[a-zA-Z0-9-_]+`/Ss',
                            $schema,
                            PREG_GREP_INVERT
                        );
                    }
                }
            }

            // Do not drop disabled columns
            foreach ($disabledColumns as $t => $fields) {
                $t = preg_quote($t, '/');

                foreach ($fields as $f => $change) {
                    $f = preg_quote($f, '/');

                    if (preg_match('/NOT NULL/Ss', $change) || !preg_match('/^.+NULL/Ss', $change)) {
                        // Change NOT NULL to NULL
                        $change = preg_replace('/^([^`]\S*[^`])\s/', '`\1` ', $change);
                        if (preg_match('/NOT NULL/Ss', $change)) {
                            $change = preg_replace('/\s+NOT NULL/Ss', ' NULL', $change);

                        } else {
                            $change = preg_replace('/^(.+)/Ss', '\1 NULL ', $change);
                        }

                        $schema = preg_replace(
                            '/^(ALTER TABLE `' . $tablePrefix . $t . '`.*) DROP `?' . $f . '`?(,|$)/Ss',
                            '\1 MODIFY ' . $change . '\2',
                            $schema
                        );

                    } else {
                        $schema = preg_replace(
                            '/(ALTER TABLE `' . $tablePrefix . $t . '`.*) DROP `?' . $f . '`?(, |$)/Ss',
                            '\1 ',
                            $schema
                        );
                    }
                }
            }

            // Assign columns' character sets
            foreach ($this->columnsCharSets as $column => $charset) {
                $schema = preg_replace(
                    '/(' . $this->_class->getTableName() .'.+' . $column . '\s+'
                    . '(?:char|varchar|tinytext|text|mediumtext|longtext)(?:\(\d+\))?)/Ssi',
                    '$1 CHARACTER SET ' . $charset,
                    $schema
                );
            }

            // Clear empty ALTER TABLE
            $schema = preg_replace(
                '/ALTER TABLE `' . $tablePrefix . '[^`]+`\s*$/Ss',
                '',
                $schema
            );
        }

        return $schema;
    }

    /**
     * Check if it's possible to disable model DB table
     *
     * @return boolean
     */
    public function canDisableTable()
    {
        return true;
    }

    // {{{ Cache

    /**
     * Get cache driver
     *
     * @return \XLite\Core\Cache\Registry
     */
    public static function getCacheDriver()
    {
        if (!isset(static::$cacheDriver)) {
            static::$cacheDriver = new \XLite\Core\Cache\Registry('datacache');
        }

        return static::$cacheDriver;
    }

    /**
     * Clean up all cache cells
     *
     * @return void
     */
    public function cleanCache()
    {
        array_map(array($this, 'deleteCache'), array_keys($this->defineCacheCells()));
    }

    /**
     * Get public class metadata
     *
     * @return array
     */
    public function getPublicClassMetadata()
    {
        return $this->getClassMetadata();
    }

    /**
     * Define cache cells
     *
     * @return array
     */
    protected function defineCacheCells()
    {
        return array();
    }

    /**
     * Get cache cells
     *
     * @param string $key Cell name OPTIONAL
     *
     * @return array
     */
    protected function getCacheCells($key = null)
    {
        if (!isset($this->cacheCells)) {
            $this->cacheCells = $this->restoreCacheCells();
        }

        return $key
            ? (isset($this->cacheCells[$key]) ? $this->cacheCells[$key] : null)
            : $this->cacheCells;
    }

    /**
     * Restore cache cells info from cache
     *
     * @return array
     */
    protected function restoreCacheCells()
    {
        $key = $this->getHashPrefix('cells');

        $cacheCells = \XLite\Model\Repo\ARepo::getCacheDriver()->fetch($key);

        if (!is_array($cacheCells)) {
            $cacheCells = $this->defineCacheCells();

            list($cacheCells, $relations) = $this->postprocessCacheCells($cacheCells);

            \XLite\Model\Repo\ARepo::getCacheDriver()->save($key, $cacheCells, self::CACHE_DEFAULT_TTL);

            // Save relations to current model cache cells from related models
            foreach ($relations as $model => $cells) {
                \XLite\Core\Database::getRepo($model)->addCacheRelations($cells);
            }
        }

        return $cacheCells;
    }

    /**
     * Postprocess cache cells info
     *
     * @param array $cacheCells Cache cells
     *
     * @return array (cache cells & relations data)
     */
    protected function postprocessCacheCells(array $cacheCells)
    {
        $relations = array();

        // Normalize cache cells
        foreach ($cacheCells as $name => $cell) {
            // Set default cell type
            if (!isset($cell[self::KEY_TYPE_CACHE_CELL])) {
                $cell[self::KEY_TYPE_CACHE_CELL] = self::DEFAULT_KEY_TYPE;
            }

            // Set default cell attributes list
            if (!isset($cell[self::ATTRS_CACHE_CELL])) {
                $cell[self::ATTRS_CACHE_CELL] = null;
            }

            // Set default cell relations list
            if (!isset($cell[self::RELATION_CACHE_CELL])) {
                $cell[self::RELATION_CACHE_CELL] = array();
            }

            // Collect related models
            foreach ($cell[self::RELATION_CACHE_CELL] as $model) {
                if (!isset($relations[$model])) {
                    $relations[$model] = array($this->_entityName => array($name));

                } elseif (!isset($relations[$model][$this->_entityName])) {
                    $relations[$model][$this->_entityName] = array($name);

                } else {
                    $relations[$model][$this->_entityName][] = $name;
                }
            }

            // Set cell attributes converter method name
            $method = $this->getCacheParamsConverterName($name);
            $cell[self::CONVERTER_CACHE_CELL] = method_exists($this, $method)
                ? $method
                : false;

            // Set cell hash generator method name
            if (self::CACHE_CUSTOM_KEY == $this->cacheCells[$name][self::KEY_TYPE_CACHE_CELL]) {
                $cell[self::GENERATOR_CACHE_CELL] = $this->getCacheHashGeneratorName($name);
            }

            $cacheCells[$name] = $cell;
        }

        return array($cacheCells, $relations);
    }

    /**
     * Get data from cache
     *
     * @param string $name   Cache cell name
     * @param array  $params Cache cell parameters OPTIONAL
     *
     * @return mixed|void
     */
    protected function getFromCache($name, array $params = array())
    {
        $result = null;
        $cell = $this->getCacheCells($name);

        if ($cell) {
            $result = \XLite\Model\Repo\ARepo::getCacheDriver()->fetch(
                $this->getCellHash($name, $cell, $params)
            );

        } else {
            // TODO - throw exception
        }

        return (isset($result) && false !== $result) ? $result : null;
    }

    /**
     * Save data to cache
     *
     * @param mixed  $data   Data
     * @param string $name   Cache cell name
     * @param array  $params Cache cell parameters OPTIONAL
     *
     * @return void
     */
    protected function saveToCache($data, $name, array $params = array())
    {
        $cell = $this->getCacheCells($name);

        if ($cell) {
            if ($data instanceof \ArrayAccess) {
                $this->detachList($data);

            } elseif ($data instanceof \XLite\Model\AEntity) {
                $data->detach();
            }

            \XLite\Model\Repo\ARepo::getCacheDriver()->save(
                $this->getCellHash($name, $cell, $params),
                $data,
                self::CACHE_DEFAULT_TTL
            );

        } else {
            // TODO - add throw exception
        }
    }

    /**
     * Get cell hash
     *
     * @param string $name   Cell name
     * @param array  $cell   Cell
     * @param array  $params Cache parameters
     *
     * @return string|void
     */
    protected function getCellHash($name, array $cell, array $params)
    {
        $hash = null;

        if (self::CACHE_ATTR_KEY == $cell[self::KEY_TYPE_CACHE_CELL]) {
            $hash = implode('.', $params);

        } elseif (self::CACHE_HASH_KEY == $cell[self::KEY_TYPE_CACHE_CELL]) {
            $hash = md5(implode('.', $params));

        } elseif (self::CACHE_CUSTOM_KEY == $cell[self::KEY_TYPE_CACHE_CELL]) {
            $hash = $this->{$cell[self::GENERATOR_CACHE_CELL]}($params);
        }

        if (isset($hash) && empty($hash)) {
            $hash = self::EMPTY_CACHE_CELL;
        }

        return $this->getHashPrefix() . '.' . $name . '.' . $hash;
    }

    /**
     * Get prefix for cache key
     *
     * @param string $suffix Cache subsection name OPTIONAL
     *
     * @return string
     */
    protected function getHashPrefix($suffix = 'data')
    {
        return str_replace('\\', '_', substr($this->_entityName, 6)) . '.' . $suffix;
    }

    /**
     * Get cell cache key generator method name
     *
     * @param string $name Cell name
     *
     * @return string
     */
    protected function getCacheHashGeneratorName($name)
    {
        return 'getCacheHash' . \XLite\Core\Converter::convertToCamelCase($name);
    }

    /**
     * Get cell cache parameters converter method name
     *
     * @param string $name Cell name
     *
     * @return string
     */
    protected function getCacheParamsConverterName($name)
    {
        return 'convertRecordToParams' . \XLite\Core\Converter::convertToCamelCase($name);
    }

    // }}}

    /**
     * Get Query builder main alias
     *
     * @param \Doctrine\ORM\QueryBuilder $qb Query builder
     *
     * @return string
     */
    protected function getMainAlias(\Doctrine\ORM\QueryBuilder $qb)
    {
        return $qb->getMainAlias();
    }

    /**
     * Get query builder
     *
     * @return \XLite\Model\QueryBuilder\AQueryBuilder
     */
    protected function getQueryBuilder()
    {
        if (!isset($this->queryBuilderClass)) {
            $this->queryBuilderClass = str_replace('\\Repo\\', '\\QueryBuilder\\', get_called_class());

            if (!\XLite\Core\Operator::isClassExists($this->queryBuilderClass)) {
                $this->queryBuilderClass = '\XLite\Model\QueryBuilder\Base\Common';
            }
        }

        $class = $this->queryBuilderClass;

        return new $class($this->_em);
    }

    /**
     * Define query for count() method
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    protected function defineCountQuery()
    {
        return $this->createPureQueryBuilder();
    }

    /**
     * Define query for countBy() method
     *
     * @param array $conditions Conditions
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    protected function defineCountByQuery(array $conditions)
    {
        $qb = $this->createPureQueryBuilder();
        $alias = $this->getMainAlias($qb);
        foreach ($conditions as $name => $value) {
            $qb->andWhere($alias . '.' . $name . ' = :' . $name)
                ->setParameter($name, $value);
        }

        return $qb->select('COUNT(' . implode(', ', $this->getIdentifiersList($qb)) . ')')
            ->setMaxResults(1);
    }

    /**
     * Define query for 'findFrame()' finder
     *
     * @param integer $start Start offset
     * @param integer $limit Frame length
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    protected function defineFrameQuery($start, $limit)
    {
        return $this->createPureQueryBuilder()->setFrameResults($start, $limit);
    }

    /**
     * Get identifiers list for specified query builder object
     *
     * @param \Doctrine\ORM\QueryBuilder $qb Query builder
     *
     * @return array
     */
    protected function getIdentifiersList(\Doctrine\ORM\QueryBuilder $qb)
    {
        $alias = $this->getMainAlias($qb);

        $list = array();

        foreach ($this->_class->identifier as $i) {
            $list[] = $alias . '.' . $i;
        }

        return $list;
    }

    /**
     * Detach entities list
     *
     * @param mixed $data Entites list
     *
     * @return array
     */
    protected function detachList($data)
    {
        if (is_array($data) || $data instanceof \ArrayAccess) {
            foreach ($data as $item) {
                $item->detach();
            }
        }

        return $data;
    }

    /**
     * Search entity by key.
     * If it's not found, the exception will be thrown
     *
     * @param integer $id Entity ID
     *
     * @return \XLite\Model\AEntity
     * @throws \Exception
     */
    protected function getById($id)
    {
        $entity = $this->find($id);

        if (!$entity) {
            throw new \Exception(get_class($this) . '::getById() - unknown ID (' . $id . ')');
        }

        return $entity;
    }

    /**
     * getAllowedModifiers
     *
     * @return array
     */
    protected function getAllowedModifiers()
    {
        return array('insert', 'update', 'delete');
    }

    /**
     * Pattern to check called method names
     *
     * @return string
     */
    protected function getModifierPattern()
    {
        return '/(' . implode('|', $this->getAllowedModifiers()) . ')(InBatch)?(ById)?/Si';
    }

    /**
     * Collect identifiers array by record
     *
     * @param array $data Record
     *
     * @return array(mixed)|boolean
     */
    protected function collectIdentifiersByRecord(array $data)
    {
        $identifiers = array();
        $found = false;

        list(, , $classIdentifiers) = $this->getEntityProperties();
        if ($classIdentifiers) {
            $found = true;
            foreach ($classIdentifiers as $ident) {
                if (!isset($data[$ident])) {
                    $found = false;
                    break;
                }

                $identifiers[$ident] = $data[$ident];
            }
        }

        if (!$found) {
            $identifiers = $this->collectAlternativeIdentifiersByRecord($data);
            if ($identifiers) {
                $found = true;
            }
        }

        return $found ? $identifiers : false;
    }

    /**
     * Collect alternative identifiers by record
     *
     * @param array $data Record
     *
     * @return boolean|array(mixed)
     */
    protected function collectAlternativeIdentifiersByRecord(array $data)
    {
        $found = false;
        $identifiers = array();

        if ($this->alternativeIdentifier) {
            // Collect identifiers by alternative unqiue keys
            foreach ($this->alternativeIdentifier as $keys) {
                foreach ($keys as $key) {
                    $found = true;
                    if (!isset($data[$key])) {
                        $found = false;
                        break;
                    }

                    $identifiers[$key] = $data[$key];
                }

                if ($found) {
                    break;
                }
            }
        }

        return $found ? $identifiers : false;
    }

    /**
     * Assemble regular fields from record
     *
     * @param array $record  Record
     * @param array $regular Regular fields info OPTIONAL
     *
     * @return array
     */
    protected function assembleRegularFieldsFromRecord(array $record, array $regular = array())
    {
        if (!$regular) {
            list($regular, ) = $this->getEntityProperties();
        }

        return array_intersect_key($record, $regular);
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
        if (!$assocs) {
            list(, $assocs) = $this->getEntityProperties();
        }

        $record = array_intersect_key($record, $assocs);
        foreach ($record as $name => $value) {
            if (!is_array($value)) {
                unset($record[$name]);
            }
        }

        return $record;

    }

    /**
     * Link loaded entity to parent object
     *
     * @param \XLite\Model\AEntity $entity      Loaded entity
     * @param \XLite\Model\AEntity $parent      Entity parent callback
     * @param array                $parentAssoc Entity mapped propery method
     *
     * @return void
     */
    protected function linkLoadedEntity(\XLite\Model\AEntity $entity, \XLite\Model\AEntity $parent, array $parentAssoc)
    {
        if (!$parentAssoc['many']
            || !$entity->getUniqueIdentifier()
            || !$parent->$parentAssoc['getter']()->contains($entity)
        ) {
            // Add entity to parent
            $parent->$parentAssoc['setter']($entity);

            // Add parent to entity
            if ($parentAssoc['mappedSetter']) {
                $entity->$parentAssoc['mappedSetter']($parent);
            }
        }
    }

    /**
     * Get calculated field
     *
     * @param \XLite\Model\QueryBuilder\AQueryBuilder $queryBuilder Query builder
     * @param string                                  $name         Field name
     *
     * @return string
     */
    protected function getCalculatedField(\XLite\Model\QueryBuilder\AQueryBuilder $queryBuilder, $name)
    {
        $uname = ucfirst($name);
        $method = 'defineCalculated' . $uname . 'DQL';

        if (method_exists($this, $method)
            && !$queryBuilder->getDataCell(static::CALCULATED_FIELD_FLAG . $name)
        ) {
            $queryBuilder->setDataStorage(
                static::CALCULATED_FIELD_FLAG . $name,
                $this->$method($queryBuilder, $queryBuilder->getRootAlias())
            );
        }

        return $queryBuilder->getDataCell(static::CALCULATED_FIELD_FLAG . $name);
    }

    /**
     * Assign calculated field
     *
     * @param \XLite\Model\QueryBuilder\AQueryBuilder $queryBuilder Query builder
     * @param string                                  $name         Field name
     *
     * @return \XLite\Model\QueryBuilder\AQueryBuilder
     */
    protected function assignCalculatedField(\XLite\Model\QueryBuilder\AQueryBuilder $queryBuilder, $name)
    {
        $uname = ucfirst($name);
        $method = 'defineCalculated' . $uname . 'DQL';
        if (method_exists($this, $method)
            && is_null($queryBuilder->getDataCell(static::CALCULATED_FIELD_FLAG . $name))
        ) {
            $value = $this->$method($queryBuilder, $queryBuilder->getRootAlias());
            $queryBuilder->addSelect($value . ' calculated' . $uname);
            $queryBuilder->setDataStorage(static::CALCULATED_FIELD_FLAG . $name, $value);
        }

        return $queryBuilder;
    }

    /**
     * Helper to get a valid value for {sort, order} pair.
     * It returns  array($sort, null)   value if $value = array($sort)
     * and returns array($sort, $order) value if $value = array($sort, $order)
     *
     * @param array $value
     *
     * @return array
     */
    protected function getSortOrderValue($value)
    {
        $order = null;

        if (count($value) < 2) {
            list($sort) = $value;
        } else {
            list($sort, $order) = $value;
        }

        return array($sort, $order);
    }

    // {{{ Export routines

    /**
     * Count items for export routine
     *
     * @return integer
     */
    public function countForExport()
    {
        return intval($this->defineCountForExportQuery()->getSingleScalarResult());
    }

    /**
     * Define items iterator
     *
     * @param integer $position Position OPTIONAL
     *
     * @return \Doctrine\ORM\Internal\Hydration\IterableResult
     */
    public function getExportIterator($position = 0)
    {
        return $this->defineExportIteratorQueryBuilder($position)
            ->iterate();
    }

    /**
     * Define query builder for COUNT query
     *
     * @return \XLite\Model\QueryBuilder\AQueryBuilder
     */
    protected function defineCountForExportQuery()
    {
        $qb = $this->createQueryBuilder();

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
        return $this->createQueryBuilder()
            ->setFirstResult($position)
            ->setMaxResults(1000000000);
    }

    // }}}

    // {{{ Import

    /**
     * Find model by import conditions
     *
     * @param array $conditions Conditions
     *
     * @return \XLite\Model\AEntity
     */
    public function findOneByImportConditions(array $conditions)
    {
        return $this->defineFindOneByImportConditionsQueryBuilder($conditions)->getSingleResult();
    }

    /**
     * Define import query builder
     *
     * @param array $conditions Conditions
     *
     * @return \XLite\Model\QueryBuilder\AQueryBuilder
     */
    protected function defineFindOneByImportConditionsQueryBuilder(array $conditions)
    {
        $qb = $this->createQueryBuilder();
        foreach ($conditions as $name => $value) {
            $this->addImportCondition($qb, $name, $value);
        }

        return $qb;
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
        $alias = $qb->getMainAlias();
        if (property_exists($this->getClassMetadata()->name, $name)) {
            $qb->andWhere($alias . '.' . $name . ' = :' . $name);

        } elseif (property_exists($this->getClassMetadata()->name, 'translations')) {
            $qb->andWhere('translations.' . $name . ' = :'. $name);
            if (is_array($value)) {
                $value = $value[\XLite\Logic\Import\Importer::getLanguageCode()];
            }
        }

        $qb->setParameter($name, $value);
    }

    // }}}
}
