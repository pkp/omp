<?php

/**
 * @file classes/publicationFormat/IdentificationCode.php
 *
 * Copyright (c) 2014-2021 Simon Fraser University
 * Copyright (c) 2003-2021 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class IdentificationCode
 *
 * @ingroup publicationFormat
 *
 * @see IdentificationCodeDAO
 *
 * @brief Basic class describing an identification code (used on the ONIX templates for publication formats)
 */

namespace APP\publicationFormat;

use APP\codelist\ONIXCodelistItemDAO;
use PKP\core\DataObject;
use PKP\db\DAORegistry;

class IdentificationCode extends DataObject
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
     * Set the ONIX code for this identification code
     *
     * @param string $code
     */
    public function setCode($code)
    {
        $this->setData('code', $code);
    }

    /**
     * Get the ONIX code for the identification code
     *
     * @return string
     */
    public function getCode()
    {
        return $this->getData('code');
    }

    /**
     * Get the human-readable name for this ONIX code
     *
     * @return string
     */
    public function getNameForONIXCode()
    {
        $onixCodelistItemDao = DAORegistry::getDAO('ONIXCodelistItemDAO'); /** @var ONIXCodelistItemDAO $onixCodelistItemDao */
        $codes = $onixCodelistItemDao->getCodes('5'); // List 5 is for product identifier type
        return $codes[$this->getCode()];
    }

    /**
     * Set the value for this identification code
     *
     * @param string $value
     */
    public function setValue($value)
    {
        $this->setData('value', $value);
    }

    /**
     * Get the value for the identification code
     *
     * @return string
     */
    public function getValue()
    {
        return $this->getData('value');
    }
}

if (!PKP_STRICT_MODE) {
    class_alias('\APP\publicationFormat\IdentificationCode', '\IdentificationCode');
}
