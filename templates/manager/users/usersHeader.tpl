{**
 * usersHeader.tpl
 *
 * Copyright (c) 2003-2010 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Header for users and roles settings pages.
 *}
{strip}
{assign var="pageCrumbTitle" value="manager.users"}
{include file="common/header.tpl"}
{/strip}

<!--
	This is a representation of HTML generated via the jQueryUI framework for tabs.
	Ideally, this process should use AJAX and jQueryUI to create this dynamically.
	See: http://jqueryui.com/demos/tabs/#ajax
-->

<div class="ui-tabs ui-widget ui-widget-content ui-corner-all">
	<ul class="ui-tabs-nav ui-helper-reset ui-helper-clearfix ui-widget-header ui-corner-all">
		<li{if $currentPage == "users"} class="ui-state-default ui-corner-top ui-tabs-selected ui-state-active"{else} class="ui-state-default ui-corner-top"{/if}>
			<a href="{url op="users"}">{translate key="manager.users.users"}</a>
			{url|assign:"currentUrl" op="users"}	
		</li>
		<li{if $currentPage == "roles"} class="ui-state-default ui-corner-top ui-tabs-selected ui-state-active"{else} class="ui-state-default ui-corner-top"{/if}>
			<a href="{url op="roles"}">{translate key="manager.users.roles"}</a>
			{url|assign:"currentUrl" op="roles"}	
		</li>
		<li{if $currentPage == "enrollment"} class="ui-state-default ui-corner-top ui-tabs-selected ui-state-active"{else} class="ui-state-default ui-corner-top"{/if}>
			<a href="{url op="enrollment"}">{translate key="manager.users.enrollment"}</a>
			{url|assign:"currentUrl" op="enrollment"}	
		</li>
	</ul>
