{**
 * templates/frontend/pages/about.tpl
 *
 * Copyright (c) 2014-2016 Simon Fraser University Library
 * Copyright (c) 2003-2016 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @brief Display the page to view the press's description, contact details,
 *  policies and more.
 *
 * @uses $contact array Contact details for this press
 * @uses $description string Description of this press
 * @uses $sponshorshipInfo array Sponsor and contributor details for this press
 * @uses $editorialPolicies array Focus, review policy, open access policy, etc
 * @has $editorialTeam array Info on members of the editorial team
 * @has $submissions array Info on the submission policy
 *}
{include file="frontend/components/header.tpl" pageTitle="about.aboutThePress"}

<div class="page page_about">
	{include file="frontend/components/breadcrumbs.tpl" currentTitleKey="about.aboutThePress"}
	{include file="frontend/components/editLink.tpl" page="management" op="settings" path="press" anchor="masthead" sectionTitleKey="about.aboutThePress"}

	{$aboutPress}
</div><!-- .page -->

{include file="common/frontend/footer.tpl"}
