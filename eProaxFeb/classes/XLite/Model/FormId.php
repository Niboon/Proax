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
 * Form unique id
 *
 * @Entity (repositoryClass="\XLite\Model\Repo\FormId")
 * @Table  (name="form_ids",
 *      uniqueConstraints={
 *          @UniqueConstraint (name="fs", columns={"form_id","session_id"})
 *      },
 *      indexes={
 *          @Index (name="session_id", columns={"session_id"})
 *      }
 * )
 * @HasLifecycleCallbacks
 */
class FormId extends \XLite\Model\AEntity
{
    /**
     * Maximum TTL of form id (1 hour)
     */
    const MAX_FORM_ID_TTL = 3600;

    
    /**
     * Unique id
     *
     * @var integer
     *
     * @Id
     * @GeneratedValue (strategy="AUTO")
     * @Column         (type="integer", nullable=false)
     */
    protected $id;

    /**
     * Session id
     *
     * @var integer
     *
     * @Column (type="integer", nullable=false)
     */
    protected $session_id;

    /**
     * Form unique id
     *
     * @var string
     *
     * @Column (type="string", length=32)
     */
    protected $form_id;

    /**
     * Date
     *
     * @var integer
     *
     * @Column (type="integer")
     */
    protected $date;

    /**
     * Set date (readonly)
     *
     * @param integer $value Date
     *
     * @return void
     */
    public function setDate($value)
    {
    }

    /**
     * Prepare form id
     *
     * @return void
     * @PrePersist
     */
    public function prepareFormId()
    {
        if (!$this->getFormId()) {
            $this->form_id = $this->getRepository()->generateFormId($this->getSessionId());
        }

        if (!$this->getDate()) {
            $this->date = \XLite\Core\Converter::time() + static::MAX_FORM_ID_TTL;
        }
    }
}
