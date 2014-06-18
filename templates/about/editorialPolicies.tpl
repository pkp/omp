{**
 * templates/about/editorialPolicies.tpl
 *
 * Copyright (c) 2014 Simon Fraser University Library
 * Copyright (c) 2003-2014 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * About the Press / Editorial Policies.
 *}
{strip}
{assign var="pageTitle" value="about.editorialPolicies"}
{include file="common/header.tpl"}
{/strip}

{url|assign:editUrl page="management" op="settings" path="press" anchor="policies"}
{include file="common/linkToEditPage.tpl" editUrl=$editUrl}

{if !empty($editorialPoliciesInfo.focusScopeDesc)}
	<div id="focusAndScope"><h3>{translate key="about.focusAndScope"}</h3>
		<p>{$editorialPoliciesInfo.focusScopeDesc|nl2br}</p>
	</div>
	<div class="separator"></div>
{/if}

{if !empty($editorialPoliciesInfo.reviewPolicy)}
	<div id="peerReviewProcess">
		<h3>{translate key="about.reviewPolicy"}</h3>
		<p>{$editorialPoliciesInfo.reviewPolicy|nl2br}</p>
	</div>
	<div class="separator"></div>
{/if}

{if !empty($editorialPoliciesInfo.openAccessPolicy)}
	<div id="openAccessPolicy">
		<h3>{translate key="about.openAccessPolicy"}</h3>
		<p>{$editorialPoliciesInfo.openAccessPolicy|nl2br}</p>
	</div>
	<div class="separator"></div>
{/if}

{foreach key=key from=$editorialPoliciesInfo.customAboutItems item=customAboutItem name=customAboutItems}
	{if !empty($customAboutItem.title)}
		<div id="custom-{$key|escape}"><h3>{$customAboutItem.title|escape}</h3>
			<p>{$customAboutItem.content|nl2br}</p>
		</div>
		{if !$smarty.foreach.customAboutItems.last}<div class="separator"></div>{/if}
	{/if}
{/foreach}

{include file="common/footer.tpl"}
