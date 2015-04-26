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
 * Profile list controller
 */
class ProfileList extends \XLite\Controller\Admin\AAdmin
{
    /**
     * Check ACL permissions
     *
     * @return boolean
     */
    public function checkACL()
    {
        return parent::checkACL() || \XLite\Core\Auth::getInstance()->isPermissionAllowed('manage users');
    }

    /**
     * Return the current page title (for the content area)
     *
     * @return string
     */
    public function getTitle()
    {
        return static::t('Search for users');
    }

    /**
     * doActionDelete
     *
     * @return void
     */
    protected function doActionDelete()
    {
        // :TODO: check if it's really needed
    }

    /**
     * Update list
     *
     * @return void
     */
    protected function doActionUpdate()
    {
        $list = new \XLite\View\ItemsList\Model\Profile;
        $list->processQuick();
    }

    // {{{ Search

    /**
     * Get search condition parameter by name
     *
     * @param string $paramName Parameter name
     *
     * @return mixed
     */
    public function getCondition($paramName)
    {
        $searchParams = $this->getConditions();

        $value = isset($searchParams[$paramName])
            ? $searchParams[$paramName]
            : '';

        return (empty($value) && isset(\XLite\Core\Request::getInstance()->{$paramName}))
            ? \XLite\Core\Request::getInstance()->{$paramName}
            : $value;
    }

    /**
     * Save search conditions
     *
     * @return void
     */
    protected function doActionSearch()
    {
        $cellName = \XLite\View\ItemsList\Model\Profile::getSessionCellName();
        \XLite\Core\Session::getInstance()->$cellName = $this->getSearchParams();

        $this->setReturnURL($this->getURL(array('searched' => 1)));
    }

    /**
     * Process 'no action'
     *
     * @return void
     */
    protected function doNoAction()
    {
        parent::doNoAction();

        if (\XLite\Core\Request::getInstance()->fast_search) {

            // Refresh search parameters from request
            \XLite\Core\Session::getInstance()->{\XLite\View\ItemsList\Model\Profile::getSessionCellName()} = array();
            \XLite\Core\Session::getInstance()->{\XLite\View\ItemsList\Model\Profile::getSessionCellName()} = $this->getSearchParams();

            // Get ItemsList widget
            $widget = new \XLite\View\ItemsList\Model\Profile();

            // Search for single profile
            $entity = $widget->searchForSingleEntity();

            if ($entity && $entity instanceOf \XLite\Model\Profile) {
                // Prepare redirect to profile page
                $url = $this->buildURL('profile', '', array('profile_id' => $entity->getProfileId()));
                $this->setReturnURL($url);
            }
        }
    }

    /**
     * Return search parameters
     *
     * @return array
     */
    protected function getSearchParams()
    {
        $searchParams = $this->getConditions();

        foreach (
            \XLite\View\ItemsList\Model\Profile::getSearchParams() as $requestParam
        ) {
            if (isset(\XLite\Core\Request::getInstance()->$requestParam)) {
                $searchParams[$requestParam] = \XLite\Core\Request::getInstance()->$requestParam;
            }
        }

        return $searchParams;
    }

    /**
     * Get search conditions
     *
     * @return array
     */
    protected function getConditions()
    {
        $cellName = \XLite\View\ItemsList\Model\Profile::getSessionCellName();

        $searchParams = \XLite\Core\Session::getInstance()->$cellName;

        if (!is_array($searchParams)) {
            $searchParams = array();
        }

        return $searchParams;
    }

    // }}}
}
