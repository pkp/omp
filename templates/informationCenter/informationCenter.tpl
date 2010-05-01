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
	{literal}
	$(function() {
		$('div#informationCenterTabs-{/literal}{$fileId}{literal}').tabs();		
		$('#informationCenterTabs-{/literal}{$fileId}{literal}').parent().dialog('option', 'buttons', null);  // Clear out default modal buttons
	});
	{/literal}
</script>

<div id="informationCenterHeader">
	<span class='fileTitle'>{$monographFile->getLocalizedName()}</span> <br />
	<span class='fileLastEvent'>{if $lastEvent}{translate key="informationCenter.lastUpdated"}: {$lastEvent->getDateLogged()}, {$lastEventUser->getFullName()}{/if}</span>
</div>
<br />
<div id="informationCenterTabs-{$fileId}">
	<ul class="ui-tabs-nav ui-helper-reset ui-helper-clearfix ui-widget-header ui-corner-all">
		<li class="ui-state-default ui-corner-top"><a href="{url router=$smarty.const.ROUTE_PAGE page="informationCenter" op="viewNotes" fileId=$fileId}">{translate key="common.notes"}</a></li>
		<li class="ui-state-default ui-corner-top"><a href="{url router=$smarty.const.ROUTE_PAGE page="informationCenter" op="viewNotify" fileId=$fileId}">{translate key="common.notify"}</a></li>
		<li class="ui-state-default ui-corner-top"><a href="{url router=$smarty.const.ROUTE_PAGE page="informationCenter" op="viewHistory" fileId=$fileId}">{translate key="informationCenter.history"}</a></li>
	</ul>
</div>