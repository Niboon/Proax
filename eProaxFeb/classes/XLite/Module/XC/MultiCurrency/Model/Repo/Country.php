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

namespace XLite\Module\XC\MultiCurrency\Model\Repo;

/**
 * Country
 */
class Country extends \XLite\Model\Repo\Country implements \XLite\Base\IDecorator
{
    const P_ORDER_BY_ACTIVE_CURRENCY = 'orderByActiveCurrency';

    const P_ACTIVE_CURRENCY = 'activeCurrency';
    const P_ENABLED = 'enabled';

    /**
     * Check if country has assigned currencies
     *
     * @param \XLite\MOdel\Country $country Country
     *
     * @return boolean
     */
    public function hasAssignedCurrencies($country)
    {
        if (isset($country)) {
            $count = $this->createPureQueryBuilder('c')
                ->select('COUNT (DISTINCT c.code)')
                ->innerJoin('c.active_currency', 'ac')
                ->andWhere('ac.enabled = :enabled')
                ->andWhere('c.code = :country_code')
                ->setParameter('country_code', $country->getCode())
                ->setParameter('enabled', true)
                ->getSingleScalarResult();
        } else {
            $count = 0;
        }

        return $count > 0;
    }

    /**
     *  Prepare certain search condition
     *
     * @param \Doctrine\ORM\QueryBuilder $queryBuilder Query builder
     * @param integer                    $value        Active currency ID
     *
     * @return void
     */
    protected function prepareCndActiveCurrency(\Doctrine\ORM\QueryBuilder $queryBuilder, $value)
    {
        $or = new \Doctrine\ORM\Query\Expr\Orx();

        $or->add('c.active_currency IS NULL')
            ->add('c.active_currency = :active_currency_id');

        $queryBuilder->andWhere($or)
            ->setParameter('active_currency_id', $value);
    }

    /**
     *  Prepare certain search condition
     *
     * @param \Doctrine\ORM\QueryBuilder $queryBuilder Query builder
     * @param integer                    $value        Active currency ID
     * @param boolean                    $countOnly    "Count only" flag. Do not need to add "order by" clauses if only count is needed.
     *
     * @return void
     */
    protected function prepareCndOrderByActiveCurrency(\Doctrine\ORM\QueryBuilder $queryBuilder, $value, $countOnly)
    {
        if (!$countOnly) {
            $queryBuilder->addOrderBy('c.active_currency', $value);
        }
    }

    /**
     *  Prepare certain search condition
     *
     * @param \Doctrine\ORM\QueryBuilder $queryBuilder Query builder
     * @param integer                    $value        Active currency ID
     *
     * @return void
     */
    protected function prepareCndEnabled(\Doctrine\ORM\QueryBuilder $queryBuilder, $value)
    {
        $queryBuilder->andWhere('c.enabled = :is_enabled')
            ->setParameter('is_enabled', (boolean) $value);
    }

    /**
     * Return list of handling search params
     *
     * @return array
     */
    protected function getHandlingSearchParams()
    {
        $return = parent::getHandlingSearchParams();

        $return[] = static::P_ORDER_BY_ACTIVE_CURRENCY;
        $return[] = static::P_ACTIVE_CURRENCY;
        $return[] = static::P_ENABLED;

        return $return;
    }
}