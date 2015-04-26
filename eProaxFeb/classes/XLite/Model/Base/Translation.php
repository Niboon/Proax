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
 * Object translation
 *
 * @MappedSuperclass
 */
abstract class Translation extends \XLite\Model\AEntity
{
    /**
     * Default code
     */
    const DEFAULT_LANGUAGE = 'en';

    /**
     * Label unique id
     *
     * @var integer
     *
     * @Id
     * @GeneratedValue (strategy="AUTO")
     * @Column         (type="integer")
     */
    protected $label_id;

    /**
     * Label language code
     *
     * @var string
     *
     * @Column (type="string", options={ "fixed": true }, length=2)
     */
    protected $code = self::DEFAULT_LANGUAGE;

    /**
     * Return list of class properties which are not translated
     *
     * @return array
     */
    public static function getInternalProperties()
    {
        return array('label_id', 'code');
    }

    /**
     * Return the owner object
     *
     * @return \XLite\Model\AEntity
     */
    public function getOwner()
    {
        return $this->owner;
    }

    /**
     * Check cache after enity persist or remove
     *
     * @return void
     */
    public function checkCache()
    {
        parent::checkCache();

        // Check translation owner cache
        if ($this->getOwner()) {
            $this->getOwner()->checkCache();
        }
    }
}
