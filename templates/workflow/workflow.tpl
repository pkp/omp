{**
 * templates/workflow/workflow.tpl
 *
 * Copyright (c) 2014-2021 Simon Fraser University
 * Copyright (c) 2003-2021 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * Display the workflow tab structure.
 *}
{extends file="layouts/backend.tpl"}

{block name="page"}
	<pkp-header :is-one-line="true" class="pkpWorkflow__header">
		<h1 class="pkpWorkflow__identification">
			<badge
				v-if="submission.status === getConstant('STATUS_PUBLISHED')"
				class="pkpWorkflow__identificationStatus"
				:is-success="true"
			>
				{translate key="publication.status.published"}
			</badge>
			<badge
				v-else-if="submission.status === getConstant('STATUS_SCHEDULED')"
				class="pkpWorkflow__identificationStatus"
				:is-primary="true"
			>
				{translate key="publication.status.scheduled"}
			</badge>
			<badge
				v-else-if="submission.status === getConstant('STATUS_DECLINED')"
				class="pkpWorkflow__identificationStatus"
				:is-warnable="true"
			>
				{translate key="common.declined"}
			</badge>
			<span class="pkpWorkflow__identificationId">{{ submission.id }}</span>
			<span class="pkpWorkflow__identificationDivider">/</span>
			<span class="pkpWorkflow__identificationAuthor">
				{{ currentPublication.authorsStringShort }}
			</span>
			<span class="pkpWorkflow__identificationDivider">/</span>
			<span class="pkpWorkflow__identificationTitle">
				{{ localizeSubmission(currentPublication.fullTitle, currentPublication.locale) }}
			</span>
		</h1>
		<template slot="actions">
			<pkp-button
				v-if="submission.status === getConstant('STATUS_PUBLISHED')"
				element="a"
				:href="submission.urlPublished"
			>
				{translate key="common.view"}
			</pkp-button>
			<dropdown
				class="pkpWorkflow__identificationWorkType"
				:label="workTypeLabel"
				submenu-label="{translate key="submission.workflowType.change"}"
			>
				<ul>
					<li v-if="isLoadingWorkType">
						<spinner></spinner>
					</li>
					<template v-else>
						<li>
							<button
								class="pkpDropdown__action"
								:disabled="submission.workType === getConstant('WORK_TYPE_EDITED_VOLUME')"
								@click="setAsEditedVolume()"
							>
								{{ editedVolumeLabel }}
							</button>
						</li>
						<li>
							<button
								class="pkpDropdown__action"
								:disabled="submission.workType === getConstant('WORK_TYPE_AUTHORED_WORK')"
								@click="setAsAuthoredWork()"
							>
								{{ monographLabel }}
							</button>
						</li>
					</template>
				</ul>
			</dropdown>
			{if $canAccessEditorialHistory}
				<pkp-button
					ref="activityButton"
					@click="openActivity"
				>
					{translate key="editor.activityLog"}
				</pkp-button>
			{/if}
			<pkp-button
				ref="library"
				@click="openLibrary"
			>
				{translate key="editor.submissionLibrary"}
			</pkp-button>
		</template>
	</pkp-header>
	<tabs default-tab="workflow" :track-history="true">
		<tab id="workflow" label="{translate key="manager.workflow"}">
			<script type="text/javascript">
				// Initialize JS handler.
				$(function() {ldelim}
					$('#submissionWorkflow').pkpHandler(
						'$.pkp.pages.workflow.WorkflowHandler'
					);
				{rdelim});
			</script>

			<div id="submissionWorkflow" class="pkp_submission_workflow">
				{include file="controllers/notification/inPlaceNotification.tpl" notificationId="workflowNotification" requestOptions=$workflowNotificationRequestOptions}
				{capture assign=submissionProgressBarUrl}{url op="submissionProgressBar" submissionId=$submission->getId() stageId=$requestedStageId contextId="submission" escape=false}{/capture}
				{load_url_in_div id="submissionProgressBarDiv" url=$submissionProgressBarUrl}
			</div>
		</tab>
		<tab id="marketing" label="{translate key="settings.libraryFiles.category.marketing"}">
			<tabs :is-side-tabs="true" :track-history="true" :label="publicationTabsLabel">
				<tab id="audience" label="{translate key="monograph.audience"}">
					<pkp-form v-bind="components.{$smarty.const.FORM_AUDIENCE}" @set="set" />
				</tab>
				<tab id="representatives" label="{translate key="grid.catalogEntry.representatives"}">
					{capture assign=representativesGridUrl}{url router=PKPApplication::ROUTE_COMPONENT component="grid.catalogEntry.RepresentativesGridHandler" op="fetchGrid" submissionId=$submission->getId() escape=false}{/capture}
					{load_url_in_div id="representativesGridContainer" url=$representativesGridUrl}
				</tab>
				<tab id="publicationDates" label="{translate key="grid.catalogEntry.publicationDates"}">
					<pkp-form v-bind="components.{$smarty.const.FORM_PUBLICATION_DATES}" @set="set" />
				</tab>
			</tab>
		</tab>
		{if $canAccessPublication}
			<tab id="publication" label="{translate key="submission.publication"}">
				<div class="pkpPublication" ref="publication" aria-live="polite">
					<pkp-header class="pkpPublication__header" :is-one-line="false">
						<span class="pkpPublication__status">
							<strong>{{ statusLabel }}</strong>
							<span v-if="workingPublication.status === getConstant('STATUS_QUEUED') && workingPublication.id === currentPublication.id" class="pkpPublication__statusUnpublished">{translate key="publication.status.unscheduled"}</span>
							<span v-else-if="workingPublication.status === getConstant('STATUS_SCHEDULED')">{translate key="publication.status.scheduled"}</span>
							<span v-else-if="workingPublication.status === getConstant('STATUS_PUBLISHED')" class="pkpPublication__statusPublished">{translate key="publication.status.published"}</span>
							<span v-else class="pkpPublication__statusUnpublished">{translate key="publication.status.unpublished"}</span>
						</span>
						<span v-if="publicationList.length > 1" class="pkpPublication__version">
							<strong tabindex="0">{{ versionLabel }}</strong> {{ workingPublication.version }}
							<dropdown
								class="pkpPublication__versions"
								label="{translate key="publication.version.all"}"
								:is-link="true"
								submenu-label="{translate key="common.submenu"}"
							>
								<ul>
									<li v-for="publication in publicationList" :key="publication.id">
										<button
											class="pkpDropdown__action"
											:disabled="publication.id === workingPublication.id"
											@click="setWorkingPublicationById(publication.id)"
										>
											{{ publication.version }} /
											<template v-if="publication.status === getConstant('STATUS_QUEUED') && publication.id === currentPublication.id">{translate key="publication.status.unscheduled"}</template>
											<template v-else-if="publication.status === getConstant('STATUS_SCHEDULED')">{translate key="publication.status.scheduled"}</template>
											<template v-else-if="publication.status === getConstant('STATUS_PUBLISHED')">{{ publication.datePublished }}</template>
											<template v-else>{translate key="publication.status.unpublished"}</template>
										</button>
									</li>
								</ul>
							</dropdown>
						</span>
						{if $canAccessProduction}
							<template slot="actions">
								<pkp-button
									v-if="workingPublication.status === getConstant('STATUS_QUEUED')"
									ref="publish"
									@click="openPublish"
								>
									{translate key="publication.publish"}
								</pkp-button>
								<pkp-button
									v-else-if="workingPublication.status === getConstant('STATUS_SCHEDULED')"
									:is-warnable="true"
									@click="openUnpublish"
								>
									{translate key="publication.unschedule"}
								</pkp-button>
								<pkp-button
									v-else-if="workingPublication.status === getConstant('STATUS_PUBLISHED')"
									:is-warnable="true"
									@click="openUnpublish"
								>
									{translate key="publication.unpublish"}
								</pkp-button>
								<pkp-button
									v-if="canCreateNewVersion"
									ref="createVersion"
									@click="openCreateVersionPrompt"
								>
									{translate key="publication.createVersion"}
								</pkp-button>
							</template>
						{/if}
					</pkp-header>
					<div
						v-if="workingPublication.status === getConstant('STATUS_PUBLISHED')"
						class="pkpPublication__versionPublished"
					>
						{translate key="publication.editDisabled"}
					</div>
					<tabs class="pkpPublication__tabs" :is-side-tabs="true" :track-history="true" :label="publicationTabsLabel">
						<tab id="titleAbstract" label="{translate key="publication.titleAbstract"}">
							<pkp-form v-bind="components.{$smarty.const.FORM_TITLE_ABSTRACT}" @set="set" />
						</tab>
						<tab id="contributors" label="{translate key="publication.contributors"}">
							<contributors-list-panel
								v-bind="components.contributors"
								class="pkpWorkflow__contributors"
								@set="set"
								:items="workingPublication.authors"
								:publication="workingPublication"
								:publication-api-url="submissionApiUrl + '/publications/' + workingPublication.id"
								@updated:publication="setWorkingPublication"
								@updated:contributors="setContributors"
							></contributors-list-panel>
						</tab>
						<tab id="chapters" label="{translate key="submission.chapters"}">
							<div id="chapters-grid" ref="chapters">
								<spinner></spinner>
							</div>
						</tab>
						{if $metadataEnabled}
							<tab id="metadata" label="{translate key="submission.informationCenter.metadata"}">
								<pkp-form v-bind="components.{$smarty.const.FORM_METADATA}" @set="set" />
							</tab>
						{/if}
						<tab v-if="supportsReferences" id="citations" label="{translate key="submission.citations"}">
							<pkp-form v-bind="components.{$smarty.const.FORM_CITATIONS}" @set="set" />
						</tab>
						{if $identifiersEnabled}
							<tab id="identifiers" label="{translate key="submission.identifiers"}">
								<pkp-form v-bind="components.{$smarty.const.FORM_PUBLICATION_IDENTIFIERS}" @set="set" />
							</tab>
						{/if}
						{if $canAccessProduction}
							<tab id="publicationFormats" label="{translate key="submission.publicationFormats"}">
								<div id="representations-grid" ref="representations">
									<spinner></spinner>
								</div>
							</tab>
							<tab id="catalogEntry" label="{translate key="publication.catalogEntry"}">
								<pkp-form v-bind="components.{$smarty.const.FORM_CATALOG_ENTRY}" @set="set" />
							</tab>
							<tab id="license" label="{translate key="publication.publicationLicense"}">
								<pkp-form v-bind="components.{$smarty.const.FORM_PUBLICATION_LICENSE}" @set="set" />
							</tab>
						{/if}
						{call_hook name="Template::Workflow::Publication"}
					</tabs>
					<span class="pkpPublication__mask" :class="publicationMaskClasses">
						<spinner></spinner>
					</span>
				</div>
			</tab>
		{/if}
		{call_hook name="Template::Workflow"}
	</tabs>
{/block}
