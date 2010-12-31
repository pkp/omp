
{**
 * submissionChecklists.tpl
 *
 * Copyright (c) 2003-2008 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * SubmissionChecklists grid form
 *}
<form id="editSubmissionChecklistForm" method="post" action="{url router=$smarty.const.ROUTE_COMPONENT component="grid.settings.submissionChecklist.SubmissionChecklistGridHandler" op="updateItem"}"}
{include file="common/formErrors.tpl"}

{fbvElementMultilingual type="textarea" name="checklistItem" id="checklistItem" value=$checklistItem required=true}

{if $gridId}
	<input type="hidden" name="gridId" value="{$gridId|escape}" />
{/if}
{if $rowId}
	<input type="hidden" name="rowId" value={$rowId|escape} />
{/if}
{if $submissionChecklistId}
	<input type="hidden" name="submissionChecklistId" value="{$submissionChecklistId|escape}" />
{/if}

</form>
