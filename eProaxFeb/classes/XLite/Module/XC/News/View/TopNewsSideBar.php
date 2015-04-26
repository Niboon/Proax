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

namespace XLite\Module\XC\News\View;

/**
 * Side-bar box for top news
 *
 * @ListChild (list="sidebar.single")
 * @ListChild (list="sidebar.first")
 */
class TopNewsSideBar extends \XLite\View\SideBarBox
{
    /**
     * Register CSS files
     *
     * @return array
     */
    public function getCSSFiles()
    {
        $list = parent::getCSSFiles();

        $list[] = 'modules/XC/News/side_bar_box/style.css';

        return $list;
    }

    /**
     * Get widget templates directory
     *
     * @return string
     */
    protected function getDir()
    {
        return 'modules/XC/News/side_bar_box';
    }

    /**
     * Get widget title
     *
     * @return string
     */
    protected function getHead()
    {
        return 'Top News';
    }

    /**
     * Register the CSS classes for this block
     *
     * @return string
     */
    protected function getBlockClasses()
    {
        return parent::getBlockClasses() . ' top-news-messages';
    }

    /**
     * Check if widget is visible
     *
     * @return boolean
     */
    protected function isVisible()
    {
        $result = parent::isVisible();
        if ($result) {
            $cnd = new \XLite\Core\CommonCell;
            $cnd->{\XLite\Module\XC\News\Model\Repo\NewsMessage::SEARCH_ENABLED} = true;
            $count = \XLite\Core\Database::getRepo('XLite\Module\XC\News\Model\NewsMessage')->search($cnd, true);

            $result = $count > 0;
        }

        return $result;
    }

    /**
     * Get 'More...' link URL for news
     *
     * @return string
     */
    protected function getMoreLinkURL()
    {
        return $this->buildURL('news_messages');
    }

    /**
     * Get 'More...' link text for news
     *
     * @return string
     */
    protected function getMoreLinkText()
    {
        return static::t('All news');
    }

    /**
     * Check status of 'More...' link for news
     *
     * @return boolean
     */
    protected function isShowMoreLink()
    {
        return true;
    }

}
