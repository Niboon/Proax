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
 * Country
 *
 * @Entity (repositoryClass="\XLite\Model\Repo\Country")
 * @Table  (name="countries",
 *      indexes={
 *          @Index (name="enabled", columns={"enabled"})
 *      }
 * )
 * @HasLifecycleCallbacks
 */
class Country extends \XLite\Model\Base\I18n
{
    /**
     * Country code (ISO 3166-1 alpha-2)
     *
     * @var string
     *
     * @Id
     * @Column (type="string", options={ "fixed": true }, length=2, unique=true)
     */
    protected $code;

    /**
     * Country code (ISO 3166-1 numeric)
     *
     * @var integer
     *
     * @Column (type="integer")
     */
    protected $id;

    /**
     * Country code (ISO 3166-1 alpha-3)
     *
     * @var string
     *
     * @Column (type="string", options={ "fixed": true }, length=3)
     */
    protected $code3 = '';

    /**
     * Enabled falg
     *
     * @var boolean
     *
     * @Column (type="boolean")
     */
    protected $enabled = true;

    /**
     * States (relation)
     *
     * @var \Doctrine\Common\Collections\ArrayCollection
     *
     * @OneToMany (targetEntity="XLite\Model\State", mappedBy="country", cascade={"all"})
     * @OrderBy   ({"state" = "ASC"})
     */
    protected $states;

    /**
     * Currency
     *
     * @var \XLite\Model\Currency
     *
     * @ManyToOne (targetEntity="XLite\Model\Currency", inversedBy="countries")
     * @JoinColumn (name="currency_id", referencedColumnName="currency_id", onDelete="CASCADE")
     */
    protected $currency;


    /**
     * Constructor
     *
     * @param array $data Entity properties OPTIONAL
     *
     * @return void
     */
    public function __construct(array $data = array())
    {
        $this->states = new \Doctrine\Common\Collections\ArrayCollection();

        parent::__construct($data);
    }

    /**
     * Get count of states
     *
     * @return integer
     */
    public function getStatesCount()
    {
        return count($this->states);
    }

    /**
     * Check if country has states
     *
     * @return boolean
     */
    public function hasStates()
    {
        return 0 < count($this->states);
    }

    /**
     * Remove zone elements
     *
     * @return void
     * @PreRemove
     */
    public function removeZoneElements()
    {
        $elements = \XLite\Core\Database::getRepo('XLite\Model\ZoneElement')->findBy(
            array(
                'element_type'  => \XLite\Model\ZoneElement::ZONE_ELEMENT_COUNTRY,
                'element_value' => $this->getCode(),
            )
        );

        foreach ($elements as $element) {
            \XLite\Core\Database::getEM()->remove($element);
        }
    }

}
