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

namespace XLite\Module\XC\SystemFields\View\Model;

/**
 * Product model widget extention
 */
class Product extends \XLite\View\Model\Product implements \XLite\Base\IDecorator
{
    /**
     * We add UPC and Mnf# fields widgets into the default section
     *
     * @param array $params
     * @param array $sections
     */
    public function __construct(array $params = array(), array $sections = array())
    {
        parent::__construct($params, $sections);

        $schema = array();
        $upcIsbnAdded = false;
        $mnfVendorAdded = false;

        $isUpcIsbnFieldVisible = $this->isUpcIsbnFieldVisible();
        $isMnfVendorFieldVisible = $this->isMnfVendorFieldVisible();

        foreach ($this->schemaDefault as $name => $value) {
            $schema[$name] = $value;

            if ('sku' == $name) {
                if ($isUpcIsbnFieldVisible) {
                    $schema['upcIsbn'] = $this->defineUpcIsbnField();
                    $upcIsbnAdded = true;
                }

                if ($isMnfVendorFieldVisible) {
                    $schema['mnfVendor'] = $this->defineMnfVendorField();
                    $mnfVendorAdded = true;
                }
            }
        }

        if (
            !$upcIsbnAdded
            && $isUpcIsbnFieldVisible
        ) {
            $schema['upcIsbn'] = $this->defineUpcIsbnField();
        }

        if (
            !$mnfVendorAdded
            && $isMnfVendorFieldVisible
        ) {
            $schema['mnfVendor'] = $this->defineMnfVendorField();
        }

        $this->schemaDefault = $schema;
    }

    /**
     * Defines the UPC field information
     *
     * @return array
     */
    protected function defineUpcIsbnField()
    {
        return array(
            static::SCHEMA_CLASS    => 'XLite\Module\XC\SystemFields\View\FormField\Input\Text\UpcIsbn',
            static::SCHEMA_LABEL    => 'UPC/ISBN',
            static::SCHEMA_REQUIRED => false,
        );
    }

    /**
     * Defines the Mnf# field information
     *
     * @return array
     */
    protected function defineMnfVendorField()
    {
        return array(
            static::SCHEMA_CLASS    => 'XLite\Module\XC\SystemFields\View\FormField\Input\Text\MnfVendor',
            static::SCHEMA_LABEL    => 'Mnf/Vendor',
            static::SCHEMA_REQUIRED => false,
        );
    }

    /**
     * Check if UPC field is visible
     *
     * @return boolean
     */
    protected function isUpcIsbnFieldVisible()
    {
        return \XLite\Module\XC\SystemFields\Logic\Field::getInstance()
            ->isVisibleField(\XLite\Module\XC\SystemFields\Logic\Field::UPC_ISBN);
    }

    /**
     * Check if Mnf# field is visible
     *
     * @return boolean
     */
    protected function isMnfVendorFieldVisible()
    {
        return \XLite\Module\XC\SystemFields\Logic\Field::getInstance()
            ->isVisibleField(\XLite\Module\XC\SystemFields\Logic\Field::MNF_VENDOR);
    }
}
