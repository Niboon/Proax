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
 * Abstract catalog model
 *
 * @MappedSuperclass
 * @HasLifecycleCallbacks
 */
abstract class Catalog extends \XLite\Model\Base\I18n
{
    const CLEAN_URL_HISTORY_LENGTH = 10;

    /**
     * Clean URLs
     *
     * @var \Doctrine\Common\Collections\Collection
     */
    protected $cleanURLs;

    /**
     * The main procedure to generate clean URL
     *
     * @return string
     */
    public function generateCleanURL()
    {
        /** @var \XLite\Model\Repo\CleanURL $repo */
        $repo = \XLite\Core\Database::getRepo('XLite\Model\CleanURL');

        return $repo->generateCleanURL($this);
    }

    /**
     * Constructor
     *
     * @param array $data Entity properties OPTIONAL
     */
    public function __construct(array $data = array())
    {
        $this->cleanURLs = new \Doctrine\Common\Collections\ArrayCollection();

        parent::__construct($data);
    }

    /**
     * Set clean urls
     *
     * @param \Doctrine\Common\Collections\Collection|string $cleanURLs
     *
     * @return void
     */
    public function setCleanURLs($cleanURLs)
    {
        if (is_string($cleanURLs)) {
            if ($cleanURLs) {
                $this->setCleanURL($cleanURLs);
            }

        } else {
            $this->cleanURLs = $cleanURLs;
        }
    }

    /**
     * Set clean URL
     *
     * @param string  $cleanURL Clean url
     * @param boolean $force    Allow non unique URL OPTIONAL
     *
     * @return void
     */
    public function setCleanURL($cleanURL, $force = false)
    {
        if ($cleanURL && $this->getCleanURL() !== $cleanURL) {
            $cleanURLObject = new \XLite\Model\CleanURL();

            $cleanURLObject->setEntity($this);
            $cleanURLObject->setCleanURL($cleanURL);

            /** @var \XLite\Model\Repo\CleanURL $repo */
            $repo = \Xlite\Core\Database::getRepo('\XLite\Model\CleanURL');

            if ($force || $repo->isURLUnique($cleanURL, $this)) {
                \XLite\Core\Database::getEM()->persist($cleanURLObject);

                /** @var \Doctrine\Common\Collections\Collection $cleanURLs */
                $cleanURLs = $this->getCleanURLs();
                $cleanURLs->add($cleanURLObject);
            }

            $this->filterCleanURLDuplicates();
            $this->filterCleanURLHistoryLength();
        }
    }

    /**
     * Get clean URL
     *
     * @return string
     */
    public function getCleanURL()
    {
        /** @var \Doctrine\Common\Collections\Collection $cleanURLs */
        $cleanURLs = $this->getCleanURLs();

        return $cleanURLs && $cleanURLs->count()
            ? $cleanURLs->last()->getCleanURL()
            : '';
    }

    /**
     * Lifecycle callback
     *
     * @return void
     *
     * @PrePersist
     * @PreUpdate
     */
    public function prepareBeforeSave()
    {
        if (\XLite\Core\Converter::isEmptyString($this->getCleanURL())) {
            $this->setCleanURL($this->generateCleanURL());
        }
    }

    /**
     * Remove duplicates from clean url history
     *
     * @return void
     */
    protected function filterCleanURLDuplicates()
    {
        $cleanURLs = array();
        foreach (array_reverse($this->getCleanURLs()->toArray()) as $cleanURLObject) {
            if (in_array($cleanURLObject->getCleanURL(), $cleanURLs)) {
                \XLite\Core\Database::getEM()->remove($cleanURLObject);
                $this->getCleanURLs()->removeElement($cleanURLObject);

            } else {
                $cleanURLs[] = $cleanURLObject->getCleanURL();
            }
        }
    }

    /**
     * Cut clean url history
     *
     * @return void
     */
    protected function filterCleanURLHistoryLength()
    {
        $count = 0;
        foreach (array_reverse($this->getCleanURLs()->toArray()) as $cleanURLObject) {
            if ($count++ >= static::CLEAN_URL_HISTORY_LENGTH) {
                $this->getCleanURLs()->removeElement($cleanURLObject);
                \XLite\Core\Database::getEM()->remove($cleanURLObject);
            }
        }
    }
}
