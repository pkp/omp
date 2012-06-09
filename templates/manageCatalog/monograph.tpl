{**
 * templates/manageCatalog/monograph.tpl
 *
 * Copyright (c) 2003-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Present a monograph in catalog management.
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

{if isset($newReleaseMonographIds[$monographId])}
	{assign var=isNewRelease value=1}
{else}
	{assign var=isNewRelease value=0}
{/if}
<script type="text/javascript">
	// Initialize JS handler.
	$(function() {ldelim}
		$('#{$monographContainerId|escape:"javascript"}').pkpHandler(
			'$.pkp.pages.manageCatalog.MonographHandler',
			{ldelim}
				{* Parameters for MonographHandler *}
				monographId: {$monographId},
				setFeaturedUrlTemplate: '{url|escape:"javascript" op="toggle" path=$monographId|to_array:$assocType:$assocId:"setFeatured":"FEATURED_DUMMY":"SEQ_DUMMY" escape=false}',
				setNewReleaseUrlTemplate: '{url|escape:"javascript" op="toggle" path=$monographId|to_array:$assocType:$assocId:"setNewRelease":"RELEASE_DUMMY" escape=false}',
				isFeatured: {$isFeatured},
				isNewRelease: {$isNewRelease},
				seq: {$featureSequence},
				datePublished: new Date('{$monograph->getDatePublished()|date_format:$datetimeFormatShort|escape:"javascript"}'),
				workflowUrl: '{url|escape:"javascript" router=$smarty.const.ROUTE_PAGE page="workflow" op="access" path=$monographId}',
				catalogUrl: '{url router=$smarty.const.ROUTE_PAGE page="catalog" op="book" path=$monographId}',
				{* Parameters for parent LinkActionHandler *}
				actionRequest: '$.pkp.classes.linkAction.ModalRequest',
				actionRequestOptions: {ldelim}
					title: '{translate|escape:"javascript" key="submission.catalogEntry"}',
					modalHandler: '$.pkp.controllers.modal.AjaxModalHandler',
					url: '{url|escape:"javascript" router=$smarty.const.ROUTE_COMPONENT component="modals.submissionMetadata.CatalogEntryHandler" op="fetch" monographId=$monographId stageId=$smarty.const.WORKFLOW_STAGE_ID_PRODUCTION escape=false}'
				{rdelim}
			{rdelim}
		);
	{rdelim});
</script>

<li class="pkp_manageCatalog_monograph monograph_id_{$monographId|escape}{if !$isFeatured} not_sortable{/if} pkp_helpers_text_center" id="{$monographContainerId|escape}">
	<div class="pkp_manageCatalog_monographDetails pkp_helpers_clear">
		<div class="pkp_manageCatalog_monograph_image">
			{assign var=coverImage value=$monograph->getCoverImage()}
			<img class="pkp_helpers_container_center" height="{$coverImage.thumbnailHeight}" width="{$coverImage.thumbnailWidth}" src="{url router=$smarty.const.ROUTE_COMPONENT component="submission.CoverHandler" op="thumbnail" monographId=$monograph->getId()}" alt="{$monograph->getLocalizedTitle()|escape}" />
		</div>
		<div class="pkp_manageCatalog_monograph_title">
			{null_link_action key=$monograph->getLocalizedTitle()|escape id="publicCatalog-"|concat:$monographId translate=false}
		</div>
		<div class="pkp_manageCatalog_monograph_authorship">
			{$monograph->getAuthorString()|escape}
		</div>
	</div>
	<div class="pkp_manageCatalog_monograph_date">
			{$monograph->getDatePublished()|date_format:$dateFormatShort}
	</div>
	<div class="pkp_manageCatalog_monograph_series">
		{$monograph->getSeriesTitle()|escape}
	</div>
	<div class="pkp_manageCatalog_monograph_actions pkp_linkActions">
			{null_link_action key="submission.editCatalogEntry" id="catalogEntry-"|concat:$monographId} | {null_link_action key="submission.submission" id="itemWorkflow-"|concat:$monographId}
	</div>
	<div class="pkp_manageCatalog_featureTools pkp_helpers_invisible pkp_linkActions submission_actions">
		<ul class="submission_actions">
			<li>
				{if $isFeatured}{assign var="featureImage" value="star_highlighted"}{else}{assign var="featureImage" value="star"}{/if}
				{null_link_action id="featureMonograph-"|concat:$monographId image=$featureImage key="catalog.manage.placeIntoCarousel"}
			</li>
			<li>
				{if $isNewRelease}{assign var="newReleaseImage" value="release_highlighted"}{else}{assign var="newReleaseImage" value="release"}{/if}
				{null_link_action id="releaseMonograph-"|concat:$monographId image=$newReleaseImage key="catalog.manage.newRelease"}
			</li>
		</ul>
	</div>
	<div class="pkp_helpers_clear"></div>
</li>
