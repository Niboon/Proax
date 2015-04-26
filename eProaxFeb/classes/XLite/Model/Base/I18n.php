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

namespace XLite\Model\Base;

/**
 * Translation-owner abstract class
 *
 * @MappedSuperclass
 */
abstract class I18n extends \XLite\Model\AEntity
{
    /**
     * Current entity language
     *
     * @var string
     */
    protected $editLanguage;

    /**
     * Constructor
     *
     * @param array $data Entity properties OPTIONAL
     *
     * @return void
     */
    public function __construct(array $data = array())
    {
        $this->translations = new \Doctrine\Common\Collections\ArrayCollection();

        parent::__construct($data);
    }

    /**
     * Set current entity language
     *
     * @param string $code Code to set
     *
     * @return self
     */
    public function setEditLanguage($code)
    {
        $this->editLanguage = $code;

        return $this;
    }

    /**
     * Return all translations
     *
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function getTranslations()
    {
        return $this->translations;
    }

    /**
     * Add translation to the list
     *
     * @param \XLite\Model\Base\Translation $translation Translation to add
     *
     * @return void
     */
    public function addTranslations(\XLite\Model\Base\Translation $translation)
    {
        $this->translations[] = $translation;
    }

    /**
     * Get translation
     *
     * @param string  $code             Language code OPTIONAL
     * @param boolean $allowEmptyResult Flag OPTIONAL
     *
     * @return \XLite\Model\Base\Translation
     */
    public function getTranslation($code = null, $allowEmptyResult = false)
    {
        $result = $this->getHardTranslation($code);

        if (!isset($result) && !$allowEmptyResult) {
            $class  = $this instanceof \Doctrine\ORM\Proxy\Proxy ? get_parent_class($this) : get_class($this);
            $class .= 'Translation';

            $result = new $class();
            $result->setOwner($this);
            $result->setCode($this->getTranslationCode($code));
        }

        return $result;
    }

    /**
     * Search for translation
     *
     * @param string $code Language code OPTIONAL
     *
     * @return \XLite\Model\Base\Translation
     */
    public function getHardTranslation($code = null)
    {
        return \Includes\Utils\ArrayManager::searchInObjectsArray(
            $this->getTranslations()->toArray(),
            'getCode',
            $this->getTranslationCode($code)
        );
    }

    /**
     * Get translation in safe mode
     *
     * @param string $code Language code OPTIONAL
     *
     * @return \XLite\Model\Base\Translation
     */
    public function getSoftTranslation($code = null)
    {
        $result = null;

        // Select by languages query (current languge -> default language -> hardcoded default language)
        $query = \XLite\Core\Translation::getLanguageQuery($this->getTranslationCode($code));
        foreach ($query as $code) {
            $result = $this->getTranslation($code, true);
            if (isset($result)) {
                break;
            }
        }

        // Get first translation
        if (!isset($result)) {
            $result = $this->getTranslations()->first() ?: null;
        }

        // Get empty dump translation with specified code
        if (!isset($result)) {
            $result = $this->getTranslation(array_shift($query));
        }

        return $result;
    }

    /**
     * Check for translation
     *
     * @param string $code Language code OPTIONAL
     *
     * @return boolean
     */
    public function hasTranslation($code = null)
    {
        return (bool) $this->getHardTranslation($code);
    }

    /**
     * Get translation codes
     *
     * @return array
     */
    public function getTranslationCodes()
    {
        return \Includes\Utils\ArrayManager::getObjectsArrayFieldValues($this->getTranslations()->toArray(), 'getCode');
    }

    /**
     * Detach self
     *
     * @return void
     */
    public function detach()
    {
        parent::detach();

        foreach ($this->getTranslations() as $translation) {
            $translation->detach();
        }
    }

    /**
     * Clone
     *
     * @return \XLite\Model\AEntity
     */
    public function cloneEntity()
    {
        $entity = parent::cloneEntity();

        foreach ($entity->getSoftTranslation()->getRepository()->findBy(array('owner' => $entity)) as $translation) {
            $newTranslation = $translation->cloneEntity();
            $newTranslation->setOwner($entity);
            $entity->addTranslations($newTranslation);
            \XLite\Core\Database::getEM()->persist($newTranslation);
        }

        return $entity;
    }

    /**
     * Return current translation code
     *
     * @param string $code Language code OPTIONAL
     *
     * @return string
     */
    protected function getTranslationCode($code = null)
    {
        if (!isset($code)) {
            if ($this->editLanguage) {
                $code = $this->editLanguage;

            } elseif (\XLite\Logic\Export\Generator::getLanguageCode()) {
                $code = \XLite\Logic\Export\Generator::getLanguageCode();

            } elseif (\XLite\Logic\Import\Importer::getLanguageCode()) {
                $code = \XLite\Logic\Import\Importer::getLanguageCode();

            } elseif (\XLite\Core\Translation::getTmpMailTranslationCode()) {
                $code = \XLite\Core\Translation::getTmpMailTranslationCode();

            } else {
                $code = $this->getSessionLanguageCode();
            }
        }

        return $code;
    }

    /**
     * Get default language code
     *
     * @return string
     */
    protected function getSessionLanguageCode()
    {
        $lng = \XLite\Core\Session::getInstance()->getLanguage();
        return $lng ? $lng->getCode() : 'en';
    }
}
