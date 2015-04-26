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
 * Zone repository
 */
class Zone extends \XLite\Model\Repo\ARepo
{
    /**
     * Common search parameters
     */
    const P_ORDER_BY = 'orderBy';
    const P_LIMIT    = 'limit';


    /**
     * Repository type
     *
     * @var string
     */
    protected $type = self::TYPE_SECONDARY;

    /**
     * Alternative record identifiers
     *
     * @var array
     */
    protected $alternativeIdentifier = array(
        array('zone_name'),
    );

    /**
     * Current search condition
     *
     * @var \XLite\Core\CommonCell
     */
    protected $currentSearchCnd = null;

    // {{{ defineCacheCells

    /**
     * Define cache cells
     *
     * @return array
     */
    protected function defineCacheCells()
    {
        $list = parent::defineCacheCells();

        $list['all'] = array(
            self::RELATION_CACHE_CELL => array('\XLite\Model\Zone'),
        );

        $list['default'] = array(
            self::RELATION_CACHE_CELL => array('\XLite\Model\Zone'),
        );

        $list['zone'] = array(
            self::ATTRS_CACHE_CELL    => array('zone_id'),
            self::RELATION_CACHE_CELL => array('\XLite\Model\Zone'),
        );

        return $list;
    }

    // }}}

    // {{{ findAllZones

    /**
     * findAllZones
     *
     * @return array
     */
    public function findAllZones()
    {
        $data = $this->getFromCache('all');

        if (!isset($data)) {
            $data = $this->defineFindAllZones()->getResult();
            $this->saveToCache($data, 'all');
        }

        return $data;
    }

    /**
     * defineGetZones
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    protected function defineFindAllZones()
    {
        return $this->createQueryBuilder()
            ->addSelect('ze')
            ->leftJoin('z.zone_elements', 'ze')
            ->addOrderBy('z.is_default', 'DESC')
            ->addOrderBy('z.zone_name');
    }

    // }}}

    // {{{ findZone

    /**
     * findZone
     *
     * @param integer $zoneId Zone Id
     *
     * @return \XLite\Model\Zone
     */
    public function findZone($zoneId)
    {
        $data = $this->getFromCache('zone', array('zone_id' => $zoneId));

        if (!isset($data)) {
            $data = $this->defineFindZone($zoneId)->getSingleResult();

            if ($data) {
                $this->saveToCache($data, 'zone', array('zone_id' => $zoneId));
            }
        }

        return $data;
    }

    /**
     * defineGetZone
     *
     * @param mixed $zoneId Zone id
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    protected function defineFindZone($zoneId)
    {
        return $this->createQueryBuilder()
            ->addSelect('ze')
            ->leftJoin('z.zone_elements', 'ze')
            ->andWhere('z.zone_id = :zoneId')
            ->setParameter('zoneId', $zoneId);
    }

    // }}}

    // {{{ findApplicableZones

    /**
     * Get the zones list applicable to the specified address
     *
     * @param array $address Address data
     *
     * @return array
     */
    public function findApplicableZones($address)
    {
        if (is_numeric($address['state'])) {
            $address['state'] = \XLite\Core\Database::getRepo('XLite\Model\State')->getCodeById($address['state']);
        }

        // Get all zones list
        $allZones = $this->findAllZones();
        $applicableZones = array();

        // Get the list of zones that are applicable for address
        /** @var \XLite\Model\Zone $zone */
        foreach ($allZones as $zone) {
            $zoneWeight = $zone->getZoneWeight($address);

            if (0 < $zoneWeight) {
                $applicableZones[] = array(
                    'weight' => $zoneWeight,
                    'zone' => $zone,
                );
            }
        }

        // Sort zones list by weight in reverse order
        usort($applicableZones, function ($a, $b) {
            return $a['weight'] == $b['weight']
                ? 0
                : (($a['weight'] > $b['weight']) ? -1 : 1);
        });

        $result = array();
        foreach ($applicableZones as $zone) {
            $result[] = $zone['zone'];
        }

        return $result;
    }

    /**
     * Return default zone
     *
     * @return \XLite\Model\Zone
     */
    protected function getDefaultZone()
    {
        $result = $this->getFromCache('default');

        if (!isset($result)) {
            $result = $this->findOneBy(array('is_default' => 1));
            $this->saveToCache($result, 'default');
        }

        return $result;
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

        $queryBuilder->addOrderBy('z.is_default', 'DESC');

        foreach ($this->currentSearchCnd as $key => $value) {
            if (self::P_LIMIT != $key || !$countOnly) {
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
        $qb->select('COUNT(z.zone_id)');

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
     * Return list of handling search params
     *
     * @return array
     */
    protected function getHandlingSearchParams()
    {
        return array(
            static::P_ORDER_BY,
            static::P_LIMIT,
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
     * @param boolean                    $countOnly    Count oly flag OPTIONAL
     *
     * @return void
     */
    protected function callSearchConditionHandler($value, $key, \Doctrine\ORM\QueryBuilder $queryBuilder, $countOnly = false)
    {
        if ($this->isSearchParamHasHandler($key)) {
            $methodName = 'prepareCnd' . ucfirst($key);
            // $methodName is assembled from 'prepareCnd' + key
            $this->$methodName($queryBuilder, $value, $countOnly);

        } else {
            // TODO - add logging here
        }
    }

    // }}}
}
