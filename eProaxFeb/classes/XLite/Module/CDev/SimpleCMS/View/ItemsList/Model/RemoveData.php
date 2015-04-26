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
 * Remove data items list
 */
abstract class RemoveData extends \XLite\View\ItemsList\Model\RemoveData implements \XLite\Base\IDecorator
{
    const TYPE_PAGES = 'pages';

    /**
     * Get plain data
     *
     * @return array
     */
    protected function getPlainData()
    {
        return parent::getPlainData() + array(
            static::TYPE_PAGES => array(
                'name' => static::t('Pages'),
            ),
        );
    }

    /**
     * Build metod name
     *
     * @param \XLite\Model\AEntity $entity  Entity
     * @param string               $pattern Pattern
     *
     * @return string
     */
    protected function buildMetodName(\XLite\Model\AEntity $entity, $pattern)
    {
        return static::TYPE_PAGES == $entity->getId()
            ? sprintf($pattern, 'Pages')
            : parent::buildMetodName($entity, $pattern);
    }

    /**
     * Check - allow remove coupons or not
     *
     * @return boolean
     */
    protected function isAllowRemovePages()
    {
        return 0 < \XLite\Core\Database::getRepo('XLite\Module\CDev\SimpleCMS\Model\Page')->count();
    }

    /**
     * Remove coupons
     *
     * @return integer
     */
    protected function removePages()
    {
        return $this->removeCommon('XLite\Module\CDev\SimpleCMS\Model\Page');
    }

}
