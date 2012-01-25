{* digital format fields *}
{fbvFormArea id="productDimensions" title="monograph.publicationFormat.digitalInformation" border="true"}
	{fbvFormSection for="digitalInformation"}
		{fbvElement type="text" label="monograph.publicationFormat.productFileSize" name="fileSize" id="fileSize" value=$fileSize|escape maxlength="255" size=$fbvStyles.size.MEDIUM inline="true"}
		{fbvElement type="select" label="monograph.publicationFormat.technicalProtection" from=$technicalProtectionCodes selected=$technicalProtectionCode translate=false  size=$fbvStyles.size.MEDIUM id="technicalProtectionCode" inline=true}
	{/fbvFormSection}
{/fbvFormArea}
