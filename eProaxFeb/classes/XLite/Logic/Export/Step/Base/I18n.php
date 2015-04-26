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

namespace XLite\Logic\Export\Step\Base;

/**
 * I18n-based abstract step
 */
abstract class I18n extends \XLite\Logic\Export\Step\AStep
{
    /**
     * Assign i18n columns 
     * 
     * @param array $columns Base columns
     *  
     * @return array
     */
    protected function assignI18nColumns(array $columns)
    {
        $result = array();

        foreach ($this->getRepository()->getTranslationRepository()->getUsedLanguageCodes() as $code) {
            foreach ($columns as $name => $column) {
                if (!isset($column[static::COLUMN_GETTER])) {
                    $column[static::COLUMN_GETTER] = 'getTranslationColumnValue';
                }
                $result[$name . '_' . $code] = $column;
            }
        }

        return $result;
    }

    /**
     * Get translation column value 
     * 
     * @param array   $dataset Dataset
     * @param string  $name    Name
     * @param integer $i       Subrowindex
     *  
     * @return string
     */
    protected function getTranslationColumnValue(array $dataset, $name, $i)
    {
        return $dataset['model']->getTranslation(substr($name, -2))->getterProperty(substr($name, 0, -3));
    }

}

