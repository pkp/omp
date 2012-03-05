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

		{assign var=publicationFormats value=$publishedMonograph->getPublicationFormats()}
		{foreach from=$publicationFormats item=publicationFormat}
			{if $publicationFormat->getIsAvailable()}
			<h3><a href="#">{$publicationFormat->getLocalizedTitle()|escape}</a></h3>
			<div class="publicationFormat">
				<div id="bookDimensionSpecs">
				{assign var=notFirst value=0}
				{if $publicationFormat->getWidth()}
					{$publicationFormat->getWidth()|escape} {$publicationFormat->getWidthUnitCode()|escape}
					{assign var=notFirst value=1}
				{/if}
				{if $publicationFormat->getHeight()}
					{if $notFirst} x {/if}
					{$publicationFormat->getHeight()|escape} {$publicationFormat->getHeightUnitCode()|escape}
					{assign var=notFirst value=1}
				{/if}
				{if $publicationFormat->getThickness()}
					{if $notFirst} x {/if}
					{$publicationFormat->getThickness()|escape} {$publicationFormat->getThicknessUnitCode()|escape}
					{assign var=notFirst value=1}
				{/if}
				</div>
				{assign var=identificationCodes value=$publicationFormat->getIdentificationCodes()}
				{assign var=identificationCodes value=$identificationCodes->toArray()}
				{if $identificationCodes}
					<div id="bookIdentificationSpecs">
					{foreach from=$identificationCodes item=identificationCode}
						<div id="bookIdentificationSpecs-{$identificationCode->getCode()|escape}">
							{$identificationCode->getNameForONIXCode()|escape}: {$identificationCode->getValue()|escape}
						</div>
					{/foreach}{* identification codes *}
					</div>
				{/if}{* $identificationCodes *}
			</div>
			{/if}{* $publicationFormat->getIsAvailable() *}
		{/foreach}{* $publicationFormats *}

		{if !$categories->wasEmpty()}
			<h3><a href="#">{translate key="catalog.relatedCategories}</a></h3>
			<ul class="relatedCategories">
				{iterate from=categories item=category}
					<li><a href="{url op="category" path=$category->getPath()}">{$category->getLocalizedTitle()|strip_unsafe_html}</a></li>
				{/iterate}{* categories *}
			</ul>
		{/if}{* !$categories->wasEmpty() *}
	</div>
</div>
