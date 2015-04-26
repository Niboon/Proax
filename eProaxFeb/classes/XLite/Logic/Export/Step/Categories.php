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

namespace XLite\Logic\Export\Step;

/**
 * Categories
 */
class Categories extends \XLite\Logic\Export\Step\Base\I18n
{
    // {{{ Data

    /**
     * Get repository
     *
     * @return \XLite\Model\Repo\ARepo
     */
    protected function getRepository()
    {
        return \XLite\Core\Database::getRepo('XLite\Model\Category');
    }

    // }}}

    // {{{ Columns

    /**
     * Define columns
     *
     * @return array
     */
    protected function defineColumns()
    {
        $columns = array(
            'path'        => array(),
            'enabled'     => array(),
            'showTitle'   => array(),
            'position'    => array(),
            'memberships' => array(),
            'image'       => array(),
            'cleanURL'    => array(),
        );

        $columns += $this->assignI18nColumns(
            array(
                'name'        => array(),
                'description' => array(),
                'metaTags'    => array(),
                'metaDesc'    => array(),
                'metaTitle'   => array(),
            )
        );

        return $columns;
    }

    // }}}

    // {{{ Getters and formatters

    /**
     * Get column value for 'path' column
     *
     * @param array   $dataset Dataset
     * @param string  $name    Column name
     * @param integer $i       Subcolumn index
     *
     * @return string
     */
    protected function getPathColumnValue(array $dataset, $name, $i)
    {
        $result = array();
        foreach ($this->getRepository()->getCategoryPath($dataset['model']->getCategoryId()) as $category) {
            $result[] = $category->getName();
        }

        return implode(' >>> ', $result);
    }

    /**
     * Get column value for 'enabled' column
     *
     * @param array   $dataset Dataset
     * @param string  $name    Column name
     * @param integer $i       Subcolumn index
     *
     * @return string
     */
    protected function getEnabledColumnValue(array $dataset, $name, $i)
    {
        return $this->getColumnValueByName($dataset['model'], 'enabled');
    }

    /**
     * Get column value for 'showTitle' column
     *
     * @param array   $dataset Dataset
     * @param string  $name    Column name
     * @param integer $i       Subcolumn index
     *
     * @return string
     */
    protected function getShowTitleColumnValue(array $dataset, $name, $i)
    {
        return $this->getColumnValueByName($dataset['model'], 'show_title');
    }

    /**
     * Get column value for 'position' column
     *
     * @param array   $dataset Dataset
     * @param string  $name    Column name
     * @param integer $i       Subcolumn index
     *
     * @return string
     */
    protected function getPositionColumnValue(array $dataset, $name, $i)
    {
        return $this->getColumnValueByName($dataset['model'], 'pos');
    }

    /**
     * Get column value for 'memberships' column
     *
     * @param array   $dataset Dataset
     * @param string  $name    Column name
     * @param integer $i       Subcolumn index
     *
     * @return string
     */
    protected function getMembershipsColumnValue(array $dataset, $name, $i)
    {
        $result = array();

        foreach ($dataset['model']->getMemberships() as $membership) {
            $result[] = $membership->getName();
        }

        return $result;
    }

    /**
     * Get column value for 'image' column
     *
     * @param array   $dataset Dataset
     * @param string  $name    Column name
     * @param integer $i       Subcolumn index
     *
     * @return string
     */
    protected function getImageColumnValue(array $dataset, $name, $i)
    {
        return $dataset['model']->getImage();
    }

    /**
     * Get column value for 'cleanUrl' column
     *
     * @param array   $dataset Dataset
     * @param string  $name    Column name
     * @param integer $i       Subcolumn index
     *
     * @return string
     */
    protected function getCleanUrlColumnValue(array $dataset, $name, $i)
    {
        return $this->getColumnValueByName($dataset['model'], 'cleanUrl');
    }

    /**
     * Copy resource
     *
     * @param \XLite\Model\Base\Storage $storage      Storage
     * @param string                    $subdirectory Subdirectory
     *
     * @return boolean
     */
    protected function copyResource(\XLite\Model\Base\Storage $storage, $subdirectory)
    {
        if ($storage instanceOf \XLite\Model\Base\Image) {
            $subdirectory .= LC_DS . 'categories';
        }

        return parent::copyResource($storage, $subdirectory);
    }

    // }}}

}
