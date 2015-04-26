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
 * Address field model
 *
 * @Entity (repositoryClass="\XLite\Model\Repo\AddressField")
 * @Table  (name="address_field")
 */
class AddressField extends \XLite\Model\Base\I18n
{
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
     * Service name for address field
     * For example: firstname, lastname, country_code and so on.
     *
     * The field is named with this value in the address forms.
     * Also the "service-name-{$serviceName}" CSS class is added to the field
     *
     * @var string
     * @Column(type="string", length=128, unique=true, nullable=false)
     */
    protected $serviceName;

    /**
     * Getter name for address field (for AView::getAddressSectionData)
     * For example:
     * country for country_code
     * state for state_id, custom_state
     *
     * The field is named with this value in the address forms.
     * Also the "service-name-{$serviceName}" CSS class is added to the field
     *
     * @var string
     * @Column(type="string", length=128, nullable=false)
     */
    protected $viewGetterName = '';

    /**
     * Schema class for "Form field widget".
     * This class will be used in form widgets
     *
     * Possible values are:
     *
     * \XLite\View\FormField\Input\Text
     * \XLite\View\FormField\Select\Country
     * \XLite\View\FormField\Select\Title
     *
     * For more information check "\XLite\View\FormField\*" class family.
     *
     * The '\XLite\View\FormField\Input\Text' class (standard input text field)
     * is taken for additional fields by default.
     *
     * @var string
     * @Column(type="string", length=256, nullable=false)
     */
    protected $schemaClass = '\XLite\View\FormField\Input\Text';

    /**
     * Flag if the field is an additional one (This field could be removed)
     *
     * @var boolean
     * @Column(type="boolean")
     */
    protected $additional = true;

    /**
     * Flag if the field is a required one
     *
     * @var boolean
     * @Column(type="boolean")
     */
    protected $required = true;

    /**
     * Flag if the field is an enabled one
     *
     * @var boolean
     * @Column(type="boolean")
     */
    protected $enabled = true;

    /**
     * Position
     *
     * @var integer
     *
     * @Column (type="integer")
     */
    protected $position = 0;


    /**
     * Return CSS classes for this field name entry
     *
     * @return string
     */
    public function getCSSFieldName()
    {
        return 'address-' . $this->getServiceName() . ($this->getAdditional() ? ' field-additional' : '');
    }

}
