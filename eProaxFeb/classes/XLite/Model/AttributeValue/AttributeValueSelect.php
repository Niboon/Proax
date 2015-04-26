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

namespace XLite\Model\AttributeValue;

/**
 * Attribute value (select)
 *
 * @Entity (repositoryClass="\XLite\Model\Repo\AttributeValue\AttributeValueSelect")
 * @Table  (name="attribute_values_select",
 *      indexes={
 *          @Index (name="product_id", columns={"product_id"}),
 *          @Index (name="attribute_id", columns={"attribute_id"}),
 *          @Index (name="attribute_option_id", columns={"attribute_option_id"})
 *      }
 * )
 */
class AttributeValueSelect extends \XLite\Model\AttributeValue\Multiple
{
    /**
     * Attribute option
     *
     * @var \XLite\Model\AttributeOption
     *
     * @ManyToOne  (targetEntity="XLite\Model\AttributeOption")
     * @JoinColumn (name="attribute_option_id", referencedColumnName="id", onDelete="CASCADE")
     */
    protected $attribute_option;

    /**
     * Return attribute value as string
     *
     * @return string
     */
    public function asString()
    {
        return $this->getAttributeOption()->getName();
    }

    /**
     * Clone
     *
     * @return \XLite\Model\AEntity
     */
    public function cloneEntity()
    {
        $newEntity = parent::cloneEntity();

        if ($this->getAttributeOption()) {
            if ($this->getAttribute()->getProduct()) {
                $attributeOption = $this->getAttributeOption()->cloneEntity();
                \XLite\Core\Database::getEM()->persist($attributeOption);

            } else {
                $attributeOption = $this->getAttributeOption();
            }
            $newEntity->setAttributeOption($attributeOption);
        }

        return $newEntity;
    }
}
