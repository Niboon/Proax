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
 * Import log
 *
 * @Entity
 * @Table  (name="import_logs")
 *
 * @HasLifecycleCallbacks
 */
class ImportLog extends \XLite\Model\AEntity
{

    /**
     * Type codes 
     */
    const TYPE_WARNING = 'W';
    const TYPE_ERROR   = 'E';

    /**
     * ID
     *
     * @var integer
     *
     * @Id
     * @GeneratedValue (strategy="AUTO")
     * @Column         (type="integer", options={ "unsigned": true })
     */
    protected $id;

    /**
     * Date
     *
     * @var   integer
     *
     * @Column (type="integer")
     */
    protected $date;

    /**
     * Type
     *
     * @var   string
     *
     * @Column (type="string", options={ "fixed": true }, length=1)
     */
    protected $type = self::TYPE_WARNING;

    /**
     * Code
     *
     * @var   string
     *
     * @Column (type="string", options={ "fixed": true }, length=32)
     */
    protected $code;

    /**
     * Arguments
     *
     * @var   array
     *
     * @Column (type="array")
     */
    protected $arguments;

    /**
     * File
     *
     * @var   string
     *
     * @Column (type="string")
     */
    protected $file;

    /**
     * Row
     *
     * @var   integer
     *
     * @Column (type="integer", options={ "unsigned": true })
     */
    protected $row;

    /**
     * Processor
     *
     * @var   string
     *
     * @Column (type="string")
     */
    protected $processor;

    /**
     * Update date 
     * 
     * @return void
     * @PrePersist
     */
    public function updateDate()
    {
        $this->setDate(\XLite\Core\Converter::time());
    }
}
