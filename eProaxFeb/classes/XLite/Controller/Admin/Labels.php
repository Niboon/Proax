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

namespace XLite\Controller\Admin;

/**
 * Language labels controller
 */
class Labels extends \XLite\Controller\Admin\AAdmin
{

    /**
     * Return the current page title (for the content area)
     *
     * @return string
     */
    public function getTitle()
    {
        return static::t('Language labels');
    }

    /**
     * Controller parameters
     * FIXME: to remove
     *
     * @var string
     */
    protected $params = array('target', 'code');

    /**
     * Get return URL
     *
     * @return string
     */
    public function getReturnURL()
    {
        if (\XLite\Core\Request::getInstance()->action) {

            $data = array();

            if (\XLite\Core\Request::getInstance()->code) {
                $data['code'] = \XLite\Core\Request::getInstance()->code;
            }

            $url = $this->buildURL('labels', '', $data);

        } else {
            $url = parent::getReturnURL();
        }

        return $url;
    }

    /**
     * Search labels
     *
     * @return void
     */
    protected function doActionSearch()
    {
        $search = array();
        $searchParams   = \XLite\View\ItemsList\Model\Translation\Labels::getSearchParams();

        foreach ($searchParams as $modelParam => $requestParam) {
            if (isset(\XLite\Core\Request::getInstance()->$requestParam)) {
                $search[$requestParam] = \XLite\Core\Request::getInstance()->$requestParam;
            }
        }

        $name = \XLite\View\ItemsList\Model\Translation\Labels::getSessionCellName();
        \XLite\Core\Session::getInstance()->$name = $search;
    }

    /**
     * Update labels
     *
     * @return void
     */
    protected function doActionUpdate()
    {
        // Update 'enabled' and 'added' properties editable in the item list
        $list = new \XLite\View\ItemsList\Model\Translation\Labels();
        $list->processQuick();

        $current = \XLite\Core\Request::getInstance()->current;

        // Edit labels for current language
        if ($current && is_array($current)) {
            $this->saveLabels(
                $current,
                static::getDefaultLanguage()
            );
        }
        unset($current);

        $translated = \XLite\Core\Request::getInstance()->translated;
        $translateFail = false;
        if ($translated && is_array($translated)) {

            $language = \XLite\Core\Request::getInstance()->code;

            if (!$language) {

                \XLite\Core\TopMessage::addWarning(
                    'Text labels have not been updated successfully: the translation language has not been specified'
                );
                $translateFail = true;

            } elseif (!\XLite\Core\Database::getRepo('\XLite\Model\Language')->findOneByCode($language)) {

                \XLite\Core\TopMessage::addWarning(
                    'Text labels have not been updated successfully: the translation language has not been found'
                );
                $translateFail = true;

            } else {
                $this->saveLabels(
                    $translated,
                    $language
                );
            }
        }
        unset($translated);

        if (!$translateFail) {
            \XLite\Core\TopMessage::addInfo('Text labels have been updated successfully');
        }
    }

    /**
     * Add label
     *
     * @return void
     */
    protected function doActionAdd()
    {
        $name = substr(\XLite\Core\Request::getInstance()->name, 0, 255);
        $label = \XLite\Core\Request::getInstance()->label;

        if (!$name) {
            $this->valid = false;
            \XLite\Core\TopMessage::addError(
                'The text label has not been added, because its name has not been specified'
            );

        } elseif (\XLite\Core\Database::getRepo('\XLite\Model\LanguageLabel')->findOneByName($name)) {
            $this->valid = false;
            \XLite\Core\TopMessage::addError(
                'The text label has not been added, because such a text label already exists'
            );

        } else {

            $lbl = new \XLite\Model\LanguageLabel();
            $lbl->setName($name);

            foreach ($label as $code => $text) {
                if (!empty($text)) {
                    $lbl->setEditLanguage($code)->setLabel($text);
                }
            }

            $lbl = \XLite\Core\Database::getRepo('\XLite\Model\LanguageLabel')->insert($lbl);

            if ($lbl && $lbl->getLabelId()) {

                // Save added label ID in session
                $addedLabels = \XLite\Core\Session::getInstance()->added_labels;

                if (is_array($addedLabels)) {
                    array_push($addedLabels, $lbl->getLabelId());

                } else {
                    $addedLabels =  array($lbl->getLabelId());
                }

                \XLite\Core\Session::getInstance()->added_labels = $addedLabels;
                $this->setHardRedirect();
            }

            \XLite\Core\Translation::getInstance()->reset();

            \XLite\Core\TopMessage::addInfo('The text label has been added successfully');
        }
    }

    /**
     * Edit label
     *
     * @return void
     */
    protected function doActionEdit()
    {
        $label = \XLite\Core\Request::getInstance()->label;
        $labelId = intval(\XLite\Core\Request::getInstance()->label_id);
        $lbl = \XLite\Core\Database::getRepo('\XLite\Model\LanguageLabel')->find($labelId);

        if (!$lbl) {
            \XLite\Core\TopMessage::addError('The edited language has not been found');

        } else {
            $objects = array('insert' => array(), 'delete' => array());
            
            foreach ($label as $code => $text) {
                if (!empty($text)) {
                    $translation = $lbl->getTranslation($code);
                    $translation->setLabel($text);

                    $objects['insert'][] = $translation;
                    
                } elseif ($lbl->hasTranslation($code)) {
                    $objects['delete'][] = $lbl->getTranslation($code);
                }   
            }

            \XLite\Core\Translation::getInstance()->reset();
            \XLite\Core\TopMessage::addInfo('The text label has been modified successfully');

            \XLite\Core\Database::getRepo('\XLite\Model\LanguageLabel')->insertInBatch($objects['insert']);
            \XLite\Core\Database::getRepo('\XLite\Model\LanguageLabel')->deleteInBatch($objects['delete']);

            $this->setSilenceClose();
        }
    }

    /**
     * Save labels from array
     *
     * @param array  $values Array
     * @param string $code   Language code
     *
     * @return void
     */
    protected function saveLabels(array $values, $code)
    {
        $labels = \XLite\Core\Database::getRepo('\XLite\Model\LanguageLabel')->findByIds(
            array_keys($values)
        );

        $list = array();

        foreach ($labels as $label) {
            if (!empty($values[$label->getLabelId()])) {
                $label->setEditLanguage($code)->setLabel($values[$label->getLabelId()]);
                $list[] = $label;

            } elseif ($label->hasTranslation($code)) {
                $translation = $label->getTranslation($code);
                $label->getTranslations()->removeElement($translation);
                \XLite\Core\Database::getRepo('\XLite\Model\LanguageLabelTranslation')->delete($translation, false);
                $list[] = $label;
            }   
        }   

        \XLite\Core\Translation::getInstance()->reset();
        
        \XLite\Core\Database::getRepo('\XLite\Model\LanguageLabel')->updateInBatch($list);
    }

    /**
     * Get search condition parameter by name
     *
     * @param string $paramName Parameter name
     *
     * @return mixed
     */
    public function getCondition($paramName)
    {
        $searchParams = $this->getConditions();

        return isset($searchParams[$paramName])
            ? $searchParams[$paramName]
            : null;
    }

    /**
     * Get search conditions
     *
     * @return array
     */
    protected function getConditions()
    {
        $name = \XLite\View\ItemsList\Model\Translation\Labels::getSessionCellName();
        $searchParams = \XLite\Core\Session::getInstance()->$name;

        return is_array($searchParams) ? $searchParams : array();
    }
}
