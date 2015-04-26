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

namespace XLite\Model\Repo\AttributeValue;

/**
 * Attribute values repository
 */
class AttributeValueSelect extends \XLite\Model\Repo\AttributeValue\Multiple
{
    /**
     * Allowable search params
     */
    const SEARCH_ATTRIBUTE_OPTION  = 'attributeOption';

    /**
     * Postprocess common
     *
     * @param array $data Data
     *
     * @return array
     */
    protected function postprocessCommon(array $data)
    {
        $result = array();

        foreach ($data as $v) {
            if (!isset($result[$v['attrId']])) {
                $result[$v['attrId']] = array();
            }
            $val = $v[0];
            unset($val['id']);
            unset($val['attribute_option_id']);
            $result[$v['attrId']][$v['attrOptionId']] = $val;
        }

        return $result;
    }

    /**
     * Return QueryBuilder for common values
     *
     * @param \XLite\Model\Product $product Product
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    protected function createQueryBuilderCommonValues(\XLite\Model\Product $product)
    {
        return parent::createQueryBuilderCommonValues($product)
            ->addSelect('ao.id attrOptionId')
            ->innerJoin('av.attribute_option', 'ao');
    }

    /**
     * Return list of handling search params
     *
     * @return array
     */
    protected function getHandlingSearchParams()
    {
        return array_merge(parent::getHandlingSearchParams(), array(static::SEARCH_ATTRIBUTE_OPTION));
    }

    /**
     * Prepare certain search condition
     *
     * @param \Doctrine\ORM\QueryBuilder $queryBuilder Query builder to prepare
     * @param mixed                      $value        Condition
     *
     * @return void
     * @see    ____func_see____
     * @since  1.0.0
     */
    protected function prepareCndAttributeOption(\Doctrine\ORM\QueryBuilder $queryBuilder, $value)
    {
        if ($value) {
            $queryBuilder->andWhere('a.attribute_option = :attributeOption')
                ->setParameter('attributeOption', $value);
        }
    }

    /**
     * Define QueryBuilder for findOneByValue() method
     *
     * @param \XLite\Model\Product   $product   Product object
     * @param \XLite\Model\Attribute $attribute Attribute object
     * @param mixed                  $value     Value
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    protected function defineFindOneByValueQuery($product, $attribute, $value)
    {
        $qb = parent::defineFindOneByValueQuery($product, $attribute, $value);

        $attrOption = \XLite\Core\Database::getRepo('XLite\Model\AttributeOption')->findOneByNameAndAttribute($value, $attribute);

        $qb->andWhere('av.attribute_option = :attrOption')
            ->setParameter('attrOption', $attrOption);

        return $qb;
    }
}
