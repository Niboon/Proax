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
 * States management page controller
 */
class States extends \XLite\Controller\Admin\AAdmin
{

    /**
     * Return the current page title (for the content area)
     *
     * @return string
     */
    public function getTitle()
    {
        return static::t('States');
    }

    /**
     * Get current country code
     *
     * @return string
     */
    public function getCountryCode()
    {
        return \XLite\Core\Request::getInstance()->{\XLite\View\ItemsList\Model\State::PARAM_COUNTRY_CODE}
            ?: $this->getCondition(\XLite\View\ItemsList\Model\State::PARAM_COUNTRY_CODE)
                ?: \XLite\Core\Config::getInstance()->Company->location_country
                    ?: 'US';
    }

    /**
     * Get session cell name for pager widget
     *
     * @return string
     */
    public function getPagerSessionCell()
    {
        return parent::getPagerSessionCell() . '_' . $this->getCountryCode();
    }

    /**
     * Get list of countries which has states
     *
     * @return array
     */
    public function getCountriesWithStates()
    {
        $cnd = new \XLite\Core\CommonCell();
        $cnd->{\XLite\Model\Repo\Country::P_HAS_STATES} = true;

        return \XLite\Core\Database::getRepo('XLite\Model\Country')->search($cnd);
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
        $name = \XLite\View\ItemsList\Model\State::getSessionCellName();
        $searchParams = \XLite\Core\Session::getInstance()->$name;

        return is_array($searchParams) ? $searchParams : array();
    }

    /**
     * doActionUpdate
     *
     * @return void
     */
    protected function doActionUpdate()
    {
        $list = new \XLite\View\ItemsList\Model\State;
        $list->processQuick();

        \XLite\Core\Database::getRepo('XLite\Model\State')->cleanCache();
    }

    /**
     * Search labels
     *
     * @return void
     */
    protected function doActionSearch()
    {
        $search = array();
        $searchParams   = \XLite\View\ItemsList\Model\State::getSearchParams();

        foreach ($searchParams as $modelParam => $requestParam) {
            if (isset(\XLite\Core\Request::getInstance()->$requestParam)) {
                $search[$requestParam] = \XLite\Core\Request::getInstance()->$requestParam;
            }
        }

        $name = \XLite\View\ItemsList\Model\State::getSessionCellName();
        \XLite\Core\Session::getInstance()->$name = $search;

        if (!empty($search[\XLite\View\ItemsList\Model\State::PARAM_COUNTRY_CODE])) {
            $this->setReturnURL(
                $this->buildURL(
                    'states',
                    '',
                    array(
                        \XLite\View\ItemsList\Model\State::PARAM_COUNTRY_CODE
                             => $search[\XLite\View\ItemsList\Model\State::PARAM_COUNTRY_CODE],
                    )
                )
            );
        }
    }

    /**
     * Do action delete
     *
     * @return void
     */
    protected function doActionDelete()
    {
        $select = \XLite\Core\Request::getInstance()->select;

        if ($select && is_array($select)) {
            \XLite\Core\Database::getRepo('\XLite\Model\State')->deleteInBatchById($select);
            \XLite\Core\TopMessage::addInfo(
                'States information has been successfully deleted'
            );

        } else {
           \XLite\Core\TopMessage::addWarning('Please select the states first');
        }
    }

}
