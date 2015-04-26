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
 * Cart repository
 */
class Cart extends \XLite\Model\Repo\ARepo
{
    /**
     * Mark cart model as order
     *
     * @param integer $orderId Order id
     *
     * @return boolean
     */
    public function markAsOrder($orderId)
    {
        $stmt = $this->defineMarkAsOrderQuery($orderId);

        return $stmt && $stmt->execute() && 0 < $stmt->rowCount();
    }

    /**
     * Get one cart for customer interface
     *
     * @param integer $id Cart id
     *
     * @return \XLite\Model\Cart
     */
    public function findOneForCustomer($id)
    {
        return $this->defineFindOneForCustomerQuery($id)->getSingleResult();
    }

    /**
     * Define query for markAsOrder() method
     *
     * @param integer $orderId Order id
     *
     * @return \Doctrine\DBAL\Statement|void
     */
    protected function defineMarkAsOrderQuery($orderId)
    {
        $stmt = $this->_em->getConnection()->prepare(
            'UPDATE ' . $this->_class->getTableName() . ' '
            . 'SET is_order = :flag '
            . 'WHERE order_id = :id'
        );

        if ($stmt) {
            $stmt->bindValue(':flag', 1);
            $stmt->bindValue(':id', $orderId);

        } else {
            $stmt = null;
        }

        return $stmt;
    }

    /**
     * Define query for findOneForCustomer() method
     *
     * @param integer $id Cart id
     *
     * @return \XLite\Model\QueryBuilder\AQueryBuilder
     */
    protected function defineFindOneForCustomerQuery($id)
    {
        return $this->createQueryBuilder()
            ->addSelect('profile')
            ->addSelect('currency')
            ->linkLeft('c.profile')
            ->linkLeft('c.currency')
            ->andWhere('c.order_id = :id')
            ->setParameter('id', $id)
            ->setMaxResults(1);
    }

}
