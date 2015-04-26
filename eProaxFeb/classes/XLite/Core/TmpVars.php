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

namespace XLite\Core;

/**
 * DB-based temporary variables
 */
class TmpVars extends \XLite\Base\Singleton
{
    /**
     * Getter
     *
     * @param string $name Name
     *
     * @return mixed
     */
    public function __get($name)
    {
        return ($var = $this->getVar($name)) ? unserialize($var->getValue()) : null;
    }

    /**
     * Setter
     *
     * @param string $name  Name
     * @param mixed  $value Value
     *
     * @return void
     */
    public function __set($name, $value)
    {
        $var = $this->getVar($name);

        if (isset($value)) {
            $data = array('value' => serialize($value));
            if (isset($var)) {
                $this->getRepo()->update($var, $data, false);
            } else {
                $var = $this->getRepo()->insert($data + array('name' => $name), false);
            }
            \XLite\Core\Database::getEM()->flush($var);
        } elseif ($var) {
            $this->getRepo()->delete($var, false);
        }
    }

    /**
     * Check if value is set
     *
     * @param string $name Variable name to check
     *
     * @return boolean
     */
    public function __isset($name)
    {
        return !is_null($this->getVar($name));
    }

    /**
     * Search var in DB table
     *
     * @param string $name Var name
     *
     * @return \XLite\Model\TmpVar
     */
    protected function getVar($name)
    {
        return $this->getRepo()->findOneBy(array('name' => $name));
    }

    /**
     * Return the Doctrine repository
     *
     * @return \XLite\Model\Repo\TmpVar
     */
    protected function getRepo()
    {
        return \XLite\Core\Database::getRepo('XLite\Model\TmpVar');
    }
}
