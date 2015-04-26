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

namespace XLite\Logic\Import\Processor;

/**
 * Customers import processor
 */
class Customers extends \XLite\Logic\Import\Processor\AProcessor
{

    const ADDRESS_FIELD_SUFFIX = 'AddressField';

    /**
     * Get title
     *
     * @return string
     */
    static public function getTitle()
    {
        return static::t('Customers imported');
    }

    /**
     * Get repository
     *
     * @return \XLite\Model\Repo\ARepo
     */
    protected function getRepository()
    {
        return \XLite\Core\Database::getRepo('XLite\Model\Profile');
    }

    // {{{ Columns

    /**
     * Define columns
     *
     * @return array
     */
    protected function defineColumns()
    {
        return array(
            'login'           => array(
                static::COLUMN_IS_KEY      => true,
            ),
            'added'           => array(),
            'firstLogin'      => array(),
            'lastLogin'       => array(),
            'status'          => array(),
            'referer'         => array(),
            'language'        => array(),
            'membership'      => array(),
            'addressField'    => array(
                static::COLUMN_IS_MULTICOLUMN  => true,
                static::COLUMN_IS_MULTIROW     => true,
                static::COLUMN_HEADER_DETECTOR => true,
            ),
        );
    }

    // }}}

    // {{{ Header detectors

    /**
     * Detect address field header(s)
     *
     * @param array $column Column info
     * @param array $row    Header row
     *
     * @return array
     */
    protected function detectAddressFieldHeader(array $column, array $row)
    {
        return $this->detectHeaderByPattern('(?:\w+' . static::ADDRESS_FIELD_SUFFIX . '|shippingAddress|billingAddress)', $row);
    }

    // }}}

    // {{{ Verification

    /**
     * Get messages
     *
     * @return array
     */
    public static function getMessages()
    {
        return parent::getMessages()
            + array(
                'USER-LOGIN-FMT'      => 'Wrong login format',
                'USER-ADDED-FMT'      => 'Wrong added format',
                'USER-FLOGIN-FMT'     => 'Wrong first login date format',
                'USER-LLOGIN-FMT'     => 'Wrong last login date format',
                'USER-STATUS-FMT'     => 'Wrong status format',
                'USER-REFERER-FMT'    => 'Wrong referer format',
                'USER-LANGUAGE-FMT'   => 'Wrong language format',
                'USER-SHPADDR-FMT'    => 'Wrong shipping address format',
                'USER-BILADDR-FMT'    => 'Wrong billing address format',
                'USER-CCODE-FMT'      => 'Wrong country code format',
                'USER-SID-FMT'        => 'Wrong state id format',
            );
    }

    /**
     * Verify 'login' value
     *
     * @param mixed $value  Value
     * @param array $column Column info
     *
     * @return void
     */
    protected function verifyLogin($value, array $column)
    {
        if (!$this->verifyValueAsEmail($value)) {
            $this->addError('USER-LOGIN-FMT', array('column' => $column, 'value' => $value));
        }
    }

    /**
     * Verify 'added date' value
     *
     * @param mixed $value  Value
     * @param array $column Column info
     *
     * @return void
     */
    protected function verifyAdded($value, array $column)
    {
        if (!$this->verifyValueAsEmpty($value) && !$this->verifyValueAsDate($value)) {
            $this->addWarning('USER-ADDED-FMT', array('column' => $column, 'value' => $value));
        }
    }

    /**
     * Verify 'first login' value
     *
     * @param mixed $value  Value
     * @param array $column Column info
     *
     * @return void
     */
    protected function verifyFirstLogin($value, array $column)
    {
        if (!$this->verifyValueAsEmpty($value) && !$this->verifyValueAsDate($value)) {
            $this->addWarning('USER-FLOGIN-FMT', array('column' => $column, 'value' => $value));
        }
    }

    /**
     * Verify 'last login' value
     *
     * @param mixed $value  Value
     * @param array $column Column info
     *
     * @return void
     */
    protected function verifyLastLogin($value, array $column)
    {
        if (!$this->verifyValueAsEmpty($value) && !$this->verifyValueAsDate($value)) {
            $this->addWarning('USER-LLOGIN-FMT', array('column' => $column, 'value' => $value));
        }
    }

    /**
     * Verify 'status' value
     *
     * @param mixed $value  Value
     * @param array $column Column info
     *
     * @return void
     */
    protected function verifyStatus($value, array $column)
    {
        if (
            !$this->verifyValueAsEmpty($value)
            && !$this->verifyValueAsSet($value, array(\XLite\Model\Profile::STATUS_ENABLED, \XLite\Model\Profile::STATUS_DISABLED))
        ) {
            $this->addError('USER-STATUS-FMT', array('column' => $column, 'value' => $value));
        }
    }

    /**
     * Verify 'language' value
     *
     * @param mixed $value  Value
     * @param array $column Column info
     *
     * @return void
     */
    protected function verifyLanguage($value, array $column)
    {
        if (!$this->verifyValueAsEmpty($value) && !$this->verifyValueAsLanguageCode($value)) {
            $this->addWarning('USER-LANGUAGE-FMT', array('column' => $column, 'value' => $value));
        }
    }

    /**
     * Verify 'shipping address' value
     *
     * @param mixed $value  Value
     * @param array $column Column info
     *
     * @return void
     */
    protected function verifyShippingAddress($value, array $column, $index)
    {
        if (!$this->verifyValueAsEmpty($value) && !$this->verifyValueAsBoolean($value)) {
            $this->addWarning('USER-SHPADDR-FMT', array('column' => $column, 'value' => $value), $index);
        }
    }

    /**
     * Verify 'billing address' value
     *
     * @param mixed $value  Value
     * @param array $column Column info
     *
     * @return void
     */
    protected function verifyBillingAddress($value, array $column, $index)
    {
        if (!$this->verifyValueAsEmpty($value) && !$this->verifyValueAsBoolean($value)) {
            $this->addWarning('USER-BILADDR-FMT', array('column' => $column, 'value' => $value), $index);
        }
    }

    /**
     * Verify 'address field' value
     *
     * @param mixed $value  Value
     * @param array $column Column info
     *
     * @return void
     */
    protected function verifyAddressField($value, array $column)
    {
        foreach ($value as $name => $rows) {
            $method = null;
            if ($this->isColumnHeaderEqual('shippingAddress', $name)) {
                $method = 'verifyShippingAddress';

            } elseif ($this->isColumnHeaderEqual('billingAddress', $name)) {
                $method = 'verifyBillingAddress';

            } else {
                $serviceName = $this->normalizeColumnHeader('(\w+)' . static::ADDRESS_FIELD_SUFFIX, $name);
                if ($serviceName) {
                    $method = 'verifyAddressField' . \XLite\Core\Converter::convertToCamelCase($serviceName);
                    if (!method_exists($this, $method)) {
                        $method = null;
                    }
                }
            }

            if ($method) {
                foreach ($rows as $i => $v) {
                    $this->$method($v, $column, $i);
                }
            }
        }
    }

    /**
     * Verify 'address field country code' value
     *
     * @param mixed $value  Value
     * @param array $column Column info
     *
     * @return void
     */
    protected function verifyAddressFieldCountryCode($value, array $column, $index)
    {
        if (!$this->verifyValueAsEmpty($value) && !$this->verifyValueAsCountryCode($value)) {
            $this->addWarning('USER-CCODE-FMT', array('column' => $column, 'value' => $value), $index);
        }
    }

    /**
     * Verify 'address field state Id' value
     *
     * @param mixed $value  Value
     * @param array $column Column info
     *
     * @return void
     */
    protected function verifyAddressFieldStateId($value, array $column, $index)
    {
        if (!$this->verifyValueAsEmpty($value) && !$this->verifyValueAsStateId($value)) {
            $this->addWarning('USER-SID-FMT', array('column' => $column, 'value' => $value), $index);
        }
    }

    /**
     * Verify 'membership' value
     *
     * @param mixed $value  Value
     * @param array $column Column info
     *
     * @return void
     */
    protected function verifyMembership($value, array $column)
    {
        if (!$this->verifyValueAsEmpty($value) && !$this->verifyValueAsMembership($value)) {
            $this->addWarning('GLOBAL-MEMBERSHIP-FMT', array('column' => $column, 'value' => $value));
        }
    }

    // }}}

    // {{{ Import

    /**
     * Import 'address field' value
     *
     * @param \XLite\Model\Profile $model  Profile
     * @param array                $value  Value
     * @param array                $column Column info
     *
     * @return void
     */
    protected function importAddressFieldColumn(\XLite\Model\Profile $model, array $value, array $column)
    {
        $data = array();
        foreach ($value as $name => $rows) {
            $method = null;
            if ($this->isColumnHeaderEqual('shippingAddress', $name)) {
                $data['is_shipping'] = $rows;

            } elseif ($this->isColumnHeaderEqual('billingAddress', $name)) {
                $data['is_billing'] = $rows;

            } else {
                $serviceName = $this->normalizeColumnHeader('(\w+)' . static::ADDRESS_FIELD_SUFFIX, $name);
                if ($serviceName) {
                    if ('state' == $serviceName) {
                        $data['state'] = $rows;

                    } else {
                        foreach (\XLite\Core\Database::getRepo('XLite\Model\AddressField')->findAllEnabled() as $field) {
                            $fname = lcfirst(\XLite\Core\Converter::convertToCamelCase($field->getServiceName()));
                            if ($fname == $serviceName) {
                                $data[$field->getServiceName()] = $rows;
                            }
                        }
                    }
                }
            }
        }

        $addresses = $this->assembleSubmodelsData($data, $column);

        $i = 0;
        foreach ($addresses as $address) {
            $this->importAddress($model, $address, $i);
            $i++;
        }

        // Remove
        while (count($model->getAddresses()) > count($addresses)) {
            $address = $model->getAddresses()->last();
            \XLite\Core\Database::getRepo('XLite\Model\Address')->delete($address, false);
            $model->getAddresses()->removeElement($address);
        }
    }

    /**
     * Import address
     *
     * @param \XLite\Model\Profile $model   Profile
     * @param array                $address Address
     * @param integer              $index   Index
     *
     * @return void
     */
    protected function importAddress(\XLite\Model\Profile $model, array $address, $index)
    {
        $addr = $model->getAddresses()->get($index);
        if (!$addr) {
            $addr = $this->createAddress();
            $model->addAddresses($addr);
            $addr->setProfile($model);
        }

        if (isset($address['is_shipping'])) {
            $address['is_shipping'] = $this->normalizeValueAsBoolean($address['is_shipping']);
        }

        if (isset($address['is_billing'])) {
            $address['is_billing'] = $this->normalizeValueAsBoolean($address['is_billing']);
        }

        if (isset($address['state'])) {
            $address['state'] = $this->normalizeValueAsState($address['state']);
        }

        $this->updateAddress($addr, $address);
    }

    /**
     * Insert address
     *
     * @return \XLite\Model\Address
     */
    protected function createAddress()
    {
        return \XLite\Core\Database::getRepo('XLite\Model\Address')->insert(null, false);
    }

    /**
     * Update address
     *
     * @param \XLite\Model\Address $entity Address to update
     * @param array                $data   New values for address
     *
     * @return void
     */
    protected function updateAddress(\XLite\Model\Address $address, array $data)
    {
        \XLite\Core\Database::getRepo('XLite\Model\Address')->update($address, $data, false);
    }

    // }}}

    // {{{ Normalizators

    /**
     * Normalize 'login' value
     *
     * @param mixed @value Value
     *
     * @return string
     */
    protected function normalizeLoginValue($value)
    {
        return $this->normalizeValueAsEmail($value);
    }

    /**
     * Normalize 'added' value
     *
     * @param mixed @value Value
     *
     * @return integer
     */
    protected function normalizeAddedValue($value)
    {
        return $this->normalizeValueAsDate($value);
    }

    /**
     * Normalize 'first login' value
     *
     * @param mixed @value Value
     *
     * @return integer
     */
    protected function normalizeFirstLoginValue($value)
    {
        return $this->normalizeValueAsDate($value);
    }

    /**
     * Normalize 'last login' value
     *
     * @param mixed @value Value
     *
     * @return intgern
     */
    protected function normalizeLastLoginValue($value)
    {
        return $this->normalizeValueAsDate($value);
    }

    /**
     * Normalize 'status' value
     *
     * @param mixed @value Value
     *
     * @return string
     */
    protected function normalizeStatusValue($value)
    {
        return strtoupper($value);
    }

    /**
     * Normalize 'language' value
     *
     * @param mixed @value Value
     *
     * @return string
     */
    protected function normalizeLanguageValue($value)
    {
        return strtolower($value);
    }

    /**
     * Normalize 'membership' value
     *
     * @param mixed @value Value
     *
     * @return \XLite\Model\Membership
     */
    protected function normalizeMembershipValue($value)
    {
        return $this->normalizeValueAsMembership($value);
    }

    // }}}
}
