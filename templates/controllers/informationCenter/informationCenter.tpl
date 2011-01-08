{**
 * informationCenter.tpl
 *
 * Copyright (c) 2003-2010 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Display information center's main modal.
 *
 *}

<script type="text/javascript">
	// Attach the Information Center handler.
	$(function() {ldelim}
		$('#informationCenter').pkpHandler(
			'$.pkp.controllers.InformationCenterHandler'
		);
	{rdelim});
</script>

{if $lastEvent}
	<span class='itemLastEvent'>{translate key="informationCenter.lastUpdated"}: {$lastEvent->getDateLogged()|date_format:$dateFormatShort}, {$lastEventUser->getFullName()|escape}</span>
	<br />
{/if}

<div id="informationCenter">
	<ul>
		<li><a href="{url op="viewNotes" monographId=$monographId itemId=$itemId}">{translate key="common.notes"}</a></li>
		<li><a href="{url op="viewNotify" monographId=$monographId itemId=$itemId}">{translate key="common.notify"}</a></li>
		<li><a href="{url op="viewHistory" monographId=$monographId itemId=$itemId}">{translate key="informationCenter.history"}</a></li>
	</ul>
</div>
