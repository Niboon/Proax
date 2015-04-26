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
 * Role
 *
 * @Entity
 * @Table  (name="roles")
 */
class Role extends \XLite\Model\Base\I18n
{
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
     * Permissions
     *
     * @var \Doctrine\Common\Collections\Collection
     *
     * @ManyToMany (targetEntity="XLite\Model\Role\Permission", mappedBy="roles", cascade={"merge","detach","persist"})
     */
    protected $permissions;

    /**
     * Profiles
     *
     * @var \Doctrine\Common\Collections\Collection
     *
     * @ManyToMany (targetEntity="XLite\Model\Profile", inversedBy="roles")
     * @JoinTable (
     *      name="profile_roles",
     *      joinColumns={@JoinColumn (name="role_id", referencedColumnName="id", onDelete="CASCADE")},
     *      inverseJoinColumns={@JoinColumn (name="profile_id", referencedColumnName="profile_id", onDelete="CASCADE")}
     * )
     */
    protected $profiles;

    /**
     * Constructor
     *
     * @param array $data Entity properties OPTIONAL
     *
     * @return void
     */
    public function __construct(array $data = array())
    {
        $this->permissions = new \Doctrine\Common\Collections\ArrayCollection();
        $this->profiles    = new \Doctrine\Common\Collections\ArrayCollection();

        parent::__construct($data);
    }

    /**
     * Get public name
     *
     * @return string
     */
    public function getPublicName()
    {
        return $this->getName();
    }

    /**
     * Check - specified permission is allowed or not
     *
     * @param string $code Permission code
     *
     * @return boolean
     */
    public function isPermissionAllowed($code)
    {
        $allowed = false;

        foreach ($this->getPermissions() as $permission) {
            if ($permission->isAllowed($code)) {
                $allowed = true;
                break;
            }
        }

        return $allowed;
    }

    /**
     * Check - specified permission (only one from list) is allowed
     *
     * @param string|array $code Permission code(s)
     *
     * @return boolean
     */
    public function isPermissionAllowedOr($code)
    {
        $result = false;

        $list = array();
        foreach (func_get_args() as $code) {
            if (is_array($code)) {
                $list = array_merge($list, $code);

            } else {
                $list[] = $code;
            }
        }

        foreach ($list as $code) {
            if ($this->isPermissionAllowed($code)) {
                $result = true;
                break;
            }
        }

        return $result;
    }

}
