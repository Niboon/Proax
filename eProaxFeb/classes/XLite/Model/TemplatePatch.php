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

namespace XLite\Model;

/**
 * Template patch
 *
 * @Entity (repositoryClass="\XLite\Model\Repo\TemplatePatch")
 * @Table  (name="template_patches",
 *          indexes={
 *              @index(name="zlt", columns={"zone", "lang", "tpl"})
 *          }
 * )
 */
class TemplatePatch extends \XLite\Model\AEntity
{
    /**
     * Patch id
     *
     * @var integer
     *
     * @Id
     * @GeneratedValue (strategy="AUTO")
     * @Column         (type="integer", length=11)
     */
    protected $patch_id;

    /**
     * Zone
     *
     * @var string
     *
     * @Column (type="string", length=16)
     */
    protected $zone = 'customer';

    /**
     * Language code
     *
     * @var string
     *
     * @Column (type="string", length=2)
     */
    protected $lang = '';

    /**
     * Template
     *
     * @var string
     *
     * @Column (type="string", length=255)
     */
    protected $tpl;

    /**
     * Patch type
     *
     * @var string
     *
     * @Column (type="string", length=8)
     */
    protected $patch_type = 'custom';

    /**
     * XPath query
     *
     * @var string
     *
     * @Column (type="string", length=255)
     */
    protected $xpath_query = '';

    /**
     * XPath insertaion type
     *
     * @var string
     *
     * @Column (type="string", length=16)
     */
    protected $xpath_insert_type = 'before';

    /**
     * XPath replacement block
     *
     * @var string
     *
     * @Column (type="text")
     */
    protected $xpath_block = '';

    /**
     * Regular expression patter
     *
     * @var string
     *
     * @Column (type="string", length=255)
     */
    protected $regexp_pattern = '';

    /**
     * Regular expression replacement block
     *
     * @var string
     *
     * @Column (type="text")
     */
    protected $regexp_replace = '';

    /**
     * Custom callback name
     *
     * @var string
     *
     * @Column (type="string", length=128)
     */
    protected $custom_callback = '';

    /**
     * Template relative path
     *
     * @var string
     *
     * @Column (type="string", length=32, nullable=true)
     */
    protected $version;

}
