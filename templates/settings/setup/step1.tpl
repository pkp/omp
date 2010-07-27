{**
 * step1.tpl
 *
 * Copyright (c) 2003-2010 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Step 1 of press setup.
 *
 * $Id$
 *}
{assign var="pageTitle" value="settings.setup.gettingDownTheDetails"}
{include file="settings/setup/setupHeader.tpl"}

<form name="setupForm" method="post" action="{url op="saveSetup" path="1"}">
{include file="common/formErrors.tpl"}

{if count($formLocales) > 1}
{fbvFormArea id="locales"}
{fbvFormSection title="form.formLanguage" for="languageSelector"}
	{fbvCustomElement}
		{url|assign:"setupFormUrl" op="setup" path="1"}
		{form_language_chooser form="setupForm" url=$setupFormUrl}
		<p>{translate key="form.formLanguage.description"}</p>
	{/fbvCustomElement}
{/fbvFormSection}
{/fbvFormArea}
{/if} {* count($formLocales) > 1*}

<h3>1.1 {translate key="settings.setup.generalInformation"}</h3>

{fbvFormArea id="generalInformation"}
{fbvFormSection title="common.name"}
	{fbvElement type="text" label="settings.setup.pressName" name="name[$formLocale]" id="name" value=$name[$formLocale] maxlength="120" size=$fbvStyles.size.LARGE}
	{fbvElement type="text" label="settings.setup.pressInitials" name="initials[$formLocale]" id="initials" value=$initials[$formLocale] maxlength="16" size=$fbvStyles.size.SMALL}
{/fbvFormSection}
{fbvFormSection title="settings.setup.pressDescription" for="description" float=$fbvStyles.float.LEFT}
	{fbvElement type="textarea" name="description[$formLocale]" id="description" value=$description[$formLocale] size=$fbvStyles.size.MEDIUM measure=$fbvStyles.measure.3OF4}
{/fbvFormSection}
{fbvFormSection title="common.mailingAddress" for="mailingAddress" group="true" float=$fbvStyles.float.RIGHT}
	{fbvCustomElement}
		{fbvTextarea id="mailingAddress" value=$mailingAddress size=$fbvStyles.size.SMALL}
		<br />
		<span>{translate key="settings.setup.mailingAddressDescription"}</span>
	{/fbvCustomElement}
{/fbvFormSection}
{fbvFormSection layout=$fbvStyles.layout.ONE_COLUMN}
	{fbvElement type="checkbox" id="pressEnabled" value="1" checked=$pressEnabled label="settings.setup.enablePressInstructions"}
{/fbvFormSection}
{/fbvFormArea}

<div class="separator"></div>

<h3>1.2 {translate key="settings.setup.emails"}</h3>

<p>{translate key="settings.setup.emailSignatureDescription"}</p>

{fbvFormArea id="emails"}
{fbvFormSection title="settings.setup.emailSignature" for="emailSignature"}
	{fbvElement type="textarea" id="emailSignature" value=$emailSignature size=$fbvStyles.size.SMALL measure=$fbvStyles.measure.2OF3}
{/fbvFormSection}
{fbvFormSection title="settings.setup.emailBounceAddress" for="envelopeSender"}
	<p>{translate key="settings.setup.emailBounceAddressDescription"}</p>
	{fbvElement type="text" id="envelopeSender" value=$envelopeSender maxlength="90" disabled=!$envelopeSenderEnabled size=$fbvStyles.size.LARGE}
	{if !$envelopeSenderEnabled}
		<div class="clear"></div>
		<p>{translate key="settings.setup.emailBounceAddressDisabled"}</p>
	{/if}
{/fbvFormSection}
{/fbvFormArea}

<div class="separator"></div>

<h3>1.3 {translate key="settings.setup.principalContact"}</h3>

<p>{translate key="settings.setup.principalContactDescription"}</p>

{fbvFormArea id="principalContact"}
{fbvFormSection title="user.name" required="true" for="contactName"}
	{fbvElement type="text" id="contactName" value=$contactName maxlength="60"}
{/fbvFormSection}
{fbvFormSection title="user.title" for="contactTitle"}
	{fbvElement type="text" name="contactTitle[$formLocale]" id="contactTitle" value=$contactTitle[$formLocale] maxlength="90"}
{/fbvFormSection}
{fbvFormSection title="user.affiliation" for="contactAffiliation"}
	{fbvElement type="textarea" name="contactAffiliation[$formLocale]" id="contactAffiliation" value=$contactAffiliation[$formLocale] size=$fbvStyles.size.SMALL measure=$fbvStyles.measure.1OF2}
{/fbvFormSection}
{fbvFormSection title="user.email" for="contactEmail" required="true"}
	{fbvElement type="text" id="contactEmail" value=$contactEmail maxlength="90"}
{/fbvFormSection}
{fbvFormSection title="user.phone" for="contactPhone" float=$fbvStyles.float.LEFT}
	{fbvElement type="text" id="contactPhone" value=$contactPhone maxlength="24"}
{/fbvFormSection}
{fbvFormSection title="user.fax" for="contactFax" float=$fbvStyles.float.RIGHT}
	{fbvElement type="text" id="contactFax" value=$contactFax maxlength="24"}
{/fbvFormSection}
{fbvFormSection title="common.mailingAddress" for="contactMailingAddress"}
	{fbvElement type="textarea" name="contactMailingAddress[$formLocale]" id="contactMailingAddress" value=$contactMailingAddress[$formLocale] size=$fbvStyles.size.SMALL measure=$fbvStyles.measure.1OF2}
{/fbvFormSection}
{/fbvFormArea}

<div class="separator"></div>

{url|assign:mastheadGridUrl router=$smarty.const.ROUTE_COMPONENT component="grid.settings.masthead.mastheadGridHandler" op="fetchGrid"}
{load_url_in_div id="#mastheadGridDiv" url=$mastheadGridUrl}

<div class="separator"></div>

<h3>1.5 {translate key="settings.setup.sponsors"}</h3>

<p>{translate key="settings.setup.sponsorsDescription"}</p>

{fbvFormArea id="sponsors"}
{fbvFormSection title="settings.setup.note" for="sponsorNote"}
	{fbvElement type="textarea" name="sponsorNote[$formLocale]" id="sponsorNote" value=$sponsorNote[$formLocale] size=$fbvStyles.size.SMALL measure=$fbvStyles.measure.3OF4}
{/fbvFormSection}
{/fbvFormArea}

{url|assign:sponsorGridUrl router=$smarty.const.ROUTE_COMPONENT component="grid.settings.sponsor.sponsorGridHandler" op="fetchGrid"}
{load_url_in_div id="#sponsorGridDiv" url=$sponsorGridUrl}

<div class="separator"></div>

<h3>1.6 {translate key="settings.setup.contributors"}</h3>

<p>{translate key="settings.setup.contributorsDescription"}</p>

<table width="100%" class="data">
	<tr valign="top">
		<td width="20%" class="label">{fieldLabel name="contributorNote" key="settings.setup.note"}</td>
		<td width="80%" class="value"><textarea name="contributorNote[{$formLocale|escape}]" id="contributorNote" rows="5" cols="40" class="textArea">{$contributorNote[$formLocale]|escape}</textarea></td>
	</tr>
</table>

{url|assign:contributorGridUrl router=$smarty.const.ROUTE_COMPONENT component="grid.settings.contributor.ContributorGridHandler" op="fetchGrid"}
{load_url_in_div id="#contributorGridDiv" url=$contributorGridUrl}

<div class="separator"></div>

<h3>1.7 {translate key="settings.setup.technicalSupportContact"}</h3>

<p>{translate key="settings.setup.technicalSupportContactDescription"}</p>

{fbvFormArea id="technicalSupportContact"}
{fbvFormSection title="user.name" for="supportName" required="true"}
	{fbvElement type="text" id="supportName" value=$supportName maxlength="60"}
{/fbvFormSection}
{fbvFormSection title="user.email" for="supportEmail" required="true" float=$fbvStyles.float.LEFT}
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
