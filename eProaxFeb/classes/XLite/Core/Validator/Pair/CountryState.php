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

namespace XLite\Core\Validator\Pair;

/**
 * Country-state validator
 */
class CountryState extends \XLite\Core\Validator\Pair\APair
{
    /**
     * Field names
     */
    const FIELD_COUNTRY        = 'country_code';
    const FIELD_STATE          = 'state_id';
    const FIELD_CUSTOM_STATE   = 'custom_state';

    /**
     * Validate
     *
     * @param mixed $data Data
     *
     * @return void
     * @throws \XLite\Core\Validator\Exception
     */
    public function validate($data)
    {
        // Check country
        if (!isset($data[static::FIELD_COUNTRY])) {
            throw $this->throwError('Country is not defined');
        }

        $countryCodeValidator = new \XLite\Core\Validator\Pair\Simple;
        $countryCodeValidator->setName(static::FIELD_COUNTRY);
        $countryCodeValidator->setValidator(
            new \XLite\Core\Validator\String\ObjectId\Country(true)
        );
        $countryCodeValidator->validate($data);

        // Check state
        if (isset($data[static::FIELD_STATE])) {
            $stateValidator = new \XLite\Core\Validator\Pair\Simple;
            $stateValidator->setName(static::FIELD_STATE);
            $stateValidator->setValidator(
                new \XLite\Core\Validator\String\ObjectId\State(true)
            );
            $stateValidator->validate($data);
        }

        $data = $this->sanitize($data);

        if (empty($data['state']) && $data['country']->hasStates()) {
            throw $this->throwError('State is not defined');

        } elseif ($data['state'] && $data['state']->getCountry()->getCode() != $data['country']->getCode()) {
            throw $this->throwError('Country has not specified state');
        }
    }

    /**
     * Sanitize
     *
     * @param mixed $data Daa
     *
     * @return mixed
     */
    public function sanitize($data)
    {
        // Get country
        $countryCodeValidator = new \XLite\Core\Validator\Pair\Simple;
        $countryCodeValidator->setName(static::FIELD_COUNTRY);
        $countryCodeValidator->setValidator(
            new \XLite\Core\Validator\String\ObjectId\Country(true)
        );
        $country = $countryCodeValidator->getValidator()->sanitize($data[static::FIELD_COUNTRY]);

        // Get state
        if ($country->hasStates()) {
            $stateValidator = new \XLite\Core\Validator\String\ObjectId\State(true);
            $state = $stateValidator->sanitize($data[static::FIELD_STATE]);

        } elseif (!empty($data[static::FIELD_CUSTOM_STATE])) {
            $state = new \XLite\Model\State;
            $state->setState($data[static::FIELD_CUSTOM_STATE]);
            $state->setCountry($country);
            $data[static::FIELD_STATE] = $data[static::FIELD_CUSTOM_STATE];

        } else {
            $state = null;
        }

        return array(
            'country'             => $country,
            'state'               => $state,
            static::FIELD_COUNTRY => $data[static::FIELD_COUNTRY],
            static::FIELD_STATE   => $state ? $data[static::FIELD_STATE] : null,
        );
    }
}
