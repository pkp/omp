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

<form class="pkp_form pkp_controllers_form" id="policiesForm" method="post" action="{url router=$smarty.const.ROUTE_COMPONENT component="tab.settings.PressSettingsTabHandler" op="saveFormData" tab="policies"}">
	{include file="common/formErrors.tpl"}
	{include file="controllers/tab/settings/wizardMode.tpl wizardMode=$wizardMode}

	{fbvFormArea id="policiesFormArea"}
		{fbvFormSection title="manager.setup.focusAndScopeOfPress"}
			<p>{translate key="manager.setup.focusAndScopeDescription"}</p>
			{fbvElement type="textarea" multilingual=true name="focusScopeDesc" id="focusScopeDesc" value=$focusScopeDesc size=$fbvStyles.size.MEDIUM  rich=true}
		{/fbvFormSection}
		{fbvFormSection title="manager.setup.authorCopyrightNotice"}
			{url|assign:"sampleCopyrightWordingUrl" page="information" op="sampleCopyrightWording"}
			<p>{translate key="manager.setup.authorCopyrightNoticeDescription" sampleCopyrightWordingUrl=$sampleCopyrightWordingUrl}</p>
			{fbvElement type="textarea" multilingual="true" name="copyrightNotice" id="copyrightNotice" value=$copyrightNotice size=$fbvStyles.size.MEDIUM}
		{/fbvFormSection}
		{fbvFormSection list=true}
			{fbvElement type="checkbox" id="includeCreativeCommons" value="1" checked=$includeCreativeCommons label="manager.setup.includeCreativeCommons"}
			{fbvElement type="checkbox" id="copyrightNoticeAgree" value="1" checked=$copyrightNoticeAgree label="manager.setup.authorCopyrightNoticeAgree"}
		{/fbvFormSection}
		{fbvFormSection title="manager.setup.privacyStatement"}
			{fbvElement type="textarea" multilingual="true" name="privacyStatement" id="privacyStatement" value=$privacyStatement size=$fbvStyles.size.MEDIUM}
		{/fbvFormSection}

		<div {if $wizardMode}class="pkp_form_hidden"{/if}>
			{fbvFormSection title="manager.setup.openAccessPolicy"}
				<p>{translate key="manager.setup.openAccessPolicyDescription"}</p>
				{fbvElement type="textarea" multilingual="true" name="openAccessPolicy" id="openAccessPolicy" value=$openAccessPolicy size=$fbvStyles.size.MEDIUM  rich=true}
				<p>{translate key="manager.setup.securitySettingsDescription"}</p>
			{/fbvFormSection}
			{fbvFormSection title="manager.setup.reviewPolicy"}
				<p>{translate key="manager.setup.peerReviewDescription"}</p>
				{fbvElement type="textarea" multilingual="true" name="reviewPolicy" id="reviewPolicy" value=$reviewPolicy size=$fbvStyles.size.MEDIUM}
			{/fbvFormSection}
			{fbvFormSection title="navigation.competingInterestPolicy"}
				{fbvElement type="textarea" multilingual="true" id="competingInterestsPolicy" value=$competingInterestsPolicy size=$fbvStyles.size.MEDIUM}
			{/fbvFormSection}
		</div>
	{/fbvFormArea}
	{if !$wizardMode}
		{fbvFormButtons id="policiesFormSubmit" submitText="common.save" hideCancel=true}
	{/if}
</form>