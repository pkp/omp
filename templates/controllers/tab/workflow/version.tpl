{**
 * templates/workflow/version.tpl
 *
 * Copyright (c) 2014-2018 Simon Fraser University
 * Copyright (c) 2003-2018 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Version view for workflow stage production
 *}
<div class="pkp_panel_wrapper">

	<div class="pkp_context_sidebar">

		{** edit metadata of this version **}
		<div id="editMetadataDiv" class="pkp_tab_actions">
			<ul class="pkp_workflow_decisions">
				<li>{include file="linkAction/linkAction.tpl" action=$editMetadataLinkAction}</li>
			</ul>
		</div>

		{** stage participants **}
		{capture assign=stageParticipantGridUrl}{url router=$smarty.const.ROUTE_COMPONENT component="grid.users.stageParticipant.StageParticipantGridHandler" op="fetchGrid" submissionId=$submission->getId() stageId=$stageId escape=false}{/capture}
		{load_url_in_div id="stageParticipantGridContainer-version_"|concat:$submissionVersion url=$stageParticipantGridUrl class="pkp_participants_grid"}
	</div>

	<div class="pkp_content_panel">
		{capture assign=productionReadyFilesGridUrl}{url router=$smarty.const.ROUTE_COMPONENT component="grid.files.productionReady.ProductionReadyFilesGridHandler" op="fetchGrid" submissionId=$submission->getId() submissionVersion=$submission->getSubmissionVersion() stageId=$stageId escape=false}{/capture}
		{load_url_in_div id="productionReadyFilesGridDiv-version_"|concat:$submissionVersion url=$productionReadyFilesGridUrl}

		{capture assign=representationsGridUrl}{url router=$smarty.const.ROUTE_COMPONENT component="grid.catalogEntry.PublicationFormatGridHandler" op="fetchGrid" submissionId=$submission->getId() submissionVersion=$submission->getSubmissionVersion() escape=false}{/capture}
		{load_url_in_div id="formatsGridContainer-version_"|concat:$submissionVersion|uniqid url=$representationsGridUrl}
	</div>
</div>
