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

namespace XLite\Logic\Import\Processor;

/**
 * Categories import processor
 */
class Categories extends \XLite\Logic\Import\Processor\AProcessor
{
    /**
     * Get title
     *
     * @return string
     */
    public static function getTitle()
    {
        return static::t('Categories imported');
    }

    /**
     * Mark all images as processed
     *
     * @return void
     */
    public function markAllImagesAsProcessed()
    {
        \XLite\Core\Database::getRepo('XLite\Model\Image\Category\Image')->unmarkAsProcessed();
    }

    /**
     * Get repository
     *
     * @return \XLite\Model\Repo\ARepo
     */
    protected function getRepository()
    {
        return \XLite\Core\Database::getRepo('XLite\Model\Category');
    }

    // {{{ Columns

    /**
     * Define columns
     *
     * @return array
     */
    protected function defineColumns()
    {
        return array(
            'path'              => array(
                static::COLUMN_IS_KEY          => true,
            ),
            'enabled'           => array(),
            'showTitle'         => array(),
            'position'          => array(),
            'memberships'       => array(
                static::COLUMN_IS_MULTIPLE     => true
            ),
            'image'             => array(),
            'cleanURL'          => array(),
            'name'              => array(
                static::COLUMN_IS_MULTILINGUAL => true,
            ),
            'description'       => array(
                static::COLUMN_IS_MULTILINGUAL => true,
            ),
            'metaTags'          => array(
                static::COLUMN_IS_MULTILINGUAL => true,
            ),
            'metaDesc'          => array(
                static::COLUMN_IS_MULTILINGUAL => true,
            ),
            'metaTitle'         => array(
                static::COLUMN_IS_MULTILINGUAL => true,
            ),
        );
    }

    // }}}

    // {{{ Verification

    /**
     * Get messages
     *
     * @return array
     */
    public static function getMessages()
    {
        return parent::getMessages()
            + array(
                'CATEGORY-ENABLED-FMT'      => 'Wrong enabled format',
                'CATEGORY-SHOW-TITLE-FMT'   => 'Wrong show title format',
                'CATEGORY-POSITION-FMT'     => 'Wrong position format',
                'CATEGORY-NAME-FMT'         => 'The name is empty',
            );
    }

    /**
     * Verify 'path' value
     *
     * @param mixed $value  Value
     * @param array $column Column info
     *
     * @return void
     */
    protected function verifyPath($value, array $column)
    {
    }

    /**
     * Verify 'enabled' value
     *
     * @param mixed $value  Value
     * @param array $column Column info
     *
     * @return void
     */
    protected function verifyEnabled($value, array $column)
    {
        if (!$this->verifyValueAsEmpty($value) && !$this->verifyValueAsBoolean($value)) {
            $this->addWarning('CATEGORY-ENABLED-FMT', array('column' => $column, 'value' => $value));
        }
    }

    /**
     * Verify 'show title' value
     *
     * @param mixed $value  Value
     * @param array $column Column info
     *
     * @return void
     */
    protected function verifyShowTitle($value, array $column)
    {
        if (!$this->verifyValueAsEmpty($value) && !$this->verifyValueAsBoolean($value)) {
            $this->addWarning('CATEGORY-SHOW-TITLE-FMT', array('column' => $column, 'value' => $value));
        }
    }

    /**
     * Verify 'position' value
     *
     * @param mixed $value  Value
     * @param array $column Column info
     *
     * @return void
     */
    protected function verifyPosition($value, array $column)
    {
        if (!$this->verifyValueAsEmpty($value) && !$this->verifyValueAsUinteger($value)) {
            $this->addWarning('CATEGORY-POSITION-FMT', array('column' => $column, 'value' => $value));
        }
    }

    /**
     * Verify 'memberships' value
     *
     * @param mixed $value  Value
     * @param array $column Column info
     *
     * @return void
     */
    protected function verifyMemberships($value, array $column)
    {
        if (!$this->verifyValueAsEmpty($value)) {
            foreach ($value as $membership) {
                if (!$this->verifyValueAsEmpty($membership) && !$this->verifyValueAsMembership($membership)) {
                    $this->addWarning('GLOBAL-MEMBERSHIP-FMT', array('column' => $column, 'value' => $membership));
                }
            }
        }
    }

    /**
     * Verify 'image' value
     *
     * @param mixed $value  Value
     * @param array $column Column info
     *
     * @return void
     */
    protected function verifyImage($value, array $column)
    {
        if (!$this->verifyValueAsEmpty($value) && !$this->verifyValueAsFile($value)) {
            $this->addWarning('GLOBAL-IMAGE-FMT', array('column' => $column, 'value' => $value));
        }
    }

    /**
     * Verify 'clean URL' value
     *
     * @param mixed $value  Value
     * @param array $column Column info
     *
     * @return void
     */
    protected function verifyCleanURL($value, array $column)
    {
    }

    /**
     * Verify 'name' value
     *
     * @param mixed $value  Value
     * @param array $column Column info
     *
     * @return void
     */
    protected function verifyName($value, array $column)
    {
        $value = $this->getDefLangValue($value);
        if ($this->verifyValueAsEmpty($value)) {
            $this->addError('CATEGORY-NAME-FMT', array('column' => $column, 'value' => $value));
        }
    }

    /**
     * Verify 'description' value
     *
     * @param mixed $value  Value
     * @param array $column Column info
     *
     * @return void
     */
    protected function verifyDescription($value, array $column)
    {
    }

    /**
     * Verify 'meta tags' value
     *
     * @param mixed $value  Value
     * @param array $column Column info
     *
     * @return void
     */
    protected function verifyMetaTags($value, array $column)
    {
    }

    /**
     * Verify 'meta desc' value
     *
     * @param mixed $value  Value
     * @param array $column Column info
     *
     * @return void
     */
    protected function verifyMetaDesc($value, array $column)
    {
    }

    /**
     * Verify 'meta title' value
     *
     * @param mixed $value  Value
     * @param array $column Column info
     *
     * @return void
     */
    protected function verifyMetaTitle($value, array $column)
    {
    }

    // }}}

    // {{{ Normalizators

    /**
     * Normalize 'enabled' value
     *
     * @param mixed @value Value
     *
     * @return boolean
     */
    protected function normalizeEnabledValue($value)
    {
        return $this->normalizeValueAsBoolean($value);
    }

    /**
     * Normalize 'show title' value
     *
     * @param mixed @value Value
     *
     * @return boolean
     */
    protected function normalizeShowTitleValue($value)
    {
        return $this->normalizeValueAsBoolean($value);
    }

    /**
     * Normalize 'position' value
     *
     * @param mixed @value Value
     *
     * @return integer
     */
    protected function normalizePositionValue($value)
    {
        return abs(intval($value));
    }

    // }}}

    // {{{ Import

    /**
     * Detect model
     *
     * @param array $data Data
     *
     * @return \XLite\Model\AEntity
     */
    protected function detectModel(array $data)
    {
        return $this->getCategoryByPath(isset($data['path']) ? $data['path'] : '', false);
    }

    /**
     * Create model
     *
     * @param array $data Data
     *
     * @return \XLite\Model\AEntity
     */
    protected function createModel(array $data)
    {
        return $this->addCategoryByPath(isset($data['path']) ? $data['path'] : '');
    }

    /**
     * Import 'memberships' value
     *
     * @param \XLite\Model\Category $model  Category
     * @param array                 $value  Value
     * @param array                 $column Column info
     *
     * @return void
     */
    protected function importMembershipsColumn(\XLite\Model\Category $model, array $value, array $column)
    {
        if ($model->getMemberships()) {
            foreach ($model->getMemberships() as $membership) {
                $membership->getCategories()->removeElement($model);
            }
            $model->getMemberships()->clear();
        }

        if ($value) {
            foreach ($value as $membership) {
                $membership = $this->normalizeValueAsMembership($membership);
                if ($membership) {
                    $model->addMemberships($membership);
                    $membership->addCategory($model);
                }
            }
        }
    }

    /**
     * Import 'image' value
     *
     * @param \XLite\Model\Category $model  Category
     * @param string                $value  Value
     * @param array                 $column Column info
     *
     * @return void
     */
    protected function importImageColumn(\XLite\Model\Category $model, $value, array $column)
    {
        if ($value && $this->verifyValueAsFile($value)) {
            $image = $model->getImage();
            if (!$image) {
                $image = new \XLite\Model\Image\Category\Image();
                $image->setCategory($model);
                $model->setImage($image);
                \XLite\Core\Database::getEM()->persist($image);
            }

            if (1 < count(parse_url($value))) {
                $image->loadFromURL($value, true);

            } else {
                $dir = \Includes\Utils\FileManager::getRealPath(LC_DIR_VAR . $this->importer->getOptions()->dir);

                $image->loadFromLocalFile($dir . LC_DS . $value);
            }
        }
    }

    /**
     * Import 'cleanURL' value
     *
     * @param \XLite\Model\Category $model  Category
     * @param string                $value  Value
     * @param array                 $column Column info
     *
     * @return void
     */
    protected function importCleanURLColumn(\XLite\Model\Category $model, $value, array $column)
    {
        $this->updateCleanURL($model, $value);
    }

    // }}}
}
