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

namespace XLite\Module\XC\ProductVariants\View\FormField\Inline\Input\Text;

/**
 * Default value
 */
class DefaultValue extends \XLite\View\FormField\Inline\Input\Text
{
    /**
     * Get initial field parameters
     *
     * @param array $field Field data
     *
     * @return array
     */
    protected function getFieldParams(array $field)
    {
        return parent::getFieldParams($field) + array('min' => 0, 'mouseWheelIcon' => false, 'placeholder' => static::t('Default'));
    }

    /**
     * Get field value from entity
     *
     * @param array $field Field
     *
     * @return mixed
     */
    protected function getFieldEntityValue(array $field)
    {
        $method = 'getDefault' . ucfirst($field[static::FIELD_NAME]);

        return !$this->getEntity()->$method() ? parent::getFieldEntityValue($field) : '';
    }

    /**
     * Save value
     *
     * @return void
     */
    public function saveValue()
    {
        foreach ($this->getFields() as $field) {
            if ('' === $field['widget']->getValue()) {
                $defaultValue = true;
                $field['widget']->setValue($this->getEmptyFieldValue());

            } else {
                $defaultValue = false;
            }

            $method = 'setDefault' . ucfirst($field['field'][static::FIELD_NAME]);
            $this->getEntity()->$method($defaultValue);
        }

        parent::saveValue();
    }

    /**
     * Get value to write to the database when default value is used (to avoid errors when MySQL works in strict mode)
     *
     * @return integer
     */
    protected function getEmptyFieldValue()
    {
        return 0;
    }
}
