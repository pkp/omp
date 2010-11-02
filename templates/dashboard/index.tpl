{**
 * index.tpl
 *
 * Copyright (c) 2003-2010 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Dashboard index.

 *}

{strip}
{assign var="pageTitle" value="navigation.dashboard"}
{include file="common/header.tpl"}
{/strip}

<div class="ui-tabs ui-widget ui-widget-content ui-corner-all">
	<ul class="ui-tabs-nav ui-helper-reset ui-helper-clearfix ui-widget-header ui-corner-all">
		<li{if $selectedTab == 1} class="ui-state-default ui-corner-top ui-tabs-selected ui-state-active"{else} class="ui-state-default ui-corner-top"{/if}>
			<a href="{url op="overview"}">1. {translate key="common.overview"}</a>
		</li>
		<li{if $selectedTab == 2} class="ui-state-default ui-corner-top ui-tabs-selected ui-state-active"{else} class="ui-state-default ui-corner-top"{/if}>
			<a href="{url op="tasks"}">2. {translate key="common.tasks"}</a>
		</li>
		<li{if $selectedTab == 3} class="ui-state-default ui-corner-top ui-tabs-selected ui-state-active"{else} class="ui-state-default ui-corner-top"{/if}>
			<a href="{url op="status"}">3. {translate key="common.status"}</a>
		</li>
	</ul>

	{include file=$pageToDisplay}
</div>

{include file="common/footer.tpl"}

