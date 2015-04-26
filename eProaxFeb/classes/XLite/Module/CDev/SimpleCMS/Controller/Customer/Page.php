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

namespace XLite\Module\CDev\SimpleCMS\Controller\Customer;

/**
 * Page controller
 *
 */
class Page extends \XLite\Controller\Customer\ACustomer
{
    /**
     * Controller parameters list
     *
     * @var array
     */
    protected $params = array('target', 'id');

    /**
     * Alias
     *
     * @return \XLite\Module\CDev\SimpleCMS\Model\Page
     */
    public function getPage()
    {
        return $this->getId()
            ? \XLite\Core\Database::getRepo('XLite\Module\CDev\SimpleCMS\Model\Page')->find($this->getId())
            : null;
    }

    /**
     * Return the current page title (for the content area)
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->isVisible() && $this->checkAccess()
            ? $this->getPage()->getName()
            : static::t('Page not found');
    }

    /**
     * Common method to determine current location
     *
     * @return string
     */
    protected function getLocation()
    {
        return $this->isVisible() && $this->checkAccess()
            ? $this->getPage()->getName()
            : static::t('Page not found');
    }

    /**
     * Return current model id
     *
     * @return integer
     */
    protected function getId()
    {
        return intval(\XLite\Core\Request::getInstance()->id);
    }


    /**
     * Get meta description
     *
     * @return string
     */
    public function getMetaDescription()
    {
        $page = $this->getPage();

        return $page ? $page->getTeaser() : parent::getMetaDescription();
    }

    /**
     * Get meta keywords
     *
     * @return string
     */
    public function getKeywords()
    {
        $page = $this->getPage();

        return $page ? $page->getMetaKeywords() : parent::getKeywords();
    }

    /**
     * Return current (or default) page object
     *
     * @return \XLite\Model\Product
     */
    public function getModelObject()
    {
        return $this->getPage();
    }

    /**
     * Check if redirect to clean URL is needed
     *
     * @return boolean
     */
    protected function isRedirectToCleanURLNeeded()
    {
        return parent::isRedirectToCleanURLNeeded()
            || (!\XLite::isCleanURL() && $this->getModelObject()->getCleanURL());
    }

    /**
     * Check if current page is accessible
     *
     * @return boolean
     */
    protected function checkAccess()
    {
        $page = $this->getPage();

        return parent::checkAccess()
            && (empty($page) || $page->getEnabled());
    }

    /**
     * Check controller visibility
     *
     * @return boolean
     */
    protected function isVisible()
    {
        return parent::isVisible()
            && $this->getPage();
    }
}
