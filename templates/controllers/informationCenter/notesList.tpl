{**
 * templates/controllers/informationCenter/notesList.tpl
 *
 * Copyright (c) 2003-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Display submission file notes form in information center.
 *}

<div id="notesList">
	{iterate from=notes item=note}
		{include file="controllers/informationCenter/note.tpl"}
		{$note->markViewed($currentUserId)}
	{/iterate}
	{if $notes->wasEmpty()}
		<span>{translate key="informationCenter.noNotes"}</span>
	{/if}
</div>
