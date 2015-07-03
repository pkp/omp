{**
 * templates/about/editorialTeam.tpl
 *
 * Copyright (c) 2014-2015 Simon Fraser University Library
 * Copyright (c) 2003-2015 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * About the Press index.
 *}
{strip}
{assign var="pageTitle" value="about.editorialTeam"}
{include file="common/header.tpl"}
{/strip}

{url|assign:editUrl page="management" op="settings" path="press" anchor="masthead"}
{include file="common/linkToEditPage.tpl" editUrl=$editUrl}

{if not empty($editorialTeamInfo.masthead)}
	{$editorialTeamInfo.masthead}
{/if}

{include file="common/footer.tpl"}
