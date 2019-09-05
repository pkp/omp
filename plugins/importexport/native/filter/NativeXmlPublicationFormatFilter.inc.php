<?php

/**
 * @file plugins/importexport/native/filter/NativeXmlPublicationFormatFilter.inc.php
 *
 * Copyright (c) 2014-2019 Simon Fraser University
 * Copyright (c) 2000-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class NativeXmlPublicationFormatFilter
 * @ingroup plugins_importexport_native
 *
 * @brief Class that converts a Native XML document to a set of publication formats.
 */

import('lib.pkp.plugins.importexport.native.filter.NativeXmlRepresentationFilter');

class NativeXmlPublicationFormatFilter extends NativeXmlRepresentationFilter {
	/**
	 * Constructor
	 * @param $filterGroup FilterGroup
	 */
	function __construct($filterGroup) {
		parent::__construct($filterGroup);
	}

	//
	// Implement template methods from NativeImportFilter
	//
	/**
	 * Return the plural element name
	 * @return string
	 */
	function getPluralElementName() {
		return 'publication_formats'; // defined if needed in the future.
	}

	/**
	 * Get the singular element name
	 * @return string
	 */
	function getSingularElementName() {
		return 'publication_format';
	}

	//
	// Implement template methods from PersistableFilter
	//
	/**
	 * @copydoc PersistableFilter::getClassName()
	 */
	function getClassName() {
		return 'plugins.importexport.native.filter.NativeXmlPublicationFormatFilter';
	}


	/**
	 * Handle a submission element
	 * @param $node DOMElement
	 * @return array Array of PublicationFormat objects
	 */
	function handleElement($node) {
		$deployment = $this->getDeployment();
		$context = $deployment->getContext();
		$submission = $deployment->getSubmission();
		assert(is_a($submission, 'Submission'));

		$representation = parent::handleElement($node);

		if ($node->getAttribute('approved') == 'true') $representation->setIsApproved(true);
		if ($node->getAttribute('available') == 'true') $representation->setIsAvailable(true);
		if ($node->getAttribute('physical_format') == 'true') $representation->setPhysicalFormat(true);

		$representationDao = Application::getRepresentationDAO();
		$representationDao->insertObject($representation);

		// Handle metadata in subelements.  Do this after the insertObject() call because it
		// creates other DataObjects which depend on a representation id.
		for ($n = $node->firstChild; $n !== null; $n=$n->nextSibling) if (is_a($n, 'DOMElement')) switch($n->tagName) {
			case 'Product': $this->_processProductNode($n, $this->getDeployment(), $representation); break;
			case 'submission_file_ref': $this->_processFileRef($n, $deployment, $representation); break;
			default:
		}

		// Update the object.
		$representationDao->updateObject($representation);

		return $representation;
	}

	/**
	 * Process the self_file_ref node found inside the publication_format node.
	 * @param $node DOMElement
	 * @param $deployment Onix30ExportDeployment
	 * @param $representation PublicationFormat
	 */
	function _processFileRef($node, $deployment, &$representation) {
		$fileId = $node->getAttribute('id');
		$revisionId = $node->getAttribute('revision');
		$DBId = $deployment->getFileDBId($fileId, $revisionId);
		if ($DBId) {
			// Update the submission file.
			$submissionFileDao = DAORegistry::getDAO('SubmissionFileDAO');
			$submissionFile = $submissionFileDao->getRevision($DBId, $revisionId);
			$submissionFile->setAssocType(ASSOC_TYPE_REPRESENTATION);
			$submissionFile->setAssocId($representation->getId());
			$submissionFileDao->updateObject($submissionFile);
		}
	}

	/**
	 * Process the Product node found inside the publication_format node.  There may be many of these.
	 * @param $node DOMElement
	 * @param $representation PublicationFormat
	 */
	function _processProductNode($node, $deployment, &$representation) {

		$request = Application::get()->getRequest();
		$onixDeployment = new Onix30ExportDeployment($request->getContext(), $request->getUser());

		$representation->setProductCompositionCode($this->_extractTextFromNode($node, $onixDeployment, 'ProductComposition'));
		$representation->setEntryKey($this->_extractTextFromNode($node, $onixDeployment, 'ProductForm'));
		$representation->setProductFormDetailCode($this->_extractTextFromNode($node, $onixDeployment, 'ProductFormDetail'));
		$representation->setImprint($this->_extractTextFromNode($node, $onixDeployment, 'ImprintName'));
		$representation->setTechnicalProtectionCode($this->_extractTextFromNode($node, $onixDeployment, 'EpubTechnicalProtection'));
		$representation->setCountryManufactureCode($this->_extractTextFromNode($node, $onixDeployment, 'CountryOfManufacture'));
		$this->_extractMeasureContent($node, $onixDeployment, $representation);
		$this->_extractExtentContent($node, $onixDeployment, $representation);

		$submission = Services::get('submission')->get($representation->getSubmissionId());
		if ($submission) {
			$submission->setAudience($this->_extractTextFromNode($node, $onixDeployment, 'AudienceCodeType'));
			$submission->setAudienceRangeQualifier($this->_extractTextFromNode($node, $onixDeployment, 'AudienceRangeQualifier'));
			$this->_extractAudienceRangeContent($node, $onixDeployment, $representation);
			DAORegistry::getDAO('SubmissionDAO')->updateObject($submission);
		}

		// Things below here require a publication format id since they are dependent on the PublicationFormat.

		// Extract ProductIdentifier elements.
		$nodeList = $node->getElementsByTagNameNS($onixDeployment->getNamespace(), 'ProductIdentifier');

		if ($nodeList->length > 0) {
			$identificationCodeDao = DAORegistry::getDAO('IdentificationCodeDAO');
			for ($i = 0 ; $i < $nodeList->length ; $i++) {
				$n = $nodeList->item($i);
				$identificationCode = $identificationCodeDao->newDataObject();
				$identificationCode->setPublicationFormatId($representation->getId());
				for ($o = $n->firstChild; $o !== null; $o=$o->nextSibling) if (is_a($o, 'DOMElement')) switch($o->tagName) {
					case 'onix:ProductIDType': $identificationCode->setCode($o->textContent); break;
					case 'onix:IDValue': $identificationCode->setValue($o->textContent); break;
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
			$publicationDateDao = DAORegistry::getDAO('PublicationDateDAO');
			for ($i = 0 ; $i < $nodeList->length ; $i++) {
				$n = $nodeList->item($i);
				$date = $publicationDateDao->newDataObject();
				$date->setPublicationFormatId($representation->getId());
				for ($o = $n->firstChild; $o !== null; $o=$o->nextSibling) if (is_a($o, 'DOMElement')) switch($o->tagName) {
					case 'onix:PublishingDateRole': $date->setRole($o->textContent); break;
					case 'onix:Date':
						$date->setDate($o->textContent);
						$date->setDateFormat($o->getAttribute('dateformat'));
						break;
				}

				$publicationDateDao->insertObject($date);
				unset($date);
			}
		}

		// Extract SalesRights elements.
		$nodeList = $node->getElementsByTagNameNS($onixDeployment->getNamespace(), 'SalesRights');
		if ($nodeList->length > 0) {
			$salesRightsDao = DAORegistry::getDAO('SalesRightsDAO');
			for ($i = 0 ; $i < $nodeList->length ; $i ++) {
				$salesRights = $salesRightsDao->newDataObject();
				$salesRights->setPublicationFormatId($representation->getId());
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
					for ($o = $territoryNode->firstChild; $o !== null; $o=$o->nextSibling) if (is_a($o, 'DOMElement')) switch($o->tagName) {
						case 'onix:RegionsIncluded': $salesRights->setRegionsIncluded(preg_split('/\s+/', $o->textContent)); break;
						case 'onix:CountriesIncluded': $salesRights->setCountriesIncluded(preg_split('/\s+/', $o->textContent)); break;
						case 'onix:RegionsExcluded': $salesRights->setRegionsExcluded(preg_split('/\s+/', $o->textContent)); break;
						case 'onix:CountriesExcluded': $salesRights->setCountriesExcluded(preg_split('/\s+/', $o->textContent)); break;
					}
				}
				$salesRightsDao->insertObject($salesRights);
				unset($salesRights);
			}
		}

		// Extract ProductSupply elements.  Contains Markets, Pricing, Suppliers, and Sales Agents.
		$nodeList = $node->getElementsByTagNameNS($onixDeployment->getNamespace(), 'ProductSupply');
		if ($nodeList->length > 0) {
			$marketDao = DAORegistry::getDAO('MarketDAO');
			$representativeDao = DAORegistry::getDAO('RepresentativeDAO');

			for ($i = 0 ; $i < $nodeList->length ; $i ++) {
				$productSupplyNode = $nodeList->item($i);
				$market = $marketDao->newDataObject();
				$market->setPublicationFormatId($representation->getId());
				// parse out the Territory for this market.
				$territoryNodeList = $productSupplyNode->getElementsByTagNameNS($onixDeployment->getNamespace(), 'Territory');
				assert($territoryNodeList->length == 1);
				$territoryNode = $territoryNodeList->item(0);
				for ($o = $territoryNode->firstChild; $o !== null; $o=$o->nextSibling) if (is_a($o, 'DOMElement')) switch($o->tagName) {
					case 'onix:RegionsIncluded': $market->setRegionsIncluded(preg_split('/\s+/', $o->textContent)); break;
					case 'onix:CountriesIncluded': $market->setCountriesIncluded(preg_split('/\s+/', $o->textContent)); break;
					case 'onix:RegionsExcluded': $market->setRegionsExcluded(preg_split('/\s+/', $o->textContent)); break;
					case 'onix:CountriesExcluded': $market->setCountriesExcluded(preg_split('/\s+/', $o->textContent)); break;
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
				}

				// Extract Pricing information for this format.
				$representation->setReturnableIndicatorCode($this->_extractTextFromNode($supplierNode, $onixDeployment, 'ReturnsCode'));
				$representation->getProductAvailabilityCode($this->_extractTextFromNode($supplierNode, $onixDeployment, 'ProductAvailability'));

				$marketDao->insertObject($market);
			}
		}
	}

	/**
	 * Extracts the text content from a node.
	 * @param $node DOMElement
	 * @param $onixDeployment Onix30ExportDeployment
	 * @param $nodeName String the name of the node.
	 * @return String
	 */
	function _extractTextFromNode($node, $onixDeployment, $nodeName) {
		$nodeList = $node->getElementsByTagNameNS($onixDeployment->getNamespace(), $nodeName);
		if ($nodeList->length == 1) {
			$n = $nodeList->item(0);
			return $n->textContent;
		} else
			return null;
	}

	/**
	 * Extracts the elements of the Extent nodes.
	 * @param $node DOMElement
	 * @param $onixDeployment Onix30ExportDeployment
	 * @param PublicationFormat $representation
	 */
	function _extractExtentContent($node, $onixDeployment, &$representation) {
		$nodeList = $node->getElementsByTagNameNS($onixDeployment->getNamespace(), 'Extent');

		for ($i = 0 ; $i < $nodeList->length ; $i++) {
			$n = $nodeList->item($i);
			$extentType = $this->_extractTextFromNode($node, $onixDeployment, 'ExtentType');
			$extentValue = $this->_extractTextFromNode($node, $onixDeployment, 'ExtentValue');

			switch ($extentType) {
				case '08': // Digital
					$representation->setFileSize($extentValue);
					break;
				case '00': // Physical, front matter.
					$representation->setFrontMatter($extentValue);
					break;
				case '04': // Physical, back matter.
					$representation->setBackMatter($extentValue);
					break;
			}
		}
	}

	/**
	 * Extracts the elements of the Measure nodes.
	 * @param $node DOMElement
	 * @param $onixDeployment Onix30ExportDeployment
	 * @param PublicationFormat $representation
	 */
	function _extractMeasureContent($node, $onixDeployment, &$representation) {
		$nodeList = $node->getElementsByTagNameNS($onixDeployment->getNamespace(), 'Measure');
		for ($i = 0 ; $i < $nodeList->length ; $i++) {
			$n = $nodeList->item($i);
			$measureType = $this->_extractTextFromNode($node, $onixDeployment, 'MeasureType');
			$measurement = $this->_extractTextFromNode($node, $onixDeployment, 'Measurement');
			$measureUnitCode = $this->_extractTextFromNode($node, $onixDeployment, 'MeasureUnitCode');

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
	 * a submission defines a specific range, or a to/from pair.
	 * @param $node DOMElement
	 * @param $onixDeployment Onix30ExportDeployment
	 * @param PublicationFormat $representation
	 */
	function _extractAudienceRangeContent($node, $onixDeployment, &$representation) {
		$nodeList = $node->getElementsByTagNameNS($onixDeployment->getNamespace(), 'AudienceRange');
		for ($i = 0 ; $i < $nodeList->length ; $i++) {
			$n = $nodeList->item($i);
			$audienceRangePrecision = 0;
			for ($o = $n->firstChild; $o !== null; $o=$o->nextSibling) if (is_a($o, 'DOMElement')) switch($o->tagName) {
				case 'AudienceRangePrecision': $audienceRangePrevision = $o->textContent; break;
				case 'AudienceRangeValue':
					switch ($audienceRangePrecision) {
						case '01':
							$representation->setAudienceRangeExact($o->textContent);
							break;
						case '03':
							$representation->setAudienceRangeTo($o->textContent);
							break;
						case '04':
							$representation->setAudienceRangeFrom($o->textContent);
							break;
					}
					break;
			}
		}
	}
}


