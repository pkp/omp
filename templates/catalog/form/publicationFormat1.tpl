{* EBook fields *}
{fbvFormArea id="productDimensions" title="monograph.publicationFormat.productDimensions" border="true"}
	{fbvFormSection title="monograph.publicationFormat.productFileSize" for="fileSize"}
		{fbvElement type="text"  name="fileSize" id="fileSize" value=$fileSize maxlength="255" size=$fbvStyles.size.SMALL disabled=$readOnly inline="true"}
	{/fbvFormSection}
{/fbvFormArea}