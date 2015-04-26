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

namespace XLite\Module\CDev\FileAttachments\Logic\Import\Processor;

/**
 * Products
 */
abstract class Products extends \XLite\Logic\Import\Processor\Products implements \XLite\Base\IDecorator
{
    // {{{ Columns

    /**
     * Define columns
     *
     * @return array
     */
    protected function defineColumns()
    {
        $columns = parent::defineColumns();

        $columns['attachments'] = array(
            static::COLUMN_IS_MULTIPLE => true,
        );
        $columns['attachmentsTitle'] = array(
            static::COLUMN_IS_MULTIPLE     => true,
            static::COLUMN_IS_MULTILINGUAL => true,
        );
        $columns['attachmentsDescription'] = array(
            static::COLUMN_IS_MULTIPLE     => true,
            static::COLUMN_IS_MULTILINGUAL => true,
        );

        return $columns;
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
                'PRODUCT-ATTACH-FMT' => 'The "{{value}}" file is not created',
            );
    }

    /**
     * Verify 'attachments' value
     *
     * @param mixed $value  Value
     * @param array $column Column info
     *
     * @return void
     */
    protected function verifyAttachments($value, array $column)
    {
        if (!$this->verifyValueAsEmpty($value)) {
            foreach ($value as $attachment) {
                if (!$this->verifyValueAsEmpty($attachment) && !$this->verifyValueAsFile($attachment)) {
                    $this->addWarning('PRODUCT-ATTACH-FMT', array('column' => $column, 'value' => $attachment));
                }
            }
        }
    }

    // }}}

    // {{{ Import

    /**
     * Import 'attachments' value
     *
     * @param \XLite\Model\Product $model  Product
     * @param array                $value  Value
     * @param array                $column Column info
     *
     * @return void
     */
    protected function importAttachmentsColumn(\XLite\Model\Product $model, array $value, array $column)
    {
        if ($value) {
            foreach ($value as $index => $path) {
                if ($this->verifyValueAsFile($path)) {
                    $attachment = $model->getAttachments()->get($index);
                    if (!$attachment) {
                        $attachment = new \XLite\Module\CDev\FileAttachments\Model\Product\Attachment();
                        $attachment->setProduct($model);
                        $model->getAttachments()->add($attachment);

                        \XLite\Core\Database::getEM()->persist($attachment);
                    }

                    if (1 < count(parse_url($path))) {
                        $attachment->getStorage()->loadFromURL($path, true);

                    } else {
                        $attachment->getStorage()->loadFromLocalFile($this->importer->getOptions()->dir . LC_DS . $path);
                    }
                }
            }

            while (count($model->getAttachments()) > count($value)) {
                $attachment = $model->getAttachments()->last();
                \XLite\Core\Database::getRepo('XLite\Module\CDev\FileAttachments\Model\Product\Attachment')->delete($attachment, false);
                $model->getAttachments()->removeElement($attachment);
            }
        }
    }

    /**
     * Import 'attachmentsTitle' value
     *
     * @param \XLite\Model\Product $model  Product
     * @param array                $value  Value
     * @param array                $column Column info
     *
     * @return void
     */
    protected function importAttachmentsTitleColumn(\XLite\Model\Product $model, array $value, array $column)
    {
        if ($value) {
            foreach ($value as $index => $val) {
                $attachment = $model->getAttachments()->get($index);
                if ($attachment) {
                    $this->updateModelTranslations($attachment, $val, 'title');
                }
            }
        }
    }

    /**
     * Import 'attachmentsDescription' value
     *
     * @param \XLite\Model\Product $model  Product
     * @param array                $value  Value
     * @param array                $column Column info
     *
     * @return void
     */
    protected function importAttachmentsDescriptionColumn(\XLite\Model\Product $model, array $value, array $column)
    {
        if ($value) {
            foreach ($value as $index => $val) {
                $attachment = $model->getAttachments()->get($index);
                if ($attachment) {
                    $this->updateModelTranslations($attachment, $val, 'description');
                }
            }
        }
    }

    // }}}
}
