{**
 * templates/index/spotlights.tpl
 *
 * Copyright (c) 2003-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Display spotlights on a press' home page.
 *}
<div id="spotlightsHome">
	<ul>
		{foreach from=$spotlights item=spotlight}
			{assign var="item" value=$spotlight->getSpotlightItem()}
				{if $spotlight->getAssocType() == 3} {* SPOTLIGHT_TYPE_BOOK *}
					{include file="catalog/monograph.tpl" publishedMonograph=$item inline=true}
				{/if}
		{/foreach}
	</ul>
</div>