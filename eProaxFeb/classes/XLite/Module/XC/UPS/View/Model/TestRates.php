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

namespace XLite\Module\XC\UPS\View\Model;

/**
 * TestRates widget
 */
class TestRates extends \XLite\View\Model\TestRates
{
    /**
     * Add CSS files
     *
     * @return array
     */
    public function getCSSFiles()
    {
        $list = parent::getCSSFiles();

        $list[] = 'modules/XC/UPS/style.css';

        return $list;
    }

    /**
     * Return name of web form widget class
     *
     * @return string
     */
    protected function getFormTarget()
    {
        return 'ups';
    }

    /**
     * Returns the list of related targets
     *
     * @param string $target Target name
     *
     * @return array
     */
    protected function getAvailableSchemaFields()
    {
        return array(
            static::SCHEMA_FIELD_WEIGHT,
            static::SCHEMA_FIELD_SUBTOTAL,
            static::SCHEMA_FIELD_SRC_CITY,
            static::SCHEMA_FIELD_SRC_COUNTRY,
            static::SCHEMA_FIELD_SRC_STATE,
            static::SCHEMA_FIELD_SRC_CUSTOM_STATE,
            static::SCHEMA_FIELD_SRC_ZIPCODE,
            static::SCHEMA_FIELD_DST_CITY,
            static::SCHEMA_FIELD_DST_COUNTRY,
            static::SCHEMA_FIELD_DST_STATE,
            static::SCHEMA_FIELD_DST_CUSTOM_STATE,
            static::SCHEMA_FIELD_DST_ZIPCODE,
            static::SCHEMA_FIELD_COD_ENABLED,
        );
    }
}
