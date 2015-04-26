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

namespace XLite\Model\Shipping;

/**
 * Shipping method model
 *
 * @Entity (repositoryClass="XLite\Model\Repo\Shipping\Method")
 * @Table  (name="shipping_methods",
 *      indexes={
 *          @Index (name="processor", columns={"processor"}),
 *          @Index (name="carrier", columns={"carrier"}),
 *          @Index (name="enabled", columns={"enabled"}),
 *          @Index (name="position", columns={"position"})
 *      }
 * )
 */
class Method extends \XLite\Model\Base\I18n
{
    /**
     * A unique ID of the method
     *
     * @var integer
     *
     * @Id
     * @GeneratedValue (strategy="AUTO")
     * @Column         (type="integer")
     */
    protected $method_id;

    /**
     * Processor class name
     *
     * @var string
     *
     * @Column (type="string", length=255)
     */
    protected $processor = '';

    /**
     * Carrier of the method (for instance, "UPS" or "USPS")
     *
     * @var string
     *
     * @Column (type="string", length=255)
     */
    protected $carrier = '';

    /**
     * Unique code of shipping method (within processor space)
     *
     * @var string
     *
     * @Column (type="string", length=255)
     */
    protected $code = '';

    /**
     * Whether the method is enabled or disabled
     *
     * @var string
     *
     * @Column (type="boolean")
     */
    protected $enabled = false;

    /**
     * A position of the method among other registered methods
     *
     * @var integer
     *
     * @Column (type="integer")
     */
    protected $position = 0;

    /**
     * Shipping rates (relation)
     *
     * @var \Doctrine\Common\Collections\ArrayCollection
     *
     * @OneToMany (targetEntity="XLite\Model\Shipping\Markup", mappedBy="shipping_method", cascade={"all"})
     */
    protected $shipping_markups;

    /**
     * Tax class (relation)
     *
     * @var \XLite\Model\TaxClass
     *
     * @ManyToOne  (targetEntity="XLite\Model\TaxClass")
     * @JoinColumn (name="tax_class_id", referencedColumnName="id", onDelete="SET NULL")
     */
    protected $taxClass;

    /**
     * Constructor
     *
     * @param array $data Entity properties OPTIONAL
     *
     * @return void
     */
    public function __construct(array $data = array())
    {
        $this->shipping_markups = new \Doctrine\Common\Collections\ArrayCollection();
        $this->classes          = new \Doctrine\Common\Collections\ArrayCollection();

        parent::__construct($data);
    }

    /**
     * Get processor class object
     *
     * @return \XLite\Model\Shipping\Processor\AProcessor
     */
    public function getProcessorObject()
    {
        $result = null;

        // Current processor ID
        $processor = $this->getProcessor();

        // List of all registered processors
        $processors = \XLite\Model\Shipping::getInstance()->getProcessors();

        // Search for processor object
        if ($processors) {
            foreach ($processors as $obj) {
                if ($obj->getProcessorId() == $processor) {
                    $result = $obj;
                    break;
                }
            }
        }

        return $result;
    }

    /**
     * Return true if rates exists for this shipping method
     *
     * @return boolean
     */
    public function hasRates()
    {
        return (bool)$this->getRatesCount();
    }

    /**
     * Get count of rates specified for this shipping method
     *
     * @return integer
     */
    public function getRatesCount()
    {
        return count($this->getShippingMarkups());
    }
}
