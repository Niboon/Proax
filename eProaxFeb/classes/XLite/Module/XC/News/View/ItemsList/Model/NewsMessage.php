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

namespace XLite\Module\XC\News\View\ItemsList\Model;

/**
 * News messages items list
 */
class NewsMessage extends \XLite\View\ItemsList\Model\Table
{
    /**
     * Sort modes
     *
     * @var array
     */
    protected $sortByModes = array(
        'n.name' => 'Name',
        'n.date' => 'Date',
    );

    /**
     * Get a list of CSS files required to display the widget properly
     *
     * @return array
     */
    public function getCSSFiles()
    {
        $list = parent::getCSSFiles();

        $list[] = 'modules/XC/News/news_messages/style.css';

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
            'date' => array(
                static::COLUMN_NAME      => \XLite\Core\Translation::lbl('Date'),
                static::COLUMN_CLASS     => 'XLite\View\FormField\Inline\Input\Text\Date',
                static::COLUMN_SORT      => 'n.date',
                static::COLUMN_ORDERBY   => 100,
            ),
            'name' => array(
                static::COLUMN_NAME      => \XLite\Core\Translation::lbl('Name'),
                static::COLUMN_CLASS     => 'XLite\View\FormField\Inline\Input\Text',
                static::COLUMN_PARAMS    => array('required' => true),
                static::COLUMN_SORT      => 'n.name',
                static::COLUMN_MAIN      => true,
                static::COLUMN_EDIT_LINK => true,
                static::COLUMN_LINK      => 'news_message',
                static::COLUMN_ORDERBY   => 200,
            ),
        );
    }

    /**
     * Define repository name
     *
     * @return string
     */
    protected function defineRepositoryName()
    {
        return 'XLite\Module\XC\News\Model\NewsMessage';
    }


    /**
     * Creation button position
     *
     * @return integer
     */
    protected function isCreation()
    {
        return static::CREATE_INLINE_TOP;
    }

    /**
     * Get create entity URL
     *
     * @return string
     */
    protected function getCreateURL()
    {
        return \XLite\Core\Converter::buildUrl('news_message');
    }

    /**
     * Return 'Order by' array.
     * array(<Field to order>, <Sort direction>)
     *
     * @return array
     * @see    ____func_see____
     * @since  1.0.6
     */
    protected function getOrderBy()
    {
        return array($this->getSortBy(), $this->getSortOrder());
    }

    /**
     * Get create button label
     *
     * @return string
     */
    protected function getCreateButtonLabel()
    {
        return 'New news message';
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
     * Mark list as switchyabvle (enable / disable)
     *
     * @return boolean
     */
    protected function isSwitchable()
    {
        return true;
    }

    // }}}

    /**
     * Get container class
     *
     * @return string
     */
    protected function getContainerClass()
    {
        return parent::getContainerClass() . ' news_messages';
    }

    /**
     * Get panel class
     *
     * @return \XLite\View\Base\FormStickyPanel
     */
    protected function getPanelClass()
    {
        return 'XLite\Module\XC\News\View\StickyPanel\ItemsList\NewsMessage';
    }


}
