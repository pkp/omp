{**
 * templates/controllers/grid/settings/genre/form/genreForm.tpl
 *
 * Copyright (c) 2003-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Genre form under press management.
 *}

<script type="text/javascript">
	$(function() {ldelim}
		// Attach the form handler.
		$('#genreForm').pkpHandler('$.pkp.controllers.form.AjaxFormHandler');
	{rdelim});
</script>

<form id="genreForm" class="pkp_controllers_form" method="post" action="{url router=$smarty.const.ROUTE_COMPONENT component="grid.settings.genre.GenreGridHandler" op="updateGenre"}">
{include file="common/formErrors.tpl"}

{fbvFormArea id="genreInfo"}
{fbvFormSection title="common.name" for="name" required="true"}
	{fbvTextInput multilingual="true" id="name" value="$name" maxlength="80"}
{/fbvFormSection}
{fbvFormSection title="common.designation" for="designation" required="true"}
	{fbvTextInput multilingual="true" id="designation" value="$designation" maxlength="80"}
{/fbvFormSection}
{fbvFormSection title="manager.setup.sortableByComponent"}
	{fbvElement type="checkbox" id="sortable" checked=$sortable label="manager.setup.genres.sortable"}
{/fbvFormSection}
{fbvFormSection title="manager.setup.groupType" for="category"}
	{fbvElement type="select" id="category" from=$monographFileCategories selected=$category translate=false}
{/fbvFormSection}
{/fbvFormArea}

{if $gridId}
	<input type="hidden" name="gridId" value="{$gridId|escape}" />
{/if}
{if $rowId}
	<input type="hidden" name="rowId" value="{$rowId|escape}" />
{/if}
{if $genreId}
	<input type="hidden" name="genreId" value="{$genreId|escape}" />
{/if}
{include file="form/formButtons.tpl" submitText="common.save"}
</form>

