{**
 * templates/frontend/pages/editorialTeam.tpl
 *
 * Copyright (c) 2014-2015 Simon Fraser University Library
 * Copyright (c) 2003-2015 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @brief Display the page to view the press's editorial masthead
 *
 * @uses $currentPress Press The current press
 * @uses $editorialTeam string Masthead with members of the editorial team
 *}
{include file="common/frontend/header.tpl" pageTitle="about.editorialTeam"}

<div class="page page_editorial_team">
	{include file="frontend/components/breadcrumbs.tpl" currentTitleKey="about.editorialTeam"}
	{include file="frontend/components/editLink.tpl" page="management" op="settings" path="press" anchor="masthead" sectionTitleKey="about.editorialTeam"}

	{$editorialTeam|strip_unsafe_html}

</div><!-- .page -->

{include file="common/frontend/footer.tpl"}
