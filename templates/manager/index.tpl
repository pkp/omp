<!-- templates/manager/index.tpl -->

{**
 * index.tpl
 *
 * Copyright (c) 2003-2010 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Press management index.
 *
 * $Id$
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
	<li>&#187; <a href="{url op="languages"}">{translate key="common.languages"}</a></li>
	<li>&#187; <a href="{url op="groups"}">{translate key="manager.groups"}</a></li>
	<li>&#187; <a href="{url op="emails"}">{translate key="manager.emails"}</a></li>
	<li>&#187; <a href="{url op="setup"}">{translate key="manager.setup"}</a></li>
	<li>&#187; <a href="{url op="plugins"}">{translate key="manager.plugins"}</a></li>
	<li>&#187; <a href="{url op="importexport"}">{translate key="manager.importExport"}</a></li>
	{call_hook name="Templates::Manager::Index::ManagementPages"}
</ul>


<h3>{translate key="manager.users"}</h3>

<ul class="plain">
	<li>&#187; <a href="{url op="people" path="all"}">{translate key="manager.people.allEnrolledUsers"}</a></li>
	<li>&#187; <a href="{url op="enrollSearch"}">{translate key="manager.people.allSiteUsers"}</a></li>
	<li>&#187; <a href="{url op="showNoRole"}">{translate key="manager.people.showNoRole"}</a></li>
	{url|assign:"managementUrl" page="manager"}
	<li>&#187; <a href="{url op="createUser" source=$managementUrl}">{translate key="manager.people.createUser"}</a></li>
	<li>&#187; <a href="{url op="mergeUsers"}">{translate key="manager.people.mergeUsers"}</a></li>
	{call_hook name="Templates::Manager::Index::Users"}
</ul>


<h3>{translate key="manager.roles"}</h3>

<ul class="plain">
{iterate from=userGroups item=userGroup}
	<li>&#187; <a href="{url op="people" path=$userGroup->getId()}">{$userGroup->getLocalizedName()}</a></li>
{/iterate}
	{call_hook name="Templates::Manager::Index::Roles"}
</ul>

{include file="common/footer.tpl"}

<!-- / templates/manager/index.tpl -->

