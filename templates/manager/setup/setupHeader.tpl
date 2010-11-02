{**
 * setupHeader.tpl
 *
 * Copyright (c) 2003-2010 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Header for press setup pages.

 *}
{strip}
{assign var="pageCrumbTitle" value="manager.setup.pressSetup"}
{url|assign:"currentUrl" op="setup"}
{include file="common/header.tpl"}
{/strip}

<!--
	This is a representation of HTML generated via the jQueryUI framework for tabs.
	Ideally, this process should use AJAX and jQueryUI to create this dynamically.
	See: http://jqueryui.com/demos/tabs/#ajax
-->

<div class="ui-tabs ui-widget ui-widget-content ui-corner-all">
	<ul class="ui-tabs-nav ui-helper-reset ui-helper-clearfix ui-widget-header ui-corner-all">
		<li{if $setupStep == 1} class="ui-state-default ui-corner-top ui-tabs-selected ui-state-active"{else} class="ui-state-default ui-corner-top"{/if}>
			<a href="{url op="setup" path="1"}">1. {translate key="manager.setup.details"}</a>
		</li>
		<li{if $setupStep == 2} class="ui-state-default ui-corner-top ui-tabs-selected ui-state-active"{else} class="ui-state-default ui-corner-top"{/if}>
			<a href="{url op="setup" path="2"}">2. {translate key="manager.setup.policies"}</a>
		</li>
		<li{if $setupStep == 3} class="ui-state-default ui-corner-top ui-tabs-selected ui-state-active"{else} class="ui-state-default ui-corner-top"{/if}>
			<a href="{url op="setup" path="3"}">3. {translate key="manager.setup.workflow"}</a>
		</li>
		<li{if $setupStep == 4} class="ui-state-default ui-corner-top ui-tabs-selected ui-state-active"{else} class="ui-state-default ui-corner-top"{/if}>
			<a href="{url op="setup" path="4"}">4. {translate key="manager.setup.settings"}</a>
		</li>
		<li{if $setupStep == 5} class="ui-state-default ui-corner-top ui-tabs-selected ui-state-active"{else} class="ui-state-default ui-corner-top"{/if}>
			<a href="{url op="setup" path="5"}">5. {translate key="manager.setup.look"}</a>
		</li>
	</ul>

