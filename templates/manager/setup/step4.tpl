{**
 * step4.tpl
 *
 * Copyright (c) 2003-2008 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Step 4 of press setup.
 *
 * $Id$
 *}
{assign var="pageTitle" value="manager.setup.managingThePress"}
{include file="manager/setup/setupHeader.tpl"}

<form name="setupForm" method="post" action="{url op="saveSetup" path="4"}" enctype="multipart/form-data">
{include file="common/formErrors.tpl"}

{if count($formLocales) > 1}
<table width="100%" class="data">
	<tr valign="top">
		<td width="20%" class="label">{fieldLabel name="formLocale" key="form.formLanguage"}</td>
		<td width="80%" class="value">
			{url|assign:"setupFormUrl" op="setup" path="4"}
			{form_language_chooser form="setupForm" url=$setupFormUrl}
			<span class="instruct">{translate key="form.formLanguage.description"}</span>
		</td>
	</tr>
</table>
{/if}

<h3>4.1 {translate key="manager.setup.securitySettings"}</h3>

<h4>{translate key="manager.setup.onlineAccessManagement"}</h4>

<h4>{translate key="manager.setup.openAccessPolicy"}</h4>
<p><span class="instruct">{translate key="manager.setup.openAccessPolicyDescription"}</span></p>

<p><textarea name="openAccessPolicy[{$formLocale|escape}]" id="openAccessPolicy" rows="12" cols="60" class="textArea">{$openAccessPolicy[$formLocale]|escape}</textarea></p>

<p>{translate key="manager.setup.securitySettingsDescription"}</p>

<script type="text/javascript">
{literal}
<!--
function setRegAllowOpts(form) {
	if(form.disableUserReg[0].checked) {
		form.allowRegReader.disabled=false;
		form.allowRegAuthor.disabled=false;
		form.allowRegReviewer.disabled=false;
	} else {
		form.allowRegReader.disabled=true;
		form.allowRegAuthor.disabled=true;
		form.allowRegReviewer.disabled=true;
	}
}
// -->
{/literal}
</script>

<h4>{translate key="manager.setup.siteAccess"}</h4>

<table width="100%" class="data">
	<tr valign="top">
		<td width="5%" class="label"><input type="checkbox" name="restrictSiteAccess" id="restrictSiteAccess" value="1"{if $restrictSiteAccess} checked="checked"{/if} /></td>
		<td width="95%" class="value"><label for="restrictSiteAccess">{translate key="manager.setup.restrictSiteAccess"}</label></td>
	</tr>
	<tr valign="top">
		<td width="5%" class="label"><input type="checkbox" name="restrictMonographAccess" id="restrictMonographAccess" value="1"{if $restrictMonographAccess} checked="checked"{/if} /></td>
		<td width="95%" class="value"><label for="restrictMonographAccess">{translate key="manager.setup.restrictMonographAccess"}</label></td>
	</tr>
</table>

<h4>{translate key="manager.setup.userRegistration"}</h4>

<table width="100%" class="data">
	<tr valign="top">
		<td width="5%" class="label"><input type="radio" name="disableUserReg" id="disableUserReg-0" value="0" onclick="setRegAllowOpts(this.form)"{if !$disableUserReg} checked="checked"{/if} /></td>
		<td width="95%" class="value">
			<label for="disableUserReg-0">{translate key="manager.setup.enableUserRegistration"}</label>
			<table width="100%">
				<tr>
					<td width="5%"><input type="checkbox" name="allowRegReader" id="allowRegReader" value="1"{if $allowRegReader} checked="checked"{/if}{if $disableUserReg} disabled="disabled"{/if} /></td>
					<td width="95%"><label for="allowRegReader">{translate key="manager.setup.enableUserRegistration.reader"}</label></td>
				</tr>
				<tr>
					<td width="5%"><input type="checkbox" name="allowRegAuthor" id="allowRegAuthor" value="1"{if $allowRegAuthor} checked="checked"{/if}{if $disableUserReg} disabled="disabled"{/if} /></td>
					<td width="95%"><label for="allowRegAuthor">{translate key="manager.setup.enableUserRegistration.author"}</label></td>
				</tr>
				<tr>
					<td width="5%"><input type="checkbox" name="allowRegReviewer" id="allowRegReviewer" value="1"{if $allowRegReviewer} checked="checked"{/if}{if $disableUserReg} disabled="disabled"{/if} /></td>
					<td width="95%"><label for="allowRegReviewer">{translate key="manager.setup.enableUserRegistration.reviewer"}</label></td>
				</tr>
			</table>
		</td>
	</tr>
	<tr valign="top">
		<td width="5%" class="label"><input type="radio" name="disableUserReg" id="disableUserReg-1" value="1" onclick="setRegAllowOpts(this.form)"{if $disableUserReg} checked="checked"{/if} /></td>
		<td width="95%" class="value"><label for="disableUserReg-1">{translate key="manager.setup.disableUserRegistration"}</label></td>
	</tr>
</table>

<h4>{translate key="manager.setup.loggingAndAuditing"}</h4>

<table width="100%" class="data">
	<tr valign="top">
		<td width="5%" class="label"><input type="checkbox" name="monographEventLog" id="monographEventLog" value="1"{if $monographEventLog} checked="checked"{/if} /></td>
		<td width="95%" class="value"><label for="monographEventLog">{translate key="manager.setup.submissionEventLogging"}</label></td>
	</tr>
	<tr valign="top">
		<td width="5%" class="label"><input type="checkbox" name="monographEmailLog" id="monographEmailLog" value="1"{if $monographEmailLog} checked="checked"{/if} /></td>
		<td width="95%" class="value"><label for="monographEmailLog">{translate key="manager.setup.submissionEmailLogging"}</label></td>
	</tr>
</table>


<div class="separator"></div>

<h3>4.2 {translate key="manager.setup.announcements"}</h3>

<p>{translate key="manager.setup.announcementsDescription"}</p>

	<script type="text/javascript">
		{literal}
		<!--
			function toggleEnableAnnouncementsHomepage(form) {
				form.numAnnouncementsHomepage.disabled = !form.numAnnouncementsHomepage.disabled;
			}
		// -->
		{/literal}
	</script>

<p>
	<input type="checkbox" name="enableAnnouncements" id="enableAnnouncements" value="1" {if $enableAnnouncements} checked="checked"{/if} />&nbsp;
	<label for="enableAnnouncements">{translate key="manager.setup.enableAnnouncements"}</label>
</p>

<p>
	<input type="checkbox" name="enableAnnouncementsHomepage" id="enableAnnouncementsHomepage" value="1" onclick="toggleEnableAnnouncementsHomepage(this.form)"{if $enableAnnouncementsHomepage} checked="checked"{/if} />&nbsp;
	<label for="enableAnnouncementsHomepage">{translate key="manager.setup.enableAnnouncementsHomepage1"}</label>
	<select name="numAnnouncementsHomepage" size="1" class="selectMenu" {if not $enableAnnouncementsHomepage}disabled="disabled"{/if}>
		{section name="numAnnouncementsHomepageOptions" start=1 loop=11}
		<option value="{$smarty.section.numAnnouncementsHomepageOptions.index}"{if $numAnnouncementsHomepage eq $smarty.section.numAnnouncementsHomepageOptions.index or ($smarty.section.numAnnouncementsHomepageOptions.index eq 1 and not $numAnnouncementsHomepage)} selected="selected"{/if}>{$smarty.section.numAnnouncementsHomepageOptions.index}</option>
		{/section}
	</select>
	{translate key="manager.setup.enableAnnouncementsHomepage2"}
</p>

<h4>{translate key="manager.setup.announcementsIntroduction"}</h4>

<p>{translate key="manager.setup.announcementsIntroductionDescription"}</p>

<p><textarea name="announcementsIntroduction[{$formLocale|escape}]" id="announcementsIntroduction" rows="12" cols="60" class="textArea">{$announcementsIntroduction[$formLocale]|escape}</textarea></p>

<div class="separator"></div>

<h3>4.3 {translate key="manager.setup.publicIdentifier"}</h3>

<h4>{translate key="manager.setup.uniqueIdentifier"}</h4>

<p>{translate key="manager.setup.uniqueIdentifierDescription"}</p>

<table width="100%" class="data">
	<tr valign="top">
		<td class="label"><input type="checkbox" name="enablePublicMonographId" id="enablePublicMonographId" value="1"{if $enablePublicMonographId} checked="checked"{/if} /></td>
		<td class="value"><label for="enablePublicMonographId">{translate key="manager.setup.enablePublicMonographId"}</label></td>
	</tr>
	<tr valign="top">
		<td class="label"><input type="checkbox" name="enablePublicGalleyId" id="enablePublicGalleyId" value="1"{if $enablePublicGalleyId} checked="checked"{/if} /></td>
		<td class="value"><label for="enablePublicGalleyId">{translate key="manager.setup.enablePublicGalleyId"}</label></td>
	</tr>
	<tr valign="top">
		<td class="label"><input type="checkbox" name="enablePublicSuppFileId" id="enablePublicSuppFileId" value="1"{if $enablePublicSuppFileId} checked="checked"{/if} /></td>
		<td class="value"><label for="enablePublicSuppFileId">{translate key="manager.setup.enablePublicSuppFileId"}</label></td>
	</tr>
</table>

<br />

<h4>{translate key="manager.setup.pageNumberIdentifier"}</h4>

<table width="100%" class="data">
	<tr valign="top">
		<td width="5%" class="label"><input type="checkbox" name="enablePageNumber" id="enablePageNumber" value="1"{if $enablePageNumber} checked="checked"{/if} /></td>
		<td width="95%" class="value"><label for="enablePageNumber">{translate key="manager.setup.enablePageNumber"}</label></td>
	</tr>
</table>

<div class="separator"></div>

<h3>4.4 {translate key="manager.setup.cataloguingMetadata"}</h3>

<div class="separator"></div>

<h3>4.5 {translate key="manager.setup.searchEngineIndexing"}</h3>

<p>{translate key="manager.setup.searchEngineIndexingDescription"}</p>

<table width="100%" class="data">
	<tr valign="top">
		<td width="20%" class="label">{fieldLabel name="searchDescription" key="common.description"}</td>
		<td width="80%" class="value"><input type="text" name="searchDescription[{$formLocale|escape}]" id="searchDescription" value="{$searchDescription[$formLocale]|escape}" size="40" maxlength="255" class="textField" /></td>
	</tr>
	<tr valign="top">
		<td width="20%" class="label">{fieldLabel name="searchKeywords" key="common.keywords"}</td>
		<td width="80%" class="value"><input type="text" name="searchKeywords[{$formLocale|escape}]" id="searchKeywords" value="{$searchKeywords[$formLocale]|escape}" size="40" maxlength="255" class="textField" /></td>
	</tr>
	<tr valign="top">
		<td width="20%" class="label">{fieldLabel name="customHeaders" key="manager.setup.customTags"}</td>
		<td width="80%" class="value">
			<textarea name="customHeaders[{$formLocale|escape}]" id="customHeaders" rows="3" cols="40" class="textArea">{$customHeaders[$formLocale]|escape}</textarea>
			<br />
			<span class="instruct">{translate key="manager.setup.customTagsDescription"}</span>
		</td>
	</tr>
</table>

<div class="separator"></div>

<h3>4.6 {translate key="manager.setup.registerPressForIndexing"}</h3>

{url|assign:"oaiSiteUrl" press=$currentPress->getPath()}
{url|assign:"oaiUrl" page="oai"}
<p>{translate key="manager.setup.registerPressForIndexingDescription" siteUrl=$oaiSiteUrl oaiUrl=$oaiUrl}</p>

<div class="separator"></div>

<p><input type="submit" value="{translate key="common.saveAndContinue"}" class="button defaultButton" /> <input type="button" value="{translate key="common.cancel"}" class="button" onclick="document.location.href='{url op="setup" escape=false}'" /></p>

<p><span class="formRequired">{translate key="common.requiredField"}</span></p>

</form>

{include file="common/footer.tpl"}
