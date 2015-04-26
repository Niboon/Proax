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
 * Task
 *
 * @Entity
 * @Table (name="tasks")
 */
class Task extends \XLite\Model\AEntity
{
    /**
     * Unique ID
     *
     * @var integer
     *
     * @Id
     * @GeneratedValue (strategy="AUTO")
     * @Column         (type="integer", options={ "unsigned": true })
     */
    protected $id;

    /**
     * Owner class
     *
     * @var string
     *
     * @Column (type="string", length=128)
     */
    protected $owner;

    /**
     * Trigger time
     *
     * @var integer
     *
     * @Column (type="integer")
     */
    protected $triggerTime = 0;

    /**
     * Task abstract data
     *
     * @var array
     *
     * @Column (type="array")
     */
    protected $data = array();

    /**
     * Owner instance
     *
     * @var \XLite\Core\Task\ATask
     */
    protected $ownerInstance;

    /**
     * Get owner instance
     *
     * @return \XLite\Core\Task\ATask
     */
    public function getOwnerInstance()
    {
        if (!isset($this->ownerInstance)) {
            $class = $this->getOwner();
            $this->ownerInstance = new $class($this);

            if (!($this->ownerInstance instanceof \XLite\Core\Task\ATask)) {
                $this->ownerInstance = false;
            }
        }

        return $this->ownerInstance;
    }
}
