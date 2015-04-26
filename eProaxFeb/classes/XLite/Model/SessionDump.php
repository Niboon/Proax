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

namespace XLite\Model;

/**
 * Session (dump)
 */
class SessionDump extends \XLite\Model\Session
{
   /**
     * Temporary data 
     * 
     * @var array
     */
    protected $temporaryData = array();

    /**
     * Session cell getter
     *
     * @param string $name Cell name
     *
     * @return mixed
     */
    public function __get($name)
    {
        return isset($this->temporaryData[$name]) ? $this->temporaryData[$name] : null;
    }

    /**
     * Session cell setter
     *
     * @param string $name  Cell name
     * @param mixed  $value Value
     *
     * @return void
     */
    public function __set($name, $value)
    {
        $this->temporaryData[$name] = $value;
    }

    /**
     * Check - set session cell with specified name or not
     *
     * @param string $name Cell name
     *
     * @return boolean
     */
    public function __isset($name)
    {
        return isset($this->temporaryData[$name]);
    }

    /**
     * Remove session cell
     *
     * @param string $name Cell name
     *
     * @return void
     */
    public function __unset($name)
    {
        if (isset($this->temporaryData[$name])) {
            unset($this->temporaryData[$name]);
        }
    }

    /**
     * Unset in batch mode
     *
     * @param string $name Cell name
     *
     * @return void
     */
    public function unsetBatch($name)
    {
        foreach (func_get_args() as $name) {
            if (isset($this->temporaryData[$name])) {
                unset($this->temporaryData[$name]);
            }
        }
    }

    /**
     * Get session cell by name
     *
     * @param string $name Cell name
     *
     * @return \XLite\Model\SessionCell|void
     */
    protected function getCellByName($name)
    {
        return null;
    }

    /**
     * Set session cell value
     *
     * @param string $name  Cell name
     * @param mixed  $value Value to set
     *
     * @return void
     */
    protected function setCellValue($name, $value)
    {
        $this->temporaryData[$name] = $value;
    }

}
