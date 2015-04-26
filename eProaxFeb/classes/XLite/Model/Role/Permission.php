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

namespace XLite\Model\Role;

/**
 * Permission
 *
 * @Entity
 * @Table  (name="permissions")
 */
class Permission extends \XLite\Model\Base\I18n
{
    const ROOT_ACCESS = 'root access';

    /**
     * ID
     *
     * @var integer
     *
     * @Id
     * @GeneratedValue (strategy="AUTO")
     * @Column         (type="integer", options={ "unsigned": true })
     */
    protected $id;

    /**
     * Code
     *
     * @var string
     *
     * @Column (type="string", options={ "fixed": true }, length=32)
     */
    protected $code;

    /**
     * Section
     *
     * @var string
     *
     * @Column (type="string", length=128)
     */
    protected $section;

    /**
     * Roles
     *
     * @var \XLite\Model\Roles
     *
     * @ManyToMany (targetEntity="XLite\Model\Role", inversedBy="permissions")
     * @JoinTable (
     *      name="role_permissions",
     *      joinColumns={@JoinColumn (name="permission_id", referencedColumnName="id", onDelete="CASCADE")},
     *      inverseJoinColumns={@JoinColumn (name="role_id", referencedColumnName="id", onDelete="CASCADE")}
     * )
     */
    protected $roles;

    /**
     * Constructor
     *
     * @param array $data Entity properties OPTIONAL
     *
     * @return void
     */
    public function __construct(array $data = array())
    {
        $this->roles = new \Doctrine\Common\Collections\ArrayCollection();

        parent::__construct($data);
    }

    /**
     * Get public name
     *
     * @return string
     */
    public function getPublicName()
    {
        return $this->getName() ?: $this->getCode();
    }

    /**
     * Use this method to check if the given permission code allows with the permission
     *
     * @return boolean
     */
    public function isAllowed($code)
    {
        return in_array($this->getCode(), array(static::ROOT_ACCESS, $code));
    }
}
