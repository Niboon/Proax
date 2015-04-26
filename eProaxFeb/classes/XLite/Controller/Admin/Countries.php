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
 * Countries management page controller
 */
class Countries extends \XLite\Controller\Admin\AAdmin
{

    /**
     * Return the current page title (for the content area)
     *
     * @return string
     */
    public function getTitle()
    {
        return static::t('Countries');
    }

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
     * Get search conditions
     *
     * @return array
     */
    protected function getConditions()
    {
        $name = \XLite\View\ItemsList\Model\Country::getSessionCellName();
        $searchParams = \XLite\Core\Session::getInstance()->$name;

        return is_array($searchParams) ? $searchParams : array();
    }

    /**
     * Search labels
     *
     * @return void
     */
    protected function doActionSearch()
    {
        $search = array();
        $searchParams   = \XLite\View\ItemsList\Model\Country::getSearchParams();

        foreach ($searchParams as $modelParam => $requestParam) {
            if (isset(\XLite\Core\Request::getInstance()->$requestParam)) {
                $search[$requestParam] = \XLite\Core\Request::getInstance()->$requestParam;
            }
        }

        $name = \XLite\View\ItemsList\Model\Country::getSessionCellName();
        \XLite\Core\Session::getInstance()->$name = $search;
    }

    /**
     * Action 'update'
     *
     * @return void
     */
    protected function doActionUpdate()
    {
        $list = new \XLite\View\ItemsList\Model\Country;
        $list->processQuick();

        \XLite\Core\Database::getRepo('XLite\Model\State')->cleanCache();
    }

    /**
     * Do action enable
     *
     * @return void
     */
    protected function doActionEnable()
    {
        $select = \XLite\Core\Request::getInstance()->select;

        if ($select && is_array($select)) {
            \XLite\Core\Database::getRepo('\XLite\Model\Country')->updateInBatchById(
                array_fill_keys(
                    array_keys($select),
                    array('enabled' => true)
                )
            );
            \XLite\Core\TopMessage::addInfo(
                'Countries information has been successfully updated'
            );

        } else {
           \XLite\Core\TopMessage::addWarning('Please select the countries first');
        }
    }

    /**
     * Do action disable
     *
     * @return void
     */
    protected function doActionDisable()
    {
        $select = \XLite\Core\Request::getInstance()->select;

        if ($select && is_array($select)) {
            \XLite\Core\Database::getRepo('\XLite\Model\Country')->updateInBatchById(
                array_fill_keys(
                    array_keys($select),
                    array('enabled' => false)
                )
            );
            \XLite\Core\TopMessage::addInfo(
                'Countries information has been successfully updated'
            );

        } else {
           \XLite\Core\TopMessage::addWarning('Please select the countries first');
        }
    }

}
