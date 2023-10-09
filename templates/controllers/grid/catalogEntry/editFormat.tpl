{**
 * templates/controllers/grid/catalogEntry/editFormat.tpl
 *
 * Copyright (c) 2014-2021 Simon Fraser University
 * Copyright (c) 2003-2021 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
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
		<li>
			<a href="{url router=PKPApplication::ROUTE_COMPONENT op="editFormatTab" submissionId=$submissionId representationId=$representationId publicationId=$publicationId}">
				{translate key="common.edit"}
			</a>
		</li>

		{if isset($representationId)}
			<li>
				<a href="{url router=PKPApplication::ROUTE_COMPONENT op="editFormatMetadata" submissionId=$submissionId representationId=$representationId publicationId=$publicationId}">
					{translate key="submission.informationCenter.metadata"}
				</a>
			</li>
		{/if}

		{if !$remoteRepresentation && $representationId}
			{if $showIdentifierTab}
				<li><a href="{url router=PKPApplication::ROUTE_COMPONENT op="identifiers" submissionId=$submissionId representationId=$representationId publicationId=$publicationId}">{translate key="submission.identifiers"}</a></li>
			{/if}
		{/if}
	</ul>
</div>
