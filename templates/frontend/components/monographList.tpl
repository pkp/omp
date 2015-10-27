{**
 * templates/frontend/components/monographList.tpl
 *
 * Copyright (c) 2014-2015 Simon Fraser University Library
 * Copyright (c) 2003-2015 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @brief Display a list of monographs.
 *
 * @uses $monographs array List of monographs to display
 * @uses $featured array Optional list of monograph IDs to feature in the list
 * @uses $titleKey string Optional translation key for a title for the list
 *}
<div class="cmp_monographs_list">

	{* Optional title *}
	{if $titleKey}
		<h3 class="title">
			{translate key=$titleKey}
		</h3>
	{/if}

	{* Show featured items first *}
	{if $featured && count($featured) > 0}
		{foreach from=$featured key=id item=array_key}
			{include file="frontend/objects/monograph_summary.tpl" monograph=$monographs[$id] isFeatured=1}
		{/foreach}
	{/if}

	{assign var=counter value=1}
	{foreach name="monographListLoop" from=$monographs item=monograph key=key}
		{if is_array($featured) && array_key_exists($key, $featured)}
			{php}continue;{/php}
		{/if}
		{if $counter is odd by 1}
			<div class="row">
		{/if}
			{include file="frontend/objects/monograph_summary.tpl" monograph=$monograph}
		{if $counter is even by 1}
			</div>
		{/if}
		{assign var=counter value=$counter+1}
	{/foreach}
	{* Close .row if we have an odd number of titles *}
	{if $counter > 1 && $counter is even by 1}
		</div>
	{/if}
</div>
