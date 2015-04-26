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
 * Import step
 */
class Import extends \XLite\Logic\Import\Step\Base\DataStep
{

    /**
     * Get final note
     *
     * @return string
     */
    public function getFinalNote()
    {
        return static::t('Imported');
    }

    /**
     * Get note
     *
     * @return string
     */
    public function getNote()
    {
        return static::t('Importing data...');
    }

    /**
     * Process row
     *
     * @return boolean
     */
    public function process()
    {
        return $this->getProcessor()->processCurrentRow(\XLite\Logic\Import\Processor\AProcessor::MODE_IMPORT);
    }

    /**
     * Initialize
     *
     * @return void
     */
    public function initialize()
    {
        parent::initialize();

        $this->importer->getOptions()->columnsMetaData = array();

        foreach ($this->importer->getProcessors() as $processor) {
            $processor->markAllImagesAsProcessed();
        }
    }

    /**
     * Finalize
     *
     * @return void
     */
    public function finalize()
    {
        parent::finalize();

        if ($this->getOptions()->clearImportDir) {
            $this->importer->deleteAllFiles();
        }
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
            'Lines imported: X out of Y with errors',
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
            'Lines imported: X out of Y',
            array(
                'X' => $options->position,
                'Y' => $options->rowsCount,
            )
        );
    }

    // {{{ Result messages

    /**
     * Get messages
     *
     * @return array
     */
    public function getMessages()
    {
        $list = parent::getMessages();

        $data = $this->getOptions()->columnsMetaData;
        if ($data) {
            foreach (\XLite\Logic\Import\Importer::getProcessorList() as $processor) {
                if (isset($data[$processor])) {
                    $message = $processor::getResultMessage($data[$processor]);

                    if ($message) {
                        $list[] = $message;
                    }
                }
            }
        }

        return $list;
    }

    // }}}

}
