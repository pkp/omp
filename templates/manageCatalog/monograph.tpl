{**
 * templates/manageCatalog/monograph.tpl
 *
 * Copyright (c) 2014-2015 Simon Fraser University Library
 * Copyright (c) 2003-2015 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Present a monograph in catalog management.
 *}
{assign var=submissionId value=$monograph->getId()}

{* Generate a unique ID for this monograph *}
{capture assign=monographContainerId}monographContainer-{$listName}-{$submissionId}{/capture}

{if isset($featuredMonographIds[$submissionId])}
	{assign var=isFeatured value=1}
	{assign var=featureSequence value=$featuredMonographIds[$submissionId]}
{else}
	{assign var=isFeatured value=0}
	{assign var=featureSequence value=$smarty.const.REALLY_BIG_NUMBER}
{/if}

{if isset($newReleaseMonographIds[$submissionId])}
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
				submissionId: {$submissionId},
				setFeaturedUrlTemplate: '{url|escape:"javascript" op="toggle" path=$submissionId|to_array:$assocType:$assocId:"setFeatured":"FEATURED_DUMMY":"SEQ_DUMMY" escape=false}',
				setNewReleaseUrlTemplate: '{url|escape:"javascript" op="toggle" path=$submissionId|to_array:$assocType:$assocId:"setNewRelease":"RELEASE_DUMMY" escape=false}',
				isFeatured: {$isFeatured},
				isNewRelease: {$isNewRelease},
				seq: {$featureSequence},
				datePublished: new Date('{$monograph->getDatePublished()|date_format:$datetimeFormatShort|escape:"javascript"}'),
				workflowUrl: '{url|escape:"javascript" router=$smarty.const.ROUTE_PAGE page="workflow" op="access" path=$submissionId}',
				catalogUrl: '{url router=$smarty.const.ROUTE_PAGE page="catalog" op="book" path=$submissionId}',
				{* Parameters for parent LinkActionHandler *}
				actionRequest: '$.pkp.classes.linkAction.ModalRequest',
				actionRequestOptions: {ldelim}
					title: '{translate|escape:"javascript" key="submission.catalogEntry"}',
					modalHandler: '$.pkp.controllers.modal.AjaxModalHandler',
					titleIcon: 'modal_more_info',
					url: '{url|escape:"javascript" router=$smarty.const.ROUTE_COMPONENT component="modals.submissionMetadata.CatalogEntryHandler" op="fetch" submissionId=$submissionId stageId=$smarty.const.WORKFLOW_STAGE_ID_PRODUCTION escape=false}'
				{rdelim}
			{rdelim}
		);
	{rdelim});
</script>

<li class="pkp_manageCatalog_monograph monograph_id_{$submissionId|escape}{if !$isFeatured} not_sortable{/if} pkp_helpers_text_center" id="{$monographContainerId|escape}">
	<div class="pkp_manageCatalog_monographDetails pkp_helpers_clear">
		<div class="pkp_manageCatalog_monograph_image">
			{include file="controllers/monographList/coverImage.tpl" monograph=$monograph}
		</div>
		<div class="pkp_manageCatalog_monograph_title">
			{assign var="monographTitle" value=$monograph->getLocalizedPrefix()|concat:' ':$monograph->getLocalizedTitle()|strip_unsafe_html}
			{null_link_action key=$monographTitle id="publicCatalog-"|concat:$submissionId translate=false}
		</div>
		<div class="pkp_manageCatalog_monograph_authorship">
			{$monograph->getAuthorString()|escape}
		</div>
	</div>
	<div class="pkp_manageCatalog_monograph_date">
			{$monograph->getDatePublished()|date_format:$dateFormatShort}
	</div>
	<div class="pkp_manageCatalog_monograph_series">
		{$monograph->getSeriesTitle()|strip_unsafe_html}
	</div>
	<div class="pkp_manageCatalog_monograph_actions pkp_linkActions">
			{null_link_action key="submission.editCatalogEntry" id="catalogEntry-"|concat:$submissionId} | {null_link_action key="submission.submission" id="itemWorkflow-"|concat:$submissionId}
	</div>
	<div class="pkp_manageCatalog_featureTools pkp_helpers_invisible pkp_linkActions pkp_helpers_text_left">
		<ul>
			<li>
				{if $isFeatured}{assign var="featureImage" value="star_highlighted"}{else}{assign var="featureImage" value="star"}{/if}
				{null_link_action id="featureMonograph-"|concat:$submissionId image=$featureImage}
			</li>
			<li>
				{if $isNewRelease}{assign var="newReleaseImage" value="release_highlighted"}{else}{assign var="newReleaseImage" value="release"}{/if}
				{null_link_action id="releaseMonograph-"|concat:$submissionId image=$newReleaseImage}
			</li>
		</ul>
	</div>
	<div class="pkp_helpers_clear"></div>
</li>
