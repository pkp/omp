<?php

/**
 * @file classes/monograph/Representative.php
 *
 * Copyright (c) 2014-2021 Simon Fraser University
 * Copyright (c) 2003-2021 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class Representative
 *
 * @ingroup monograph
 *
 * @see RepresentativeDAO
 *
 * @brief Basic class describing a representative composite type (used on the ONIX templates for publication formats).
 * This type is used for both Agents and Suppliers.
 */

namespace APP\monograph;

use APP\codelist\ONIXCodelistItemDAO;
use PKP\db\DAORegistry;

class Representative extends \PKP\core\DataObject
{
    /**
     * get monograph id.
     *
     * @return int
     */
    public function getMonographId()
    {
        return $this->getData('monographId');
    }

    /**
     * set monograph id.
     *
     * @param int $monographId
     */
    public function setMonographId($monographId): void
    {
        $this->setData('monographId', $monographId);
    }

    /**
     * Set the ONIX code for this representative role (List93 for Suppliers, List69 for Agents)
     */
    public function setRole($role): void
    {
        $this->setData('role', $role);
    }

    /**
     * Get the ONIX code for this representative role.
     *
     * @return string
     */
    public function getRole()
    {
        return $this->getData('role');
    }

    /**
     * Get the human-readable name for this ONIX code
     *
     * @return string
     */
    public function getNameForONIXCode()
    {
        $onixCodelistItemDao = DAORegistry::getDAO('ONIXCodelistItemDAO'); /** @var ONIXCodelistItemDAO $onixCodelistItemDao */
        if ($this->getIsSupplier()) {
            $listName = '93'; // List 93 -> Supplier role
        } else {
            $listName = '69'; // List 69 -> Agent role
        }
        $codes = $onixCodelistItemDao->getCodes($listName);
        return $codes[$this->getRole()];
    }

    /**
     * Set the ONIX code for this representative's ID type (List92) (GLN, SAN, etc).  GLN is the recommended one.
     *
     * @param string $representativeIdType
     */
    public function setRepresentativeIdType($representativeIdType)
    {
        $this->setData('representativeIdType', $representativeIdType);
    }

    /**
     * Get the representative ID type (ONIX Code).
     *
     * @return string
     */
    public function getRepresentativeIdType()
    {
        return $this->getData('representativeIdType');
    }

    /**
     * Set this representative's ID value.
     *
     * @param string $representativeIdValue
     */
    public function setRepresentativeIdValue($representativeIdValue)
    {
        $this->setData('representativeIdValue', $representativeIdValue);
    }

    /**
     * Get the representative ID value.
     *
     * @return string
     */
    public function getRepresentativeIdValue()
    {
        return $this->getData('representativeIdValue');
    }

    /**
     * Get the representative name.
     *
     * @return string
     */
    public function getName()
    {
        return $this->getData('name');
    }

    /**
     * Set the representative name.
     *
     * @param string $name
     */
    public function setName($name)
    {
        $this->setData('name', $name);
    }

    /**
     * Get the representative phone.
     *
     * @return string
     */
    public function getPhone()
    {
        return $this->getData('phone');
    }

    /**
     * Set the representative phone.
     *
     * @param string $phone
     */
    public function setPhone($phone)
    {
        $this->setData('phone', $phone);
    }

    /**
     * Get the representative email address.
     *
     * @return string
     */
    public function getEmail()
    {
        return $this->getData('email');
    }

    /**
     * Set the representative email address.
     *
     * @param string $email
     */
    public function setEmail($email)
    {
        $this->setData('email', $email);
    }

    /**
     * Get the representative's url.
     *
     * @return string
     */
    public function getUrl()
    {
        return $this->getData('url');
    }

    /**
     * Set the representative url.
     *
     * @param string $url
     */
    public function setUrl($url)
    {
        $this->setData('url', $url);
    }

    /**
     * Get the representative's is_supplier setting.
     *
     * @return int
     */
    public function getIsSupplier()
    {
        return $this->getData('isSupplier');
    }

    /**
     * Set the representative's is_supplier setting.
     *
     * @param int $isSupplier
     */
    public function setIsSupplier($isSupplier)
    {
        $this->setData('isSupplier', $isSupplier);
    }
}
