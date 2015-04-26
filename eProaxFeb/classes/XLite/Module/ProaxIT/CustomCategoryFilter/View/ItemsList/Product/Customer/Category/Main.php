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
 * @copyright Copyright (c) 2011-2014 Qualiteam software Ltd <info@x-cart.com>. All rights reserved
 * @license   http://www.x-cart.com/license-agreement.html X-Cart 5 License Agreement
 * @link      http://www.x-cart.com/
 */

namespace XLite\Module\ProaxIT\CustomCategoryFilter\View\ItemsList\Product\Customer\Category;

use XLite\Base\IDecorator;

/**
 * Category products list widget
 */
class Main extends \XLite\View\ItemsList\Product\Customer\Category\Main implements IDecorator
{
    /**
     * CategoryPath
     *
     * @var string
     */
    protected $categoryPath;

    /**
     * Widget parameter names
     */
    const PARAM_CATEGORY_PATH = 'category_path';

    /**
     * Get requested category object
     *
     * @return string
     */
    protected function getCategoryPath()
    {
        return \XLite\Core\Request::getInstance()->{static::PARAM_CATEGORY_PATH}
            ? : "ROOT;" . \XLite\Core\Database::getRepo('XLite\Model\Category')->find($this->getCategoryId())->getName() . ";";
    }

    /**
     * Define so called "request" parameters
     *
     * @return void
     */
    protected function defineRequestParams()
    {
        parent::defineRequestParams();

        $this->requestParams[] = static::PARAM_CATEGORY_PATH;
    }

    /**
     * Return name of the session cell identifier
     *
     * @return string
     */
    protected function getSessionCell()
    {
        return parent::getSessionCell() .
        \XLite\Core\Request::getInstance()->{static::PARAM_CATEGORY_PATH};
    }

    /**
     * Defines if the widget is listening to #hash changes
     *
     * @return boolean
     */
    protected function getListenToHash()
    {
        return false;
    }

}
