<!-- templates/controllers/grid/settings/contributor/form/contributorForm.tpl -->


{**
 * sponsors.tpl
 *
 * Copyright (c) 2003-2010 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Sponsors grid form
 *
 * $Id$
 *}

<form name="editSponsorForm" id="editSponsorForm" method="post" action="{url component="grid.settings.contributor.ContributorGridHandler" op="updateContributor"}">
{include file="common/formErrors.tpl"}

{fbvFormArea id="mastheadInfo"}
{fbvFormSection}
	{fbvElement type="text" label="settings.setup.institution" id="institution" value="$institution" maxlength="90"}
{/fbvFormSection}
{fbvFormSection}
	{fbvElement type="text" label="common.url" id="url" value="$url" maxlength="255"}
{/fbvFormSection}
{/fbvFormArea}

{if $gridId}
	<input type="hidden" name="gridId" value="{$gridId|escape}" />	
{/if}
{if $rowId}
	<input type="hidden" name="rowId" value={$rowId|escape} />
{/if}
{if $sponsorId}
	<input type="hidden" name="sponsorId" value="{$sponsorId|escape}" />
{/if}

</form>

<!-- / templates/controllers/grid/settings/contributor/form/contributorForm.tpl -->

