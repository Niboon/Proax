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

return function()
{
    $prefix = \XLite\Core\Database::getInstance()->getTablePrefix();

    // Create tables
    $query = file_get_contents(__DIR__ . LC_DS . 'dump.sql');
    $query = preg_replace('/%%PREFIX%%/', $prefix, $query);

    $tmpFileName = sprintf('dump-%d.sql', time());

    file_put_contents($tmpFileName, $query);

    \Includes\Utils\Database::uploadSQLFromFile($tmpFileName, true);

    \Includes\Utils\FileManager::deleteFile($tmpFileName);

    // Get the default language code
    $code = \XLite::getInstance()->getDefaultLanguage() ?: 'en';

    // Copy multilingual data of pages
    $pages = \XLite\Core\Database::getRepo('XLite\Module\CDev\SimpleCMS\Model\Page')->findAll();

    $queries = array();

    $tableName = $prefix . 'page_translations';

    foreach ($pages as $page) {
        $id           = $page->getId();
        $name         = addslashes($page->getName());
        $teaser       = addslashes($page->getTeaser());
        $body         = addslashes($page->getBody());
        $metaKeywords = addslashes($page->getMetaKeywords());

        $queries[] = <<<OUT
INSERT INTO $tableName
(id, name, teaser, body, metaKeywords, code)
VALUES ($id, '$name', '$teaser', '$body', '$metaKeywords', '$code');
OUT;
    }

    \XLite\Core\Database::getInstance()->executeQueries($queries);


    // Copy multilingual data of menus
    $menus = \XLite\Core\Database::getRepo('XLite\Module\CDev\SimpleCMS\Model\Menu')->findAll();

    $queries = array();

    $tableName = $prefix . 'menu_translations';

    foreach ($menus as $menu) {
        $id   = $menu->getId();
        $name = addslashes($menu->getName());

        $queries[] = <<<OUT
INSERT INTO $tableName
(id, name, code)
VALUES ($id, '$name', '$code');
OUT;
    }

    \XLite\Core\Database::getInstance()->executeQueries($queries);
};
