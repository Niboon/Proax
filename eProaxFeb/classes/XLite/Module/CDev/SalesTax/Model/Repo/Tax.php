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

namespace XLite\Module\CDev\SalesTax\Model\Repo;

/**
 * Tax repository
 */
class Tax extends \XLite\Model\Repo\ARepo
{
    /**
     * Get tax
     *
     * @return \XLite\Module\CDev\SalesTax\Model\Tax
     */
    public function getTax()
    {
        $tax = $this->createQueryBuilder()
            ->setMaxResults(1)
            ->getSingleResult();

        if (!$tax) {
            $tax = $this->createTax();
        }

        return $tax;
    }

    /**
     * Find active taxes
     *
     * @return array
     */
    public function findActive()
    {
        $list = $this->defineFindActiveQuery()->getResult();
        if (0 == count($list) && 0 == count($this->findAll())) {
            $this->createTax();
            $list = $this->defineFindActiveQuery()->getResult();
        }

        return $list;
    }

    /**
     * Define query for findActive() method
     *
     * @return \XLite\Model\QueryBuilder\AQueryBuilder
     */
    protected function defineFindActiveQuery()
    {
        return $this->createQueryBuilder()
            ->addSelect('tr')
            ->linkInner('t.rates', 'tr')
            ->andWhere('t.enabled = :true')
            ->setParameter('true', true);
    }

    /**
     * Create tax
     *
     * @return \XLite\Module\CDev\SalesTax\Model\Tax
     */
    protected function createTax()
    {
        $tax = new \XLite\Module\CDev\SalesTax\Model\Tax;
        $tax->setName('Sales tax');
        $tax->setEnabled(true);
        \XLite\Core\Database::getEM()->persist($tax);

        return $tax;
    }
}
