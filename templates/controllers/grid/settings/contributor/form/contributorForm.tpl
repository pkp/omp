
{**
 * templates/controllers/grid/settings/contributor/form/contributorForm.tpl
 *
 * Copyright (c) 2003-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Contributors grid form
 *}
<script type="text/javascript">
	$(function() {ldelim}
		// Attach the form handler.
		$('#contributorForm').pkpHandler('$.pkp.controllers.form.AjaxFormHandler');
	{rdelim});
</script>

<form class="pkp_form" id="contributorForm" method="post" action="{url router=$smarty.const.ROUTE_COMPONENT component="grid.settings.contributor.ContributorGridHandler" op="updateContributor" form="mastheadForm"}">

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
	<input type="hidden" name="rowId" value="{$rowId|escape}" />
{/if}
{if $sponsorId}
	<input type="hidden" name="sponsorId" value="{$sponsorId|escape}" />
{/if}
{fbvFormButtons}
</form>

