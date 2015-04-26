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

namespace XLite\Module\CDev\Paypal\Model\Repo\Payment;

/**
 * Payment method model repository
 */
class Method extends \XLite\Model\Repo\Payment\Method implements \XLite\Base\IDecorator
{
    /**
     * Find payment methods by specified type for dialog 'Add payment method'
     *
     * @param string $type Payment method type
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function findForAdditionByType($type)
    {
        $result = array();
        $methods = parent::findForAdditionByType($type);
        foreach ($methods as $m) {
            $result[] = $m[0];
        }

        return $result;
    }

    /**
     * Define query for findAdditionByType()
     *
     * @param \Doctrine\ORM\QueryBuilder $qb Query builder
     * 
     * @return \Doctrine\ORM\QueryBuilder
     */
    protected function addOrderByForAdditionByTypeQuery($qb)
    {
        $qb->addSelect('LOCATE(:modulePrefix, m.class) module_prefix')
            ->addOrderBy('module_prefix', 'desc')
            ->setParameter('modulePrefix', 'Module\\CDev\\Paypal');

        return parent::addOrderByForAdditionByTypeQuery($qb);
    }

    /**
     * Prepare certain search condition for moduleEnabled flag
     *
     * @param \Doctrine\ORM\QueryBuilder $queryBuilder Query builder to prepare
     * @param boolean                    $value        Condition data
     * @param boolean                    $countOnly    "Count only" flag
     *
     * @return void
     */
    protected function prepareCndModuleEnabled(\Doctrine\ORM\QueryBuilder $queryBuilder, $value, $countOnly)
    {
        parent::prepareCndModuleEnabled($queryBuilder, $value, $countOnly);

        $queryBuilder->andWhere($this->getMainAlias($queryBuilder) . '.service_name != :paypalCreditMethod')
            ->setParameter('paypalCreditMethod', \XLite\Module\CDev\Paypal\Main::PP_METHOD_PC);
    }
}
