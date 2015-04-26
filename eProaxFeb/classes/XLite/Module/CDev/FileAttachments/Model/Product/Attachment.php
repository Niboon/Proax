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

namespace XLite\Module\CDev\FileAttachments\Model\Product;

/**
 * Product attchament 
 *
 * @Entity
 * @Table  (name="product_attachments",
 *      indexes={
 *          @Index (name="o", columns={"orderby"})
 *      }
 * )
 */
class Attachment extends \XLite\Model\Base\I18n
{
    // {{{ Collumns

    /**
     * Unique id
     *
     * @var integer
     *
     * @Id
     * @GeneratedValue (strategy="AUTO")
     * @Column         (type="uinteger")
     */
    protected $id;

    /**
     * Sort position
     *
     * @var integer
     *
     * @Column (type="integer")
     */
    protected $orderby = 0;

    // }}}

    // {{{ Associations

    /**
     * Relation to a product entity
     *
     * @var \XLite\Model\Product
     *
     * @ManyToOne  (targetEntity="XLite\Model\Product", inversedBy="attachments")
     * @JoinColumn (name="product_id", referencedColumnName="product_id")
     */
    protected $product;

    /**
     * Relation to a product entity
     *
     * @var \XLite\Module\CDev\FileAttachments\Model\Product\Attachment\Storage
     *
     * @OneToOne  (targetEntity="XLite\Module\CDev\FileAttachments\Model\Product\Attachment\Storage", mappedBy="attachment", cascade={"all"}, fetch="EAGER")
     */
    protected $storage;

    // }}}

    // {{{ Getters / setters

    /**
     * Get storage 
     * 
     * @return \XLite\Module\CDev\FileAttachments\Model\Product\Attachment\Storage
     */
    public function getStorage()
    {
        if (!$this->storage) {
            $this->setStorage(new \XLite\Module\CDev\FileAttachments\Model\Product\Attachment\Storage);
            $this->storage->setAttachment($this);
        }

        return $this->storage;
    }

    /**
     * Get public title 
     * 
     * @return string
     */
    public function getPublicTitle()
    {
        return $this->getTitle() ?: $this->getStorage()->getFileName();
    }

    // }}}

    /**
     * Clone for product
     *
     * @param \XLite\Model\Product $product Product
     *
     * @return \XLite\Model\AEntity
     */
    public function cloneEntityForProduct(\XLite\Model\Product $product)
    {
        $newAttachment = parent::cloneEntity();
        
        $newAttachment->setProduct($product);
        $product->addAttachments($newAttachment);

        $this->getStorage()->cloneEntityForAttachment($newAttachment);

        return $newAttachment;
    }
}
