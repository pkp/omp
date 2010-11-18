{**
 * systemHeader.tpl
 *
 * Copyright (c) 2003-2010 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Header for system settings pages.
 *}
{strip}
{assign var="pageCrumbTitle" value="manager.system"}
{include file="common/header.tpl"}
{/strip}

<!--
	This is a representation of HTML generated via the jQueryUI framework for tabs.
	Ideally, this process should use AJAX and jQueryUI to create this dynamically.
	See: http://jqueryui.com/demos/tabs/#ajax
-->

<div class="ui-tabs ui-widget ui-widget-content ui-corner-all">
	<ul class="ui-tabs-nav ui-helper-reset ui-helper-clearfix ui-widget-header ui-corner-all">
		<li{if $currentPage == "languages"} class="ui-state-default ui-corner-top ui-tabs-selected ui-state-active"{else} class="ui-state-default ui-corner-top"{/if}>
			<a href="{url op="languages"}">{translate key="manager.system.languages"}</a>
			{url|assign:"currentUrl" op="languages"}
		</li>
		<li{if $currentPage == "preparedEmails"} class="ui-state-default ui-corner-top ui-tabs-selected ui-state-active"{else} class="ui-state-default ui-corner-top"{/if}>
			<a href="{url op="preparedEmails"}">{translate key="manager.system.preparedEmails"}</a>
			{url|assign:"currentUrl" op="preparedEmails"}
		</li>
		<li{if $currentPage == "reviewForms"} class="ui-state-default ui-corner-top ui-tabs-selected ui-state-active"{else} class="ui-state-default ui-corner-top"{/if}>
			<a href="{url op="reviewForms"}">{translate key="manager.system.reviewForms"}</a>
			{url|assign:"currentUrl" op="reviewForms"}
		</li>
		<li{if $currentPage == "readingTools"} class="ui-state-default ui-corner-top ui-tabs-selected ui-state-active"{else} class="ui-state-default ui-corner-top"{/if}>
			<a href="{url op="readingTools"}">{translate key="manager.system.readingTools"}</a>
			{url|assign:"currentUrl" op="readingTools"}
		</li>
		<li{if $currentPage == "payments"} class="ui-state-default ui-corner-top ui-tabs-selected ui-state-active"{else} class="ui-state-default ui-corner-top"{/if}>
			<a href="{url op="payments"}">{translate key="manager.system.payments"}</a>
			{url|assign:"currentUrl" op="payments"}
		</li>
		<li{if $currentPage == "plugins"} class="ui-state-default ui-corner-top ui-tabs-selected ui-state-active"{else} class="ui-state-default ui-corner-top"{/if}>
			<a href="{url op="plugins"}">{translate key="manager.system.plugins"}</a>
			{url|assign:"currentUrl" op="plugins"}
		</li>
		<li{if $currentPage == "archiving"} class="ui-state-default ui-corner-top ui-tabs-selected ui-state-active"{else} class="ui-state-default ui-corner-top"{/if}>
			<a href="{url op="archiving"}">{translate key="manager.system.archiving"}</a>
			{url|assign:"currentUrl" op="archiving"}
		</li>
	</ul>
