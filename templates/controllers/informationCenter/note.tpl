{**
 * note.tpl
 *
 * Copyright (c) 2003-2010 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Display a single information center note.
 *
 *}

{assign var="noteId" value=$note->getId()}
{assign var="user" value=$note->getUser()}
<div id="note-{$noteId}">
	<table width=100%>
		<tr valign="top">
			<td style="padding-right: 5px;">{$note->getDateCreated()|date_format:"%d %b %Y %T"}</td>
			<td style="padding-right: 5px;">{$user->getFullName()|escape}</td>
			<td align="right">
				{url|assign:deleteNoteUrl op="deleteNote" noteId=$noteId}
				{confirm url=$deleteNoteUrl dialogText="informationCenter.deleteConfirm" button="#deleteNote-$noteId}
				<a href="#" id="deleteNote-{$noteId}">{translate key="common.delete"}</a>
			</td>
		</tr>
		<tr valign="top">
			<td colspan="3">{$note->getContents()|escape}</td>
		</tr>
	</table>
	<hr />
</div>

