{**
 * templates/catalog/monograph.tpl
 *
 * Copyright (c) 2003-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Present a monograph.
 *}
{assign var=monographId value=$monograph->getId()}

{* Generate a unique ID for this monograph *}
{capture assign=monographContainerId}monographContainer-{$listName}-{$monographId}{/capture}

{if isset($featuredMonographIds[$monographId])}
	{assign var=isFeatured value=1}
	{assign var=featureSequence value=$featuredMonographIds[$monographId]}
{else}
	{assign var=isFeatured value=0}
	{assign var=featureSequence value=$smarty.const.REALLY_BIG_NUMBER}
{/if}

<script type="text/javascript">
	// Initialize JS handler.
	$(function() {ldelim}
		$('#{$monographContainerId|escape:"javascript"}').pkpHandler(
			'$.pkp.pages.catalog.MonographHandler',
			{ldelim}
				monographId: {$monographId},
				setFeaturedUrlTemplate: '{url|escape:"javascript" op="setFeatured" path=$monographId|to_array:$featureAssocType:$featureAssocId:"FEATURED_DUMMY":"SEQ_DUMMY" escape=false}',
				isFeatured: {$isFeatured},
				seq: {$featureSequence},
				datePublished: new Date('{$monograph->getDatePublished()|date_format:$datetimeFormatShort|escape:"javascript"}')
			{rdelim}
		);
	{rdelim});
</script>

<li id="{$monographContainerId|escape}" class="pkp_catalog_monograph monograph_id_{$monographId|escape}{if !$isFeatured} not_sortable{/if}">
	<div class="pkp_catalog_monograph_image">
		<!-- FIXME: Image goes here -->
	</div>
	<div class="pkp_catalog_monograph_title">
		{$monograph->getLocalizedTitle()|escape}
	</div>
	<div class="pkp_catalog_monograph_authorship">
		{$monograph->getAuthorString()|escape}
	</div>
	<div class="pkp_catalog_monograph_date">
		{$monograph->getDatePublished()|date_format:$dateFormatShort}
	</div>
	<div class="pkp_catalog_monograph_series">
		{$monograph->getSeriesTitle()|escape}
	</div>
	<div class="pkp_catalog_monograph_abstract">
		<span class="pkp_catalog_monograph_abstractLabel">{translate key="submission.synopsis"}:</span>
		{$monograph->getLocalizedAbstract()|strip_unsafe_html|truncate:80}
	</div>
	<div class="pkp_catalog_organizeTools pkp_helpers_invisible pkp_linkActions">
		{if $isFeatured}
			{assign var="featureImage" value="star_highlighted"}
		{else}
			{assign var="featureImage" value="star"}
		{/if}
		{null_link_action id="feature-monograph-"|concat:$monographId image=$featureImage}
	</div>
	<div class="pkp_helpers_clear"></div>
</li>
