<?php

/**
 * @file classes/publicationFormat/SalesRights.php
 *
 * Copyright (c) 2014-2021 Simon Fraser University
 * Copyright (c) 2003-2021 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class SalesRights
 *
 * @ingroup publicationFormat
 *
 * @see SalesRightsDAO
 *
 * @brief Basic class describing a sales rights composite type (used on the ONIX templates for publication formats)
 */

namespace APP\publicationFormat;

use APP\codelist\ONIXCodelistItemDAO;
use PKP\db\DAORegistry;

class SalesRights extends \PKP\core\DataObject
{
    /**
     * get publication format id
     *
     * @return int
     */
    public function getPublicationFormatId()
    {
        return $this->getData('publicationFormatId');
    }

    /**
     * set publication format id
     */
    public function setPublicationFormatId($publicationFormatId)
    {
        $this->setData('publicationFormatId', $publicationFormatId);
    }

    /**
     * Set the ONIX code for this sales rights entry
     *
     * @param string $type
     */
    public function setType($type)
    {
        $this->setData('type', $type);
    }

    /**
     * Get the ONIX code for this sales rights entry
     *
     * @return string
     */
    public function getType()
    {
        return $this->getData('type');
    }

    /**
     * Get the human-readable name for this ONIX code
     *
     * @return string
     */
    public function getNameForONIXCode()
    {
        $onixCodelistItemDao = DAORegistry::getDAO('ONIXCodelistItemDAO'); /** @var ONIXCodelistItemDAO $onixCodelistItemDao */
        $codes = $onixCodelistItemDao->getCodes('46'); // List 46 is for sales rights types
        return $codes[$this->getType()];
    }

    /**
     * Set the ROWSetting for this sales rights entry (Rest Of World)
     *
     * @param bool $rowSetting
     */
    public function setROWSetting($rowSetting)
    {
        $this->setData('rowSetting', $rowSetting);
    }

    /**
     * Get the ROWSetting value for this sales rights entry (Rest Of World)
     *
     * @return string
     */
    public function getROWSetting()
    {
        return $this->getData('rowSetting');
    }

    /**
     * Get the included countries for this sales rights entry
     *
     * @return array
     */
    public function getCountriesIncluded()
    {
        return $this->getData('countriesIncluded');
    }

    /**
     * Set the included country list for this sales rights entry
     *
     * @param array $countriesIncluded
     */
    public function setCountriesIncluded($countriesIncluded)
    {
        $this->setData('countriesIncluded', array_filter($countriesIncluded, [&$this, '_removeEmptyElements']));
    }

    /**
     * Get the excluded countries for this sales rights entry
     *
     * @return array
     */
    public function getCountriesExcluded()
    {
        return $this->getData('countriesExcluded');
    }

    /**
     * Set the excluded country list for this sales rights entry
     *
     * @param array $countriesExcluded
     */
    public function setCountriesExcluded($countriesExcluded)
    {
        $this->setData('countriesExcluded', array_filter($countriesExcluded, [&$this, '_removeEmptyElements']));
    }

    /**
     * Get the included regions for this sales rights entry
     *
     * @return array
     */
    public function getRegionsIncluded()
    {
        return $this->getData('regionsIncluded');
    }

    /**
     * Set the included region list for this sales rights entry
     *
     * @param array $regionsIncluded
     */
    public function setRegionsIncluded($regionsIncluded)
    {
        $this->setData('regionsIncluded', array_filter($regionsIncluded, [&$this, '_removeEmptyElements']));
    }

    /**
     * Get the excluded regions for this sales rights entry
     *
     * @return array
     */
    public function getRegionsExcluded()
    {
        return $this->getData('regionsExcluded');
    }

    /**
     * Set the excluded region list for this sales rights entry
     *
     * @param array $regionsExcluded
     */
    public function setRegionsExcluded($regionsExcluded)
    {
        $this->setData('regionsExcluded', array_filter($regionsExcluded, [&$this, '_removeEmptyElements']));
    }

    /**
     * Internal function for an array_filter to remove empty countries.
     * array_filter() can be called without a callback to remove empty array elements but it depends
     * on type juggling and may not be reliable.
     *
     * @param string $value
     *
     * @return bool
     */
    public function _removeEmptyElements($value)
    {
        return trim($value) != '';
    }
}
