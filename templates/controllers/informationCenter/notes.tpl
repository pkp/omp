{**
 * templates/controllers/informationCenter/notes.tpl
 *
 * Copyright (c) 2003-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Display submission file notes/note form in information center.
 *}

<script type="text/javascript">
	// Attach the Information Center handler.
	$(function() {ldelim}
		$('#informationCenterNotes').pkpHandler(
			'$.pkp.controllers.informationCenter.NotesHandler',
			{ldelim}
				fetchUrl: '{url|escape:"javascript" router=$smarty.const.ROUTE_COMPONENT op="listNotes" params=$linkParams escape=false}'
			{rdelim}
		);
	{rdelim});
</script>

<div id="informationCenterNotes">
	{include file="controllers/informationCenter/newNoteForm.tpl"}
	<br />
	<hr />

	{* Leave an empty div to be filled with notes *}
	<div id="notesList">
	</div>
</div>
