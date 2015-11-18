{**
 * templates/frontend/components/monographList_featured.tpl
 *
 * Copyright (c) 2014-2015 Simon Fraser University Library
 * Copyright (c) 2003-2015 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @brief Display a list of featured monographs
 *
 * @uses $monographs array List of monographs to display
 * @uses $titleKey string Optional translation key for a title for the list
 *}
<div class="cmp_monographs_list_featured">

	{* Optional title *}
	{if $titleKey}
		<h3 class="title">
			{translate key=$titleKey}
		</h3>
	{/if}

	{if $monographs && count($monographs) > 0}
		<ul>
			{foreach from=$monographs item=monograph}
				<li class="monograph">
					<a href="{url page="catalog" op="book" path=$monograph->getId()}" class="cover">
						<img alt="{translate key="catalog.coverImageTitle" monographTitle=$monograph->getLocalizedFullTitle()|strip_tags|escape}" src="{url router=$smarty.const.ROUTE_COMPONENT component="submission.CoverHandler" op="thumbnail" submissionId=$monograph->getId() random=$monograph->getId()|uniqid}" />
					</a>
				</li>
			{/foreach}
		</ul>
	{/if}
</div>
