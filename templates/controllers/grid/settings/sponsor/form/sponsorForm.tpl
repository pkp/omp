
{**
 * sponsors.tpl
 *
 * Copyright (c) 2003-2010 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Sponsors grid form
 *}
<form id="editSponsorForm" method="post" action="{url router=$smarty.const.ROUTE_COMPONENT component="grid.settings.sponsor.SponsorGridHandler" op="updateSponsor"}">
{include file="common/formErrors.tpl"}

{fbvFormArea id="mastheadInfo"}
{fbvFormSection}
	{fbvElement type="text" label="manager.setup.institution" id="institution" value="$institution" maxlength="90"}
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

