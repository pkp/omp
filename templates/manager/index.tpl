{**
 * index.tpl
 *
 * Copyright (c) 2003-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Press management index.
 *}
{strip}
{assign var="pageTitle" value="manager.pressManagement"}
{include file="common/header.tpl"}
{/strip}

<h3>{translate key="manager.managementPages"}</h3>

<ul class="plain">
	{if $announcementsEnabled}
		<li>&#187; <a href="{url op="announcements"}">{translate key="manager.announcements"}</a></li>
	{/if}
	<li>&#187; <a href="{url op="files"}">{translate key="manager.filesBrowser"}</a></li>
	<li>&#187; <a href="{url op="groups"}">{translate key="manager.groups"}</a></li>
	<li>&#187; <a href="{url page="settings" op="index"}">{translate key="manager.settings"}</a></li>
	<li>&#187; <a href="{url op="importexport"}">{translate key="manager.importExport"}</a></li>
	{call_hook name="Templates::Manager::Index::ManagementPages"}
</ul>


<h3>{translate key="manager.users"}</h3>

<ul class="plain">
	<li>&#187; <a href="{url op="mergeUsers"}">{translate key="manager.people.mergeUsers"}</a></li>
	{call_hook name="Templates::Manager::Index::Users"}
</ul>

{include file="common/footer.tpl"}

