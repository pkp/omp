{**
 * templates/frontend/pages/aboutThisPublishingSystem.tpl
 *
 * Copyright (c) 2014-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @brief Display the page to view details about the OMP software.
 *
 * @uses $currentPress Press The press currently being viewed
 * @uses $appVersion string Current version of OMP
 *}
{include file="frontend/components/header.tpl" pageTitle="about.aboutThisPublishingSystem"}

<div class="page page_about_publishing_system">
	<h1 class="page_title">
		{translate key="about.aboutThisPublishingSystem"}
	</h1>

	<p>
		{if $currentPress}
			{translate key="about.aboutOMPPress" ompVersion=$appVersion}
		{else}
			{translate key="about.aboutOMPSite" ompVersion=$appVersion}
		{/if}
	</p>

</div><!-- .page -->

{include file="frontend/components/footer.tpl"}
