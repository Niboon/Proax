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

namespace XLite\Module\XC\ProductFilter\View\Model;

/**
 * Category view model
 *
 */
class Category extends \XLite\View\Model\Category implements \XLite\Base\IDecorator
{
    /**
     * Save current form reference and initialize the cache
     *
     * @param array $params   Widget params OPTIONAL
     * @param array $sections Sections list OPTIONAL
     *
     * @return void
     */
    public function __construct(array $params = array(), array $sections = array())
    {
        $this->schemaDefault['useClasses'] = array(
            static::SCHEMA_CLASS      => 'XLite\Module\XC\ProductFilter\View\FormField\Select\UseClasses',
            static::SCHEMA_LABEL      => 'Classes for product filter',
            static::SCHEMA_REQUIRED   => false,
        );
        $this->schemaDefault['productClasses'] = array(
            static::SCHEMA_CLASS      => 'XLite\Module\XC\ProductFilter\View\FormField\Select\Classes',
            static::SCHEMA_FIELD_ONLY => true,
        );

        parent::__construct($params, $sections);
    }

    /**
     * Populate model object properties by the passed data
     *
     * @param array $data Data to set
     *
     * @return void
     */
    protected function setModelProperties(array $data)
    {
        $data['productClasses'] = isset($data['productClasses']) && $data['productClasses']
            ? \XLite\Core\Database::getRepo('\XLite\Model\ProductClass')->findByIds($data['productClasses'])
            : array();

        parent::setModelProperties($data);
    }
}
