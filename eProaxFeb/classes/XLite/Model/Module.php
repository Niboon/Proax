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
 * Module
 *
 * @Entity (repositoryClass="\XLite\Model\Repo\Module")
 * @Table  (name="modules",
 *      uniqueConstraints={
 *          @UniqueConstraint (name="moduleVersion", columns={"author","name","majorVersion","minorVersion","fromMarketplace"})
 *      },
 *      indexes={
 *          @Index (name="enabled", columns={"enabled"}),
 *          @Index (name="date", columns={"date"}),
 *          @Index (name="downloads", columns={"downloads"}),
 *          @Index (name="rating", columns={"rating"}),
 *          @Index (name="ani", columns={"author","name","installed"}),
 *          @Index (name="anmm", columns={"author","name","majorVersion","minorVersion"}),
 *          @Index (name="anf", columns={"author","name","fromMarketplace"})
 *      }
 * )
 * @HasLifecycleCallbacks
 */
class Module extends \XLite\Model\AEntity
{
    /**
     * Value of not X-Cart 5 module plan (zero)
     */
    const NOT_XCN_MODULE = 0;

    /**
     * Value of xcn_plan for non-available modules
     */
    const NOT_AVAILABLE_MODULE = -1;


    /**
     * Module ID
     *
     * @var integer
     *
     * @Id
     * @GeneratedValue (strategy="AUTO")
     * @Column         (type="integer")
     */
    protected $moduleID;

    /**
     * Name
     *
     * @var string
     *
     * @Column (type="string", length=64)
     */
    protected $name;

    /**
     * Author
     *
     * @var string
     *
     * @Column (type="string", length=64)
     */
    protected $author;

    /**
     * Author
     *
     * @var string
     *
     * @Column (type="string", length=255)
     */
    protected $authorEmail;

    /**
     * Enabled
     *
     * @var boolean
     *
     * @Column (type="boolean")
     */
    protected $enabled = false;

    /**
     * Installed status
     *
     * @var boolean
     *
     * @Column (type="boolean")
     */
    protected $installed = false;

    /**
     * install.yaml file load status
     *
     * @var boolean
     *
     * @Column (type="boolean")
     */
    protected $yamlLoaded = false;

    /**
     * Order creation timestamp
     *
     * @var integer
     *
     * @Column (type="integer")
     */
    protected $date = 0;

    /**
     * Rating
     *
     * @var float
     *
     * @Column (type="decimal", precision=14, scale=4)
     */
    protected $rating = 0;

    /**
     * Votes
     *
     * @var integer
     *
     * @Column (type="integer")
     */
    protected $votes = 0;

    /**
     * Downloads
     *
     * @var integer
     *
     * @Column (type="integer")
     */
    protected $downloads = 0;

    /**
     * Price
     *
     * @var float
     *
     * @Column (type="decimal", precision=14, scale=2)
     */
    protected $price = 0.00;

    /**
     * Currency code
     *
     * @var string
     *
     * @Column (type="string", length=3)
     */
    protected $currency = 'USD';

    /**
     * Major version
     *
     * @var string
     *
     * @Column (type="string", length=8)
     */
    protected $majorVersion;

    /**
     * Minor version
     *
     * @var string
     *
     * @Column (type="integer")
     */
    protected $minorVersion;

    /**
     * Minor core version which is required for module functionality
     *
     * @var string
     *
     * @Column (type="integer")
     */
    protected $minorRequiredCoreVersion = 0;

    /**
     * Revision date
     *
     * @var integer
     *
     * @Column (type="integer")
     */
    protected $revisionDate = 0;

    /**
     * Module pack size (received from marketplace)
     *
     * @var integer
     *
     * @Column (type="bigint")
     */
    protected $packSize = 0;

    /**
     * X-Cart 5 plan identificator for module
     *  0 - for all kinds of installation
     * -1 - not for downloadable
     * >0 - part of specific X-Cart 5 plan
     *
     * @var integer
     *
     * @Column (type="bigint")
     */
    protected $xcnPlan = 0;

    /**
     * Module name
     *
     * @var string
     *
     * @Column (type="string", length=255)
     */
    protected $moduleName;

    /**
     * Author name
     *
     * @var string
     *
     * @Column (type="string", length=255)
     */
    protected $authorName;

    /**
     * Description
     *
     * @var string
     *
     * @Column (type="text")
     */
    protected $description = '';

    /**
     * Icon URL
     *
     * @var string
     *
     * @Column (type="string", length=255, nullable=true)
     */
    protected $iconURL;

    /**
     * Icon URL
     *
     * @var string
     *
     * @Column (type="string", length=255)
     */
    protected $pageURL = '';

    /**
     * Icon URL
     *
     * @var string
     *
     * @Column (type="string", length=255)
     */
    protected $authorPageURL = '';

    /**
     * Module dependencies
     *
     * @var array
     *
     * @Column (type="array")
     */
    protected $dependencies = array();

    /**
     * Module tags
     *
     * @var array
     *
     * @Column (type="array")
     */
    protected $tags = array();

    /**
     * Flag
     *
     * @var boolean
     *
     * @Column (type="boolean")
     */
    protected $fromMarketplace = false;

    /**
     * Landing page flag
     *
     * @var boolean
     *
     * @Column (type="boolean")
     */
    protected $isLanding = false;

    /**
     * Position on the landing page
     *
     * @var integer
     *
     * @Column (type="integer")
     */
    protected $landingPosition = 0;

    /**
     * Public identifier (cache)
     *
     * @var string
     */
    protected $marketplaceID;

    /**
     * Flag (true if module is 'system module')
     *
     * @var boolean
     *
     * @Column (type="boolean")
     */
    protected $isSystem = false;

    /**
     * Flag (true if module has license text)
     *
     * @var boolean
     *
     * @Column (type="boolean")
     */
    protected $hasLicense = false;

    /**
     * Module availability in the store's edition
     * 0 - addon is not available in any editions
     * 1 - addon is available in store's edition
     * 2 - addon is available in other editions only
     *
     * @var integer
     *
     * @Column (type="integer")
     */
    protected $editionState = 0;

    /**
     * Available editions
     *
     * @var array
     *
     * @Column (type="array")
     */
    protected $editions = array();

    /**
     * XB product ID
     *
     * @var integer
     *
     * @Column (type="integer")
     */
    protected $xbProductId = 0;

    /**
     * Icon URL cached 
     * 
     * @var   string
     */
    protected $iconURLCached;

    // {{{ Routines to access methods of (non)installed modules

    /**
     * Return main class name for current module
     *
     * @return string
     */
    public function getMainClass()
    {
        return '\XLite\Module\\' . $this->getActualName() . '\Main';
    }

    /**
     * Method to call functions from module main classes
     *
     * @param string $method Method to call
     * @param mixed  $result Method return value for the current class (model) OPTIONAL
     * @param array  $args   Call arguments OPTIONAL
     *
     * @return mixed
     */
    public function callModuleMethod($method, $result = null, array $args = array())
    {
        return $this->checkModuleMainClass()
            ? call_user_func_array(array($this->getMainClass(), $method), $args)
            : $result;
    }

    /**
     * Check if we can call method from the module main class
     *
     * @return boolean
     */
    public function checkModuleMainClass()
    {
        $result = $this->isInstalled();

        if ($result && !\Includes\Utils\Operator::checkIfClassExists($this->getMainClass())) {
            if (defined('LC_MODULE_CONTROL')) {
                $path = LC_DIR_CLASSES . \Includes\Utils\Converter::getClassFile($this->getMainClass());
                if (\Includes\Utils\FileManager::isFileReadable($path)) {
                    require_once $path;

                } else {
                    $result = false;
                }

            } else {
                $result = false;
            }
        }

        return $result;
    }

    // }}}

    // {{{ Some common getters and setters

    /**
     * Compose module actual name
     *
     * @return string
     */
    public function getActualName()
    {
        return \Includes\Utils\ModulesManager::getActualName($this->getAuthor(), $this->getName());
    }

    /**
     * Return module full version
     *
     * @return string
     */
    public function getVersion()
    {
        return \Includes\Utils\Converter::composeVersion($this->getMajorVersion(), $this->getMinorVersion());
    }

    /**
     * Check if module has a custom icon
     *
     * @return boolean
     */
    public function hasIcon()
    {
        return (bool) $this->getPublicIconURL();
    }

    /**
     * Get public icon URL
     *
     * @return string
     */
    public function getPublicIconURL()
    {
        if (!isset($this->iconURLCached)) {
            $this->iconURLCached = $this->getIconURL();

            if (!$this->iconURLCached) {
                $path = \Includes\Utils\ModulesManager::getModuleIconFile($this->getAuthor(), $this->getName());

                if (\Includes\Utils\FileManager::isFileReadable($path)) {
                    $this->iconURLCached = \XLite\Core\Converter::buildURL(
                        'module',
                        null,
                        array(
                            'author' => $this->getAuthor(),
                            'name'   => $this->getName(),
                        ),
                        'image.php'
                    );
                }
            }
        }

        return $this->iconURLCached;
    }

    /**
     * Return link to settings form
     *
     * @return string
     */
    public function getSettingsForm()
    {
        return $this->callModuleMethod('getSettingsForm')
            ?: \XLite\Core\Converter::buildURL('module', '', array('moduleId' => $this->getModuleId()), \XLite::ADMIN_SELF);
    }

    /**
     * Get list of dependency modules as Doctrine entities
     *
     * @param mixed $onlyDisabled Flag OPTIONAL
     *
     * @return array
     */
    public function getDependencyModules($onlyDisabled = false)
    {
        $result  = array();
        $classes = array_fill_keys($this->getDependencies(), true);

        if (!empty($classes)) {
            foreach ($this->getRepository()->getDependencyModules($classes) as $module) {
                unset($classes[$module->getActualName()]);

                if (!($onlyDisabled && $module->getEnabled())) {
                    $result[] = $module;
                }
            }

            foreach ($classes as $class => $tmp) {
                list($author, $name) = explode('\\', $class);

                $module = new \XLite\Model\Module();
                $module->setName($name);
                $module->setAuthor($author);
                $module->setModuleName($name);
                $module->setAuthorName($author);
                $module->setEnabled(false);
                $module->setInstalled(false);

                $result[] = $module;
            }
        }

        return $result;
    }

    /**
     * Get list of dependent modules as Doctrine entities
     *
     * @return array
     */
    public function getDependentModules()
    {
        $result  = array();
        $current = \Includes\Decorator\ADecorator::getModulesGraph()->find($this->getActualName());

        if ($current) {
            foreach ($current->getChildren() as $node) {
                $class = $node->getActualName();
                $result[$class] = $this->getRepository()
                    ->findOneBy(array_combine(array('author', 'name'), explode('\\', $class)), array('installed' => 'DESC'));
            }
        }

        return array_filter($result);
    }

    /**
     * Check if the module can be disabled
     *
     * @param \XLite\Model\Module $module Module
     *
     * @return boolean
     */
    public function canDisable()
    {
        return !(bool)$this->getDependentModules();
    }

    /**
     * Check - module can uninstall or not
     * 
     * @return boolean
     */
    public function canUninstall()
    {
        return !$this->getEnabled() || !$this->callModuleMethod('isSeparateUninstall');
    }

    /**
     * Check if the module can be enabled
     *
     * @param boolean $safeCheck True - check if dependent modules can be enabled, false - dependent modules must be active
     *
     * @return boolean
     */
    public function canEnable($safeCheck = false)
    {
        $result = $this->isModuleCompatible();

        if ($result) {

            $dependencies = $this->getDependencies();

            if ($dependencies) {
                $modules = array_keys(\Includes\Utils\ModulesManager::getActiveModules());
                $result  = ! (bool) array_diff($dependencies, $modules);
            }

            if (!$result && $safeCheck) {
                $result = true;
                foreach ($this->getDependencyModules(true) as $dep) {
                    if ($dep->getFromMarketplace() || !$dep->canEnable($safeCheck)) {
                        $result = false;
                        break;
                    }
                }
            }
        }

        if ($result) {
            $result = !$this->hasWrongDependencies($safeCheck);
        }

        return $result;
    }

    /**
     * Return true if module compatible with current core.
     *
     * @return boolean
     */
    public function isModuleCompatible()
    {
        return \XLite::getInstance()->checkVersion($this->getMajorVersion(), '=')
            && \XLite::getInstance()->checkMinorVersion($this->getMinorRequiredCoreVersion(), '>=');
    }

    /**
     * Check if there are modules current module depends on
     *
     * @param boolean $safeCheck True - do not check module dependencies
     *
     * @return array
     */
    public function hasWrongDependencies($safeCheck = false)
    {
        return (!$safeCheck && (bool) $this->getDependencyModules(true)) || ((bool) $this->getEnabledMutualModules());
    }

    /**
     * Return list of modules current module requires to be disabled
     *
     * @return array
     */
    public function getEnabledMutualModules()
    {
        $list = array();

        $modules = \Includes\Utils\ModulesManager::getActiveModules();

        foreach ($modules as $m => $data) {

            $mutualModules = \Includes\Utils\ModulesManager::callModuleMethod($m, 'getMutualModulesList');

            if (in_array($this->getActualName(), $mutualModules) && !isset($list[$m])) {
                $list[$m] = \XLite\Core\Database::getRepo('XLite\Model\Module')
                    ->findOneBy(array_combine(array('author', 'name'), explode('\\', $m)));
            }
        }

        return $list;
    }

    /**
     * Check if the module is free
     *
     * @return boolean
     */
    public function isFree()
    {
        return 0 >= $this->getPrice();
    }

    /**
     * Check if module is already purchased
     *
     * @return boolean
     */
    public function isPurchased()
    {
        $xcnModulePlan = $this->getXcnPlan();

        return (bool) (static::NOT_XCN_MODULE == $xcnModulePlan
            ? $this->getLicenseKey()
            : $this->isAvailable() && $xcnModulePlan == \XLite::getXCNLicenseType()
        );
    }

    /**
     * Return true if module is not available for installation in XCN
     *
     * @return boolean
     */
    public function isAvailable()
    {
        return static::NOT_AVAILABLE_MODULE != $this->getXcnPlan();
    }

    /**
     * Check for custom module
     *
     * @return boolean
     */
    public function isCustom()
    {
        $module = $this->getRepository()->getModuleFromMarketplace($this);

        return !isset($module) || $module->getModuleID() === $this->getModuleID();
    }

    /**
     * Search for license key
     *
     * @return \XLite\Model\ModuleKey
     */
    public function getLicenseKey()
    {
        return \XLite\Core\Database::getRepo('\XLite\Model\ModuleKey')->findKey($this->getAuthor(), $this->getName());
    }

    /**
     * Return list of editions names
     *
     * @return array
     */
    public function getEditionNames()
    {
        $result = $this->getEditions();

        foreach ($result as $k => $v) {
            $result[$k] = preg_replace('/^(\d+)_(.+)$/', '\2', $v);
        }

        return $result;
    }

    /**
     * Return currency for paid modules
     *
     * @return \XLite\Model\Currency
     */
    public function getCurrency()
    {
        return \XLite\Core\Database::getRepo('\XLite\Model\Currency')->findOneByCode($this->currency)
            ?: $this->currency;
    }

    /**
     * Check if module is installed in LC
     *
     * @return boolean
     */
    public function isInstalled()
    {
        return $this->installed ?: (bool) $this->getRepository()->getModuleInstalled($this);
    }

    /**
     * Return some data to identify module
     *
     * @return array
     */
    public function getIdentityData()
    {
        return array(
            'author'       => $this->getAuthor(),
            'name'         => $this->getName(),
            'majorVersion' => $this->getMajorVersion(),
            'minorVersion' => $this->getMinorVersion(),
        );
    }

    /**
     * Generate marketplace ID
     *
     * @return string
     */
    public function getMarketplaceID()
    {
        if (!isset($this->marketplaceID)) {
            $this->marketplaceID = md5(implode('', $this->getIdentityData()));
        }

        return $this->marketplaceID;
    }

    /**
     * Return true if module is translation
     *
     * @return boolean
     */
    public function isTranslation()
    {
        $result = false;

        if (!$this->isInstalled()) {

            $tags = $this->getTags();

            if (is_array($tags) && in_array('Translation', $tags)) {
                $result = true;
            }
        }

        return $result;
    }

    /**
     * Module has license restriction if the core has free license but the module does not have free edition
     * The empty editions field means the module has no license restriction
     *
     * @return boolean
     */
    public function hasLicenseRestriction()
    {
        $result = false;
        $coreKey = \XLite::getXCNLicenseKey();

        // The store without any license (trial license active) has no license-restricted module
        if ($coreKey !== '') {
            $editions = $this->getEditionNames();

            // The module with the empty editions structure has no restrictions
            if (!empty($editions)) {
                $freeEdition = \XLite\Core\Marketplace::getInstance()->getFreeLicenseEdition();

                // Module has restricted if there is no freeEdition in the editions structure
                $result = !in_array($freeEdition, $editions);
            }
        }

        return $result;
    }

    // }}}

    // {{{ Change module state routines

    /**
     * Lifecycle callback
     *
     * @return void
     *
     * @PreUpdate
     */
    public function prepareBeforeUpdate()
    {
        $changeSet = \XLite\Core\Database::getEM()->getUnitOfWork()->getEntityChangeSet($this);

        if (!empty($changeSet['enabled'])) {
            \XLite\Core\Database::getInstance()->setDisabledStructures(
                $this->getActualName(),
                $this->getEnabled()
                    ? array()
                    : \Includes\Utils\ModulesManager::getModuleProtectedStructures($this->getAuthor(), $this->getName())
            );

            $this->switchLinkedModels($this->getEnabled());
        }
    }

    /**
     * Switch linked models
     *
     * @param boolean $enabled Module enabled status
     *
     * @return void
     */
    protected function switchLinkedModels($enabled)
    {
        foreach ($this->getLinkedModelDefinition() as $repo) {
            \XLite\Core\Database::getRepo($repo)->switchModuleLink($enabled, $this);
        }
    }

    /**
     * Get linked model definition
     *
     * @return array
     */
    protected function getLinkedModelDefinition()
    {
        return array(
            'XLite\Model\Payment\Method',
        );
    }

    // }}}
}
