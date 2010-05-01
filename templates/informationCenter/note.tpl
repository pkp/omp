{**
 * note.tpl
 *
 * Copyright (c) 2003-2010 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
<<<<<<< HEAD
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
=======
 * Display a note.
 *
 *}

{assign var="noteId" value=$note->getId()}
{assign var="user" value=$note->getUser()}
<div id="note-{$noteId}">
	<table width=100%>
		<tr valign="top">
			<td style="padding-right: 5px;">{$note->getDateCreated()|date_format:"%d %b %Y %T"}</td>
			<td style="padding-right: 5px;">{$user->getFullName()}</td>
			<td align="right">
				{url|assign:deleteNoteUrl router=$smarty.const.ROUTE_PAGE page="informationCenter" op="deleteNote" noteId=$noteId}
				{confirm url=$deleteNoteUrl dialogText="informationCenter.deleteConfirm" button="#deleteNote-$noteId}
				<a href="#" id="deleteNote-{$noteId}">{translate key="common.delete"}</a>
>>>>>>> 3b2b4ce... *5388* Information center
			</td>
		</tr>
		<tr valign="top">
			<td colspan="3">{$note->getContents()}</td>
		</tr>
	</table>
	<hr />
</div>
