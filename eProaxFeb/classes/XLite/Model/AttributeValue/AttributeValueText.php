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
 * Attribute value (text)
 *
 * @Entity (repositoryClass="\XLite\Model\Repo\AttributeValue\AttributeValueText")
 * @Table  (name="attribute_values_text",
 *      indexes={
 *          @Index (name="product_id", columns={"product_id"}),
 *          @Index (name="attribute_id", columns={"attribute_id"})
 *      }
 * )
 */
class AttributeValueText extends \XLite\Model\AttributeValue\AAttributeValue
{
    /**
     * Editable flag
     *
     * @var boolean
     *
     * @Column (type="boolean")
     */
    protected $editable = false;

    /**
     * Return diff
     *
     * @param array oldValues Old values
     * @param array newValues New values
     *
     * @return array
     */
    static public function getDiff(array $oldValues, array $newValues)
    {
        $diff = array();
        if ($newValues) {
            foreach ($newValues as $attributeId => $value) {
                if (
                    !isset($oldValues[$attributeId])
                    || $value != $oldValues[$attributeId]
                ) {
                    $diff[$attributeId] = $value;
                }
            }
        }

        return $diff;
    }

    /**
     * Return attribute value as string
     *
     * @return string
     */
    public function asString()
    {
        return $this->getValue();
    }
}
