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
 *}
<div class="cmp_monographs_list">
	{foreach name="monographListLoop" from=$monographs item=monograph}
		{if $smarty.foreach.monographListLoop.iteration is odd by 1}
			<div class="row">
		{/if}
			{include file="frontend/objects/monograph_summary.tpl" monograph=$monograph}
		{if $smarty.foreach.monographListLoop.iteration is even by 1}
			</div>
		{/if}
	{/foreach}
	{* Close .row if we have an odd number of titles *}
	{if count($monographs) > 0 && $smarty.foreach.monographListLoop.iteration is odd by 1}
		</div>
	{/if}
</div>
