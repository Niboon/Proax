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

namespace XLite\Module\XC\RESTAPI\Model\Repo\Base;

/**
 * Storage  repository
 */
abstract class Storage extends \XLite\Model\Repo\Base\Storage implements \XLite\Base\IDecorator
{

    /**
     * Load raw fixture
     *
     * @param \XLite\Model\AEntity $entity  Entity
     * @param array                $record  Record
     * @param array                $regular Regular fields info OPTIONAL
     * @param array                $assocs  Associations info OPTIONAL
     *
     * @return void
     */
    public function loadRawFixture(\XLite\Model\AEntity $entity, array $record, array $regular = array(), array $assocs = array())
    {
        $path = null;
        if (!empty($record['content']) && !empty($record['path'])) {
            $path = $record['path'];
            unset($record['path']);
        }

        parent::loadRawFixture($entity, $record, $regular, $assocs);

        if (\XLite\Core\Database::getEM()->contains($entity) && !empty($record['content'])) {
            $content = base64_decode($record['content'], true);
            if ($content) {
                $filename = basename($path);
                $path = LC_DIR_TMP . $filename;
                file_put_contents($path, $content);
                unset($content);
                $entity->loadFromLocalFile($path, $filename);
                unlink($path);
            }
        }
    }

}
