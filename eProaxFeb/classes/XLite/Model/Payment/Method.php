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

namespace XLite\Model\Payment;

/**
 * Payment method
 *
 * @Entity (repositoryClass="\XLite\Model\Repo\Payment\Method")
 * @Table  (name="payment_methods",
 *      indexes={
 *          @Index (name="orderby", columns={"orderby"}),
 *          @Index (name="class", columns={"class","enabled"}),
 *          @Index (name="enabled", columns={"enabled"})
 *      }
 * )
 */
class Method extends \XLite\Model\Base\I18n
{
    /**
     * Type codes
     */
    const TYPE_ALLINONE    = 'A';
    const TYPE_CC_GATEWAY  = 'C';
    const TYPE_ALTERNATIVE = 'N';
    const TYPE_OFFLINE     = 'O';


    /**
     * Payment method unique id
     *
     * @var integer
     *
     * @Id
     * @GeneratedValue (strategy="AUTO")
     * @Column         (type="integer")
     */
    protected $method_id;

    /**
     * Method service name (gateway or API name)
     *
     * @var string
     *
     * @Column (type="string", length=128)
     */
    protected $service_name;

    /**
     * Process class name
     *
     * @var string
     *
     * @Column (type="string", length=255)
     */
    protected $class;

    /**
     * Specific module family name
     *
     * @var string
     *
     * @Column (type="string", length=255)
     */
    protected $moduleName = '';

    /**
     * Position
     *
     * @var integer
     *
     * @Column (type="integer")
     */
    protected $orderby = 0;

    /**
     * Enabled status
     *
     * @var boolean
     *
     * @Column (type="boolean")
     */
    protected $enabled = false;

    /**
     * Module enabled status
     *
     * @var boolean
     *
     * @Column (type="boolean")
     */
    protected $moduleEnabled = true;

    /**
     * Added status
     *
     * @var boolean
     *
     * @Column (type="boolean")
     */
    protected $added = false;

    /**
     * Type
     *
     * @var string
     *
     * @Column (type="string", options={ "fixed": true }, length=1)
     */
    protected $type = self::TYPE_OFFLINE;

    /**
     * Settings
     *
     * @var \XLite\Model\Payment\MethodSetting
     *
     * @OneToMany (targetEntity="XLite\Model\Payment\MethodSetting", mappedBy="payment_method", cascade={"all"})
     */
    protected $settings;

    /**
     * Get processor
     *
     * @return \XLite\Model\Payment\Base\Processor
     */
    public function getProcessor()
    {
        $class = '\XLite\\' . $this->getClass();

        return \XLite\Core\Operator::isClassExists($class) ? $class::getInstance() : null;
    }

    /**
     * Check - enabled method or not
     * FIXME - must be removed
     *
     * @return boolean
     */
    public function isEnabled()
    {
        return ($this->getEnabled() || $this->isForcedEnabled())
            && $this->getAdded()
            && $this->getModuleEnabled()
            && $this->getProcessor()
            && $this->getProcessor()->isConfigured($this);
    }

    /**
     * Set class
     *
     * @return void
     */
    public function setClass($class)
    {
        $this->class = preg_replace('/^\\\?(?:XLite\\\)?\\\?/Sis', '', $class);

        if (preg_match('/^Module/Sis', $class) > 0) {

            list($modulePrefix, $author, $name) = explode('\\', $class, 4);

            $this->setModuleName($author . '_' . $name);
        }
    }

    /**
     * Get setting value by name
     *
     * @param string $name Name
     *
     * @return string|void
     */
    public function getSetting($name)
    {
        $entity = $this->getSettingEntity($name);

        return $entity ? $entity->getValue() : null;
    }

    /**
     * Get position
     *
     * @return integer
     */
    public function getPosition()
    {
        return $this->getOrderby();
    }

    /**
     * Set position
     *
     * @param integer $position Position
     *
     * @return integer
     */
    public function setPosition($position)
    {
        return $this->setOrderby($position);
    }

    /**
     * Get setting by name
     *
     * @param string $name Name
     *
     * @return \XLite\Model\Payment\MethodSetting
     */
    public function getSettingEntity($name)
    {
        $result = null;

        foreach ($this->getSettings() as $setting) {
            if ($setting->getName() == $name) {
                $result = $setting;
                break;
            }
        }

        return $result;
    }

    /**
     * Set setting value by name
     *
     * @param string $name  Name
     * @param string $value Value
     *
     * @return boolean
     */
    public function setSetting($name, $value)
    {
        $result = false;

        // Update settings which is already stored in database
        $setting = $this->getSettingEntity($name);

        if ($setting) {
            $setting->setValue(strval($value));
            $result = true;

        } else {

            // Create setting which is not in database but specified in the processor class

            $processor = $this->getProcessor();

            if ($processor && method_exists($processor, 'getAvailableSettings')) {
                $availableSettings = $processor->getAvailableSettings();

                if (in_array($name, $availableSettings)) {
                    $setting = new \XLite\Model\Payment\MethodSetting();
                    $setting->setName($name);
                    $setting->setValue(strval($value));
                    $setting->setPaymentMethod($this);

                    \XLite\Core\Database::getEM()->persist($setting);
                }
            }
        }

        return $result;
    }

    /**
     * Constructor
     *
     * @param array $data Entity properties OPTIONAL
     *
     * @return void
     */
    public function __construct(array $data = array())
    {
        $this->settings     = new \Doctrine\Common\Collections\ArrayCollection();
        $this->transactions = new \Doctrine\Common\Collections\ArrayCollection();

        parent::__construct($data);
    }

    /**
     * Call processor methods
     *
     * @param string $method    Method name
     * @param array  $arguments Arguments OPTIONAL
     *
     * @return mixed
     */
    public function __call($method, array $arguments = array())
    {
        array_unshift($arguments, $this);

        return $this->getProcessor()
            ? call_user_func_array(array($this->getProcessor(), $method), $arguments)
            : null;
    }

    /**
     * Get warning note
     *
     * @return string
     */
    public function getWarningNote()
    {
        $message = null;

        if ($this->getProcessor() && !$this->getProcessor()->isConfigured($this)) {
            $message = static::t('The method is not configured and cannot be used');
        }

        if (!$message) {
            $message = $this->getProcessor() ? $this->getProcessor()->getWarningNote($this) : null;
        }

        return $message;
    }

    /**
     * Get payment method admin zone icon URL
     *
     * @return string
     */
    public function getAdminIconURL()
    {
        $url = $this->getProcessor() ? $this->getProcessor()->getAdminIconURL($this) : null;

        if (true === $url) {
            $module = $this->getProcessor()->getModule();
            $url = $module
                ? \XLite\Core\Layout::getInstance()->getResourceWebPath('modules/' . $module->getAuthor() . '/' . $module->getName() . '/method_icon.png')
                : null;
        }

        return $url;
    }

    /**
     * Get payment method alternative admin zone icon URL
     *
     * @return string
     */
    public function getAltAdminIconURL()
    {
        $module = $this->getProcessor()->getModule();

        return $module
            ? \XLite\Core\Layout::getInstance()->getResourceWebPath('modules/' . $module->getAuthor() . '/' . $module->getName() . '/method_icon_' . $this->getServiceName() . '.png')
            : null;
    }

    /**
     * Set enabled
     *
     * @param boolean $enabled Property value
     *
     * @return \XLite\Model\Payment\Method
     */
    public function setEnabled($enabled)
    {
        $this->enabled = $enabled;

        if ($this->getProcessor()) {
            $this->getProcessor()->enableMethod($this);
        }

        return $this;
    }

    /**
     * Set 'added' property
     *
     * @param boolean $added Property value
     *
     * @return \XLite\Model\Payment\Method
     */
    public function setAdded($added)
    {
        $this->added = $added;

        if (!$added) {
            $this->setEnabled(false);
        }

        return $this;
    }
}
