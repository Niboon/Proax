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

namespace XLite\Module\CDev\FeaturedProducts\Controller\Admin;

/**
 * Featured products
 */
class FeaturedProducts extends \XLite\Controller\Admin\AAdmin
{

    /**
     * params
     *
     * @var string
     */
    protected $params = array('target', 'id');

    // {{{ Search

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
     * Save search conditions
     *
     * @return void
     */
    protected function doActionSearch()
    {
        $cellName = \XLite\Module\CDev\FeaturedProducts\View\ItemsList\Model\FeaturedProduct::getSessionCellName();

        \XLite\Core\Session::getInstance()->$cellName = $this->getSearchParams();
    }

    /**
     * Update list
     *
     * @return void
     */
    protected function doActionUpdate()
    {
        $list = new \XLite\Module\CDev\FeaturedProducts\View\ItemsList\Model\FeaturedProduct();
        $list->processQuick();
    }

    /**
     * Return search parameters
     *
     * @return array
     */
    protected function getSearchParams()
    {
        $searchParams = $this->getConditions();

        foreach (
            \XLite\Module\CDev\FeaturedProducts\View\ItemsList\Model\FeaturedProduct::getSearchParams() as $requestParam
        ) {
            if (isset(\XLite\Core\Request::getInstance()->$requestParam)) {
                $searchParams[$requestParam] = \XLite\Core\Request::getInstance()->$requestParam;
            }
        }

        return $searchParams;
    }

    /**
     * Get search conditions
     *
     * @return array
     */
    protected function getConditions()
    {
        $cellName = \XLite\Module\CDev\FeaturedProducts\View\ItemsList\Model\FeaturedProduct::getSessionCellName();

        $searchParams = \XLite\Core\Session::getInstance()->$cellName;

        if (!is_array($searchParams)) {
            $searchParams = array();
        }

        return $searchParams;
    }

    // }}}

    /**
     * Return the current page title (for the content area)
     *
     * @return string
     */
    public function getTitle()
    {
        return \XLite\Core\Request::getInstance()->id
            ? static::t('Manage category (X)', array('category_name' => $this->getCategoryName()))
            : static::t('Front page');
    }

    /**
     * Return the category name for the title
     *
     * @return string
     */
    public function getCategoryName()
    {
        $id = \XLite\Core\Request::getInstance()->id ?: $this->getRootCategoryId();

        return \XLite\Core\Database::getRepo('XLite\Model\Category')->find($id)
            ->getName();
    }

    /**
     * Get featured products list
     *
     * @return array(\XLite\Module\CDev\FeaturedProducts\Model\FeaturedProduct) Objects
     */
    public function getFeaturedProductsList()
    {
        return \XLite\Core\Database::getRepo('\XLite\Module\CDev\FeaturedProducts\Model\FeaturedProduct')
            ->getFeaturedProducts($this->id);
    }

    /**
     * doActionAddFeaturedProducts
     *
     * @return void
     */
    protected function doActionAdd()
    {
        if (isset(\XLite\Core\Request::getInstance()->select)) {
            $pids = \XLite\Core\Request::getInstance()->select;
            $products = \XLite\Core\Database::getRepo('\XLite\Model\Product')
                ->findByIds($pids);

            $this->id = \XLite\Core\Request::getInstance()->id ?: $this->getRootCategoryId();
            $category = \XLite\Core\Database::getRepo('\XLite\Model\Category')->find($this->id);

            $existingLinksIds = array();
            $existingLinks = $this->getFeaturedProductsList();

            if ($existingLinks) {
                foreach ($existingLinks as $k => $v) {
                    $existingLinksIds[] = $v->getProduct()->getProductId();
                }
            }

            if ($products) {
                foreach ($products as $product) {
                    if (in_array($product->getProductId(), $existingLinksIds)) {
                        \XLite\Core\TopMessage::addWarning(
                            'The product SKU#"X" is already set as featured for the category',
                            array('SKU' => $product->getSku())
                        );
                    } else {
                        $fp = new \XLite\Module\CDev\FeaturedProducts\Model\FeaturedProduct();
                        $fp->setProduct($product);

                        if ($category) {
                            $fp->setCategory($category);
                        }

                        \XLite\Core\Database::getEM()->persist($fp);
                    }
                }
            }

            \XLite\Core\Database::getEM()->flush();
        }

        $this->setReturnURL($this->buildURL(
            'featured_products',
            '',
            \XLite\Core\Request::getInstance()->id
                ? array('id' => $this->id)
                : array()
        ));
    }
}
