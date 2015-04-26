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

namespace XLite\Core\DataSource;

/**
 * Abstract data source
 */
abstract class ADataSource
{
    /**
     * Data source configuration
     * 
     * @var \XLite\Model\DataSource
     */
    protected $configuration;

    /**
     * Get standardized data source information array
     * 
     * @return array
     */
    abstract public function getInfo();

    /**
     * Checks whether the data source is valid
     * 
     * @return boolean
     */
    abstract public function isValid();

    /**
     * Request and return products collection
     * 
     * @return \XLite\Core\DataSource\Base\Products
     */
    abstract public function getProductsCollection();

    /**
     * Request and return categories collection
     * 
     * @return \XLite\Core\DataSource\Base\Categories
     */
    abstract public function getCategoriesCollection();

    /**
     * Get all data sources
     * 
     * @return array
     */
    public static function getDataSources()
    {
        return array(
            '\XLite\Core\DataSource\Ecwid',
        );
    }

    /**
     * Constructor
     * 
     * @param \XLite\Model\DataSource $configuration Data source configuration model
     *  
     * @return void
     */
    public function __construct(\XLite\Model\DataSource $configuration)
    {
        $this->configuration = $configuration;
    }

    /**
     * Get current data source configuration object
     * 
     * @return \XLite\Model\DataSource
     */
    protected function getConfiguration()
    {
        return $this->configuration;
    }
}
