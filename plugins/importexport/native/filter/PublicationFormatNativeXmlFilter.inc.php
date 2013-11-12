<?php

/**
 * @file plugins/importexport/native/filter/PublicationFormatNativeXmlFilter.inc.php
 *
 * Copyright (c) 2000-2013 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class PublicationFormatNativeXmlFilter
 * @ingroup plugins_importexport_native
 *
 * @brief Class that converts a PublicationFormat to a Native XML document.
 */

import('lib.pkp.plugins.importexport.native.filter.RepresentationNativeXmlFilter');

class PublicationFormatNativeXmlFilter extends RepresentationNativeXmlFilter {
	/**
	 * Constructor
	 * @param $filterGroup FilterGroup
	 */
	function PublicationFormatNativeXmlFilter($filterGroup) {
		parent::RepresentationNativeXmlFilter($filterGroup);
	}


	//
	// Implement template methods from PersistableFilter
	//
	/**
	 * @copydoc PersistableFilter::getClassName()
	 */
	function getClassName() {
		return 'plugins.importexport.native.filter.PublicationFormatNativeXmlFilter';
	}

	//
	// Extend functions in RepresentationNativeXmlFilter
	//
	/**
	 * Create and return a representation node. Extend the parent class
	 * with publication format specific data.
	 * @param $doc DOMDocument
	 * @param $representation Representation
	 * @return DOMElement
	 */
	function createRepresentationNode($doc, $representation) {
		$representationNode = parent::createRepresentationNode($doc, $representation);
		$onixRootNode = $this->createONIXMessageNode($doc, $representation);
		$representationNode->appendChild($onixRootNode);
		$representationNode->setAttribute('approved', $representation->getIsApproved()?'true':'false');
		$representationNode->setAttribute('physical_format', $representation->getPhysicalFormat()?'true':'false');

		return $representationNode;
	}

	/**
	 * Create and return a node representing the ONIX metadata for this publication format.
	 * @param $doc DOMDocument
	 * @param $representation Representation
	 * @return DOMElement
	 */
	function createONIXMessageNode($doc, $representation) {
		$deployment = $this->getDeployment();
		$context = $deployment->getContext();

		$onixRootNode = $doc->createElementNS($deployment->getNamespace(), 'ONIXMessage');
		$onixRootNode->setAttribute('release', '3.0');
		$onixRootNode->setAttribute('namespace', 'http://ns.editeur.org/onix/3.0/reference');

		$headNode = $doc->createElementNS($deployment->getNamespace(), 'Header');
		$senderNode = $doc->createElementNS($deployment->getNamespace(), 'Sender');

		// Assemble SenderIdentifier element.
		$senderIdentifierNode = $doc->createElementNS($deployment->getNamespace(), 'SenderIdentifier');
		$senderIdTypeNode = $doc->createElementNS($deployment->getNamespace(), 'SenderIDType');
		$senderIdTypeNode->appendChild($doc->createTextNode($context->getSetting('codeType')));
		$senderIdValueNode = $doc->createElementNS($deployment->getNamespace(), 'SenderIDValue');
		$senderIdValueNode->appendChild($doc->createTextNode($context->getSetting('codeValue')));

		$senderIdentifierNode->appendChild($senderIdTypeNode);
		$senderIdentifierNode->appendChild($senderIdValueNode);
		$senderNode->appendChild($senderIdentifierNode);

		// Assemble SenderName element.
		$senderNameNode = $doc->createElementNS($deployment->getNamespace(), 'SenderName');
		$senderNameNode->appendChild($doc->createTextNode($context->getLocalizedName()));
		$contactNameNode = $doc->createElementNS($deployment->getNamespace(), 'ContactName');
		$contactNameNode->appendChild($doc->createTextNode($context->getContactName()));
		$contactEmailNode = $doc->createElementNS($deployment->getNamespace(), 'EmailAddress');
		$contactEmailNode->appendChild($doc->createTextNode($context->getContactEmail()));

		$senderNode->appendChild($senderNameNode);
		$senderNode->appendChild($contactNameNode);
		$senderNode->appendChild($contactEmailNode);

		$headNode->appendChild($senderNode);

		// add SentDateTime element.
		$sentDateTimeNode = $doc->createElementNS($deployment->getNamespace(), 'SentDateTime');
		$sentDateTimeNode->appendChild($doc->createTextNode(date('Ymd')));
		$headNode->appendChild($sentDateTimeNode);

		$onixRootNode->appendChild($headNode);
		return $onixRootNode;
	}

	/**
	 * Get the available submission files for a representation
	 * @param $representation Representation
	 * @return array
	 */
	function getFiles($representation) {
		$deployment = $this->getDeployment();
		$submission = $deployment->getSubmission();
		$submissionFileDao = DAORegistry::getDAO('SubmissionFileDAO');
		return array_filter(
			$submissionFileDao->getLatestRevisions($submission->getId()),
			create_function('$a', 'return $a->getAssocType() == ASSOC_TYPE_PUBLICATION_FORMAT && $a->getAssocId() == ' . ((int) $representation->getId()) . ';')
		);
	}
}

?>
