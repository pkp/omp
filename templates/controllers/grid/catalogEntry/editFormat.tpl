{**
 * templates/controllers/grid/catalogEntry/editFormat.tpl
 *
 * Copyright (c) 2014-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * The "edit publication format" tabset.
 *}
<script type="text/javascript">
	// Attach the JS file tab handler.
	$(function() {ldelim}
		$('#editPublicationFormatMetadataTabs').pkpHandler('$.pkp.controllers.TabHandler');
	{rdelim});
</script>
<div id="editPublicationFormatMetadataTabs">
	<ul>
		<li><a href="{url router=$smarty.const.ROUTE_COMPONENT op="editFormatTab" submissionId=$submissionId representationId=$representationId publicationId=$publicationId}">{translate key="common.edit"}</a></li>
		{if !$remoteRepresentation && $representationId}
			<li><a href="{url router=$smarty.const.ROUTE_COMPONENT op="identifiers" submissionId=$submissionId representationId=$representationId publicationId=$publicationId}">{translate key="submission.identifiers"}</a></li>
			<li><a href="{url router=$smarty.const.ROUTE_COMPONENT op="editFormatMetadata" submissionId=$submissionId representationId=$representationId publicationId=$publicationId}">{translate key="submission.informationCenter.metadata"}</a></li>
		{/if}
	</ul>
</div>
