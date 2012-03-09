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
			{foreach from=spotlights item=spotlight}
				{assign var="item" value=$spotlight->getSpotlightItem()}
				<li class="pkp_sidebar_spotlight" id="sidebarSpotlight{$spotlight->getId()|escape}">
					{if $spotlight>getAssocType() == 3} {* SPOTLIGHT_TYPE_BOOK *}
						<a href="{url router=$smarty.const.ROUTE_PAGE page="catalog" op="book" path=$publishedMonograph->getId()}"><img src="{url router=$smarty.const.ROUTE_COMPONENT component="submission.CoverHandler" op="thumbnail" monographId=$item->getId()}" /></a>
						<div class="pkp_sidebar_spotlight_monographTitle pkp_helpers_clear">{$item->getLocalizedTitle()|strip_unsafe_html}</div>
						<div class="pkp_sidebar_spotlight_monographAbstract">{$item->getLocalizedAbstract()|strip_unsafe_html}</div>
					{/if}
				</li>
			{/foreach}
		</ul>
	{else}
		<p>{translate key="plugins.block.spotlight.noSpotlights"}</p>
	{/if}
</div>
