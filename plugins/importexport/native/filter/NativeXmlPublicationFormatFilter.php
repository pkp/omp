<?php

/**
 * @file plugins/importexport/native/filter/NativeXmlPublicationFormatFilter.php
 *
 * Copyright (c) 2014-2022 Simon Fraser University
 * Copyright (c) 2000-2022 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class NativeXmlPublicationFormatFilter
 *
 * @brief Class that converts a Native XML document to a set of publication formats.
 */

namespace APP\plugins\importexport\native\filter;

use APP\core\Application;
use APP\facades\Repo;
use APP\monograph\RepresentativeDAO;
use APP\plugins\importexport\onix30\Onix30ExportDeployment;
use APP\publicationFormat\IdentificationCodeDAO;
use APP\publicationFormat\MarketDAO;
use APP\publicationFormat\PublicationDateDAO;
use APP\publicationFormat\PublicationFormat;
use APP\publicationFormat\SalesRightsDAO;
use APP\submission\Submission;
use DOMElement;
use PKP\core\PKPApplication;
use PKP\db\DAORegistry;
use PKP\plugins\importexport\PKPImportExportDeployment;

class NativeXmlPublicationFormatFilter extends \PKP\plugins\importexport\native\filter\NativeXmlRepresentationFilter
{
    //
    // Implement template methods from NativeImportFilter
    //
    /**
     * Return the plural element name
     *
     * @return string
     */
    public function getPluralElementName()
    {
        return 'publication_formats'; // defined if needed in the future.
    }

    /**
     * Get the singular element name
     *
     * @return string
     */
    public function getSingularElementName()
    {
        return 'publication_format';
    }

    /**
     * Handle a submission element
     *
     * @param DOMElement $node
     *
     * @return PublicationFormat objects
     */
    public function handleElement($node)
    {
        /** @var Onix30ExportDeployment */
        $deployment = $this->getDeployment();
        $submission = $deployment->getSubmission();
        assert($submission instanceof Submission);
        /** @var PublicationFormat */
        $representation = parent::handleElement($node);

        if ($node->getAttribute('approved') == 'true') {
            $representation->setIsApproved(true);
        }
        if ($node->getAttribute('available') == 'true') {
            $representation->setIsAvailable(true);
        }
        if ($node->getAttribute('physical_format') == 'true') {
            $representation->setPhysicalFormat(true);
        }
        if ($node->getAttribute('entry_key')) {
            $representation->setEntryKey($node->getAttribute('entry_key'));
        }

        $representationDao = Application::getRepresentationDAO();
        $representationDao->insertObject($representation);

        // Handle metadata in sub-elements. Do this after the insertObject() call because it
        // creates other DataObjects which depend on a representation id.
        for ($n = $node->firstChild; $n !== null; $n = $n->nextSibling) {
            if ($n instanceof DOMElement) {
                switch ($n->tagName) {
                    case 'Product':
                        $this->_processProductNode($n, $this->getDeployment(), $representation);
                        break;
                    case 'submission_file_ref':
                        $this->_processFileRef($n, $deployment, $representation);
                        break;
                    default:
                }
            }
        }

        // Update the object.
        $representationDao->updateObject($representation);

        return $representation;
    }

    /**
     * Process the self_file_ref node found inside the publication_format node.
     *
     * @param DOMElement $node
     * @param Onix30ExportDeployment $deployment
     * @param PublicationFormat $representation
     */
    public function _processFileRef($node, $deployment, &$representation)
    {
        $fileId = $node->getAttribute('id');
        $DBId = $deployment->getSubmissionFileDBId($fileId);
        if ($DBId) {
            // Update the submission file.
            $submissionFile = Repo::submissionFile()->get($DBId);

            $params = [
                'assocType' => PKPApplication::ASSOC_TYPE_REPRESENTATION,
                'assocId' => $representation->getId(),
            ];

            Repo::submissionFile()->edit($submissionFile, $params);
        }
    }

    /**
     * Process the Product node found inside the publication_format node. There may be many of these.
     *
     * @param DOMElement $node
     * @param PKPImportExportDeployment $deployment
     * @param PublicationFormat $representation
     */
    public function _processProductNode($node, $deployment, &$representation)
    {
        $request = Application::get()->getRequest();
        $onixDeployment = new Onix30ExportDeployment($request->getContext(), $request->getUser());

        $submission = $deployment->getSubmission();

        $representation->setProductCompositionCode($this->_extractTextFromNode($node, $onixDeployment, 'ProductComposition'));
        $representation->setEntryKey($this->_extractTextFromNode($node, $onixDeployment, 'ProductForm'));
        $representation->setProductFormDetailCode($this->_extractTextFromNode($node, $onixDeployment, 'ProductFormDetail'));
        $representation->setImprint($this->_extractTextFromNode($node, $onixDeployment, 'ImprintName'));
        $representation->setTechnicalProtectionCode($this->_extractTextFromNode($node, $onixDeployment, 'EpubTechnicalProtection'));
        $representation->setCountryManufactureCode($this->_extractTextFromNode($node, $onixDeployment, 'CountryOfManufacture'));
        $this->_extractMeasureContent($node, $onixDeployment, $representation);
        $this->_extractExtentContent($node, $onixDeployment, $representation);

        if ($submission) {
            $submission->setData('audience', $this->_extractTextFromNode($node, $onixDeployment, 'AudienceCodeType'));
            $submission->setData('audienceRangeQualifier', $this->_extractTextFromNode($node, $onixDeployment, 'AudienceRangeQualifier'));
            $this->_extractAudienceRangeContent($node, $onixDeployment, $submission);

            Repo::submission()->dao->update($submission);
        }

        // Things below here require a publication format id since they are dependent on the PublicationFormat.

        // Extract ProductIdentifier elements.
        $nodeList = $node->getElementsByTagNameNS($onixDeployment->getNamespace(), 'ProductIdentifier');

        if ($nodeList->length > 0) {
            $identificationCodeDao = DAORegistry::getDAO('IdentificationCodeDAO'); /** @var IdentificationCodeDAO $identificationCodeDao */
            for ($i = 0; $i < $nodeList->length; $i++) {
                $n = $nodeList->item($i);
                $identificationCode = $identificationCodeDao->newDataObject();
                $identificationCode->setPublicationFormatId($representation->getId());
                for ($o = $n->firstChild; $o !== null; $o = $o->nextSibling) {
                    if ($o instanceof DOMElement) {
                        switch ($o->tagName) {
                            case 'onix:ProductIDType':
                                $identificationCode->setCode($o->textContent);
                                break;
                            case 'onix:IDValue':
                                $identificationCode->setValue($o->textContent);
                                break;
                        }
                    }
                }
                // if this is a DOI, use the DOI-plugin structure instead.
                if ($identificationCode->getCode() == '06') { // DOI code
                    $representation->setStoredPubId('doi', $identificationCode->getValue());
                } else {
                    $identificationCodeDao->insertObject($identificationCode);
                }

                unset($identificationCode);
            }
        }

        // Extract PublishingDate elements.
        $nodeList = $node->getElementsByTagNameNS($onixDeployment->getNamespace(), 'PublishingDate');

        if ($nodeList->length > 0) {
            $publicationDateDao = DAORegistry::getDAO('PublicationDateDAO'); /** @var PublicationDateDAO $publicationDateDao */
            for ($i = 0; $i < $nodeList->length; $i++) {
                $n = $nodeList->item($i);
                $date = $publicationDateDao->newDataObject();
                $date->setPublicationFormatId($representation->getId());
                for ($o = $n->firstChild; $o !== null; $o = $o->nextSibling) {
                    if ($o instanceof DOMElement) {
                        switch ($o->tagName) {
                            case 'onix:PublishingDateRole':
                                $date->setRole($o->textContent);
                                break;
                            case 'onix:Date':
                                $date->setDate($o->textContent);
                                $date->setDateFormat($o->getAttribute('dateformat'));
                                break;
                        }
                    }
                }

                $publicationDateDao->insertObject($date);
                unset($date);
            }
        }

        // Extract SalesRights elements.
        $nodeList = $node->getElementsByTagNameNS($onixDeployment->getNamespace(), 'SalesRights');
        if ($nodeList->length > 0) {
            $salesRightsDao = DAORegistry::getDAO('SalesRightsDAO'); /** @var SalesRightsDAO $salesRightsDao */
            for ($i = 0; $i < $nodeList->length; $i++) {
                $salesRights = $salesRightsDao->newDataObject();
                $salesRights->setPublicationFormatId($representation->getId());
                /** @var DOMElement */
                $salesRightsNode = $nodeList->item($i);
                $salesRightsROW = $this->_extractTextFromNode($salesRightsNode, $onixDeployment, 'ROWSalesRightsType');
                if ($salesRightsROW) {
                    $salesRights->setROWSetting(true);
                    $salesRights->setType($salesRightsROW);
                } else {
                    // Not a 'rest of world' sales rights entry.  Parse the Territory elements as well.
                    $salesRights->setType($this->_extractTextFromNode($salesRightsNode, $onixDeployment, 'SalesRightsType'));
                    $salesRights->setROWSetting(false);
                    $territoryNodeList = $salesRightsNode->getElementsByTagNameNS($onixDeployment->getNamespace(), 'Territory');
                    assert($territoryNodeList->length == 1);
                    $territoryNode = $territoryNodeList->item(0);
                    for ($o = $territoryNode->firstChild; $o !== null; $o = $o->nextSibling) {
                        if ($o instanceof DOMElement) {
                            switch ($o->tagName) {
                                case 'onix:RegionsIncluded':
                                    $salesRights->setRegionsIncluded(preg_split('/\s+/', $o->textContent));
                                    break;
                                case 'onix:CountriesIncluded':
                                    $salesRights->setCountriesIncluded(preg_split('/\s+/', $o->textContent));
                                    break;
                                case 'onix:RegionsExcluded':
                                    $salesRights->setRegionsExcluded(preg_split('/\s+/', $o->textContent));
                                    break;
                                case 'onix:CountriesExcluded':
                                    $salesRights->setCountriesExcluded(preg_split('/\s+/', $o->textContent));
                                    break;
                            }
                        }
                    }
                }
                $salesRightsDao->insertObject($salesRights);
                unset($salesRights);
            }
        }

        // Extract ProductSupply elements.  Contains Markets, Pricing, Suppliers, and Sales Agents.
        $nodeList = $node->getElementsByTagNameNS($onixDeployment->getNamespace(), 'ProductSupply');
        if ($nodeList->length > 0) {
            $marketDao = DAORegistry::getDAO('MarketDAO'); /** @var MarketDAO $marketDao */
            $representativeDao = DAORegistry::getDAO('RepresentativeDAO'); /** @var RepresentativeDAO $representativeDao */

            for ($i = 0; $i < $nodeList->length; $i++) {
                /** @var DOMElement */
                $productSupplyNode = $nodeList->item($i);
                $market = $marketDao->newDataObject();
                $market->setPublicationFormatId($representation->getId());
                // parse out the Territory for this market.
                $territoryNodeList = $productSupplyNode->getElementsByTagNameNS($onixDeployment->getNamespace(), 'Territory');
                assert($territoryNodeList->length == 1);
                $territoryNode = $territoryNodeList->item(0);
                for ($o = $territoryNode->firstChild; $o !== null; $o = $o->nextSibling) {
                    if ($o instanceof DOMElement) {
                        switch ($o->tagName) {
                            case 'onix:RegionsIncluded':
                                $market->setRegionsIncluded(preg_split('/\s+/', $o->textContent));
                                break;
                            case 'onix:CountriesIncluded':
                                $market->setCountriesIncluded(preg_split('/\s+/', $o->textContent));
                                break;
                            case 'onix:RegionsExcluded':
                                $market->setRegionsExcluded(preg_split('/\s+/', $o->textContent));
                                break;
                            case 'onix:CountriesExcluded':
                                $market->setCountriesExcluded(preg_split('/\s+/', $o->textContent));
                                break;
                        }
                    }
                }

                // Market date information.
                $market->setDate($this->_extractTextFromNode($productSupplyNode, $onixDeployment, 'Date'));
                $market->setDateRole($this->_extractTextFromNode($productSupplyNode, $onixDeployment, 'MarketDateRole'));
                $market->setDateFormat($this->_extractTextFromNode($productSupplyNode, $onixDeployment, 'DateFormat'));

                // A product supply may have an Agent.  Look for the PublisherRepresentative element and parse if found.
                $publisherRepNodeList = $productSupplyNode->getElementsByTagNameNS($onixDeployment->getNamespace(), 'PublisherRepresentative');
                if ($publisherRepNodeList->length == 1) {
                    $publisherRepNode = $publisherRepNodeList->item(0);
                    $representative = $representativeDao->newDataObject();
                    $representative->setMonographId($deployment->getSubmission()->getId());
                    $representative->setRole($this->_extractTextFromNode($publisherRepNode, $onixDeployment, 'AgentRole'));
                    $representative->setName($this->_extractTextFromNode($publisherRepNode, $onixDeployment, 'AgentName'));
                    $representative->setUrl($this->_extractTextFromNode($publisherRepNode, $onixDeployment, 'WebsiteLink'));

                    // to prevent duplicate Agent creation, check to see if this agent already exists.  If it does, use it instead of creating a new one.
                    $existingAgents = $representativeDao->getAgentsByMonographId($deployment->getSubmission()->getId());
                    $foundAgent = false;
                    while ($agent = $existingAgents->next()) {
                        if ($agent->getRole() == $representative->getRole() && $agent->getName() == $representative->getName() && $agent->getUrl() == $representative->getUrl()) {
                            $market->setAgentId($agent->getId());
                            $foundAgent = true;
                            break;
                        }
                    }
                    if (!$foundAgent) {
                        $market->setAgentId($representativeDao->insertObject($representative));
                    }
                }

                // Now look for a SupplyDetail element, for the Supplier information.
                $supplierNodeList = $productSupplyNode->getElementsByTagNameNS($onixDeployment->getNamespace(), 'Supplier');
                if ($supplierNodeList->length == 1) {
                    $supplierNode = $supplierNodeList->item(0);
                    $representative = $representativeDao->newDataObject();
                    $representative->setMonographId($deployment->getSubmission()->getId());
                    $representative->setRole($this->_extractTextFromNode($supplierNode, $onixDeployment, 'SupplierRole'));
                    $representative->setName($this->_extractTextFromNode($supplierNode, $onixDeployment, 'SupplierName'));
                    $representative->setPhone($this->_extractTextFromNode($supplierNode, $onixDeployment, 'TelephoneNumber'));
                    $representative->setEmail($this->_extractTextFromNode($supplierNode, $onixDeployment, 'EmailAddress'));
                    $representative->setUrl($this->_extractTextFromNode($supplierNode, $onixDeployment, 'WebsiteLink'));
                    $representative->setIsSupplier(true);

                    // Again, to prevent duplicate Supplier creation, check to see if this rep already exists.  If it does, use it instead of creating a new one.
                    $existingSuppliers = $representativeDao->getSuppliersByMonographId($deployment->getSubmission()->getId());
                    $foundSupplier = false;
                    while ($supplier = $existingSuppliers->next()) {
                        if ($supplier->getRole() == $representative->getRole() && $supplier->getName() == $representative->getName() &&
                            $supplier->getUrl() == $representative->getUrl() &&
                            $supplier->getPhone() == $representative-> getPhone() && $supplier->getEmail() == $representative->getEmail()) {
                            $market->setSupplierId($supplier->getId());
                            $foundSupplier = true;
                            break;
                        }
                    }
                    if (!$foundSupplier) {
                        $market->setSupplierId($representativeDao->insertObject($representative));
                    }

                    $priceNodeList = $productSupplyNode->getElementsByTagNameNS($onixDeployment->getNamespace(), 'Price');
                    if ($priceNodeList->length == 1) {
                        $priceNode = $priceNodeList->item(0);
                        $market->setPriceTypeCode($this->_extractTextFromNode($priceNode, $onixDeployment, 'PriceType'));
                        $market->setDiscount($this->_extractTextFromNode($priceNode, $onixDeployment, 'DiscountPercent'));
                        $market->setPrice($this->_extractTextFromNode($priceNode, $onixDeployment, 'PriceAmount'));
                        $market->setTaxTypeCode($this->_extractTextFromNode($priceNode, $onixDeployment, 'TaxType'));
                        $market->setTaxRateCode($this->_extractTextFromNode($priceNode, $onixDeployment, 'TaxRateCode'));
                        $market->setCurrencyCode($this->_extractTextFromNode($priceNode, $onixDeployment, 'CurrencyCode'));
                    }

                    // Extract Pricing information for this format.
                    $representation->setReturnableIndicatorCode($this->_extractTextFromNode($supplierNode, $onixDeployment, 'ReturnsCode'));
                    $representation->setProductAvailabilityCode($this->_extractTextFromNode($supplierNode, $onixDeployment, 'ProductAvailability'));
                }

                $marketDao->insertObject($market);
            }
        }
    }

    /**
     * Extracts the text content from a node.
     *
     * @param DOMElement $node
     * @param Onix30ExportDeployment $onixDeployment
     * @param string $nodeName the name of the node.
     *
     * @return null|string
     */
    public function _extractTextFromNode($node, $onixDeployment, $nodeName)
    {
        $nodeList = $node->getElementsByTagNameNS($onixDeployment->getNamespace(), $nodeName);
        if ($nodeList->length == 1) {
            $n = $nodeList->item(0);
            return $n->textContent;
        } else {
            return null;
        }
    }

    /**
     * Extracts the elements of the Extent nodes.
     *
     * @param DOMElement $node
     * @param Onix30ExportDeployment $onixDeployment
     * @param PublicationFormat $representation
     */
    public function _extractExtentContent($node, $onixDeployment, &$representation)
    {
        $nodeList = $node->getElementsByTagNameNS($onixDeployment->getNamespace(), 'Extent');

        for ($i = 0; $i < $nodeList->length; $i++) {
            $n = $nodeList->item($i);
            $extentType = $this->_extractTextFromNode($n, $onixDeployment, 'ExtentType');
            $extentValue = $this->_extractTextFromNode($n, $onixDeployment, 'ExtentValue');

            switch ($extentType) {
                case '03': // Physical, front matter.
                    $representation->setFrontMatter($extentValue);
                    break;
                case '04': // Physical, back matter.
                    $representation->setBackMatter($extentValue);
                    break;
                case '22': // Digital, filesize.
                    $representation->setFileSize($extentValue);
                    break;
            }
        }
    }

    /**
     * Extracts the elements of the Measure nodes.
     *
     * @param DOMElement $node
     * @param Onix30ExportDeployment $onixDeployment
     * @param PublicationFormat $representation
     */
    public function _extractMeasureContent($node, $onixDeployment, &$representation)
    {
        $nodeList = $node->getElementsByTagNameNS($onixDeployment->getNamespace(), 'Measure');
        for ($i = 0; $i < $nodeList->length; $i++) {
            $n = $nodeList->item($i);
            $measureType = $this->_extractTextFromNode($n, $onixDeployment, 'MeasureType');
            $measurement = $this->_extractTextFromNode($n, $onixDeployment, 'Measurement');
            $measureUnitCode = $this->_extractTextFromNode($n, $onixDeployment, 'MeasureUnitCode');

            // '01' => 'Height', '02' => 'Width', '03' => 'Thickness', '08' => 'Weight'
            switch ($measureType) {
                case '01':
                    $representation->setHeight($measurement);
                    $representation->setHeightUnitCode($measureUnitCode);
                    break;
                case '02':
                    $representation->setWidth($measurement);
                    $representation->setWidthUnitCode($measureUnitCode);
                    break;
                case '03':
                    $representation->setThickness($measurement);
                    $representation->setThicknessUnitCode($measureUnitCode);
                    break;
                case '08':
                    $representation->setWeight($measurement);
                    $representation->setWeightUnitCode($measureUnitCode);
                    break;
            }
        }
    }

    /**
     * Extracts the AudienceRange elements, which vary depending on whether
     * a submission defines a specific range, or a from/to pair.
     *
     * @param DOMElement $node
     * @param Onix30ExportDeployment $onixDeployment
     * @param Submission $submission
     */
    public function _extractAudienceRangeContent($node, $onixDeployment, &$submission)
    {
        $nodeList = $node->getElementsByTagNameNS($onixDeployment->getNamespace(), 'AudienceRange');
        for ($i = 0; $i < $nodeList->length; $i++) {
            $n = $nodeList->item($i);
            $audienceRangePrecision = 0;
            for ($o = $n->firstChild; $o !== null; $o = $o->nextSibling) {
                if ($o instanceof DOMElement) {
                    switch ($o->tagName) {
                        case 'onix:AudienceRangePrecision':
                            $audienceRangePrecision = $o->textContent;
                            break;
                        case 'onix:AudienceRangeValue':
                            switch ($audienceRangePrecision) {
                                case '01':
                                    $submission->setData('audienceRangeExact', $o->textContent);
                                    break;
                                case '03':
                                    $submission->setData('audienceRangeFrom', $o->textContent);
                                    break;
                                case '04':
                                    $submission->setData('audienceRangeTo', $o->textContent);
                                    break;
                            }
                            break;
                    }
                }
            }
        }
    }
}
