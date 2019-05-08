{**
 * templates/controllers/tab/catalogEntry/form/digitalPublicationFormat.tpl
 *
 * Copyright (c) 2014-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Digital publication format form fields for the catalog entry form.
 *}
{* digital format fields *}
{fbvFormArea id="productDimensions" title="monograph.publicationFormat.digitalInformation" class="border"}
	{fbvFormSection for="digitalInformation"}
		{fbvElement type="text" label="monograph.publicationFormat.productFileSize" name="fileSize" id="fileSize" value=$fileSize maxlength="255" size=$fbvStyles.size.MEDIUM inline="true" required="true"}
		{fbvElement type="select" label="monograph.publicationFormat.technicalProtection" from=$technicalProtectionCodes selected=$technicalProtectionCode translate=false  size=$fbvStyles.size.MEDIUM inline="true" id="technicalProtectionCode"}
	{/fbvFormSection}
	{fbvFormSection for="override" list="true"}
		{fbvElement type="checkbox" label="monograph.publicationFormat.productFileSize.override" id="override" checked=$override}
	{/fbvFormSection}
{/fbvFormArea}
