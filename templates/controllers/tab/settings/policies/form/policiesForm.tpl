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

<form id="policiesForm" class="pkp_controllers_form" method="post" action="{url router=$smarty.const.ROUTE_COMPONENT component="tab.settings.PressSettingsTabHandler" op="saveFormData" tab="policies"}">
	{include file="common/formErrors.tpl"}

	<h3>1.1 {translate key="manager.setup.focusAndScopeOfPress"}</h3>
	<p>{translate key="manager.setup.focusAndScopeDescription"}</p>

	{fbvFormArea id="focusAndScopeDescription"}
		{fbvFormSection}
			{fbvTextArea multilingual=true name="focusScopeDesc" id="focusScopeDesc" value=$focusScopeDesc size=$fbvStyles.size.MEDIUM measure=$fbvStyles.measure.3OF4 rich=true}
		{/fbvFormSection}
	{/fbvFormArea}

	<h3>1.2 {translate key="manager.setup.peerReviewPolicy"}</h3>
	<p>{translate key="manager.setup.peerReviewDescription"}</p>

	{fbvFormArea id="peerReviewPolicy"}
		{fbvFormSection title="manager.setup.reviewPolicy"}
			{fbvTextArea multilingual="true" name="reviewPolicy" id="reviewPolicy" value=$reviewPolicy size=$fbvStyles.size.MEDIUM measure=$fbvStyles.measure.3OF4}
		{/fbvFormSection}
	{/fbvFormArea}

	<h3>1.3 {translate key="manager.setup.authorCopyrightNotice"}</h3>

	{url|assign:"sampleCopyrightWordingUrl" page="information" op="sampleCopyrightWording"}
	<p>{translate key="manager.setup.authorCopyrightNoticeDescription" sampleCopyrightWordingUrl=$sampleCopyrightWordingUrl}</p>

	{fbvFormArea id="authorCopyrightNotice"}
		{fbvFormSection}
			{fbvTextArea multilingual="true" name="copyrightNotice" id="copyrightNotice" value=$copyrightNotice size=$fbvStyles.size.MEDIUM measure=$fbvStyles.measure.3OF4}
		{/fbvFormSection}
		{fbvFormSection layout=$fbvStyles.layout.TWO_COLUMNS}
			{fbvElement type="checkbox" id="includeCreativeCommons" value="1" checked=$includeCreativeCommons label="manager.setup.includeCreativeCommons"}
			{fbvElement type="checkbox" id="copyrightNoticeAgree" value="1" checked=$copyrightNoticeAgree label="manager.setup.authorCopyrightNoticeAgree"}
		{/fbvFormSection}
	{/fbvFormArea}

	<div class="separator"></div>

	<h3>1.4 {translate key="manager.setup.privacyStatement"}</h3>

	{fbvFormArea id="privacyStatementContainer"}
		{fbvFormSection}
			{fbvTextArea multilingual="true" name="privacyStatement" id="privacyStatement" value=$privacyStatement size=$fbvStyles.size.MEDIUM measure=$fbvStyles.measure.3OF4}
		{/fbvFormSection}
	{/fbvFormArea}

	{include file="form/formButtons.tpl" submitText="common.save"}
</form>