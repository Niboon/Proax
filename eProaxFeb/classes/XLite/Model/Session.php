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
 * Session
 *
 * @Entity (repositoryClass="\XLite\Model\Repo\Session")
 * @Table  (name="sessions",
 *      uniqueConstraints={
 *          @UniqueConstraint (name="sid", columns={"sid"})
 *      },
 *      indexes={
 *          @Index (name="expiry", columns={"expiry"})
 *      }
 * )
 * @HasLifecycleCallbacks
 */
class Session extends \XLite\Model\AEntity
{
    /**
     * Maximum admin session TTL (12 hours)
     */
    const MAX_ADMIN_TTL = 43200;

    /**
     * Maximum customer session TTL (7 days)
     */
    const MAX_CUSTOMER_TTL = 604800;


    /**
     * Session increment id
     *
     * @var integer
     *
     * @Id
     * @GeneratedValue (strategy="AUTO")
     * @Column         (type="integer")
     */
    protected $id;

    /**
     * Public session id
     *
     * @var string
     *
     * @Column (type="string", options={ "fixed": true }, length=32)
     */
    protected $sid;

    /**
     * Session expiration time
     *
     * @var integer
     *
     * @Column (type="integer", options={ "unsigned": true })
     */
    protected $expiry;

    /**
     * Cells cache
     *
     * @var array
     */
    protected $cache;


    /**
     * Get maximum session TTL
     *
     * @return integer
     */
    protected static function getMaxTTL()
    {
        return \XLite::isAdminZone()
            ? self::MAX_ADMIN_TTL
            : self::MAX_CUSTOMER_TTL;
    }

    /**
     * Return instance of the session cell repository
     *
     * @return \XLite\Model\Repo\SessionCell
     */
    protected static function getSessionCellRepo()
    {
        return \XLite\Core\Database::getRepo('XLite\Model\SessionCell');
    }

    /**
     * Set session id
     *
     * @param string $value Session id
     *
     * @return void
     */
    public function setSid($value)
    {
        $this->sid = $value;
    }

    /**
     * Update expiration time
     *
     * @return void
     */
    public function updateExpiry()
    {
        $ttl = \XLite\Core\Session::getTTL();
        $this->setExpiry(0 < $ttl ? $ttl : \XLite\Core\Converter::time() + self::getMaxTTL());
    }

    /**
     * Session cell getter
     *
     * @param string $name Cell name
     *
     * @return mixed
     */
    public function __get($name)
    {
        $cell = $this->getCellByName($name);

        return $cell ? $cell->getValue() : null;
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
        $this->setCellValue($name, $value);
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
        return !is_null($this->getCellByName($name));
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
        $this->setCellValue($name, null);
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
        $count = 0;
        foreach (func_get_args() as $name) {
            $cell = $this->getCellByName($name);
            if ($cell && !$cell->isDetached()) {
                static::getSessionCellRepo()->delete($cell, false);
                $count++;
            }
        }

        if (0 < $count) {
            \XLite\Core\Database::getEM()->flush();
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
        if (!isset($this->cache)) {
            $this->cache = array();

            foreach ((array) static::getSessionCellRepo()->findById($this->getId()) as $cell) {
                $this->cache[$cell->getName()] = $cell;
            }
        }

        return \Includes\Utils\ArrayManager::getIndex($this->cache, $name, true);
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
        // Check if cell exists (need to perform update or delete)
        $cell = $this->getCellByName($name);

        if (!$cell) {

            // Cell not found - create new
            if (isset($value)) {
                $this->cache[$name] = static::getSessionCellRepo()->insertCell($this->getId(), $name, $value);
            }

        } elseif (isset($value)) {

            // Only perform SQL query if cell value is changed
            if ($cell->getValue() !== $value) {
                $this->cache[$name] = static::getSessionCellRepo()->insertCell($this->getId(), $name, $value);
            }

        } else {

            // Set the "null" value to delete current cell
            static::getSessionCellRepo()->removeCell($cell);
            unset($this->cache[$name]);
        }
    }
}
