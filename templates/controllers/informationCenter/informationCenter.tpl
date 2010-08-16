<!-- templates/controllers/informationCenter/informationCenter.tpl -->

{**
 * informationCenter.tpl
 *
 * Copyright (c) 2003-2010 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Display information center's main modal.
 *
 *}

{modal_title id="div#informationCenterTabs-$itemId" keyTranslated=$title iconClass="fileManagement" canClose=1}

{init_tabs id="#informationCenterTabs-$itemId"}
<script type="text/javascript">
	{literal}
	$(function() {
		$('#informationCenterTabs-{/literal}{$itemId}{literal}').parent().dialog('option', 'buttons', null);  // Clear out default modal buttons
	});
	{/literal}
</script>

<span class='itemLastEvent'>{if $lastEvent}{translate key="informationCenter.lastUpdated"}: {$lastEvent->getDateLogged()}, {$lastEventUser->getFullName()}{/if}</span>

<br />

<div id="informationCenterTabs-{$itemId}">
	<ul class="ui-tabs-nav ui-helper-reset ui-helper-clearfix ui-widget-header ui-corner-all">
		<li class="ui-state-default ui-corner-top"><a href="{url op="viewNotes" monographId=$monographId itemId=$itemId}">{translate key="common.notes"}</a></li>
		<li class="ui-state-default ui-corner-top"><a href="{url op="viewNotify" monographId=$monographId itemId=$itemId}">{translate key="common.notify"}</a></li>
		<li class="ui-state-default ui-corner-top"><a href="{url op="viewHistory" monographId=$monographId itemId=$itemId}">{translate key="informationCenter.history"}</a></li>
	</ul>
</div>
<!-- / templates/controllers/informationCenter/informationCenter.tpl -->

