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

namespace XLite\Controller\Admin;

/**
 * Event task controller
 */
class EventTask extends \XLite\Controller\Admin\AAdmin
{
    /**
     * Check if current page is accessible
     *
     * @return boolean
     */
    public function checkAccess()
    {
        return parent::checkAccess() && $this->isAJAX();
    }

    /**
     * Check ACL permissions
     *
     * @return boolean
     */
    public function checkACL()
    {
        return true;
    }

    /**
     * Process request
     *
     * @return void
     */
    public function processRequest()
    {
    }

    /**
     * Run task
     *
     * @return void
     */
    protected function doActionRun()
    {
        $event = \XLite\Core\Request::getInstance()->event;
        $result = false;
        $errors = array();

        $task = \XLite\Core\Database::getRepo('XLite\Model\EventTask')->findOneBy(array('name' => $event));
        if ($task) {
            if (\XLite\Core\EventListener::getInstance()->handle($task->getName(), $task->getArguments())) {
                \XLite\Core\Database::getEM()->remove($task);
                $result = true;
            }
            $errors = \XLite\Core\EventListener::getInstance()->getErrors();

        } else {
            \XLite\Core\Database::getRepo('XLite\Model\TmpVar')->removeEventState($event);
        }

        \XLite\Core\Database::getEM()->flush();

        $state = \XLite\Core\Database::getRepo('XLite\Model\TmpVar')->getEventState($event);

        $this->setPureAction(true);
        if ($result && $state) {
            $data = array(
                'percent' => \XLite\Core\Database::getRepo('XLite\Model\TmpVar')->getEventStatePercent($event),
                'error'   => !empty($errors),
            );

            if (!empty($state['touchData'])) {
                $data += $state['touchData'];
            }

            \XLite\Core\Event::eventTaskRun($data);

        } else {
            \XLite\Core\Event::eventTaskRun(
                array(
                    'percent' => 100,
                    'error'   => true
                )
            );
            $result = false;
        }

        if ($errors) {
            foreach ($errors as $message) {
                \XLite\Core\TopMessage::addError($message);
            }
            $result = false;
        }

        $this->valid = $result;
    }

    /**
     * Run task
     *
     * @return void
     */
    protected function doActionTouch()
    {
        $event = \XLite\Core\Request::getInstance()->event;
        $state = \XLite\Core\Database::getRepo('XLite\Model\TmpVar')->getEventState($event);

        $this->setPureAction(true);

        $percent = ($state && 0 < $state['position'])
            ? min(100, round($state['position'] / $state['length'] * 100))
            : 0;

        $data = array(
            'percent' => $percent,
            'error'   => false,
        );

        if (!empty($state['touchData'])) {
            $data += $state['touchData'];
        }

        print (json_encode($data));
    }

    /**
     * Set if the form id is needed to make an actions
     * Form class uses this method to check if the form id should be added
     * 
     * @return boolean
     */    
    public static function needFormId()
    {
        return false;
    }
}
