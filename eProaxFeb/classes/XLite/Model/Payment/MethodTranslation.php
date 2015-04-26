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

namespace XLite\Model\Payment;

/**
 * Payment method multilingual data
 *
 * @Entity
 * @Table (name="payment_method_translations",
 *      indexes={
 *          @Index (name="ci", columns={"code","id"}),
 *          @Index (name="id", columns={"id"})
 *      }
 * )
 */
class MethodTranslation extends \XLite\Model\Base\Translation
{
    /**
     * Name
     *
     * @var string
     *
     * @Column (type="string", length=255)
     */
    protected $name;

    /**
     * Title (Name of payment method which is displayed for customer on checkout)
     *
     * @var string
     *
     * @Column (type="string", length=255)
     */
    protected $title = '';

    /**
     * Description
     *
     * @var string
     *
     * @Column (type="text")
     */
    protected $description = '';

    /**
     * Admin description
     *
     * @var string
     *
     * @Column (type="text")
     */
    protected $adminDescription = '';

    /**
     * Alternative admin description
     *
     * @var string
     *
     * @Column (type="text")
     */
    protected $altAdminDescription = '';

    /**
     * Instruction
     *
     * @var string
     *
     * @Column (type="text")
     */
    protected $instruction = '';

    /**
     * Title getter
     * If no title is given then the "name" field must be used
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title ?: $this->getName();
    }

    /**
     * Admin description getter
     * If no admin description is given then the "description" field must be used
     *
     * @return string
     */
    public function getAdminDescription()
    {
        return $this->adminDescription ?: $this->getDescription();
    }
}
