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
 * @copyright Copyright (c) 2011-2014 Qualiteam software Ltd <info@x-cart.com>. All rights reserved
 * @license   http://www.x-cart.com/license-agreement.html X-Cart 5 License Agreement
 * @link      http://www.x-cart.com/
 */

namespace XLite\Module\XC\SystemFields\Model\Repo;

/**
 * The "product" model repository
 */
class Product extends \XLite\Model\Repo\Product implements \XLite\Base\IDecorator
{
    /**
     * Allowable search params
     */
    const P_BY_UPC_ISBN   = 'byUpcIsbn';
    const P_BY_MNF_VENDOR = 'byMnfVendor';

    const P_UPC_ISBN   = 'upcIsbn';
    const P_MNF_VENDOR = 'mnfVendor';

    const UPC_ISBN_FIELD   = 'p.upcIsbn';
    const MNF_VENDOR_FIELD = 'p.mnfVendor';

    /**
     * Return list of handling search params
     *
     * @return array
     */
    protected function getHandlingSearchParams()
    {
        return array_merge(
            parent::getHandlingSearchParams(),
            array(
                self::P_UPC_ISBN,
                self::P_MNF_VENDOR,
            )
        );
    }

    /**
     * Return conditions parameters that are responsible for substring set of fields.
     *
     * @return array
     */
    protected function getConditionBy()
    {
        return array_merge(
            parent::getConditionBy(),
            array(
                self::P_BY_UPC_ISBN,
                self::P_BY_MNF_VENDOR,
            )
        );
    }

    /**
     * Return fields set for UPC/ISBN search
     *
     * @return array
     */
    protected function getSubstringSearchFieldsByUpcIsbn()
    {
        return array(
            self::UPC_ISBN_FIELD,
        );
    }

    /**
     * Return fields set for Mnf#/Vendor# search
     *
     * @return array
     */
    protected function getSubstringSearchFieldsByMnfVendor()
    {
        return array(
            self::MNF_VENDOR_FIELD,
        );
    }

    /**
     * Prepare certain search condition
     *
     * @param \Doctrine\ORM\QueryBuilder $queryBuilder Query builder to prepare
     * @param mixed                      $value        Condition data
     *
     * @return void
     */
    protected function prepareCndUpcIsbn(\Doctrine\ORM\QueryBuilder $queryBuilder, $value)
    {
        $value = trim($value);
        if (0 < strlen($value)) {
            $queryBuilder->andWhere('p.upcIsbn LIKE :upcIsbn')
                ->setParameter('upcIsbn', '%' . $value . '%');
        }
    }

    /**
     * Prepare certain search condition
     *
     * @param \Doctrine\ORM\QueryBuilder $queryBuilder Query builder to prepare
     * @param mixed                      $value        Condition data
     *
     * @return void
     */
    protected function prepareCndMnfVendor(\Doctrine\ORM\QueryBuilder $queryBuilder, $value)
    {
        $value = trim($value);
        if (0 < strlen($value)) {
            $queryBuilder->andWhere('p.mnfVendor LIKE :mnfVendor')
                ->setParameter('mnfVendor', '%' . $value . '%');
        }
    }
}
