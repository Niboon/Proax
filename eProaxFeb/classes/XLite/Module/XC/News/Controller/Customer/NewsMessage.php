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

namespace XLite\Module\XC\News\Controller\Customer;

/**
 * News message controller
 */
class NewsMessage extends \XLite\Controller\Customer\ACustomer
{
    /**
     * News message
     *
     * @var \XLite\Module\XC\News\Model\NewsMessage
     */
    protected $newsMessage;

    /**
     * Define and set handler attributes; initialize handler
     *
     * @param array $params Handler params OPTIONAL
     */
    public function __construct(array $params = array())
    {
        parent::__construct($params);

        $this->params[] = 'id';
    }

    /**
     * Return the current page title (for the content area)
     *
     * @return string
     */
    public function getTitle()
    {
        $model = $this->getNewsMessage();

        if ($model) {
            $result = $model->getMetaTitle() ?: $model->getName();

        } else {
            $result = parent::getTitle();
        }

        return $result;
    }

    /**
     * Returns the page title (for the <title> tag)
     *
     * @return string
     */
    public function getPageTitle()
    {
        $model = $this->getNewsMessage();

        if ($model) {
            $result = $model->getMetaTitle() ?: $model->getName();

        } else {
            $result = parent::getPageTitle();
        }

        return $result;
    }

    /**
     * Get meta description
     *
     * @return string
     */
    public function getMetaDescription()
    {
        $model = $this->getNewsMessage();

        return $model && $model->getMetaDesc() ? $model->getMetaDesc() : parent::getMetaDescription();
    }

    /**
     * Get meta keywords
     *
     * @return string
     */
    public function getKeywords()
    {
        $model = $this->getNewsMessage();

        return $model ? $model->getMetaTags() : parent::getKeywords();
    }

    /**
     * Return news message ID
     *
     * @return integer
     */
    public function getNewsMessage()
    {
        if (!isset($this->newsMessage)) {
            $this->newsMessage = $this->defineNewsMessage();
        }

        return $this->newsMessage;
    }

    /**
     * Return URL previous news message
     *
     * @param \XLite\Module\XC\News\Model\NewsMessage $news News message
     *
     * @return string
     */
    public function getPreviousURL(\XLite\Module\XC\News\Model\NewsMessage $news)
    {
        list($previous,) = \XLite\Core\Database::getRepo('XLite\Module\XC\News\Model\NewsMessage')
            ->findSiblingsByNews($news);

        return $previous ? static::buildURL('news_message', null, array('id' => $previous->getId())) : null;
    }

    /**
    * Return URL next news message
    *
    * @param \XLite\Module\XC\News\Model\NewsMessage $news News message
    *
    * @return string
    */
    public function getNextURL(\XLite\Module\XC\News\Model\NewsMessage $news)
    {
        list(, $next) = \XLite\Core\Database::getRepo('XLite\Module\XC\News\Model\NewsMessage')
            ->findSiblingsByNews($news);

        return $next ? static::buildURL('news_message', null, array('id' => $next->getId())) : null;
    }

    /**
     * Add part to the location nodes list
     *
     * @return void
     */
    protected function addBaseLocation()
    {
        parent::addBaseLocation();

        $this->addLocationNode(
            'News',
            static::buildURL('news_messages')
        );
    }

    /**
     * Common method to determine current location
     *
     * @return string
     */
    protected function getLocation()
    {
        return $this->getTitle();
    }

    /**
     * Define news message
     *
     * @return \XLite\Module\XC\News\Model\NewsMessage
     */
    protected function defineNewsMessage()
    {
        $model = \XLite\Core\Database::getRepo('XLite\Module\XC\News\Model\NewsMessage')
            ->find(\XLite\Core\Request::getInstance()->id);

        return $model;
    }

    /**
     * Check if current page is accessible
     *
     * @return boolean
     */
    protected function checkAccess()
    {
        $newsMessage = $this->getNewsMessage();

        return parent::checkAccess()
            && (empty($newsMessage) || $newsMessage->isEnabled());
    }

    /**
     * Check controller visibility
     *
     * @return boolean
     */
    protected function isVisible()
    {
        return parent::isVisible()
            && $this->getNewsMessage();
    }
}
