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
 * The "address field value" model repository
 */
class AddressFieldValue extends \XLite\Model\Repo\ARepo
{
    /**
     * Find value by address and address field 
     * 
     * @param \XLite\Model\Address      $address Address
     * @param \XLite\Model\AddressField $field   Address field
     *  
     * @return \XLite\Model\AddressFieldValue
     */
    public function findOneByAddressAndField(\XLite\Model\Address $address, \XLite\Model\AddressField $field)
    {
        return $this->defineFindOneByAddressAndFieldQuery($address, $field)->getSingleResult();
    }

    /**
     * Define query for findOneByAddressAndField() method
     *
     * @param \XLite\Model\Address      $address Address
     * @param \XLite\Model\AddressField $field   Address field
     *
     * @return \XLite\Model\QueryBuilder\AQueryBuilder
     */
    protected function defineFindOneByAddressAndFieldQuery(\XLite\Model\Address $address, \XLite\Model\AddressField $field)
    {
        return $this->createQueryBuilder()
            ->andWhere('a.address = :address AND a.addressField = :field')
            ->setMaxResults(1)
            ->setParameter('address', $address)
            ->setParameter('field', $field);
    }

}
