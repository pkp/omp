<?php

/**
 * @file classes/publicationFormat/PublicationFormat.php
 *
 * Copyright (c) 2014-2024 Simon Fraser University
 * Copyright (c) 2003-2024 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class PublicationFormat
 *
 * @ingroup publicationFormat
 *
 * @see PublicationFormatDAO
 *
 * @brief A publication format for a monograph.
 */

namespace APP\publicationFormat;

use APP\codelist\ONIXCodelistItemDAO;
use APP\core\Application;
use APP\facades\Repo;
use PKP\db\DAORegistry;
use PKP\db\DAOResultFactory;
use PKP\submission\Representation;
use PKP\submissionFile\SubmissionFile;

class PublicationFormat extends Representation
{
    /**
     * Return the "best" publication format ID -- If a public ID is set,
     * use it; otherwise use the internal ID.
     *
     * @return string
     */
    public function getBestId()
    {
        return strlen($urlPath = (string) $this->getData('urlPath')) ? $urlPath : $this->getId();
    }

    /**
     * get physical format flag
     *
     * @return bool
     */
    public function getPhysicalFormat()
    {
        return $this->getData('physicalFormat');
    }

    /**
     * set physical format flag
     *
     * @param bool $physicalFormat
     */
    public function setPhysicalFormat($physicalFormat): void
    {
        $this->setData('physicalFormat', $physicalFormat);
    }

    /**
     * Get the ONIX code for this publication format
     *
     * @return string
     */
    public function getEntryKey()
    {
        return $this->getData('entryKey');
    }

    /**
     * Sets the ONIX code for the publication format
     */
    public function setEntryKey($entryKey)
    {
        $this->setData('entryKey', $entryKey);
    }

    /**
     * Get the human-readable name for this ONIX code
     *
     * @return string
     */
    public function getNameForONIXCode()
    {
        $onixCodelistItemDao = DAORegistry::getDAO('ONIXCodelistItemDAO'); /** @var ONIXCodelistItemDAO $onixCodelistItemDao */
        $codes = $onixCodelistItemDao->getCodes('150'); // List 150 is for product forms
        return $codes[$this->getEntryKey()];
    }

    /**
     * Get the country of manufacture code that this format was manufactured in.
     *
     * @return string
     */
    public function getCountryManufactureCode()
    {
        return $this->getData('countryManufactureCode');
    }

    /**
     * Set the country of manufacture code for a publication format.
     *
     * @param string $countryManufactureCode
     */
    public function setCountryManufactureCode($countryManufactureCode): void
    {
        $this->setData('countryManufactureCode', $countryManufactureCode);
    }

    /**
     * Get the product availability code (ONIX value) for this format (List65).
     *
     * @return string
     */
    public function getProductAvailabilityCode()
    {
        return $this->getData('productAvailabilityCode');
    }

    /**
     * Set the product availability code (ONIX value) for a publication format.
     *
     * @param string $productAvailabilityCode
     */
    public function setProductAvailabilityCode($productAvailabilityCode): void
    {
        $this->setData('productAvailabilityCode', $productAvailabilityCode);
    }

    /**
     * Get the height of the monograph format.
     *
     * @return string
     */
    public function getHeight()
    {
        return $this->getData('height');
    }

    /**
     * Set the height of a publication format.
     *
     * @param string $height
     */
    public function setHeight($height): void
    {
        $this->setData('height', $height);
    }

    /**
     * Get the height unit (ONIX value) of the monograph format (List50).
     *
     * @return string
     */
    public function getHeightUnitCode()
    {
        return $this->getData('heightUnitCode');
    }

    /**
     * Set the height unit (ONIX value) for a publication format.
     *
     * @param string $heightUnitCode
     */
    public function setHeightUnitCode($heightUnitCode): void
    {
        $this->setData('heightUnitCode', $heightUnitCode);
    }

    /**
     * Get the width of the monograph format.
     *
     * @return string
     */
    public function getWidth()
    {
        return $this->getData('width');
    }

    /**
     * Set the width of a publication format.
     *
     * @param string $width
     */
    public function setWidth($width): void
    {
        $this->setData('width', $width);
    }

    /**
     * Get the width unit code (ONIX value) of the monograph format (List50).
     *
     * @return string
     */
    public function getWidthUnitCode()
    {
        return $this->getData('widthUnitCode');
    }

    /**
     * Set the width unit code (ONIX value) for a publication format.
     *
     * @param string $widthUnitCode
     */
    public function setWidthUnitCode($widthUnitCode): void
    {
        $this->setData('widthUnitCode', $widthUnitCode);
    }

    /**
     * Get the thickness of the monograph format.
     *
     * @return string
     */
    public function getThickness()
    {
        return $this->getData('thickness');
    }

    /**
     * Set the thickness of a publication format.
     *
     * @param string $thickness
     */
    public function setThickness($thickness): void
    {
        $this->setData('thickness', $thickness);
    }

    /**
     * Get the thickness unit code (ONIX value) of the monograph format (List50).
     *
     * @return string
     */
    public function getThicknessUnitCode()
    {
        return $this->getData('thicknessUnitCode');
    }

    /**
     * Set the thickness unit code (ONIX value) for a publication format.
     *
     * @param string $thicknessUnitCode
     */
    public function setThicknessUnitCode($thicknessUnitCode): void
    {
        $this->setData('thicknessUnitCode', $thicknessUnitCode);
    }

    /**
     * Get the weight of the monograph format.
     *
     * @return string
     */
    public function getWeight()
    {
        return $this->getData('weight');
    }

    /**
     * Set the weight for a publication format.
     *
     * @param string $weight
     */
    public function setWeight($weight): void
    {
        $this->setData('weight', $weight);
    }

    /**
     * Get the weight unit code (ONIX value) of the monograph format (List50).
     *
     * @return string
     */
    public function getWeightUnitCode()
    {
        return $this->getData('weightUnitCode');
    }

    /**
     * Set the weight unit code (ONIX value) for a publication format.
     *
     * @param string $weightUnitCode
     */
    public function setWeightUnitCode($weightUnitCode): void
    {
        $this->setData('weightUnitCode', $weightUnitCode);
    }

    /**
     * Get the file size of the monograph format.
     *
     * @return string
     */
    public function getFileSize()
    {
        return $this->getData('fileSize');
    }

    /**
     * Get the file size of the monograph format based on calculated sizes
     * for approved proof files.
     *
     * @return string
     */
    public function getCalculatedFileSize()
    {
        $fileSize = 0;
        $publication = Repo::publication()->get((int) $this->getData('publicationId'));
        $stageMonographFiles = Repo::submissionFile()
            ->getCollector()
            ->filterBySubmissionIds([$publication->getData('submissionId')])
            ->filterByFileStages([SubmissionFile::SUBMISSION_FILE_PROOF])
            ->filterByAssoc(
                Application::ASSOC_TYPE_PUBLICATION_FORMAT,
                [$this->getId()]
            )
            ->getMany();

        foreach ($stageMonographFiles as $monographFile) {
            if ($monographFile->getViewable()) {
                $fileSize += (int) app()->get('file')->fs->fileSize($monographFile->getData('path'));
            }
        }

        return sprintf('%d.3', $fileSize / (1024 * 1024)); // bytes to Mb
    }

    /**
     * Set the file size of the publication format.
     *
     * @param string $fileSize
     */
    public function setFileSize($fileSize): void
    {
        $this->setData('fileSize', $fileSize);
    }

    /**
     * Get the SalesRights objects for this format.
     *
     * @return DAOResultFactory<SalesRights> SalesRights
     */
    public function getSalesRights()
    {
        $salesRightsDao = DAORegistry::getDAO('SalesRightsDAO'); /** @var SalesRightsDAO $salesRightsDao */
        return $salesRightsDao->getByPublicationFormatId($this->getId());
    }

    /**
     * Get the IdentificationCode objects for this format.
     *
     * @return DAOResultFactory<IdentificationCode> IdentificationCode
     */
    public function getIdentificationCodes()
    {
        $identificationCodeDao = DAORegistry::getDAO('IdentificationCodeDAO'); /** @var IdentificationCodeDAO $identificationCodeDao */
        return $identificationCodeDao->getByPublicationFormatId($this->getId());
    }

    /**
     * Get the PublicationDate objects for this format.
     *
     * @return DAOResultFactory<PublicationDate> PublicationDate
     */
    public function getPublicationDates()
    {
        $publicationDateDao = DAORegistry::getDAO('PublicationDateDAO'); /** @var PublicationDateDAO $publicationDateDao */
        return $publicationDateDao->getByPublicationFormatId($this->getId());
    }

    /**
     * Get the Market objects for this format.
     *
     * @return DAOResultFactory<Market> Market
     */
    public function getMarkets()
    {
        $marketDao = DAORegistry::getDAO('MarketDAO'); /** @var MarketDAO $marketDao */
        return $marketDao->getByPublicationFormatId($this->getId());
    }

    /**
     * Get the product form detail code (ONIX value) for the format used for this format (List151).
     *
     * @return string
     */
    public function getProductFormDetailCode()
    {
        return $this->getData('productFormDetailCode');
    }

    /**
     * Set the product form detail code (ONIX value) for a publication format.
     *
     * @param string $productFormDetailCode
     */
    public function setProductFormDetailCode($productFormDetailCode): void
    {
        $this->setData('productFormDetailCode', $productFormDetailCode);
    }

    /**
     * Get the product composition code (ONIX value) used for this format (List2).
     *
     * @return string
     */
    public function getProductCompositionCode()
    {
        return $this->getData('productCompositionCode');
    }

    /**
     * Set the product composition code (ONIX value) for a publication format.
     *
     * @param string $productCompositionCode
     */
    public function setProductCompositionCode($productCompositionCode): void
    {
        $this->setData('productCompositionCode', $productCompositionCode);
    }

    /**
     * Get the page count for the front matter section of a publication format.
     *
     * @return string
     */
    public function getFrontMatter()
    {
        return $this->getData('frontMatter');
    }

    /**
     * Set the front matter page count for a publication format.
     *
     * @param string $frontMatter
     */
    public function setFrontMatter($frontMatter): void
    {
        $this->setData('frontMatter', $frontMatter);
    }

    /**
     * Get the page count for the back matter section of a publication format.
     *
     * @return string
     */
    public function getBackMatter()
    {
        return $this->getData('backMatter');
    }

    /**
     * Set the back matter page count for a publication format.
     *
     * @param string $backMatter
     */
    public function setBackMatter($backMatter): void
    {
        $this->setData('backMatter', $backMatter);
    }

    /**
     * Get the imprint brand name for a publication format.
     *
     * @return string
     */
    public function getImprint()
    {
        return $this->getData('imprint');
    }

    /**
     * Set the imprint brand name for a publication format.
     *
     * @param string $imprint
     */
    public function setImprint($imprint): void
    {
        $this->setData('imprint', $imprint);
    }

    /**
     * Get the technical protection code for a digital publication format (List144).
     *
     * @return string
     */
    public function getTechnicalProtectionCode()
    {
        return $this->getData('technicalProtectionCode');
    }

    /**
     * Set the technical protection code for a publication format.
     *
     * @param string $technicalProtectionCode
     */
    public function setTechnicalProtectionCode($technicalProtectionCode): void
    {
        $this->setData('technicalProtectionCode', $technicalProtectionCode);
    }

    /**
     * Get the return code for a physical publication format (List66).
     *
     * @return string
     */
    public function getReturnableIndicatorCode()
    {
        return $this->getData('returnableIndicatorCode');
    }

    /**
     * Set the return code for a publication format.
     *
     * @param string $returnableIndicatorCode
     */
    public function setReturnableIndicatorCode($returnableIndicatorCode): void
    {
        $this->setData('returnableIndicatorCode', $returnableIndicatorCode);
    }

    /**
     * Get whether this format is available in the catalog.
     *
     * @return int
     */
    public function getIsAvailable()
    {
        return $this->getData('isAvailable');
    }

    /**
     * Set whether this format is available in the catalog.
     *
     * @param int $isAvailable
     */
    public function setIsAvailable($isAvailable): void
    {
        $this->setData('isAvailable', $isAvailable);
    }

    /**
     * Check to see if this publication format has everything it needs for valid ONIX export
     * Ideally, do this with a DOMDocument schema validation. We do it this way for now because
     * of a potential issue with libxml2:  http://stackoverflow.com/questions/6284827
     *
     * @return string[]
     */
    public function hasNeededONIXFields()
    {
        // ONIX requires one identification code and a market region with a defined price.
        $assignedIdentificationCodes = $this->getIdentificationCodes();
        $assignedMarkets = $this->getMarkets();

        $errors = [];
        if ($assignedMarkets->wasEmpty()) {
            $errors[] = 'monograph.publicationFormat.noMarketsAssigned';
        }

        if ($assignedIdentificationCodes->wasEmpty()) {
            $errors[] = 'monograph.publicationFormat.noCodesAssigned';
        }

        return array_merge($errors, $this->_checkRequiredFieldsAssigned());
    }

    /**
     * Internal function to provide some validation for the ONIX export by
     * checking the required ONIX fields associated with this format.
     *
     * @return array
     */
    public function _checkRequiredFieldsAssigned()
    {
        $requiredFields = [
            'productCompositionCode' => 'grid.catalogEntry.codeRequired',
            'productAvailabilityCode' => 'grid.catalogEntry.productAvailabilityRequired'
        ];

        $errors = [];

        foreach ($requiredFields as $field => $errorCode) {
            if ($this->getData($field) == '') {
                $errors[] = $errorCode;
            }
        }

        if (!$this->getPhysicalFormat()) {
            if (!$this->getFileSize() && !$this->getCalculatedFileSize()) {
                $errors['fileSize'] = 'grid.catalogEntry.fileSizeRequired';
            }
        }

        return $errors;
    }

    /**
     * Get the press id from the monograph assigned to this publication format.
     *
     * @return int
     */
    public function getPressId()
    {
        return $this->getContextId();
    }

    /**
     * Return the format's physical dimensions
     *
     * @return string
     */
    public function getDimensions()
    {
        if (!$this->getPhysicalFormat()) {
            return '';
        }

        $width = $this->getWidth();
        $height = $this->getHeight();
        $thickness = $this->getThickness();

        $dimensions = [];
        if (!empty($width)) {
            $dimensions[] = $width . $this->getWidthUnitCode();
        }
        if (!empty($height)) {
            $dimensions[] = $height . $this->getHeightUnitCode();
        }
        if (!empty($thickness)) {
            $dimensions[] = $thickness . $this->getThicknessUnitCode();
        }

        return join(__('monograph.publicationFormat.productDimensionsSeparator'), $dimensions);
    }

    /**
     * Set the stored public ID of the submission.
     *
     * @param string $pubIdType One of the NLM pub-id-type values or
     * 'other::something' if not part of the official NLM list
     * (see <http://dtd.nlm.nih.gov/publishing/tag-library/n-4zh0.html>).
     * @param string $pubId
     */
    public function setStoredPubId($pubIdType, $pubId)
    {
        if ($pubIdType == 'doi') {
            if ($doiObject = $this->getData('doiObject')) {
                Repo::doi()->edit($doiObject, ['doi' => $pubId]);
            } else {
                $newDoiObject = Repo::doi()->newDataObject(
                    [
                        'doi' => $pubId,
                        'contextId' => $this->getContextId()
                    ]
                );
                $doiId = Repo::doi()->add($newDoiObject);
                $this->setData('doiId', $doiId);
            }
        } else {
            parent::setStoredPubId($pubIdType, $pubId);
        }
    }
}
