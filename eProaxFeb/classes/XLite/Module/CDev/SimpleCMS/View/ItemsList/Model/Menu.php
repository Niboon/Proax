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

namespace XLite\Module\CDev\SimpleCMS\View\ItemsList\Model;

/**
 * Menus items list
 */
class Menu extends \XLite\View\ItemsList\Model\Table
{
    /**
     * Get a list of CSS files required to display the widget properly
     *
     * @return array
     */
    public function getCSSFiles()
    {
        $list = parent::getCSSFiles();

        $list[] = 'modules/CDev/SimpleCMS/menus/style.css';

        return $list;
    }

    /**
     * Define columns structure
     *
     * @return array
     */
    protected function defineColumns()
    {
        return array(
            'name' => array(
                static::COLUMN_NAME         => static::t('Item name'),
                static::COLUMN_CLASS        => 'XLite\View\FormField\Inline\Input\Text',
                static::COLUMN_PARAMS       => array('required' => true),
                static::COLUMN_ORDERBY  => 100,
            ),
            'link' => array(
                static::COLUMN_NAME         => static::t('Link'),
                static::COLUMN_CLASS        => 'XLite\View\FormField\Inline\Input\Text',
                static::COLUMN_PARAMS       => array('required' => true),
                static::COLUMN_HEAD_HELP    => $this->getColumnLinkHelp(),
                static::COLUMN_ORDERBY  => 200,
            ),
            'visibleFor' => array(
                static::COLUMN_NAME         => static::t('Visible for'),
                static::COLUMN_CLASS        => 'XLite\Module\CDev\SimpleCMS\View\FormField\VisibleFor',
                static::COLUMN_PARAMS       => array('fieldOnly' => true),
                static::COLUMN_ORDERBY  => 300,
            ),
        );
    }

    /**
     * Get message for 'link' column header help
     *
     * @return string
     */
    protected function getColumnLinkHelp()
    {
        return static::t('Menu links help text', array('URL' => $this->getShopURL()));
    }

    /**
     * Define repository name
     *
     * @return string
     */
    protected function defineRepositoryName()
    {
        return 'XLite\Module\CDev\SimpleCMS\Model\Menu';
    }

    /**
     * Get create entity URL
     *
     * @return string
     */
    protected function getCreateURL()
    {
        return \XLite\Core\Converter::buildURL('menu');
    }

    /**
     * Get create button label
     *
     * @return string
     */
    protected function getCreateButtonLabel()
    {
        return 'New item';
    }

    /**
     * Inline creation mechanism position
     *
     * @return integer
     */
    protected function isInlineCreation()
    {
        return static::CREATE_INLINE_TOP;
    }


    // {{{ Behaviors

    /**
     * Mark list as removable
     *
     * @return boolean
     */
    protected function isRemoved()
    {
        return true;
    }

    /**
     * Mark list as switchable (enable / disable)
     *
     * @return boolean
     */
    protected function isSwitchable()
    {
        return true;
    }

    /**
     * Mark list as sortable
     *
     * @return integer
     */
    protected function getSortableType()
    {
        return static::SORT_TYPE_MOVE;
    }

    // }}}

    /**
     * Get container class
     *
     * @return string
     */
    protected function getContainerClass()
    {
        return parent::getContainerClass() . ' menus';
    }

    /**
     * Get panel class
     *
     * @return \XLite\View\Base\FormStickyPanel
     */
    protected function getPanelClass()
    {
        return 'XLite\Module\CDev\SimpleCMS\View\StickyPanel\ItemsList\Menu';
    }

    /**
     * Create entity
     *
     * @return \XLite\Model\AEntity
     */
    protected function createEntity()
    {
        $entity = parent::createEntity();

        $entity->setType($this->getPage());

        return $entity;
    }

    // {{{ Search

    /**
     * Return search parameters.
     *
     * @return array
     */
    static public function getSearchParams()
    {
        return array();
    }

    /**
     * Return params list to use for search
     * TODO refactor
     *
     * @return \XLite\Core\CommonCell
     */
    protected function getSearchCondition()
    {
        $result = parent::getSearchCondition();

        foreach (static::getSearchParams() as $modelParam => $requestParam) {
            $paramValue = $this->getParam($requestParam);

            if ('' !== $paramValue && 0 !== $paramValue) {
                $result->$modelParam = $paramValue;
            }
        }

        $result->type = $this->getPage();

        return $result;
    }

    /**
     * Get URL common parameters
     *
     * @return array
     */
    protected function getCommonParams()
    {
        $this->commonParams = parent::getCommonParams();
        $this->commonParams['page'] = $this->getPage();

        return $this->commonParams;
    }

    /**
     * Return name of the session cell identifier
     *
     * @return string
     */
    protected function getSessionCell()
    {
        return parent::getSessionCell() . $this->getPage();
    }

    // }}}
}
