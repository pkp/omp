{**
 * plugins/blocks/spotlight/block.tpl
 *
 * Copyright (c) 2003-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Add spotlights to the sidebar
 *}
<div class="block" id="sidebarSpotlight">
	{if $spotlights && $spotlights|@count > 0}
		<span class="blockTitle">{translate key="plugins.block.spotlight.title"}</span>
		<ul>
			{foreach from=$spotlights item=spotlight}
				{assign var="item" value=$spotlight->getSpotlightItem()}
				{if $spotlight->getAssocType() == $smarty.const.SPOTLIGHT_TYPE_BOOK}
					{include file="catalog/monograph.tpl" publishedMonograph=$item}
				{/if}
			{/foreach}
		</ul>
	{/if}
</div>
