{**
 * templates/settings/index.tpl
 *
 * Copyright (c) 2003-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Settings index.
 *}

{strip}
{assign var="pageTitle" value="manager.settings"}
{include file="common/header.tpl"}
{/strip}

<h3>{translate key="manager.settings"}</h3>

<div class="unit size1of2">
	<h4>{translate key="manager.settings.press"}</h4>
	<p>{translate key="manager.settings.pressDescription"}</p>
	<a href="{url page="manager" op="setup"}" class="button defaultButton">{translate key="common.takeMeThere"}</a>
</div>
<div class="unit size2of2 lastUnit">
	<h4>{translate key="manager.settings.data"}</h4>
	<p>{translate key="manager.settings.dataDescription"}</p>
	<a href="{url page="manager" op="index"}" class="button defaultButton">{translate key="common.takeMeThere"}</a>
</div>
<div class="unit size1of2">
	<h4>{translate key="manager.settings.system"}</h4>
	<p>{translate key="manager.settings.systemDescription"}</p>
	<a href="{url page="manager" op="system"}" class="button defaultButton">{translate key="common.takeMeThere"}</a>
</div>
<div class="unit size2of2 lastUnit">
	<h4>{translate key="settings.access"}</h4>
	<p>{translate key="settings.accessDescription"}</p>
	<a href="{url page="settings" op="access"}" class="button defaultButton">{translate key="common.takeMeThere"}</a>
</div>

{include file="common/footer.tpl"}
