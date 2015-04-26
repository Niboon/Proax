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

namespace XLite\Model\WidgetParam;

/**
 * Abstract Object id widget parameter
 */
abstract class ObjectId extends \XLite\Model\WidgetParam\Int
{
    /**
     * Return object class name
     *
     * @return string
     */
    abstract protected function getClassName();


    /**
     * Return object with passed/predefined ID
     *
     * @param integer $id Object ID OPTIONAL
     *
     * @return \XLite\Model\AEntity
     */
    public function getObject($id = null)
    {
        return \XLite\Core\Database::getRepo($this->getClassName())->find($this->getId($id));
    }


    /**
     * getIdValidCondition
     *
     * @param mixed $value Value to check
     *
     * @return array
     */
    protected function getIdValidCondition($value)
    {
        return array(
            self::ATTR_CONDITION => 0 >= $value,
            self::ATTR_MESSAGE   => ' is a non-positive number',
        );
    }

    /**
     * getObjectExistsCondition
     *
     * @param mixed $value Value to check
     *
     * @return array
     */
    protected function getObjectExistsCondition($value)
    {
        return array(
            self::ATTR_CONDITION => !is_object($this->getObject($value)),
            self::ATTR_MESSAGE   => ' record with such ID is not found',
        );
    }

    /**
     * Return object ID
     *
     * @param integer $id Object ID OPTIONAL
     *
     * @return integer
     */
    protected function getId($id = null)
    {
        return isset($id) ? $id : $this->value;
    }

    /**
     * Return list of conditions to check
     *
     * @param mixed $value Value to validate
     *
     * @return void
     */
    protected function getValidaionSchema($value)
    {
        $schema = parent::getValidaionSchema($value);
        $schema[] = $this->getIdValidCondition($value);
        $schema[] = $this->getObjectExistsCondition($value);

        return $schema;
    }
}
