{**
 * templates/controllers/informationCenter/informationCenter.tpl
 *
 * Copyright (c) 2003-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Display information center's main modal.
 *}

<script type="text/javascript">
	// Attach the Information Center handler.
	$(function() {ldelim}
		$('#informationCenter').pkpHandler(
			'$.pkp.controllers.informationCenter.InformationCenterHandler', {ldelim}
				selected: {$selectedTabIndex}
			{rdelim}
		);
	{rdelim});
</script>

{if $lastEvent}
	<span class='pkp_controllers_informationCenter_itemLastEvent'>{translate key="informationCenter.lastUpdated"}: {$lastEvent->getDateLogged()|date_format:$dateFormatShort}, {$lastEventUser->getFullName()|escape}</span>
	<br />
{/if}

<div id="informationCenter" class="pkp_controllers_informationCenter">
	<ul>
		{if array_intersect(array(ROLE_ID_PRESS_MANAGER, ROLE_ID_SERIES_EDITOR), $userRoles) && $showMetadataLink}
			<li><a href="{url op="metadata" params=$linkParams}">{translate key="submission.informationCenter.metadata"}</a></li>
		{/if}
		<li><a href="{url op="viewNotes" params=$linkParams}">{translate key="submission.informationCenter.notes"}</a></li>
		<li><a href="{url op="viewNotify" params=$linkParams}">{translate key="submission.informationCenter.notify"}</a></li>
		<li><a href="{url op="viewHistory" params=$linkParams}">{translate key="submission.informationCenter.history"}</a></li>
	</ul>
</div>
