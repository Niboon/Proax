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

namespace XLite\Module\CDev\SimpleCMS\Logic;

/**
 * Sitemap links iterator
 *
 * @LC_Dependencies ("CDev\XMLSitemap")
 */
class SitemapIterator extends \XLite\Module\CDev\XMLSitemap\Logic\SitemapIterator implements \XLite\Base\IDecorator
{
    /**
     * Get current data
     *
     * @return array
     */
    public function current()
    {
        $data = parent::current();

        if (
            $this->position >= parent::count()
            && $this->position < (parent::count() + $this->getPagesLength())
        ) {
            $data = \XLite\Core\Database::getRepo('XLite\Module\CDev\SimpleCMS\Model\Page')
                ->findOneAsSitemapLink($this->position - parent::count(), 1);
            $data = $this->assemblePageData($data);
        }

        return $data;
    }

    /**
     * Get length
     *
     * @return integer
     */
    public function count()
    {
        return parent::count() + $this->getPagesLength();
    }

    /**
     * Get pages length
     *
     * @return integer
     */
    protected function getPagesLength()
    {
        if (!isset($this->pagesLength)) {
            $this->pagesLength = \XLite\Core\Database::getRepo('XLite\Module\CDev\SimpleCMS\Model\Page')
                ->countPagesAsSitemapsLinks();
        }

        return $this->pagesLength;
    }

    /**
     * Assemble page data
     *
     * @param \XLite\Module\CDev\SimpleCMS\Model\Page $page Page
     *
     * @return array
     */
    protected function assemblePageData(\XLite\Module\CDev\SimpleCMS\Model\Page $page)
    {
        return array(
            'loc'        => array('target' => 'page', 'id' => $page->getId()),
            'lastmod'    => \XLite\Core\Converter::time(),
            'changefreq' => \XLite\Core\Config::getInstance()->CDev->XMLSitemap->page_changefreq,
            'priority'   => $this->processPriority(\XLite\Core\Config::getInstance()->CDev->XMLSitemap->page_priority),
        );
    }

}
