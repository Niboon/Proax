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
 * View list
 *
 * @Entity (repositoryClass="\XLite\Model\Repo\ViewList")
 * @Table  (name="view_lists",
 *          indexes={
 *              @Index (name="tl", columns={"tpl", "list"}),
 *              @Index (name="lzv", columns={"list", "zone", "version"})
 *          }
 * )
 * @HasLifecycleCallbacks
 */
class ViewList extends \XLite\Model\AEntity
{
    /**
     * Predefined weights
     */
    const POSITION_FIRST = 0;
    const POSITION_LAST  = 16777215;

    /**
     * Predefined interfaces
     */
    const INTERFACE_CUSTOMER = 'customer';
    const INTERFACE_ADMIN    = 'admin';
    const INTERFACE_CONSOLE  = 'console';
    const INTERFACE_MAIL     = 'mail';

    /**
     * Version key 
     * 
     * @var string
     */
    protected static $versionKey;

    /**
     * List id
     *
     * @var integer
     *
     * @Id
     * @GeneratedValue (strategy="AUTO")
     * @Column         (type="integer", length=11)
     */
    protected $list_id;

    /**
     * Class name
     *
     * @var string
     *
     * @Column (type="string")
     */
    protected $class = '';

    /**
     * Class list name
     *
     * @var string
     *
     * @Column (type="string")
     */
    protected $list;

    /**
     * List interface
     *
     * @var string
     *
     * @Column (type="string", length=16)
     */
    protected $zone = self::INTERFACE_CUSTOMER;

    /**
     * Child class name
     *
     * @var string
     *
     * @Column (type="string", length=512)
     */
    protected $child = '';

    /**
     * Child weight
     *
     * @var integer
     *
     * @Column (type="integer", length=11)
     */
    protected $weight = 0;

    /**
     * Template relative path
     *
     * @var string
     *
     * @Column (type="string", length=512)
     */
    protected $tpl = '';

    /**
     * Template relative path
     *
     * @var string
     *
     * @Column (type="string", length=32, nullable=true)
     */
    protected $version;

    /**
     * Set version key 
     * 
     * @param string $key Key
     *  
     * @return void
     */
    public static function setVersionKey($key)
    {
        static::$versionKey = $key;
    }

    /**
     * Prepare creation date
     *
     * @return void
     *
     * @PrePersist
     */
    public function prepareBeforeCreate()
    {
        if (static::$versionKey && !$this->getVersion()) {
            $this->setVersion(static::$versionKey);
        }        
    }

}
