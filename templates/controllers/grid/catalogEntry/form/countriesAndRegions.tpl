{**
 * templates/controllers/grid/catalogEntry/form/countriesAndRegions.tpl
 *
 * Copyright (c) 2014-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * A form used for Markets and Sales Rights entities to describe territories.
 *}

{fbvFormSection title="grid.catalogEntry.countries" for="countriesIncludedCode"}
	{fbvElement type="select" label="grid.catalogEntry.included" from=$countryCodes selected=$countriesIncluded translate=false id="countriesIncluded" name="countriesIncluded[]" multiple="multiple" size=$fbvStyles.size.MEDIUM defaultValue="" defaultLabel="" inline=true}
	{fbvElement type="select" label="grid.catalogEntry.excluded" from=$countryCodes selected=$countriesExcluded translate=false id="countriesExcluded" name="countriesExcluded[]" multiple="multiple" size=$fbvStyles.size.MEDIUM defaultValue="" defaultLabel="" inline=true}
{/fbvFormSection}

{fbvFormSection title="grid.catalogEntry.regions" for="countriesIncludedCode"}
	{fbvElement type="select" label="grid.catalogEntry.included" from=$regionCodes selected=$regionsIncluded translate=false id="regionsIncluded" name="regionsIncluded[]" multiple="multiple" size=$fbvStyles.size.MEDIUM defaultValue="" defaultLabel="" inline=true}
	{fbvElement type="select" label="grid.catalogEntry.excluded" from=$regionCodes selected=$regionsExcluded translate=false id="regionsExcluded" name="regionsExcluded[]" multiple="multiple" size=$fbvStyles.size.MEDIUM defaultValue="" defaultLabel="" inline=true}
{/fbvFormSection}
