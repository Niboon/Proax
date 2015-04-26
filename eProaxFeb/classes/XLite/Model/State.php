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
 * State
 *
 * @Entity (repositoryClass="\XLite\Model\Repo\State")
 * @Table (name="states",
 *      uniqueConstraints={
 *          @UniqueConstraint (name="code", columns={"code","country_code"})
 *      },
 *      indexes={
 *          @Index (name="state", columns={"state"})
 *      }
 * )
 * @HasLifecycleCallbacks
 */
class State extends \XLite\Model\AEntity
{
    /**
     * State unique id
     *
     * @var integer
     *
     * @Id
     * @GeneratedValue (strategy="AUTO")
     * @Column (type="integer")
     */
    protected $state_id;

    /**
     * State name
     *
     * @var string
     *
     * @Column (type="string", length=64)
     */
    protected $state;

    /**
     * State code
     *
     * @var string
     *
     * @Column (type="string", length=64)
     */
    protected $code;

    /**
     * Country (relation)
     *
     * @var \XLite\Model\Country
     *
     * @ManyToOne (targetEntity="XLite\Model\Country", inversedBy="states", cascade={"merge","detach"})
     * @JoinColumn (name="country_code", referencedColumnName="code", onDelete="CASCADE")
     */
    protected $country;

    /**
     * Set code 
     * 
     * @param string $code Code
     *  
     * @return void
     */
    public function setCode($code)
    {
        if ($this->code != $code && $this->getCountry()) {
            $elements = \XLite\Core\Database::getRepo('XLite\Model\ZoneElement')->findBy(
                array(
                    'element_type'  => \XLite\Model\ZoneElement::ZONE_ELEMENT_STATE,
                    'element_value' => $this->getCountry()->getCode() . '_' . $this->code,
                )
            );

            foreach ($elements as $element) {
                $element->setElementValue($this->getCountry()->getCode() . '_' . $code);
            }

            if ($elements) {
                \XLite\Core\Database::getRepo('XLite\Model\Zone')->cleanCache();
            }
        }

        $this->code = $code;

        return $this;
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
                'element_type'  => \XLite\Model\ZoneElement::ZONE_ELEMENT_STATE,
                'element_value' => $this->getCountry()->getCode() . '_' . $this->getCode(),
            )
        );

        foreach ($elements as $element) {
            \XLite\Core\Database::getEM()->remove($element);
        }
    }
}
