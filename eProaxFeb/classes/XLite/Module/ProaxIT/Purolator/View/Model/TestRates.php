<?php
// vim: set ts=4 sw=4 sts=4 et:

namespace XLite\Module\ProaxIT\Purolator\View\Model;

/**
 * TestRates widget
 */
class TestRates extends \XLite\View\Model\TestRates
{
    /**
     * Add CSS files
     *
     * @return array
     */
    public function getCSSFiles()
    {
        $list = parent::getCSSFiles();

        $list[] = 'modules/ProaxIT/Purolator/style.css';

        return $list;
    }

    /**
     * Return name of web form widget class
     *
     * @return string
     */
    protected function getFormTarget()
    {
        return 'purolator';
    }

    /**
     * Returns the list of related targets
     *
     * @return array
     */
    protected function getAvailableSchemaFields()
    {
        return array(
            static::SCHEMA_FIELD_WEIGHT,
            static::SCHEMA_FIELD_SUBTOTAL,
            static::SCHEMA_FIELD_SRC_ZIPCODE,
            static::SCHEMA_FIELD_DST_COUNTRY,
            static::SCHEMA_FIELD_DST_ZIPCODE,
        );
    }
}
