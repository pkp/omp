
{**
 * templates/controllers/grid/settings/submissionChecklist/form/submissionChecklists.tpl
 *
 * Copyright (c) 2003-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * SubmissionChecklists grid form
 *}

<script type="text/javascript">
	$(function() {ldelim}
		// Attach the form handler.
		$('#editSubmissionChecklistForm').pkpHandler('$.pkp.controllers.form.AjaxFormHandler');
	{rdelim});
</script>

<form id="editSubmissionChecklistForm" class="pkp_controllers_form" method="post" action="{url router=$smarty.const.ROUTE_COMPONENT component="grid.settings.submissionChecklist.SubmissionChecklistGridHandler" op="updateItem"}"}
{include file="common/formErrors.tpl"}
{fbvFormArea id="checklist"}
	{fbvFormSection title="grid.submissionChecklist.column.checklistItem" required="true" for="checklistItem"}
		{fbvTextArea multilingual="true" name="checklistItem" id="checklistItem" value=$checklistItem}
	{/fbvFormSection}
{/fbvFormArea}
{if $gridId != null}
	<input type="hidden" name="gridId" value="{$gridId|escape}" />
{/if}
{if $rowId != null}
	<input type="hidden" name="rowId" value="{$rowId|escape}" />
{/if}
{if $submissionChecklistId != null}
	<input type="hidden" name="submissionChecklistId" value="{$submissionChecklistId|escape}" />
{/if}
{include file="form/formButtons.tpl" submitText="common.save"}
</form>
