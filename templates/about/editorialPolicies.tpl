{**
 * editorialPolicies.tpl
 *
 * Copyright (c) 2003-2010 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * About the Press / Editorial Policies.
 * 
 * $Id$
 *}
{strip}
{assign var="pageTitle" value="about.editorialPolicies"}
{include file="common/header.tpl"}
{/strip}

<ul class="plain">
	{if $currentPress->getLocalizedSetting('focusScopeDesc') != ''}<li>&#187; <a href="{url op="editorialPolicies" anchor="focusAndScope"}">{translate key="about.focusAndScope"}</a></li>{/if}
	{if count($series) > 0}<li>&#187; <a href="{url op="editorialPolicies" anchor="seriesPolicies"}">{translate key="about.seriesPolicies"}</a></li>{/if}
	{if $currentPress->getLocalizedSetting('reviewPolicy') != ''}<li>&#187; <a href="{url op="editorialPolicies" anchor="peerReviewProcess"}">{translate key="about.peerReviewProcess"}</a></li>{/if}
	{if $currentPress->getLocalizedSetting('openAccessPolicy') != ''}<li>&#187; <a href="{url op="editorialPolicies" anchor="openAccessPolicy"}">{translate key="about.openAccessPolicy"}</a></li>{/if}
	{foreach key=key from=$currentPress->getLocalizedSetting('customAboutItems') item=customAboutItem}
		{if !empty($customAboutItem.title)}
			<li>&#187; <a href="{url op="editorialPolicies" anchor=custom-$key}">{$customAboutItem.title|escape}</a></li>
		{/if}
	{/foreach}
</ul>

{if $currentPress->getLocalizedSetting('focusScopeDesc') != ''}
<div id="focusAndScope"><h3>{translate key="about.focusAndScope"}</h3>
<p>{$currentPress->getLocalizedSetting('focusScopeDesc')|nl2br}</p>

<div class="separator">&nbsp;</div>
</div>
{/if}

{if count($series) > 0}
<div id="seriesPolicies"><h3>{translate key="about.seriesPolicies"}</h3>
{foreach from=$series item=series}{if !$series->getHideAbout()}
	<h4>{$series->getLocalizedTitle()}</h4>
	{if strlen($series->getLocalizedPolicy()) > 0}
		<p>{$series->getLocalizedPolicy()|nl2br}</p>
	{/if}

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

	<table class="plain" width="60%">
		<tr>
			<td width="50%">{if !$series->getEditorRestricted()}{icon name="checked"}{else}{icon name="unchecked"}{/if} {translate key="manager.series.open"}</td>
			<td width="50%">{if $series->getMetaIndexed()}{icon name="checked"}{else}{icon name="unchecked"}{/if} {translate key="manager.series.indexed"}</td>
		</tr>
	</table>
{/if}{/foreach}
</div>

<div class="separator">&nbsp;</div>
{/if}

{if $currentPress->getLocalizedSetting('reviewPolicy') != ''}<div id="peerReviewProcess"><h3>{translate key="about.peerReviewProcess"}</h3>
<p>{$currentPress->getLocalizedSetting('reviewPolicy')|nl2br}</p>

<div class="separator">&nbsp;</div>
</div>
{/if}

{if $currentPress->getLocalizedSetting('openAccessPolicy') != ''} 
<div id="openAccessPolicy"><h3>{translate key="about.openAccessPolicy"}</h3>
<p>{$currentPress->getLocalizedSetting('openAccessPolicy')|nl2br}</p>

<div class="separator">&nbsp;</div>
</div>
{/if}

{foreach key=key from=$currentPress->getLocalizedSetting('customAboutItems') item=customAboutItem name=customAboutItems}
	{if !empty($customAboutItem.title)}
		<div id="custom-{$key|escape}"><h3>{$customAboutItem.title|escape}</h3>
		<p>{$customAboutItem.content|nl2br}</p>
		{if !$smarty.foreach.customAboutItems.last}<div class="separator">&nbsp;</div>{/if}
		</div>
	{/if}
{/foreach}

{include file="common/footer.tpl"}
