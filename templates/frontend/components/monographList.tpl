{**
 * templates/frontend/components/monographList.tpl
 *
 * Copyright (c) 2014-2017 Simon Fraser University
 * Copyright (c) 2003-2017 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @brief Display a list of monographs.
 *
 * @uses $monographs array List of monographs to display
 * @uses $featured array Optional list of monograph IDs to feature in the list
 * @uses $titleKey string Optional translation key for a title for the list
 * @uses $heading string HTML heading element, default: h2
 *}
{if !$heading}
	{assign var="heading" value="h2"}
{/if}
<div class="cmp_monographs_list">

	{* Optional title *}
	{if $titleKey}
		<{$heading} class="title">
			{translate key=$titleKey}
		</{$heading}>
	{/if}

	{assign var=counter value=1}
	{foreach from=$monographs item=monograph}
		{if $featured && in_array($monograph->getId(), $featured)}
			{assign var="isFeatured" value=true}
		{else}
			{assign var="isFeatured" value=false}
		{/if}
		{if $counter is odd by 1}
			<div class="row">
		{/if}
			{include file="frontend/objects/monograph_summary.tpl" monograph=$monograph isFeatured=$isFeatured}
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
