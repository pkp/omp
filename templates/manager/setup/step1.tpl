{**
 * templates/manager/setup/step1.tpl
 *
 * Copyright (c) 2003-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Step 1 of press setup.
 *}

{assign var="pageTitle" value="manager.setup.gettingDownTheDetails"}
{include file="manager/setup/setupHeader.tpl"}
<script type="text/javascript">
	$(function() {ldelim}
		// Attach the form handler.
		$('#setupFormStep1').pkpHandler('$.pkp.controllers.form.FormHandler');
	{rdelim});
</script>

	<form id="setupFormStep1" class="pkp_controllers_form" method="post" action="{url op="saveSetup" path="1"}">
		{include file="common/formErrors.tpl"}

		<h3>1.1 {translate key="manager.setup.generalInformation"}</h3>

		{fbvFormArea id="generalInformation"}
			{fbvFormSection title="manager.setup.pressName" for="name" required=true}
				{fbvTextInput type="text" multilingual=true name="name" id="name" value=$name maxlength="120" size=$fbvStyles.size.LARGE}
			{/fbvFormSection}
			{fbvFormSection title="manager.setup.pressInitials" for="initials" required=true}
				{fbvTextInput type="text" multilingual=true name="initials" id="initials" value=$initials maxlength="16" size=$fbvStyles.size.SMALL}
			{/fbvFormSection}
			{fbvFormSection title="manager.setup.pressDescription" for="description" float=$fbvStyles.float.LEFT}
				{fbvTextArea multilingual=true name="description" id="description" value=$description size=$fbvStyles.size.MEDIUM measure=$fbvStyles.measure.3OF4 rich=true}
			{/fbvFormSection}
			{fbvFormSection title="common.mailingAddress" for="mailingAddress" group=true float=$fbvStyles.float.RIGHT}
				{fbvCustomElement}
					{fbvTextArea id="mailingAddress" value=$mailingAddress size=$fbvStyles.size.SMALL}
					<br />
					<span>{translate key="manager.setup.mailingAddressDescription"}</span>
				{/fbvCustomElement}
			{/fbvFormSection}
			{fbvFormSection layout=$fbvStyles.layout.ONE_COLUMN}
				{fbvElement type="checkbox" id="pressEnabled" value="1" checked=$pressEnabled label="manager.setup.enablePressInstructions"}
			{/fbvFormSection}
		{/fbvFormArea}

		<div class="separator"></div>

		<h3>1.2 {translate key="manager.setup.emails"}</h3>

		{fbvFormArea id="emails"}
			<p>{translate key="manager.setup.emailSignatureDescription"}</p>
			{fbvFormSection title="manager.setup.emailSignature" for="emailSignature"}
				{fbvElement type="textarea" id="emailSignature" value=$emailSignature size=$fbvStyles.size.SMALL measure=$fbvStyles.measure.2OF3}
			{/fbvFormSection}
			{fbvFormSection title="manager.setup.emailBounceAddress" for="envelopeSender"}
				<p>{translate key="manager.setup.emailBounceAddressDescription"}</p>
				{fbvElement type="text" id="envelopeSender" value=$envelopeSender maxlength="90" disabled=!$envelopeSenderEnabled size=$fbvStyles.size.LARGE}
				{if !$envelopeSenderEnabled}
					<div class="pkp_helpers_clear"></div>
					<p>{translate key="manager.setup.emailBounceAddressDisabled"}</p>
				{/if}
			{/fbvFormSection}
		{/fbvFormArea}

		<div class="separator"></div>

		<h3>1.3 {translate key="manager.setup.principalContact"}</h3>
		<p>{translate key="manager.setup.principalContactDescription"}</p>

		{fbvFormArea id="principalContact"}
			{fbvFormSection title="user.name" required=true for="contactName"}
				{fbvElement type="text" id="contactName" value=$contactName maxlength="60"}
			{/fbvFormSection}
			{fbvFormSection title="user.title" for="contactTitle"}
				{fbvTextInput multilingual=true name="contactTitle" id="contactTitle" value=$contactTitle maxlength="90"}
			{/fbvFormSection}
			{fbvFormSection title="user.affiliation" for="contactAffiliation"}
				{fbvTextArea multilingual=true name="contactAffiliation" id="contactAffiliation" value=$contactAffiliation size=$fbvStyles.size.SMALL measure=$fbvStyles.measure.1OF2}
			{/fbvFormSection}
			{fbvFormSection title="user.email" for="contactEmail" required=true}
				{fbvElement type="text" id="contactEmail" value=$contactEmail maxlength="90"}
			{/fbvFormSection}
			{fbvFormSection title="user.phone" for="contactPhone" float=$fbvStyles.float.LEFT}
				{fbvElement type="text" id="contactPhone" value=$contactPhone maxlength="24"}
			{/fbvFormSection}
			{fbvFormSection title="user.fax" for="contactFax" float=$fbvStyles.float.RIGHT}
				{fbvElement type="text" id="contactFax" value=$contactFax maxlength="24"}
			{/fbvFormSection}
			{fbvFormSection title="common.mailingAddress" for="contactMailingAddress"}
				{fbvTextArea multilingual=true name="contactMailingAddress" id="contactMailingAddress" value=$contactMailingAddress size=$fbvStyles.size.SMALL measure=$fbvStyles.measure.1OF2 rich=true}
			{/fbvFormSection}
		{/fbvFormArea}

		<div class="separator"></div>

		{url|assign:mastheadGridUrl router=$smarty.const.ROUTE_COMPONENT component="grid.settings.masthead.mastheadGridHandler" op="fetchGrid"}
		{load_url_in_div id="mastheadGridDiv" url=$mastheadGridUrl}

		<div class="separator"></div>

		<h3>1.5 {translate key="manager.setup.sponsors"}</h3>
		<p>{translate key="manager.setup.sponsorsDescription"}</p>

		{fbvFormArea id="sponsors"}
			{fbvFormSection title="manager.setup.note" for="sponsorNote"}
				{fbvTextArea multilingual=true name="sponsorNote" id="sponsorNote" value=$sponsorNote size=$fbvStyles.size.SMALL measure=$fbvStyles.measure.3OF4 rich=true}
			{/fbvFormSection}
		{/fbvFormArea}

		{url|assign:sponsorGridUrl router=$smarty.const.ROUTE_COMPONENT component="grid.settings.sponsor.sponsorGridHandler" op="fetchGrid"}
		{load_url_in_div id="sponsorGridDiv" url=$sponsorGridUrl}

		<div class="separator"></div>

		{fbvFormArea id="contributor"}
			<h3>1.6 {translate key="manager.setup.contributors"}</h3>
			<p>{translate key="manager.setup.contributorsDescription"}</p>
			{fbvFormSection title="manager.setup.note" for="contributorNote"}
				{fbvTextArea id="contributorNote" multilingual=true name="contributorNote" value=$contributorNote size=$fbvStyles.size.MEDIUM measure=$fbvStyles.measure.3OF4 rich=true}
			{/fbvFormSection}
			{url|assign:contributorGridUrl router=$smarty.const.ROUTE_COMPONENT component="grid.settings.contributor.ContributorGridHandler" op="fetchGrid"}
			{load_url_in_div id="contributorGridDiv" url=$contributorGridUrl}
		{/fbvFormArea}

		<div class="separator"></div>

		<h3>1.7 {translate key="manager.setup.technicalSupportContact"}</h3>

		<p>{translate key="manager.setup.technicalSupportContactDescription"}</p>

		{fbvFormArea id="technicalSupportContact"}
		{fbvFormSection title="user.name" for="supportName" required=true}
			{fbvElement type="text" id="supportName" value=$supportName maxlength="60"}
		{/fbvFormSection}
		{fbvFormSection title="user.email" for="supportEmail" required=true float=$fbvStyles.float.LEFT}
			{fbvElement type="text" id="supportEmail" value=$supportEmail maxlength="90"}
		{/fbvFormSection}
		{fbvFormSection title="user.phone" for="supportPhone" float=$fbvStyles.float.RIGHT}
			{fbvElement type="text" id="supportPhone" value=$supportPhone maxlength="24"}
		{/fbvFormSection}
		{/fbvFormArea}

		<div class="separator"></div>

		<p><input type="submit" value="{translate key="common.saveAndContinue"}" class="button defaultButton" /> <input type="button" value="{translate key="common.cancel"}" class="button" onclick="document.location.href='{url op="setup" escape=false}'" /></p>

		<p><span class="formRequired">{translate key="common.requiredField"}</span></p>

	</form>
</div>

{include file="common/footer.tpl"}
