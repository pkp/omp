{**
 * editorialPolicies.tpl
 *
 * Copyright (c) 2003-2012 John Willinsky
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

{if $currentPress->getLocalizedSetting('focusScopeDesc') != ''}
	<div id="focusAndScope"><h3>{translate key="about.focusAndScope"}</h3>
		<p>{$currentPress->getLocalizedSetting('focusScopeDesc')|nl2br}</p>
	</div>
	<div class="separator"></div>
{/if}

{if count($seriesList) > 0}
	<div id="seriesPolicies"><h3>{translate key="about.seriesPolicies"}</h3>
		{foreach from=$seriesList item=series}
			<h4>{$series->getLocalizedTitle()}</h4>
			{assign var="hasEditors" value=0}
			{foreach from=$seriesEditorEntriesBySeries item=seriesEditorEntries key=key}
				{if $key == $series->getId()}
					{foreach from=$seriesEditorEntries item=seriesEditorEntry}
						{assign var=seriesEditor value=$seriesEditorEntry.user}
						{if 0 == $hasEditors++}
						{translate key="user.role.editors"}
						<ul class="plain">
						{/if}
						<li>{$seriesEditor->getFirstName()|escape} {$seriesEditor->getLastName()|escape}{if strlen($seriesEditor->getLocalizedAffiliation()) > 0}, {$seriesEditor->getLocalizedAffiliation()|escape}{/if}</li>
					{/foreach}
				{/if}
			{/foreach}
			{if $hasEditors}</ul>{/if}
		{/foreach}
	</div>
	<div class="separator"></div>
{/if}

{if $currentPress->getLocalizedSetting('reviewPolicy') != ''}
	<div id="peerReviewProcess">
		<h3>{translate key="about.peerReviewProcess"}</h3>
		<p>{$currentPress->getLocalizedSetting('reviewPolicy')|nl2br}</p>
	</div>
	<div class="separator"></div>
{/if}

{if $currentPress->getLocalizedSetting('openAccessPolicy') != ''}
	<div id="openAccessPolicy">
		<h3>{translate key="about.openAccessPolicy"}</h3>
		<p>{$currentPress->getLocalizedSetting('openAccessPolicy')|nl2br}</p>
	</div>
	<div class="separator"></div>
{/if}

{foreach key=key from=$currentPress->getLocalizedSetting('customAboutItems') item=customAboutItem name=customAboutItems}
	{if !empty($customAboutItem.title)}
		<div id="custom-{$key|escape}"><h3>{$customAboutItem.title|escape}</h3>
			<p>{$customAboutItem.content|nl2br}</p>
		</div>
		{if !$smarty.foreach.customAboutItems.last}<div class="separator"></div>{/if}
	{/if}
{/foreach}

{include file="common/footer.tpl"}