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

namespace XLite\Controller\Customer;

/**
 * Category
 */
class Category extends \XLite\Controller\Customer\Base\Catalog
{
    /**
     * Check whether the category title is visible in the content area
     *
     * @return boolean
     */
    public function isTitleVisible()
    {
        return $this->isVisible() && $this->getModelObject()->getShowTitle();
    }

    /**
     * Returns the page title (for the content area)
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->isVisible() ? parent::getTitle() : static::t('Page not found');
    }

    /**
     * getDescription
     *
     * @return string
     */
    public function getDescription()
    {
        $model = $this->getModelObject();

        return $model ? $model->getViewDescription() : null;
    }

    /**
     * Get session cell name for pager widget
     *
     * @return string
     */
    public function getPagerSessionCell()
    {
        return parent::getPagerSessionCell() . ($this->getCategory() ? $this->getCategory()->getCategoryId() : '');
    }

    /**
     * getModelObject
     *
     * @return \XLite\Model\AEntity
     */
    protected function getModelObject()
    {
        return $this->getCategory();
    }

    /**
     * Check controller visibility
     *
     * @return boolean
     */
    protected function isVisible()
    {
        return parent::isVisible()
            && !is_null($this->getCategory())
            && $this->getCategory()->isVisible();
    }

    /**
     * Check if redirect to clean URL is needed
     *
     * @return boolean
     */
    protected function isRedirectToCleanURLNeeded()
    {
        return parent::isRedirectToCleanURLNeeded() && $this->getCategory()->getParent();
    }

    /**
     * Preprocessor for no-action ren
     *
     * @return void
     */
    protected function doNoAction()
    {
        parent::doNoAction();

        if (!\XLite\Core\Request::getInstance()->isAJAX()) {
            \XLite\Core\Session::getInstance()->continueShoppingURL = $this->getURL();
        }
    }
}
