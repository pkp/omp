{**
 * templates/management/tools/index.tpl
 *
 * Copyright (c) 2014-2016 Simon Fraser University Library
 * Copyright (c) 2003-2016 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Settings index.
 *}
{strip}
{assign var="pageTitle" value="manager.tools"}
{include file="common/header.tpl"}
{/strip}

<h3>{translate key="manager.tools"}</h3>
<div class="unit size1of2">
	<h4>{translate key="navigation.tools.importExport"}</h4>
	<p>{translate key="manager.tools.importExport"}</p>
	<a href="{url page="management" op="importexport"}" class="button defaultButton">{translate key="common.takeMeThere"}</a>
</div>
<div class="unit size1of2">
	<h4>{translate key="navigation.tools.statistics"}</h4>
	<p>{translate key="manager.tools.statistics"}</p>
	<a href="{url page="management" op="statistics"}" class="button defaultButton">{translate key="common.takeMeThere"}</a>
</div>

{include file="common/footer.tpl"}
