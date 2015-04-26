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

namespace XLite\Logic\Import\Step;

/**
 * Verification step 
 */
class Verification extends \XLite\Logic\Import\Step\Base\DataStep
{

    /**
     * Get final note
     *
     * @return string
     */
    public function getFinalNote()
    {
        return static::t('Verified');
    }

    /**
     * Get note
     *
     * @return string
     */
    public function getNote()
    {
        return static::t('Verifying data before importing...');
    }

    /**
     * Process row
     *
     * @return boolean
     */
    public function process()
    {
        return $this->getProcessor()->processCurrentRow(\XLite\Logic\Import\Processor\AProcessor::MODE_VERIFICATION);
    }

    /**
     * Check - step's work has been done or not
     *
     * @return boolean
     */
    public function isStepDone()
    {
        $result = parent::isStepDone();

        if ($result && $this->isCurrentStep()) {
            $columnsMetaData = $this->getOptions()->columnsMetaData;

            $count = 0;
            foreach ($columnsMetaData as $v) {
                if (isset($v['count'])) {
                    $count =+ $v['count'];
                    if ($count > 0) {
                        break;
                    }
                }
            }

            $result = $count > 0;
        }

        return $result;
    }

    /**
     * Get error language label
     *
     * @return array
     */
    public function getErrorLanguageLabel()
    {
        $options = $this->getOptions();

        return static::t(
            'Lines verified: X out of Y with errors',
            array(
                'X'      => $options->position,
                'Y'      => $options->rowsCount,
                'errors' => $options->errorsCount,
                'warns'  => $options->warningsCount,
            )
        );
    }

    /**
     * Get normal language label
     *
     * @return array
     */
    public function getNormalLanguageLabel()
    {
        $options = $this->getOptions();

        return static::t(
            'Lines checked: X out of Y',
            array(
                'X' => $options->position,
                'Y' => $options->rowsCount,
            )
        );
    }

}

