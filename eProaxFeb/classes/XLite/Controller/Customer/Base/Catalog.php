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

namespace XLite\Controller\Customer\Base;

/**
 * Catalog
 */
abstract class Catalog extends \XLite\Controller\Customer\ACustomer
{
    /**
     * getModelObject
     *
     * @return \XLite\Model\AEntity
     */
    abstract protected function getModelObject();

    /**
     * Define and set handler attributes; initialize handler
     *
     * @param array $params Handler params OPTIONAL
     *
     * @return void
     */
    public function __construct(array $params = array())
    {
        parent::__construct($params);

        $this->params[] = 'category_id';
    }

    /**
     * Handles the request.
     * Parses the request variables if necessary. Attempts to call the specified action function
     *
     * @return void
     */
    public function handleRequest()
    {
        \XLite\Core\Request::getInstance()->category_id = intval(\XLite\Core\Request::getInstance()->category_id);

        parent::handleRequest();
    }

    /**
     * Return current (or default) category object
     *
     * @return \XLite\Model\Category
     */
    public function getCategory()
    {
        return \XLite\Core\Database::getRepo('XLite\Model\Category')->getCategory($this->getCategoryId());
    }

    /**
     * Returns the page title (for the content area)
     *
     * @return string
     */
    public function getTitle()
    {
        $model = $this->getModelObject();

        return ($model && $model->getName()) ? $model->getName() : parent::getTitle();
    }

    /**
     * Returns the page title (for the <title> tag)
     *
     * @return string
     */
    public function getPageTitle()
    {
        $model = $this->getModelObject();

        return ($model && $model->getMetaTitle()) ? $model->getMetaTitle() : $this->getTitle();
    }

    /**
     * getDescription
     *
     * @return string
     */
    public function getDescription()
    {
        $model = $this->getModelObject();

        return $model ? $model->getDescription() : null;
    }

    /**
     * Get meta description
     *
     * @return string
     */
    public function getMetaDescription()
    {
        $model = $this->getModelObject();

        if ($model) {
            $result = $model->getMetaDesc() ?: $this->getDescription();

        } else {
            $result = parent::getMetaDescription();
        }

        return $result;
    }

    /**
     * Get meta keywords
     *
     * @return string
     */
    public function getKeywords()
    {
        $model = $this->getModelObject();

        return $model ? $model->getMetaTags() : parent::getKeywords();
    }


    /**
     * Return path for the current category
     *
     * @return array
     */
    protected function getCategoryPath()
    {
        return \XLite\Core\Database::getRepo('\XLite\Model\Category')->getCategoryPath($this->getCategoryId());
    }

    /**
     * Preprocessor for no-action run
     *
     * @return void
     */
    protected function doNoAction()
    {
        parent::doNoAction();

        if (!$this->isAJAX()) {
            \XLite\Core\Session::getInstance()->productListURL = $this->getURL();
        }
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
     * Return link to category page
     *
     * @param \XLite\Model\Category $category Category model object to use
     *
     * @return string
     */
    protected function getCategoryURL(\XLite\Model\Category $category)
    {
        return $this->buildURL('category', '', array('category_id' => $category->getCategoryId()));
    }

    /**
     * Prepare subnodes for the location path node
     *
     * @param \XLite\Model\Category $category Node category
     *
     * @return array
     */
    protected function getLocationNodeSubnodes(\XLite\Model\Category $category)
    {
        $nodes = array();

        foreach ($category->getSiblings(true) as $category) {
            $nodes[] = \XLite\View\Location\Node::create(
                $category->getName(),
                $this->getCategoryURL($category)
            );
        }

        return $nodes;
    }

    /**
     * Add part to the location nodes list
     *
     * @return void
     */
    protected function addBaseLocation()
    {
        parent::addBaseLocation();

        foreach ($this->getCategoryPath() as $category) {
            if ($category->isVisible()) {
                $this->addLocationNode(
                    $category->getName(),
                    $this->getCategoryURL($category),
                    $this->getLocationNodeSubnodes($category)
                );

            } else {
                break;
            }
        }
    }

    /**
     * Get cart fingerprint exclude keys
     *
     * @return array
     */
    protected function getCartFingerprintExclude()
    {
        return array('shippingMethodsHash', 'paymentMethodsHash', 'shippingMethodId', 'paymentMethodId', 'shippingTotal');
    }
}
