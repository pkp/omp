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
