{**
 * templates/manager/importexport/plugins.tpl
 *
 * Copyright (c) 2014-2016 Simon Fraser University Library
 * Copyright (c) 2003-2016 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * List available import/export plugins.
 *}
{include file="common/header.tpl" pageTitle="manager.importExport"}

{help file="chapter7/import-export.md" class="pkp_helpers_align_right"}

<ul>
	{foreach from=$plugins item=plugin}
	<li><a href="{url op="importexport" path="plugin"|to_array:$plugin->getName()}">{$plugin->getDisplayName()|escape}</a>:&nbsp;{$plugin->getDescription()|escape}</li>
	{/foreach}
</ul>

{include file="common/footer.tpl"}

