{**
 * editorialTeam.tpl
 *
 * Copyright (c) 2003-2010 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * About the Press index.
 *
 * $Id$
 *}
{strip}
{assign var="pageTitle" value="about.editorialTeam"}
{include file="common/header.tpl"}
{/strip}

{if count($editors) > 0}
	{if count($editors) == 1}
		<h4>{translate key="user.role.editor"}</h4>
	{else}
		<h4>{translate key="user.role.editors"}</h4>
	{/if}

	<ol class="editorialTeam">
		{foreach from=$editors item=editor}
			<li><a href="javascript:openRTWindow('{url op="editorialTeamBio" path=$editor->getId()}')">{$editor->getFullName()|escape}</a>{if $editor->getLocalizedAffiliation()}, {$editor->getLocalizedAffiliation()|escape}{/if}{if $editor->getCountry()}{assign var=countryCode value=$editor->getCountry()}{assign var=country value=$countries.$countryCode}, {$country|escape}{/if}</li>
		{/foreach}
	</ol>
{/if}

{if count($seriesEditors) > 0}
	{if count($seriesEditors) == 1}
		<h4>{translate key="user.role.seriesEditor"}</h4>
	{else}
		<h4>{translate key="user.role.seriesEditors"}</h4>
	{/if}

	<ol class="editorialTeam">
		{foreach from=$seriesEditors item=seriesEditor}
			<li><a href="javascript:openRTWindow('{url op="editorialTeamBio" path=$seriesEditor->getId()}')">{$seriesEditor->getFullName()|escape}</a>{if $seriesEditor->getLocalizedAffiliation()}, {$seriesEditor->getLocalizedAffiliation()|escape}{/if}{if $seriesEditor->getCountry()}{assign var=countryCode value=$seriesEditor->getCountry()}{assign var=country value=$countries.$countryCode}, {$country|escape}{/if}</li>
		{/foreach}
	</ol>
{/if}

{if count($productionEditors) > 0}
	{if count($productionEditors) == 1}
		<h4>{translate key="user.role.productionEditor"}</h4>
	{else}
		<h4>{translate key="user.role.productionEditors"}</h4>
	{/if}

	<ol class="editorialTeam">
		{foreach from=$productionEditors item=productionEditor}
			<li><a href="javascript:openRTWindow('{url op="editorialTeamBio" path=$productionEditor->getId()}')">{$productionEditor->getFullName()|escape}</a>{if $productionEditor->getLocalizedAffiliation()}, {$productionEditor->getLocalizedAffiliation()|escape}{/if}{if $productionEditor->getCountry()}{assign var=countryCode value=$productionEditor->getCountry()}{assign var=country value=$countries.$countryCode}, {$country|escape}{/if}</li>
		{/foreach}
	</ol>
{/if}

{if count($copyEditors) > 0}
	{if count($copyEditors) == 1}
		<h4>{translate key="user.role.copyeditor"}</h4>
	{else}
		<h4>{translate key="user.role.copyeditors"}</h4>
	{/if}

	<ol class="editorialTeam">
		{foreach from=$copyEditors item=copyEditor}
			<li><a href="javascript:openRTWindow('{url op="editorialTeamBio" path=$copyEditor->getId()}')">{$copyEditor->getFullName()|escape}</a>{if $copyEditor->getLocalizedAffiliation()}, {$copyEditor->getLocalizedAffiliation()|escape}{/if}{if $copyEditor->getCountry()}{assign var=countryCode value=$copyEditor->getCountry()}{assign var=country value=$countries.$countryCode}, {$country|escape}{/if}</li>
		{/foreach}
	</ol>
{/if}

{if count($proofreaders) > 0}
	{if count($proofreaders) == 1}
		<h4>{translate key="user.role.proofreader"}</h4>
	{else}
		<h4>{translate key="user.role.proofreaders"}</h4>
	{/if}

	<ol class="editorialTeam">
		{foreach from=$proofreaders item=proofreader}
			<li><a href="javascript:openRTWindow('{url op="editorialTeamBio" path=$proofreader->getId()}')">{$proofreader->getFullName()|escape}</a>{if $proofreader->getLocalizedAffiliation()}, {$proofreader->getLocalizedAffiliation()|escape}{/if}{if $proofreader->getCountry()}{assign var=countryCode value=$proofreader->getCountry()}{assign var=country value=$countries.$countryCode}, {$country|escape}{/if}</li>
		{/foreach}
	</ol>
{/if}

{include file="common/footer.tpl"}
