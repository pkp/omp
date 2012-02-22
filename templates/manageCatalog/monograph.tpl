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

<script type="text/javascript">
	// Initialize JS handler.
	$(function() {ldelim}
		$('#{$monographContainerId|escape:"javascript"}').pkpHandler(
			'$.pkp.pages.manageCatalog.MonographHandler',
			{ldelim}
				{* Parameters for MonographHandler *}
				monographId: {$monographId},
				setFeaturedUrlTemplate: '{url|escape:"javascript" op="setFeatured" path=$monographId|to_array:$featureAssocType:$featureAssocId:"FEATURED_DUMMY":"SEQ_DUMMY" escape=false}',
				isFeatured: {$isFeatured},
				seq: {$featureSequence},
				datePublished: new Date('{$monograph->getDatePublished()|date_format:$datetimeFormatShort|escape:"javascript"}'),
				workflowUrl: '{url|escape:"javascript" router=$smarty.const.ROUTE_PAGE page="workflow" op="access" path=$monographId}',
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

<li class="pkp_manageCatalog_monograph monograph_id_{$monographId|escape}{if !$isFeatured} not_sortable{/if}" id="{$monographContainerId|escape}">
	<div class="pkp_manageCatalog_monograph_image">
		<img src="{url router=$smarty.const.ROUTE_COMPONENT component="submission.CoverHandler" op="thumbnail" monographId=$monograph->getId()}" alt="{$monograph->getLocalizedTitle()|escape}" />
	</div>
	<div class="pkp_manageCatalog_monograph_actions pkp_linkActions">
		{fbvFormSection list="true"}
			<li>{null_link_action key="submission.catalogEntry" id="catalogEntry-"|concat:$monographId image="information"}</li>
			<li>{null_link_action key="submission.submission" id="workflow-"|concat:$monographId image="information"}</li>
		{/fbvFormSection}
	</div>
	<div class="pkp_manageCatalog_monograph_title pkp_helpers_clear">
		{$monograph->getLocalizedTitle()|escape}
	</div>
	<div class="pkp_manageCatalog_monograph_authorship">
		{$monograph->getAuthorString()|escape}
	</div>
	<div class="pkp_manageCatalog_monograph_date">
		{$monograph->getDatePublished()|date_format:$dateFormatShort}
	</div>
	<div class="pkp_manageCatalog_monograph_series">
		{$monograph->getSeriesTitle()|escape}
	</div>
	<div class="pkp_manageCatalog_monograph_abstract">
		<span class="pkp_manageCatalog_monograph_abstractLabel">{translate key="submission.synopsis"}:</span>
		{$monograph->getLocalizedAbstract()|strip_unsafe_html|truncate:80}
	</div>
	<div class="pkp_manageCatalog_organizeTools pkp_helpers_invisible pkp_linkActions">
		{if $isFeatured}
			{assign var="featureImage" value="star_highlighted"}
		{else}
			{assign var="featureImage" value="star"}
		{/if}
		{null_link_action id="feature-monograph-"|concat:$monographId image=$featureImage}
	</div>
	<div class="pkp_helpers_clear"></div>
</li>
