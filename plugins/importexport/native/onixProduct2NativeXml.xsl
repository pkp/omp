<?xml version="1.0" encoding="UTF-8"?>
<!--
  * plugins/importexport/native/onixProduct2NativeXml.xsl
  *
  * Copyright (c) 2014-2019 Simon Fraser University
  * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
  *
  * Converter to take ONIX products into the OMP XML native submission format.
  -->
<xsl:stylesheet
	version="1.0"
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform" 
	xmlns:xs="http://www.w3.org/2001/XMLSchema"
	xmlns="http://pkp.sfu.ca"
	xmlns:onix="http://ns.editeur.org/onix/3.0/reference"
>
	<xsl:output omit-xml-declaration="yes" method="xml"/>
	<xsl:strip-space elements="*"/>

	<xsl:template match="node()|@*">
		<xsl:copy>
			<xsl:apply-templates select="node()|@*"/>
		</xsl:copy>
	</xsl:template>

	<!-- Roll the onix:Product node into the PKP namespace, leaving the sub nodes intact. -->
	<xsl:template match="onix:Product">
		<Product>
			<xsl:apply-templates />
		</Product>
	</xsl:template>

	<!-- 
	  - Two other nodes, onix:TitleDetail and onix:Publisher,
	  - are currently populated with Submission-level detail and 
	  - are not unique to a Representation.  However, removing them
	  - from the Product node invalidates the XSD for ONIX and may 
	  - make import difficult.  Also, since ONIX does support
	  - different Title and Publisher information on a per-Product basis,
	  - we may want to leave this for the time being in case OMP
	  - supports it in the future.
	  -
	  - For now, if these nodes are present on import, skip them.
	 -->

	<!-- 
	  - these nodes can be removed as they are not specific
	  - to a Representation and do not break XSD validity 
	-->
	<xsl:template match="onix:Contributor"/>
	<xsl:template match="onix:RecordReference"/>
	<xsl:template match="onix:NotificationType"/>
	<xsl:template match="onix:RecordSourceType"/>
	<xsl:template match="onix:Collection"/>
	<xsl:template match="onix:CollateralDetail"/>
	<xsl:template match="onix:Imprint" />
</xsl:stylesheet>
