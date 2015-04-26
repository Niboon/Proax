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
 * The "address field" model repository
 */
class AddressField extends \XLite\Model\Repo\Base\I18n
{
    /**
     * Allowable search params
     */
    const CND_LIMIT    = 'limit';
    const CND_ENABLED  = 'enabled';
    const CND_REQUIRED = 'required';
    const CND_WITHOUT_CSTATE = 'withoutCState';

    /**
     * Default 'order by' field name
     *
     * @var string
     */
    protected $defaultOrderBy = 'position';

    /**
     * currentSearchCnd
     *
     * @var \XLite\Core\CommonCell
     */
    protected $currentSearchCnd = null;


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
        $queryBuilder = $countOnly
            ? $this->createPureQueryBuilder()
            : $this->createQueryBuilder();

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
        $qb->select('COUNT(DISTINCT ' . $this->getMainAlias($qb) . '.' . $this->getPrimaryKeyField() . ')');

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
     * Get all enabled address fields
     *
     * @return \Doctrine\ORM\PersistentCollection|integer
     */
    public function findAllEnabled()
    {
        return $this->search(new \XLite\Core\CommonCell(array('enabled' => true)));
    }

    /**
     * Return address field service name value
     *
     * @param \XLite\Model\AddressField $field
     *
     * @return string
     */
    public function getServiceName(\XLite\Model\AddressField $field)
    {
        return $field->getServiceName();
    }

    /**
     * Get billing address-specified required fields
     *
     * @return array
     */
    public function getBillingRequiredFields()
    {
        return $this->findRequiredFields();
    }

    /**
     * Get shipping address-specified required fields
     *
     * @return array
     */
    public function getShippingRequiredFields()
    {
        return $this->findRequiredFields();
    }

    /**
     * Get all enabled and required address fields
     *
     * @return array
     */
    public function findRequiredFields()
    {
        return array_map(array($this, 'getServiceName'), $this->search(
            new \XLite\Core\CommonCell(array(
                'enabled' => true,
                'required' => true,
            )
        )));
    }

    /**
     * Get all enabled and required address fields
     *
     * @return array
     */
    public function findEnabledFields()
    {
        return array_map(
            array($this, 'getServiceName'),
            $this->search(
                new \XLite\Core\CommonCell(array('enabled' => true,))
            )
        );
    }

    /**
     * Find one by record
     *
     * @param array                $data   Record
     * @param \XLite\Model\AEntity $parent Parent model OPTIONAL
     *
     * @return \XLite\Model\AEntity
     */
    public function findOneByRecord(array $data, \XLite\Model\AEntity $parent = null)
    {
        if (isset($data['serviceName'])) {
            $result = $this->findOneByServiceName($data['serviceName']);

        } else {
            $result = parent::findOneByRecord($data, $parent);
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
            static::CND_LIMIT,
            static::CND_ENABLED,
            static::CND_REQUIRED,
            static::CND_WITHOUT_CSTATE,
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
     * Call corresponded method to handle a search condition
     *
     * @param mixed                      $value        Condition data
     * @param string                     $key          Condition name
     * @param \Doctrine\ORM\QueryBuilder $queryBuilder Query builder to prepare
     *
     * @return void
     */
    protected function callSearchConditionHandler($value, $key, \Doctrine\ORM\QueryBuilder $queryBuilder, $countOnly)
    {
        if ($this->isSearchParamHasHandler($key)) {
            $this->{'prepareCnd' . ucfirst($key)}($queryBuilder, $value, $countOnly);

        } else {
            // TODO - add logging here
        }
    }

    /**
     * Prepare query builder for enabled status search
     *
     * @param \Doctrine\ORM\QueryBuilder $queryBuilder
     * @param boolean                    $value
     * @param boolean                    $countOnly
     *
     * @return void
     */
    protected function prepareCndEnabled(\Doctrine\ORM\QueryBuilder $queryBuilder, $value, $countOnly)
    {
        $queryBuilder
            ->andWhere($this->getMainAlias($queryBuilder) . '.enabled = :enabled_value')
            ->setParameter('enabled_value', $value);
    }

    /**
     * Prepare query builder for required status search
     *
     * @param \Doctrine\ORM\QueryBuilder $queryBuilder
     * @param boolean                    $value
     * @param boolean                    $countOnly
     *
     * @return void
     */
    protected function prepareCndRequired(\Doctrine\ORM\QueryBuilder $queryBuilder, $value, $countOnly)
    {
        $queryBuilder
            ->andWhere($this->getMainAlias($queryBuilder) . '.required = :required_value')
            ->setParameter('required_value', $value);
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
     * Prepare query builder for required status search
     *
     * @param \Doctrine\ORM\QueryBuilder $queryBuilder
     * @param boolean                    $value
     * @param boolean                    $countOnly
     *
     * @return void
     */
    protected function prepareCndWithoutCState(\Doctrine\ORM\QueryBuilder $queryBuilder, $value, $countOnly)
    {
        if ($value) {
            $queryBuilder
                ->andWhere($this->getMainAlias($queryBuilder) . '.serviceName != :cstate')
                ->setParameter('cstate', 'custom_state');
        }
    }

}
