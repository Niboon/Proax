<?php
// vim: set ts=4 sw=4 sts=4 et:

namespace XLite\Module\ProaxIT\Purolator\Controller\Admin;

/**
 * Purolator shipping module settings controller
 */
class Purolator extends \XLite\Controller\Admin\ShippingSettings
{
    /**
     * Return the current page title (for the content area)
     *
     * @return string
     */
    public function getTitle()
    {
        return 'Purolator settings';
    }

    /**
     * Update settings
     *
     * @return void
     */
    public function doActionUpdate()
    {
        $postedData = \XLite\Core\Request::getInstance()->getData();
        $options    = \XLite\Core\Database::getRepo('\XLite\Model\Config')
            ->findBy(array('category' => $this->getOptionsCategory(), 'type' => 'checkbox'));

        foreach ($options as $key => $option) {

            $name = $option->getName();

            if (!isset($postedData[$name])) {
                \XLite\Core\Request::getInstance()->{$name} = '';
            }
        }

        parent::doActionUpdate();
    }

    /**
     * getOptionsCategory
     *
     * @return string
     */
    protected function getOptionsCategory()
    {
        return 'ProaxIT\Purolator';
    }

    /**
     * Get shipping processor
     *
     * @return object
     */
    protected function getProcessor()
    {
        return new \XLite\Module\ProaxIT\Purolator\Model\Shipping\Processor\Purolator();
    }

    /**
     * Get input data to calculate test rates
     *
     * @param array $schema  Input data schema
     * @param array &$errors Array of fields which are not set
     *
     * @return array
     */
    protected function getTestRatesData(array $schema, &$errors)
    {
        $data = parent::getTestRatesData($schema, $errors);

        $package = array(
            'weight'   => $data['weight'],
            'subtotal' => $data['subtotal'],
        );

        $data['packages'] = array();
        $data['packages'][] = $package;

        unset($data['weight']);
        unset($data['subtotal']);

        return $data;
    }

    /**
     * Get schema of an array for test rates routine
     *
     * @return array
     */
    protected function getTestRatesSchema()
    {
        return array(
            'weight' => \XLite\View\Model\TestRates::SCHEMA_FIELD_WEIGHT,
            'subtotal' => \XLite\View\Model\TestRates::SCHEMA_FIELD_SUBTOTAL,
            'srcAddress' => array(
                'zipcode' => \XLite\View\Model\TestRates::SCHEMA_FIELD_SRC_ZIPCODE,
            ),
            'dstAddress' => array(
                'country' => \XLite\View\Model\TestRates::SCHEMA_FIELD_DST_COUNTRY,
                'zipcode' => \XLite\View\Model\TestRates::SCHEMA_FIELD_DST_ZIPCODE
            )
        );
    }

}
