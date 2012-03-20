{**
 * plugins/blocks/spotlight/block.tpl
 *
 * Copyright (c) 2003-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Add spotlights to the sidebar
 *}
<div class="block" id="sidebarSpotlight">
	<span class="blockTitle">{translate key="plugins.block.spotlight.title"}</span>
	{if $spotlights|@count > 0}
		<ul>
			{foreach from=$spotlights item=spotlight}
				{assign var="item" value=$spotlight->getSpotlightItem()}
				{if $spotlight->getAssocType() == 3} {* SPOTLIGHT_TYPE_BOOK *}
					{include file="catalog/monograph.tpl" publishedMonograph=$item}
				{/if}
			{/foreach}
		</ul>
	{else}
		<p>{translate key="plugins.block.spotlight.noSpotlights"}</p>
	{/if}
</div>
