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

namespace XLite\Model\Repo;

/**
 * Temporary variables repository
 */
class TmpVar extends \XLite\Model\Repo\ARepo
{
    /**
     * Event task state prefix
     */
    const EVENT_TASK_STATE_PREFIX = 'eventTaskState.';

    /**
     * Set variable 
     * 
     * @param string $name  Variable name
     * @param mixed  $value Variable value
     *  
     * @return void
     */
    public function setVar($name, $value)
    {
        $entity = $this->findOneBy(array('name' => $name));

        if (!$entity) {
            $entity = new \XLite\Model\TmpVar;
            $entity->setName($name);
            \XLite\Core\Database::getEM()->persist($entity);
        }

        if (!is_scalar($value)) {
            $value = serialize($value);
        }

        $entity->setValue($value);

        \XLite\Core\Database::getEM()->flush();
    }

    /**
     * Get variable 
     * 
     * @param string $name Variable name
     *  
     * @return mixed
     */
    public function getVar($name)
    {
        $entity = $this->findOneBy(array('name' => $name));

        $value = $entity ? $entity->getValue() : null;

        if (!empty($value)) {
            $tmp = @unserialize($value);
            if (false !== $tmp) {
                $value = $tmp;
            }
        }

        return $value;
    }

    // {{{ Event tasks-based temporary variable operations

    /**
     * Initialize event task state
     *
     * @param string $name    Event task name
     * @param array  $options Event options OPTIONAL
     *
     * @return array
     */
    public function initializeEventState($name, array $options = array())
    {
        $this->setEventState(
            $name,
            array('position' => 0, 'length' => 0, 'state' => \XLite\Core\EventTask::STATE_STANDBY) + $options
        );
    }

    /**
     * Get event task state 
     * 
     * @param string $name Event task name
     *  
     * @return array
     */
    public function getEventState($name)
    {
        return $this->getVar(static::EVENT_TASK_STATE_PREFIX . $name);
    }

    /**
     * Set event state 
     * 
     * @param string $name Event task name
     * @param array  $rec  Event task state
     *  
     * @return void
     */
    public function setEventState($name, array $rec)
    {
        $this->setVar(static::EVENT_TASK_STATE_PREFIX . $name, $rec);
    }

    /**
     * Set event state
     *
     * @param string $name Event task name
     *
     * @return void
     */
    public function removeEventState($name)
    {
        $var = $this->findOneBy(array('name' => static::EVENT_TASK_STATE_PREFIX . $name));
        if ($var) {
            \XLite\Core\Database::getEM()->remove($var);
            \XLite\Core\Database::getEM()->flush();
        }
    }

    /**
     * Check event state - finished or not
     *
     * @param string $name Event task name
     *
     * @return boolean
     */
    public function isFinishedEventState($name)
    {
        $record = $this->getEventState($name);

        return $record
            && ($record['state'] == \XLite\Core\EventTask::STATE_FINISHED || $record['state'] == \XLite\Core\EventTask::STATE_ABORTED);
    }

    /**
     * Check event state - finished or not
     *
     * @param string $name Event task name
     *
     * @return boolean
     */
    public function getEventStatePercent($name)
    {
        $record = $this->getEventState($name);

        $percent = 0;

        if ($record) {
            if ($this->isFinishedEventState($name)) {
                $percent = 100;

            } elseif (0 < $record['length']) {
                $percent = min(100, intval($record['position'] / $record['length'] * 100));
            }
        }

        return $percent;
    }


    // }}}
}

