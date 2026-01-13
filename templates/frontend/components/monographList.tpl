{**
 * templates/frontend/components/monographList.tpl
 *
 * Copyright (c) 2014-2021 Simon Fraser University
 * Copyright (c) 2003-2021 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @brief Display a list of monographs.
 *
 * @uses $monographs array List to display -- either monographs or SubmissionSearchResult data
 * @uses $featured array Optional list of monograph IDs to feature in the list
 * @uses $titleKey string Optional translation key for a title for the list
 * @uses $heading string HTML heading element, default: h2
 *}
{if !$heading}
	{assign var="heading" value="h2"}
{/if}
{if !$titleKey}
	{assign var="monographHeading" value=$heading}
{elseif $heading == 'h2'}
	{assign var="monographHeading" value="h3"}
{elseif $heading == 'h3'}
	{assign var="monographHeading" value="h4"}
{else}
	{assign var="monographHeading" value="h5"}
{/if}

<div class="cmp_monographs_list">

	{* Optional title *}
	{if $titleKey}
		<{$heading} class="title">
			{translate key=$titleKey}
		</{$heading}>
	{/if}

	{assign var=counter value=1}
	{foreach name="monographListLoop" from=$monographs item=monograph}
		{* Accept either a list of monographs or data from SubmissionSearchResult. *}
		{if is_array($monograph)}
			{assign var=monograph value=$monograph.submission}{* Unpack SubmissionSearchResult *}
		{/if}

		{if is_array($featured) && array_key_exists($monograph->getId(), $featured)}
			{assign var="isFeatured" value=true}
		{else}
			{assign var="isFeatured" value=false}
		{/if}
		{if $isFeatured}
			{include file="frontend/objects/monograph_summary.tpl" monograph=$monograph isFeatured=$isFeatured heading=$monographHeading}
		{else}
			{if $counter is odd by 1}
				<div class="row">
			{/if}
				{include file="frontend/objects/monograph_summary.tpl" monograph=$monograph isFeatured=$isFeatured heading=$monographHeading}
			{if $counter is even by 1}
				</div>
			{/if}
			{assign var=counter value=$counter+1}
		{/if}
	{/foreach}
	{* Close .row if we have an odd number of titles *}
	{if $counter > 1 && $counter is even by 1}
		</div>
	{/if}
</div>
