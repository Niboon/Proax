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

namespace XLite\Module\CDev\SimpleCMS\Controller\Admin;

/**
 * Page controller
 *
 */
class Page extends \XLite\Controller\Admin\AAdmin
{
    /**
     * Controller parameters
     *
     * @var array
     */
    protected $params = array('target', 'id');

    /**
     * Check ACL permissions
     *
     * @return boolean
     */
    public function checkACL()
    {
        return parent::checkACL()
            || \XLite\Core\Auth::getInstance()->isPermissionAllowed('manage custom pages');
    }

    /**
     * Return the current page title (for the content area)
     *
     * @return string
     */
    public function getTitle()
    {
        $id = intval(\XLite\Core\Request::getInstance()->id);
        $model = $id
            ? \XLite\Core\Database::getRepo('XLite\Module\CDev\SimpleCMS\Model\Page')->find($id)
            : null;

        return ($model && $model->getId())
            ? static::t('Edit page')
            : static::t('New page');
    }

    /**
     * Update model
     *
     * @return void
     */
    protected function doActionUpdate()
    {
        $this->getModelForm()->performAction('modify');
        if (!\XLite\Core\Request::getInstance()->id) {
            $this->setReturnURL(
                $this->buildURL(
                    'page',
                    '',
                    array('id' => $this->getModelForm()->getModelObject()->getId())
                )
            );
        }
    }

    /**
     * Update model and close page
     *
     * @return void
     */
    protected function doActionUpdateAndClose()
    {
        if ($this->getModelForm()->performAction('modify')) {
            $this->setReturnUrl(
                \XLite\Core\Converter::buildURL('pages')
            );
        }
    }

    /**
     * Get model form class
     *
     * @return string
     */
    protected function getModelFormClass()
    {
        return 'XLite\Module\CDev\SimpleCMS\View\Model\Page';
    }

}
