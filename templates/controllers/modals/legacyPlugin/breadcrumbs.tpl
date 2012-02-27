{**
 * templates/controllers/modals/legacyPlugin/breadcrumbs.tpl
 *
 * Copyright (c) 2000-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Breadcrumbs for plugins management in modals. This must be used only by plugins
 * that don't have its UI adapted to OMP yet. 
 * FIXME: this must be removed after we modernize all plugins interface.
 *}
<ul class="pkp_helpers_flatlist pkp_structure_breadcrumb align_left">
	{foreach from=$pageHierarchy item=hierarchyLink}
		<li><a href="{$hierarchyLink[0]|escape}">{if not $hierarchyLink[2]}{translate key=$hierarchyLink[1]}{else}{$hierarchyLink[1]|escape}{/if}</a></li>
	{/foreach}
	<li class="current">{if !$requiresFormRequest}<a href="{$currentUrl|escape}" class="current">{/if}{$pageCrumbTitleTranslated}{if !$requiresFormRequest}</a>{/if}</li>
</ul>