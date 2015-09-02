{**
 * templates/frontend/pages/editorialTeam.tpl
 *
 * Copyright (c) 2014-2015 Simon Fraser University Library
 * Copyright (c) 2003-2015 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @brief Display the page to view the press's description, contact details,
 *  policies and more.
 *
 * @uses $currentPress Press The current press
 * @uses $editorialTeam string Masthead with members of the editorial team
 *}
{include file="common/frontend/header.tpl" pageTitle="about.editorialTeam"}

<div class="page page_about">
	<h1 class="page_title">
		{translate key="about.editorialTeam"}
		{include file="frontend/components/editLink.tpl" page="management" op="settings" path="press" anchor="masthead" sectionTitleKey="about.editorialTeam"}
	</h1>

	{$editorialTeam}

</div><!-- .page -->

{include file="common/frontend/footer.tpl"}
