{**
 * templates/admin/index.tpl
 *
 * Copyright (c) 2014-2015 Simon Fraser University Library
 * Copyright (c) 2003-2015 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Site administration index.
 *}

{strip}
{assign var="pageTitle" value="admin.siteAdmin"}
{include file="common/header.tpl"}
{/strip}

<h3>{translate key="admin.siteManagement"}</h3>

<ul class="plain">
	<li><a href="{url op="contexts"}">{translate key="admin.hostedPresses"}</a></li>
	{call_hook name="Templates::Admin::Index::SiteManagement"}
	{if $multipleContexts}
		<li><a href="{url op="settings"}">{translate key="admin.settings"}</a></li>
	{/if}
</ul>


<h3>{translate key="admin.adminFunctions"}</h3>

<ul class="plain">
	<li><a href="{url op="systemInfo"}">{translate key="admin.systemInformation"}</a></li>
	<li><a href="{url op="expireSessions"}" onclick="return confirm('{translate|escape:"jsparam" key="admin.confirmExpireSessions"}')">{translate key="admin.expireSessions"}</a></li>
	<li><a href="{url op="clearDataCache"}">{translate key="admin.clearDataCache"}</a></li>
	<li><a href="{url op="clearTemplateCache"}" onclick="return confirm('{translate|escape:"jsparam" key="admin.confirmClearTemplateCache"}')">{translate key="admin.clearTemplateCache"}</a></li>
	<li><a href="{url op="clearScheduledTaskLogFiles"}" onclick="return confirm('{translate|escape:"jsparam" key="admin.scheduledTask.confirmClearLogs"}')">{translate key="admin.scheduledTask.clearLogs"}</a></li>	
	{call_hook name="Templates::Admin::Index::AdminFunctions"}
</ul>

{include file="common/footer.tpl"}

