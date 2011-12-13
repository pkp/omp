{**
 * templates/controllers/informationCenter/note.tpl
 *
 * Copyright (c) 2003-2011 John Willinsky
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
	<table width=100%>
		<tr valign="top">
			<td style="padding-right: 5px;">{$note->getDateCreated()|date_format:"%d %b %Y %T"}</td>
			<td style="padding-right: 5px;">
				{assign var="noteUser" value=$note->getUser()}
				{$noteUser->getFullName()|escape}
			</td>
			<td align="right">
				{if $canAdministerNotes}
					<form class="pkp_form" id="{$formId}" action="{url op="deleteNote" noteId=$noteId params=$linkParams}">
						{assign var=deleteNoteButtonId value="deleteNote-$noteId"}
						{include file="linkAction/buttonConfirmationLinkAction.tpl" buttonSelector="#$deleteNoteButtonId" dialogText="informationCenter.deleteConfirm"}
						<input type="submit" id="{$deleteNoteButtonId}" class="button" value="{translate key='common.delete'}" />
					</form>
				{/if}
			</td>
		</tr>
		<tr valign="top">
			<td colspan="3">{$note->getContents()|escape}</td>
		</tr>
	</table>
	<hr />
</div>

