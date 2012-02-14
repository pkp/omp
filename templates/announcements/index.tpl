{**
 * templates/announcements/index.tpl
 *
 * Copyright (c) 2003-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Public announcements page.
 *
 *}
 
{strip}
{assign var="pageTitle" value="announcement.announcements"}
{include file="common/header.tpl"}
{/strip}

{if $announcementsIntroduction}
	<div id="announcementsIntro">
		{$announcementsIntroduction|nl2br}
	</div>
{/if}

{url|assign:announcementGridUrl router=$smarty.const.ROUTE_COMPONENT component="grid.announcements.AnnouncementGridHandler" op="fetchGrid"}
{load_url_in_div id="announcementGridContainer" url="$announcementGridUrl"}

{include file="common/footer.tpl"}