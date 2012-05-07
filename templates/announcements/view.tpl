{**
 * templates/announcements/view.tpl
 *
 * Copyright (c) 2003-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * 'More' page for a specific announcement.
 *
 *}

{strip}
{if $announcement}
	{assign var="pageTitleTranslated" value=$announcement->getLocalizedTitle() translate=false}
{/if}
{include file="common/header.tpl"}
{/strip}

{if $announcement}
	<div id="announcement">
		{$announcement->getLocalizedDescription()|nl2br}
	</div>
	<div id="announcementDate">
		{translate key="announcement.postedOn" postDate=$announcement->getDatePosted()}
	</div>
{/if}

{include file="common/footer.tpl"}
