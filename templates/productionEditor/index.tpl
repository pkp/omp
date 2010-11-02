{**
 * index.tpl
 *
 * Copyright (c) 2003-2010 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Production Editor index.

 *}
{strip}
{assign var="pageTitle" value="common.queue.long.$pageToDisplay"}
{include file="common/header.tpl"}
{/strip}


<div class="ui-tabs ui-widget ui-widget-content ui-corner-all">

<ul class="ui-tabs-nav ui-helper-reset ui-helper-clearfix ui-widget-header ui-corner-all">
	<li{if ($pageToDisplay == "active")} class="ui-state-default ui-corner-top ui-tabs-selected ui-state-active"{else} class="ui-state-default ui-corner-top"{/if}>
		<a href="{url path="active"}">{translate key="common.queue.short.active"}</a>
	</li>
	<li{if ($pageToDisplay == "completed")} class="ui-state-default ui-corner-top ui-tabs-selected ui-state-active"{else} class="ui-state-default ui-corner-top"{/if}>
		<a href="{url path="completed"}">{translate key="common.queue.short.completed"}</a>
	</li>
</ul>

{include file="productionEditor/$pageToDisplay.tpl"}

</div>

{include file="common/footer.tpl"}

