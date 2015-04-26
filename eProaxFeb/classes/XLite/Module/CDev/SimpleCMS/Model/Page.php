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

namespace XLite\Module\CDev\SimpleCMS\Model;

/**
 * Page
 *
 * @Entity
 * @Table  (name="pages",
 *      indexes={
 *          @Index (name="enabled", columns={"enabled"}),
 *      }
 * )
 */
class Page extends \XLite\Model\Base\Catalog
{
    /**
     * Unique ID
     *
     * @var integer
     *
     * @Id
     * @GeneratedValue (strategy="AUTO")
     * @Column         (type="integer", options={ "unsigned": true })
     */
    protected $id;

    /**
     * Is menu enabled or not
     *
     * @var boolean
     *
     * @Column (type="boolean")
     */
    protected $enabled = true;

    /**
     * One-to-one relation with page_images table
     *
     * @var \XLite\Module\CDev\SimpleCMS\Model\Image\Page\Image
     *
     * @OneToOne  (targetEntity="XLite\Module\CDev\SimpleCMS\Model\Image\Page\Image", mappedBy="page", cascade={"all"})
     */
    protected $image;

    /**
     * Clean URLs
     *
     * @var \Doctrine\Common\Collections\Collection
     *
     * @OneToMany (targetEntity="XLite\Model\CleanURL", mappedBy="page", cascade={"all"})
     * @OrderBy   ({"id" = "ASC"})
     */
    protected $cleanURLs;

    /**
     * Get front URL
     *
     * @return string
     */
    public function getFrontURL()
    {
        $result = null;

        if ($this->getId()) {
            $result = \XLite\Core\Converter::makeURLValid(
                \XLite::getInstance()->getShopURL(
                    \XLite\Core\Converter::buildURL('page', '', array('id' => $this->getId()), 'cart.php', true)
                )
            );
        }

        return $result;
    }
}
