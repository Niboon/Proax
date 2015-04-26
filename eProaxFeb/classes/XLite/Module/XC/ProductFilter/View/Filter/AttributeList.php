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

namespace XLite\Module\XC\ProductFilter\View\Filter;

/**
 * Attribute list widget
 *
 *
 */
class AttributeList extends \XLite\Module\XC\ProductFilter\View\Filter\AFilter
{
    /**
     * Widget param names
     */
    const PARAM_GROUP   = 'group';
    const PARAM_CLASSES = 'classes';

    /**
     * Get step title
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->getAttributeGroup()
            ? $this->getAttributeGroup()->getName()
            : null;
    }

    /**
     * Check if widget is visible
     *
     * @return boolean
     */
    protected function isVisible()
    {
        return parent::isVisible()
            && $this->getAttributesList(true);
    }

    /**
     * Define widget params
     *
     * @return void
     */
    protected function defineWidgetParams()
    {
        parent::defineWidgetParams();

        $this->widgetParams += array(
            self::PARAM_GROUP => new \XLite\Model\WidgetParam\Object(
                'Group', null, false, '\XLite\Model\AttributeGroup'
            ),
            self::PARAM_CLASSES => new \XLite\Model\WidgetParam\Collection(
                'Product classes', array()
            ),
        );
    }

    /**
     * Get attribute group
     *
     * @return \XLite\Model\AttributeGroup
     */
    protected function getAttributeGroup()
    {
        return $this->getParam(static::PARAM_GROUP);
    }

    /**
     * Get product classes
     *
     * @return array
     */
    protected function getProductClasses()
    {
        return $this->getParam(static::PARAM_CLASSES);
    }

    /**
     * Get widget templates directory
     *
     * @return string
     */
    protected function getDir()
    {
        return 'modules/XC/ProductFilter/sidebar';
    }

    /**
     * Return default template
     *
     * @return string
     */
    protected function getDefaultTemplate()
    {
        return $this->getDir() . '/attributes.tpl';
    }


    /**
     * Get attributes list
     *
     * @param boolean $countOnly Return items list or only its size OPTIONAL
     *
     * @return array|integer
     */
    protected function getAttributesList($countOnly = false)
    {
        $data = \XLite\Core\Database::getRepo('\XLite\Model\Attribute')->search(
            $this->getAttributesListConditions($countOnly),
            $countOnly
        );

        return $countOnly
            ? $data
            : $this->prepareAttributesList($data);
    }

    /**
     * Get attributes list conditions 
     * 
     * @param boolean $countOnly Countr only flag
     *  
     * @return \XLite\Core\CommonCell
     */
    protected function getAttributesListConditions($countOnly)
    {
        $cnd = new \XLite\Core\CommonCell;

        $cnd->attributeGroup = $this->getAttributeGroup();
        if (!$cnd->attributeGroup) {
            $cnd->productClass = $this->getProductClasses() ?: null;
        }
        $cnd->product = null;
        $cnd->type = \XLite\Model\Attribute::getFilteredTypes();

        return $cnd;
    }

    /**
     * Prepare attributes list 
     * 
     * @param array $data Attributes
     *  
     * @return array
     */
    protected function prepareAttributesList(array $data)
    {
        $filterValues = $this->getFilterValues();
        $filterValues = (isset($filterValues['attribute']) && is_array($filterValues['attribute']))
            ? $filterValues['attribute']
            : array();

        $result = array();
        foreach ($data as $attribute) {
            $row = $this->prepareAttributeElement($attribute, $filterValues);
            if ($row) {
                $result[] = $row;
            }
        }

        return $result;
    }

    /**
     * Prepare attribute element 
     * 
     * @param \XLite\Model\Attribute $attribute    Attribute
     * @param array                  $filterValues Filter values defined in prepareAttributesList()
     *  
     * @return array
     */
    protected function prepareAttributeElement(\XLite\Model\Attribute $attribute, array $filterValues)
    {
        $result = null;

        if ($attribute::TYPE_SELECT != $attribute->getType() || 0 < count($attribute->getAttributeOptions())) {
            $params = array(
                'fieldName' => 'filter[attribute][' . $attribute->getId() . ']',
                'label'     => $attribute->getName(),
                'attribute' => $attribute,
                'useColon'  => false,
                'value'     => isset($filterValues[$attribute->getId()]) ? $filterValues[$attribute->getId()] : ''
            );
            $class = 'type-' . strtolower($attribute->getType());
            if ($attribute::TYPE_CHECKBOX == $attribute->getType() && $params['value']) {
                $class .= ' checked';
            }

            $result = array(
                'class'  => $class,
                'widjet' => $this->getWidget($params, $attribute->getFilterWidgetClass()),
            );
        }

        return $result;
    }
}
