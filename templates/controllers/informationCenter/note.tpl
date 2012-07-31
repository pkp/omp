{**
 * templates/controllers/informationCenter/note.tpl
 *
 * Copyright (c) 2003-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Display a single information center note.
 *
 *}

{* These variables are both "safe" to be used unescaped. *}
{assign var="noteId" value=$note->getId()}
{assign var="formId" value="deleteNoteForm-$noteId"}

<script type="text/javascript">
	$(function() {ldelim}
			// Attach the form handler.
			$('#{$formId}').pkpHandler('$.pkp.controllers.form.AjaxFormHandler', {ldelim}
				baseUrl: '{$baseUrl|escape:"javascript"}'
			{rdelim});
	{rdelim});
</script>

<div id="note-{$noteId}">
	<table width="100%">
		<tr valign="top">
			<td>{$note->getDateCreated()|date_format:$datetimeFormatShort}</td>
			<td>
				{assign var="noteUser" value=$note->getUser()}
				{$noteUser->getFullName()|escape}
			</td>
			<td class="pkp_helpers_align_right">
				{* Check that notes are deletable (i.e. not attached to files from previous stages) and the current user has permission to delete. *}
				{if $notesDeletable && array_intersect(array(ROLE_ID_PRESS_MANAGER, ROLE_ID_SERIES_EDITOR), $userRoles)}
					<form class="pkp_form" id="{$formId}" action="{url op="deleteNote" noteId=$noteId params=$linkParams}">
						{assign var=deleteNoteButtonId value="deleteNote-$noteId"}
						{include file="linkAction/buttonConfirmationLinkAction.tpl" buttonSelector="#$deleteNoteButtonId" dialogText="informationCenter.deleteConfirm"}
						<input type="submit" id="{$deleteNoteButtonId}" class="xIcon" value="{translate key='common.delete'}" />
					</form>
				{/if}
			</td>
		</tr>
		<tr valign="top">
			{assign var="contents" value=$note->getContents()}
			<td colspan="3">
				<span>
					{$contents|truncate:250|nl2br|strip_unsafe_html}
					{if $contents|strlen > 250}<a href="javascript:$.noop();" class="showMore">{translate key="common.more"}</a>{/if}
				</span>
				{if $contents|strlen > 250}
					<span class="hidden">
						{$contents|nl2br|strip_unsafe_html} <a href="javascript:$.noop();" class="showLess">{translate key="common.less"}</a>
					</span>
				{/if}
			</td>
		</tr>
	</table>
	<hr />
</div>

