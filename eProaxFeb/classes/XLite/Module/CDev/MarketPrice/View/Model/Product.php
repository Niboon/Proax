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

namespace XLite\Module\CDev\MarketPrice\View\Model;

/**
 * Product model widget extention
 */
class Product extends \XLite\View\Model\Product implements \XLite\Base\IDecorator
{
    /**
     * We add market price widget into the default section
     *
     * @param array $params
     * @param array $sections
     */
    public function __construct(array $params = array(), array $sections = array())
    {
        parent::__construct($params, $sections);

        $schema = array();
        $marketPriceAdded = false;

        foreach ($this->schemaDefault as $name => $value) {
            $schema[$name] = $value;
            if ('price' == $name) {
                $schema['marketPrice'] = $this->defineMarketPriceField();
                $marketPriceAdded = true;
            }
        }

        if (!$marketPriceAdded) {
            $schema['marketPrice'] = $this->defineMarketPriceField();
        }

        $this->schemaDefault = $schema;
    }

    /**
     * Defines the sale price field information
     *
     * @return array
     */
    protected function defineMarketPriceField()
    {
        return array(
            static::SCHEMA_CLASS    => 'XLite\View\FormField\Input\Text\Price',
            static::SCHEMA_LABEL    => 'Market price',
            static::SCHEMA_REQUIRED => false,
        );
    }
}
