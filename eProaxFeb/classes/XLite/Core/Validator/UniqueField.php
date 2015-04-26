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

namespace XLite\Core\Validator;

/**
 * Service name of the address field
 */
class UniqueField extends \XLite\Core\Validator\AValidator
{
    /**
     * Field class for checking
     *
     * @var string
     */
    protected $fieldClass;

    /**
     * Field name for checking
     *
     * @var string
     */
    protected $fieldName;

    /**
     * Field value for checking (saved)
     *
     * @var string
     */
    protected $fieldValue;

    /**
     * Constructor
     *
     * @param mixed $fieldClass Field class OPTIONAL
     * @param mixed $fieldName  Field identifier OPTIONAL
     * @param mixed $fieldValue Field value OPTIONAL
     *
     * @return void
     */
    public function __construct($fieldClass = null, $fieldName = null, $fieldValue = null)
    {
        parent::__construct();

        if (isset($fieldClass)) {
            $this->fieldClass = $fieldClass;
        }

        if (isset($fieldName)) {
            $this->fieldName = $fieldName;
        }

        if (isset($fieldValue)) {
            $this->fieldValue = $fieldValue;
        }
    }

    /**
     * Validate
     *
     * @param mixed $data Data
     *
     * @return void
     */
    public function validate($data)
    {
        if (!\XLite\Core\Converter::isEmptyString($data)) {
            $entity = \XLite\Core\Database::getRepo($this->fieldClass)->findOneBy(
                array(
                    $this->fieldName => $this->sanitize($data),
                )
            );

            // DO NOT use "!==" here
            if (
                $entity
                && (
                    empty($this->fieldValue)
                    || $entity->
                        {'get' . \XLite\Core\Converter::convertToCamelCase($this->fieldName)}() != $this->fieldValue
                )
            ) {
                $this->throwSKUError();
            }
        }
    }

    /**
     * Sanitize
     *
     * @param mixed $data Data
     *
     * @return string
     */
    public function sanitize($data)
    {
        return substr($data, 0, \XLite\Core\Database::getRepo($this->fieldClass)->getFieldInfo($this->fieldName, 'length'));
    }

    /**
     * Wrapper
     *
     * @return void
     * @throws \XLite\Core\Validator\Exception
     */
    protected function throwSKUError()
    {
        throw $this->throwError('The field must be unique');
    }
}
