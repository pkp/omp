{**
 * note.tpl
 *
 * Copyright (c) 2003-2010 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Display a single information center note.
 *
 * $Id$
 *}

{assign var="noteId" value=$note->getId()}

<div id="note-{$noteId}">
	<table>
		<tr valign="top">
			<td>{$note->getDateCreated()|date_format:$dateFormatShort}</td>
			<td>{assign var="user" value=$note->getUser()}{$user->getFullName()|escape}</td>
			<td align="right">
				{url|assign:"deleteUrl" router=$smarty.const.ROUTE_PAGE page="informationCenter" op="deleteNote" noteId=$noteId fileId=$fileId}
				{confirm url=$deleteUrl dialogText="informationCenter.deleteConfirm" button="#deleteNote-$noteId"}
				<a href="#" id="deleteNote-{$noteId}" >{translate key="common.delete"}</a>
			</td>
		</tr>
		<tr valign="top">
			<td colspan="3">{$note->getContents()}</td>
		</tr>
	</table>
	<hr />
</div>
