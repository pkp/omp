{**
 * controllers/tab/settings/policies/form/policiesForm.tpl
 *
 * Copyright (c) 2003-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Policies management form.
 *
 *}

<script type="text/javascript">
	$(function() {ldelim}
		// Attach the form handler.
		$('#policiesForm').pkpHandler('$.pkp.controllers.form.AjaxFormHandler');
	{rdelim});
</script>

<form class="pkp_form" id="policiesForm" method="post" action="{url router=$smarty.const.ROUTE_COMPONENT component="tab.settings.PressSettingsTabHandler" op="saveFormData" tab="policies"}">
	{include file="controllers/notification/inPlaceNotification.tpl" notificationId="policiesFormNotification"}
	{include file="controllers/tab/settings/wizardMode.tpl" wizardMode=$wizardMode}

	{url|assign:"sampleCopyrightWordingUrl" page="information" op="sampleCopyrightWording"}
	{translate|assign:"authorCopyrightNoticeDescription" key="manager.setup.authorCopyrightNotice.description" sampleCopyrightWordingUrl=$sampleCopyrightWordingUrl}
	{* FIXME #6843 JM: The above workaround provided by Juan to place a dynamic link into a description text doesn't seem to be working *}

	{fbvFormArea id="policiesFormArea"}
		{fbvFormSection label="manager.setup.authorCopyrightNotice" description=$authorCopyrightNoticeDescription translate=false}
			{fbvElement type="textarea" multilingual="true" name="copyrightNotice" id="copyrightNotice" value=$copyrightNotice}
		{/fbvFormSection}
		{fbvFormSection list=true}
			{fbvElement type="checkbox" id="includeCreativeCommons" value="1" checked=$includeCreativeCommons label="manager.setup.includeCreativeCommons"}
			{fbvElement type="checkbox" id="copyrightNoticeAgree" value="1" checked=$copyrightNoticeAgree label="manager.setup.authorCopyrightNoticeAgree"}
		{/fbvFormSection}
		{fbvFormSection label="manager.setup.privacyStatement" description="manager.setup.privacyStatement.description"}
			{fbvElement type="textarea" multilingual="true" name="privacyStatement" id="privacyStatement" value=$privacyStatement}
		{/fbvFormSection}

		<div {if $wizardMode}class="pkp_form_hidden"{/if}>
			{fbvFormSection label="manager.setup.focusAndScopeOfPress" description="manager.setup.focusAndScope.description"}
				{fbvElement type="textarea" multilingual=true name="focusScopeDesc" id="focusScopeDesc" value=$focusScopeDesc rich=true}
			{/fbvFormSection}
			{fbvFormSection label="manager.setup.openAccessPolicy" description="manager.setup.openAccessPolicy.description"}
				{url|assign:"accessAndSecurityUrl" page="settings" op="access"}
				{translate|assign:"securitySettingsNote" key="manager.setup.securitySettings.note" accessAndSecurityUrl=$accessAndSecurityUrl}
				{fbvElement type="textarea" multilingual="true" name="openAccessPolicy" id="openAccessPolicy" value=$openAccessPolicy rich=true}
			{/fbvFormSection}
			{fbvFormSection label="manager.setup.reviewPolicy" description="manager.setup.peerReview.description"}
				{fbvElement type="textarea" multilingual="true" name="reviewPolicy" id="reviewPolicy" value=$reviewPolicy}
			{/fbvFormSection}
			{fbvFormSection label="navigation.competingInterestPolicy" description="manager.setup.competingInterestsDescription"}
				{fbvElement type="textarea" multilingual="true" id="competingInterestsPolicy" value=$competingInterestsPolicy}
			{/fbvFormSection}
		</div>
	{/fbvFormArea}
	{if !$wizardMode}
		{fbvFormButtons id="policiesFormSubmit" submitText="common.save" hideCancel=true}
	{/if}
</form>