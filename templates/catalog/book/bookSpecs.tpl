{**
 * templates/catalog/book/bookSpecs.tpl
 *
 * Copyright (c) 2003-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Display the book specs portion of the public-facing book view.
 *}

<script type="text/javascript">
	// Initialize JS handler for catalog header.
	$(function() {ldelim}
		$('#bookAccordion').accordion();
	{rdelim});
</script>

<div class="bookSpecs">
	<img src="{url router=$smarty.const.ROUTE_COMPONENT component="submission.CoverHandler" op="cover" monographId=$publishedMonograph->getId()}" alt="{translate|escape key="monograph.coverImage"}"/>
	<div id="bookAccordion">
		<h3><a href="#">{translate key="catalog.publicationInfo"}</a></h3>
		<div class="publicationInfo">
			<div class="dateAdded">{translate key="catalog.dateAdded" dateAdded=$publishedMonograph->getDatePublished()|date_format:$dateFormatShort}</div>
		</div>

		{assign var=assignedPublicationFormats value=$publishedMonograph->getAssignedPublicationFormats()}
		{foreach from=$assignedPublicationFormats item=assignedPublicationFormat}
			<h3><a href="#">{$assignedPublicationFormat->getLocalizedTitle()|escape}</a></h3>
			<div class="assignedPublicationFormat">
				{assign var=notFirst value=0}
				{if $assignedPublicationFormat->getWidth()}
					{$assignedPublicationFormat->getWidth()|escape} {$assignedPublicationFormat->getWidthUnitCode()|escape}
					{assign var=notFirst value=1}
				{/if}
				{if $assignedPublicationFormat->getHeight()}
					{if $notFirst} x {/if}
					{$assignedPublicationFormat->getHeight()|escape} {$assignedPublicationFormat->getHeightUnitCode()|escape}
					{assign var=notFirst value=1}
				{/if}
				{if $assignedPublicationFormat->getThickness()}
					{if $notFirst} x {/if}
					{$assignedPublicationFormat->getThickness()|escape} {$assignedPublicationFormat->getThicknessUnitCode()|escape}
					{assign var=notFirst value=1}
				{/if}
			</div>
		{/foreach}{* $assignedPublicationFormats *}

		{if !$categories->wasEmpty()}
			<h3><a href="#">{translate key="catalog.relatedCategories}</a></h3>
			<ul class="relatedCategories">
				{iterate from=categories item=category}
					<li><a href="{url op="category" path=$category->getPath()}">{$category->getLocalizedTitle()|strip_unsafe_html}</a></li>
				{/iterate}
			</ul>
		{/if}
	</div>
</div>
