{**
 * templates/controllers/informationCenter/notesList.tpl
 *
 * Copyright (c) 2003-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Display submission file notes form in information center.
 *}

<div id="notesList">
	{iterate from=notes item=note}
		{include file="controllers/informationCenter/note.tpl"}
	{/iterate}
	{if $notes->wasEmpty()}
		<h5 id="noNotes" class="pkp_helpers_text_center">{translate key="informationCenter.noNotes"}</h5>
	{/if}
</div>
