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
 * @copyright Copyright (c) 2011-2014 Qualiteam software Ltd <info@x-cart.com>. All rights reserved
 * @license   http://www.x-cart.com/license-agreement.html X-Cart 5 License Agreement
 * @link      http://www.x-cart.com/
 */

namespace XLite\Module\XC\SystemFields\Logic;

/**
 * System Fields modifier
 */
class Field extends \XLite\Logic\ALogic
{
    /**
     * System field names
     */
    const UPC_ISBN   = 'upcIsbn';
    const MNF_VENDOR = 'mnfVendor';

    /**
     * System field values
     */
    const NOWHERE = 'N';
    const ADMIN_ONLY = 'A';
    const ADMIN_CUSTOMER = 'AC';

    /**
     * Allowed system fields
     *
     * @var array
     */
    protected $allowedFields = array(
        self::UPC_ISBN,
        self::MNF_VENDOR,
    );

    /**
     * Allowed values for showing of the system fields
     *
     * @var array
     */
    protected $allowedFieldsValues = array(
        'admin' => array(
            self::ADMIN_ONLY,
            self::ADMIN_CUSTOMER,
        ),
        'customer' => array(
            self::ADMIN_CUSTOMER,
        )
    );

    /**
     * Check if field is visible
     *
     * @param string $fieldName Name of the field
     *
     * @return boolean
     */
    public function isVisibleField($fieldName)
    {
        $result = false;

        if (in_array($fieldName, $this->allowedFields)) {
            $fieldValue = \XLite\Core\Config::getInstance()->XC->SystemFields->{'show_' . $fieldName . '_field'};

            if (\XLite::isAdminZone()) {
                $allowedFieldsValues = $this->allowedFieldsValues['admin'];
            } else {
                $allowedFieldsValues = $this->allowedFieldsValues['customer'];
            }

            $result = in_array(
                $fieldValue,
                $allowedFieldsValues
            );
        }

        return $result;
    }
}
