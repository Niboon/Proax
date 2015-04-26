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

namespace XLite\Base;

/**
 * Module patcher interface
 */
interface IPatcher
{
    /**
     *  Common patch record cell names
     */

    const PATCHER_CELL_TYPE = 'type';
    const PATCHER_CELL_TPL  = 'tpl';


    /**
     * XPath-based patch specific record cell names
     */

    const XPATH_CELL_QUERY       = 'query';
    const XPATH_CELL_INSERT_TYPE = 'insertType';
    const XPATH_CELL_BLOCK       = 'block';


    /**
     * XPath-based patch insertion mode
     */

    const XPATH_INSERT_BEFORE    = 'before';
    const XPATH_INSERT_AFTER     = 'after';
    const XPATH_REPLACE          = 'replace';


    /**
     * Regular expression-based patch specific record cell names
     */

    const REGEXP_CELL_PATTERN = 'pattern';
    const REGEXP_CELL_REPLACE = 'replace';

    /**
     * Callback-based patch specific record cell names
     */

    const CUSTOM_CELL_CALLBACK = 'callback';


    /**
     * Patch types
     */

    const PATCH_TYPE_XPATH  = 'xpath';
    const PATCH_TYPE_REGEXP = 'regexp';
    const PATCH_TYPE_CUSTOM = 'custom';


    /**
     * Interface codes
     */

    const INTERFACE_ADMIN    = 'admin';
    const INTERFACE_CUSTOMER = 'customer';


    /**
     * Get patches
     *
     * @return array(array)
     */
    public static function getPatches();
}
