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

namespace XLite\Model\Base;

/**
 * Dump entity - without DB storage
 */
abstract class Dump extends \XLite\Model\AEntity
{

    /**
     * Unique ID
     *
     * @var integer
     */
    protected $id;

    /**
     * Get entity repository
     *
     * @return \XLite\Model\Doctrine\Repo\AbstractRepo
     */
    public function getRepository()
    {
        return null;
    }

    /**
     * Update entity
     *
     * @return boolean
     */
    public function update()
    {
        return true;
    }

    /**
     * Delete entity
     *
     * @return boolean
     */
    public function delete()
    {
        return true;
    }

    /**
     * Get entity unique identifier name
     *
     * @return string
     */
    public function getUniqueIdentifierName()
    {
        return 'id';
    }

   /**
     * Process files
     *
     * @param mixed $file       File
     * @param array $data       Data to save
     * @param array $properties Properties
     *
     * @return void
     */
    protected function processFile($file, $data, $properties)
    {
        return true;
    }

    /**
     * Clone
     *
     * @return \XLite\Model\AEntity
     */
    public function cloneEntity()
    {
        return clone $this;
    }

    /**
     * Detach static
     *
     * @return void
     */
    public function detach()
    {
    }

    /**
     * The Entity state getter
     *
     * @return integer
     */
    protected function getEntityState()
    {
        return null;
    }

}
