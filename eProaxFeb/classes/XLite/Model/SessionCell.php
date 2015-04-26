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
 * @Entity (repositoryClass="\XLite\Model\Repo\SessionCell")
 * @Table  (name="session_cells",
 *      uniqueConstraints={
 *          @UniqueConstraint (name="iname", columns={"id", "name"})
 *      },
 *      indexes={
 *          @Index (name="id", columns={"id"})
 *      }
 * )
 */
class SessionCell extends \XLite\Model\AEntity
{
    /**
     * Cell unique id
     *
     * @var integer
     *
     * @Id
     * @GeneratedValue (strategy="AUTO")
     * @Column         (type="integer")
     */
    protected $cell_id;

    /**
     * Session id
     *
     * @var integer
     *
     * @Column (type="integer")
     */
    protected $id;

    /**
     * Name
     *
     * @var string
     *
     * @Column (type="string", length=255)
     */
    protected $name;

    /**
     * Value
     *
     * @var string
     *
     * @Column (type="text")
     */
    protected $value = '';

    /**
     * Value type
     *
     * @var string
     *
     * @Column (type="string", length=16)
     */
    protected $type;

    /**
     * Automatically get variable type
     *
     * @param mixed $value Variable to check
     *
     * @return string
     */
    public static function getTypeByValue($value)
    {
        $type = gettype($value);

        return in_array($type, array('NULL', 'unknown type')) ? null : $type;
    }

    /**
     * Common getter
     *
     * NOTE: this function is designed as "static public" to use in repository
     * NOTE: customize this method instead of the "getValue()" one
     *
     * @param mixed  $value Value to prepare
     * @param string $type  Field type OPTIONAL
     *
     * @return mixed
     */
    public static function prepareValueForGet($value, $type = null)
    {
        $type = $type ?: static::getTypeByValue($value);

        switch ($type) {
            case 'boolean':
                $value = (bool) $value;
                break;

            case 'integer':
                $value = intval($value);
                break;

            case 'double':
                $value = doubleval($value);
                break;

            case 'string':
                $value = $value;
                break;

            case 'array':
            case 'object':
                $value = unserialize($value);
                break;

            default:
                $value = null;
        }

        return $value;
    }

    /**
     * Common setter
     *
     * NOTE: this function is designed as "static public" to use in repository
     * NOTE: customize this method instead of the "getValue()" one
     *
     * @param mixed  $value Value to prepare
     * @param string $type  Field type OPTIONAL
     *
     * @return mixed
     */
    public static function prepareValueForSet($value, $type = null)
    {
        $type = $type ?: static::getTypeByValue($value);

        switch ($type) {
            case 'boolean':
            case 'integer':
            case 'double':
            case 'string':
                break;

            case 'array':
            case 'object':
                $value = serialize($value);
                break;

            default:
                $value = null;
        }

        return $value;
    }


    /**
     * Get value
     *
     * @return mixed
     */
    public function getValue()
    {
        return static::prepareValueForGet($this->value, $this->getType());
    }

    /**
     * Set value
     *
     * @param mixed $value Value
     *
     * @return void
     */
    public function setValue($value)
    {
        $this->type  = static::getTypeByValue($value);
        $this->value = static::prepareValueForSet($value, $this->type);
    }
}
