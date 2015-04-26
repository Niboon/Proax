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

namespace XLite\Module\CDev\SimpleCMS\Controller\Admin;

/**
 * Menus controller
 *
 */
class Menus extends \XLite\Controller\Admin\AAdmin
{
    /**
     * FIXME- backward compatibility
     *
     * @var array
     */
    protected $params = array('target', 'page');

    /**
     * Check ACL permissions
     *
     * @return boolean
     */
    public function checkACL()
    {
        return parent::checkACL()
            || \XLite\Core\Auth::getInstance()->isPermissionAllowed('manage menus');
    }

    /**
     * Get pages sections
     *
     * @return array
     */
    public function getPages()
    {
        return \XLite\Module\CDev\SimpleCMS\Model\Menu::getTypes();
    }

    /**
     * Get current page
     *
     * @return string
     */
    public function getPage()
    {
        return \XLite\Core\Request::getInstance()->page ?: \XLite\Module\CDev\SimpleCMS\Model\Menu::MENU_TYPE_PRIMARY;
    }

    /**
     * Return the current page title (for the content area)
     *
     * @return string
     */
    public function getTitle()
    {
        return static::t('Menus');
    }

    /**
     * Check if the option "Show default menu along with the custom one" is displayed
     *
     * @return boolean
     */
    public function isVisibleShowDefaultOption()
    {
        return false;
    }

    /**
     * Get pages templates
     *
     * @return array
     */
    protected function getPageTemplates()
    {
        $list = array();
        foreach (\XLite\Module\CDev\SimpleCMS\Model\Menu::getTypes() as $k => $v) {
            $list[$k] = 'modules/CDev/SimpleCMS/menus/body.tpl';
        }

        return $list;
    }

    /**
     * Update list
     *
     * @return void
     */
    protected function doActionUpdate()
    {
        $list = new \XLite\Module\CDev\SimpleCMS\View\ItemsList\Model\Menu;
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

        return isset($searchParams[$paramName])
            ? $searchParams[$paramName]
            : null;
    }

    /**
     * Save search conditions
     *
     * @return void
     */
    protected function doActionSearch()
    {
        $cellName = \XLite\Module\CDev\SimpleCMS\View\ItemsList\Model\Menu::getSessionCellName();

        \XLite\Core\Session::getInstance()->$cellName = $this->getSearchParams();
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
            \XLite\Module\CDev\SimpleCMS\View\ItemsList\Model\Menu::getSearchParams() as $requestParam
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
        $cellName = \XLite\Module\CDev\SimpleCMS\View\ItemsList\Model\Menu::getSessionCellName();

        $searchParams = \XLite\Core\Session::getInstance()->$cellName;

        if (!is_array($searchParams)) {
            $searchParams = array();
        }

        return $searchParams;
    }

    // }}}
}
