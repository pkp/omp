{**
 * templates/controllers/tab/catalogEntry/form/physicalPublicationFormat.tpl
 *
 * Copyright (c) 2014-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Physical publication format form fields for the catalog entry form.
 *}
{* Hardcover fields *}
<div class="pkp_helpers_align_right pkp_helpers_half">
	{fbvFormArea id="pageCounts" title="monograph.publicationFormat.pageCounts" class="border"}
		{fbvFormSection for="frontMatter" title="monograph.publicationFormat.frontMatterCount"}
			{fbvElement type="text" name="frontMatter" id="frontMatter" value=$frontMatter maxlength="255" size=$fbvStyles.size.MEDIUM}
		{/fbvFormSection}
		{fbvFormSection for="backMatter" title="monograph.publicationFormat.backMatterCount"}
			{fbvElement type="text" name="backMatter" id="backMatter" value=$backMatter maxlength="255" size=$fbvStyles.size.MEDIUM}
		{/fbvFormSection}
	{/fbvFormArea}
	
	{fbvFormArea id="returnInformation" title="monograph.publicationFormat.returnInformation" class="border"}
		{fbvFormSection for="returnableIndicator"}
			{fbvElement type="select" from=$returnableIndicatorCodes selected=$returnableIndicatorCode translate=false id="returnableIndicatorCode" inline="true"}
		{/fbvFormSection}
	{/fbvFormArea}
</div>
<div id="dimensionsContainer" class="pkp_helpers_half left">
	{fbvFormArea id="productDimensions" title="monograph.publicationFormat.productDimensions" class="border"}
		{fbvFormSection title="monograph.publicationFormat.productHeight" for="productHeight"}
			{fbvElement type="text"  name="height" id="height" value=$height maxlength="255" size=$fbvStyles.size.SMALL inline="true"}
			{fbvElement type="select" from=$measurementUnitCodes selected=$heightUnitCode translate=false id="heightUnitCode" inline="true"}
		{/fbvFormSection}
		{fbvFormSection title="monograph.publicationFormat.productWidth" for="productWidth"}
			{fbvElement type="text"  name="width" id="width" value=$width maxlength="255" size=$fbvStyles.size.SMALL inline="true"}
			{fbvElement type="select" from=$measurementUnitCodes selected=$widthUnitCode translate=false id="widthUnitCode" inline="true"}
		{/fbvFormSection}
		{fbvFormSection title="monograph.publicationFormat.productThickness" for="productThickness"}
			{fbvElement type="text"  name="thickness" id="thickness" value=$thickness maxlength="255" size=$fbvStyles.size.SMALL inline="true"}
			{fbvElement type="select" from=$measurementUnitCodes selected=$thicknessUnitCode translate=false id="thicknessUnitCode" inline="true"}
		{/fbvFormSection}
		{fbvFormSection title="monograph.publicationFormat.productWeight" for="productWeight"}
			{fbvElement type="text"  name="weight" id="weight" value=$weight maxlength="255" size=$fbvStyles.size.SMALL inline="true"}
			{fbvElement type="select" from=$weightUnitCodes selected=$weightUnitCode translate=false id="weightUnitCode" inline="true"}
		{/fbvFormSection}
		{fbvFormSection title="monograph.publicationFormat.countryOfManufacture" for="country"}
			{fbvElement type="select" from=$countriesIncludedCodes selected=$countryManufactureCode translate=false id="countryManufactureCode" inline="true"}
		{/fbvFormSection}
	{/fbvFormArea}
</div>
