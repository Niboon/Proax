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

namespace XLite\Module\CDev\UserPermissions\View\FormField;

/**
 * Permissions selector
 */
class Permissions extends \XLite\View\FormField\Select\Tags\ATags
{
    /**
     * Root permission
     *
     * @var \XLite\Model\Role\Permission
     */
    protected $root;

    /**
     * Register JS files
     *
     * @return array
     */
    public function getJSFiles()
    {
        $list = parent::getJSFiles();

        $list[] = 'modules/CDev/UserPermissions/role/permissions.js';

        return $list;
    }

    /**
     * Return default options list
     *
     * @return array
     */
    protected function getDefaultOptions()
    {
        $list = array();

        foreach (\XLite\Core\Database::getRepo('XLite\Model\Role\Permission')->findByEnabled(true) as $perm) {
            $section = $perm->getSection();
            if (!isset($list[$section])) {
                $list[$section] = array(
                    'label'   => $section,
                    'options' => array(),
                );
            }

            $list[$section]['options'][$perm->getId()] = $perm->getPublicName();
        }

        return $list;
    }

    /**
     * Get option attributes
     *
     * @param mixed $value Value
     * @param mixed $text  Text
     *
     * @return array
     */
    protected function getOptionAttributes($value, $text)
    {
        $list = parent::getOptionAttributes($value, $text);

        if ($value == $this->getRootPermission()->getId()) {
            $list['data-isRoot'] = '1';
        }

        return $list;
    }

    /**
     * Get root permission
     *
     * @return void
     */
    protected function getRootPermission()
    {
        if (!isset($this->root)) {
            $this->root = \XLite\Core\Database::getRepo('XLite\Model\Role\Permission')
                ->findOneBy(array('code' => \XLite\Model\Role\Permission::ROOT_ACCESS));
        }

        return $this->root;
    }
}
