{**
 * templates/controllers/informationCenter/informationCenter.tpl
 *
 * Copyright (c) 2003-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Display information center's main modal.
 *}

<script type="text/javascript">
	// Attach the Information Center handler.
	$(function() {ldelim}
		$('#informationCenter').pkpHandler(
			'$.pkp.controllers.informationCenter.InformationCenterHandler'
		);
	{rdelim});
</script>

{if $lastEvent}
	<span class='itemLastEvent'>{translate key="informationCenter.lastUpdated"}: {$lastEvent->getDateLogged()|date_format:$dateFormatShort}, {$lastEventUser->getFullName()|escape}</span>
	<br />
{/if}

<div id="informationCenter">
	<ul>
		<li><a href="{url op="viewNotes" params=$linkParams}">{translate key="common.notes"}</a></li>
		<li><a href="{url op="viewNotify" params=$linkParams}">{translate key="common.notify"}</a></li>
		<li><a href="{url op="viewHistory" params=$linkParams}">{translate key="informationCenter.history"}</a></li>
	</ul>
</div>
