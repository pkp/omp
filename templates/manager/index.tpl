{**
 * index.tpl
 *
 * Copyright (c) 2003-2008 John Willinsky
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
	<li>&#187; <a href="{url op="series"}">{translate key="series.series"}</a></li>
	<li>&#187; <a href="{url op="submissionCategory"}">{translate key="submissionCategory.submissionCategories"}</a></li>
	<li>&#187; <a href="{url op="reviewForms"}">{translate key="manager.reviewForms"}</a></li>
	<li>&#187; <a href="{url op="languages"}">{translate key="common.languages"}</a></li>
	<li>&#187; <a href="{url op="groups"}">{translate key="manager.groups"}</a></li>
	<li>&#187; <a href="{url op="emails"}">{translate key="manager.emails"}</a></li>
<!--	<li>&#187; <a href="{url page="rtadmin"}">{translate key="manager.readingTools"}</a></li>-->
	<li>&#187; <a href="{url op="setup"}">{translate key="manager.setup"}</a></li>
<!--	<li>&#187; <a href="{url op="statistics"}">{translate key="manager.statistics"}</a></li>-->
<!--	<li>&#187; <a href="{url op="payments"}">{translate key="manager.payments"}</a></li>
	{if $subscriptionsEnabled}
		<li>&#187; <a href="{url op="subscriptions"}">{translate key="manager.subscriptions"}</a></li>
	{/if}-->
	<li>&#187; <a href="{url op="plugins"}">{translate key="manager.plugins"}</a></li>
	<li>&#187; <a href="{url op="importexport"}">{translate key="manager.importExport"}</a></li>
	{call_hook name="Templates::Manager::Index::ManagementPages"}
</ul>


<h3>{translate key="manager.users"}</h3>

<ul class="plain">
	<li>&#187; <a href="{url op="people" path="all"}">{translate key="manager.people.allEnrolledUsers"}</a></li>
	<li>&#187; <a href="{url op="enrollSearch"}">{translate key="manager.people.allSiteUsers"}</a></li>
	{url|assign:"managementUrl" page="manager"}
	<li>&#187; <a href="{url op="createUser" source=$managementUrl}">{translate key="manager.people.createUser"}</a></li>
	<li>&#187; <a href="{url op="mergeUsers"}">{translate key="manager.people.mergeUsers"}</a></li>
	{call_hook name="Templates::Manager::Index::Users"}
</ul>


<h3>{translate key="manager.roles"}</h3>

<ul class="plain">
	<li>&#187; <a href="{url op="people" path="managers"}">{translate key="user.role.managers"}</a></li>
	<li>&#187; <a href="{url op="people" path="authors"}">{translate key="user.role.authors"}</a></li>
	<li>&#187; <a href="{url op="people" path="editors"}">{translate key="user.role.editors"}</a></li>
	<li>&#187; <a href="{url op="people" path="reviewers"}">{translate key="user.role.reviewers"}</a></li>
	<li>&#187; <a href="{url op="people" path="acquisitionsEditors"}">{translate key="user.role.acquisitionsEditors"}</a></li>
	<li>&#187; <a href="{url op="people" path="designers"}">{translate key="user.role.designers"}</a></li>
	<li>&#187; <a href="{url op="people" path="copyeditors"}">{translate key="user.role.copyeditors"}</a></li>
	<li>&#187; <a href="{url op="people" path="proofreaders"}">{translate key="user.role.proofreaders"}</a></li>
	<li>&#187; <a href="{url op="people" path="editorialMembers"}">{translate key="user.role.editorialMembers"}</a></li>
	<li>&#187; <a href="{url op="people" path="productionEditors"}">{translate key="user.role.productionEditors"}</a></li>
	{call_hook name="Templates::Manager::Index::Roles"}
</ul>

{include file="common/footer.tpl"}
