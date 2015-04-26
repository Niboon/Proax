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

namespace XLite\Module\XC\ThemeTweaker\Controller\Admin;

/**
 * Theme tweaker template controller
 */
class ThemeTweakerTemplate extends \XLite\Controller\Admin\AAdmin
{
    /**
     * Controller parameters
     *
     * @var array
     */
    protected $params = array('target', 'id', 'template');

    /**
     * Return the current page title (for the content area)
     *
     * @return string
     */
    public function getTitle()
    {
        return static::t('Look & Feel') . ' :: ' . $this->getTemplateLocalPath();
    }

    /**
     * Is create request
     *
     * @return boolean
     */
    public function isCreate()
    {
        return (bool) \XLite\Core\Request::getInstance()->template;
    }

    /**
     * Update model
     *
     * @return void
     */
    protected function doActionUpdate()
    {
        if ($this->getModelForm()->performAction('modify')) {

            if (\Xlite\Core\Request::getInstance()->isCreate) {

                echo <<<HTML
<script type="text/javascript">window.opener.location.reload();window.close()</script>
HTML;
                exit;

            } else {
                $this->setReturnUrl(\XLite\Core\Converter::buildURL('theme_tweaker_templates'));
            }
        }
    }

    /**
     * Get model form class
     *
     * @return string
     */
    protected function getModelFormClass()
    {
        return 'XLite\Module\XC\ThemeTweaker\View\Model\Template';
    }

    /**
     * Returns current template short path
     *
     * @return string
     */
    protected function getTemplateLocalPath()
    {
        $localPath = '';

        if ($this->isCreate()) {
            $localPath = \XLite\Core\Request::getInstance()->template;
        } elseif (\XLite\Core\Request::getInstance()->id) {
            $template = \XLite\Core\Database::getRepo('XLite\Module\XC\ThemeTweaker\Model\Template')
                ->find(\XLite\Core\Request::getInstance()->id);

            $localPath = $template ? $template->getTemplate() : '';
        }

        return $localPath;
    }
}
