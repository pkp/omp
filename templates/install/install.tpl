{**
 * install.tpl
 *
 * Copyright (c) 2000-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Installation form.
 *}

{strip}
{assign var="pageTitle" value="installer.ompInstallation"}
{include file="common/header.tpl"}
{/strip}

{if is_writeable('config.inc.php')}{translate|assign:"writable_config" key="installer.checkYes"}{else}{translate|assign:"writable_config" key="installer.checkNo"}{/if}
{if is_writeable('cache')}{translate|assign:"writable_cache" key="installer.checkYes"}{else}{translate|assign:"writable_cache" key="installer.checkNo"}{/if}
{if is_writeable('public')}{translate|assign:"writable_public" key="installer.checkYes"}{else}{translate|assign:"writable_public" key="installer.checkNo"}{/if}
{if is_writeable('cache/_db')}{translate|assign:"writable_db_cache" key="installer.checkYes"}{else}{translate|assign:"writable_db_cache" key="installer.checkNo"}{/if}
{if is_writeable('cache/t_cache')}{translate|assign:"writable_templates_cache" key="installer.checkYes"}{else}{translate|assign:"writable_templates_cache" key="installer.checkNo"}{/if}
{if is_writeable('cache/t_compile')}{translate|assign:"writable_templates_compile" key="installer.checkYes"}{else}{translate|assign:"writable_templates_compile" key="installer.checkNo"}{/if}

{if !$phpIsSupportedVersion}
	{translate|assign:"wrongPhpText" key="installer.installationWrongPhp"}
{/if}

{url|assign:"upgradeUrl" page="install" op="upgrade"}
{translate key="installer.installationInstructions" version=$version->getVersionString() upgradeUrl=$upgradeUrl baseUrl=$baseUrl writable_config=$writable_config writable_db_cache=$writable_db_cache writable_cache=$writable_cache writable_public=$writable_public writable_templates_cache=$writable_templates_cache writable_templates_compile=$writable_templates_compile phpRequiredVersion=$phpRequiredVersion wrongPhpText=$wrongPhpText phpVersion=$phpVersion}

<div class="separator"></div>

<script type="text/javascript">
	$(function() {ldelim}
		// Attach the form handler.
		$('#installForm').pkpHandler('$.pkp.controllers.form.FormHandler');
	{rdelim});
</script>
<form class="pkp_form" method="post" id="installForm" action="{url op="install"}">
	<input type="hidden" name="installing" value="0" />

	{if $isInstallError}
	<p>
		<span class="pkp_controllers_form_error">{translate key="installer.installErrorsOccurred"}:</span>
		<ul class="pkp_controllers_form_error_list">
			<li class="error">{if $dbErrorMsg}{translate key="common.error.databaseError" error=$dbErrorMsg}{else}{translate key=$errorMsg}{/if}</li>
		</ul>
	</p>
	{/if}

	<!-- Locale configuration -->
	<div id="localeSettings">
		<h3>{translate key="installer.localeSettings"}</h3>

		<p>{translate key="installer.localeSettingsInstructions" supportsMBString=$supportsMBString}</p>

		{fbvFormArea id="localeSettingsFormArea"}
			{fbvFormSection title="locale.primary"}
				{fbvElement type="select" name="locale" id="localeOptions" from=$localeOptions selected=$locale translate=false}
				<br />
				<span class="instruct">{translate key="installer.localeInstructions"}</span>
			{/fbvFormSection}
			{fbvFormSection title="installer.additionalLocales}
				{foreach from=$localeOptions key=localeKey item=localeName}
					{assign var=localeKeyEscaped value=$localeKey|escape}
					{fbvElement type="checkbox" name="additionalLocales[]" id="additionalLocales-$localeKeyEscaped" value=$localeKeyEscaped translate=false label="manager.people.createUserSendNotify" checked=$sendNotify label=$localeName|escape} ({$localeKey|escape})
					{if !$localesComplete[$localeKey]}
							<span class="pkp_controllers_form_error">*</span>
							{assign var=incompleteLocaleFound value=1}
					{/if}<br />
				{/foreach}
				<span class="instruct">{translate key="installer.additionalLocalesInstructions"}</span>
				{if $incompleteLocaleFound}
					<br/>
					<span class="pkp_controlles_form_error">*</span>&nbsp;{translate key="installer.locale.maybeIncomplete"}
				{/if}{* $incompleteLocaleFound *}
			{/fbvFormSection}


			{fbvFormSection title="installer.clientCharset"}
				{fbvElement type="select" id="clientCharset" from=$clientCharsetOptions selected=$clientCharset translate=false}
				<br />
				<span class="instruct">{translate key="installer.clientCharsetInstructions"}</span>
			{/fbvFormSection}
			{fbvFormSection title="installer.connectionCharset"}
				{fbvElement type="select" id="connectionCharset" from=$connectionCharsetOptions selected=$connectionCharset translate=false}
				<br />
				<span class="instruct">{translate key="installer.connectionCharsetInstructions"}</span>
			{/fbvFormSection}
			{fbvFormSection title="installer.databaseCharset"}
				{fbvElement type="select" id="databaseCharset" from=$databaseCharsetOptions selected=$databaseCharset translate=false}
				<br />
				<span class="instruct">{translate key="installer.databaseCharsetInstructions"}</span>
			{/fbvFormSection}
		{/fbvFormArea}
	</div>

	<div class="separator"></div>

	<!-- Files directory configuration -->
	{if !$skipFilesDirSection}
	<div id="fileSettings">
		<h3>{translate key="installer.fileSettings"}</h3>

		{fbvFormArea id="fileSettingsFormArea"}
			{fbvFormSection title="installer.filesDir"}
				{fbvElement type="text" id="filesDir" value=$filesDir|escape maxlength="255" size=$fbvStyles.size.LARGE}
				{fbvElement type="checkbox" id="skipFilesDir" value="1" checked=$skipFilesDir label="installer.skipFilesDir"}
				<br />
				<span class="instruct">{translate key="installer.filesDirInstructions"}</span>
			{/fbvFormSection}
		{/fbvFormArea}

		<div class="separator"></div>
	</div>
	{/if}{* !$skipFilesDirSection *}

	<!-- Security configuration -->
	<div id="security">
		<h3>{translate key="installer.securitySettings"}</h3>

		{fbvFormArea  id="securityFormArea"}
			{fbvFormSection title="installer.encryption"}
				{fbvElement type="select" id="encryption" from=$encryptionOptions selected=$encryption translate=false}
				<br />
				<span class="instruct">{translate key="installer.encryptionInstructions"}</span>
			{/fbvFormSection}
		{/fbvFormArea}
	</div>

	<div class="separator"></div>

	<!-- Administrator username, password, and email -->
	<div id="administratorAccount">
		<h3>{translate key="installer.administratorAccount"}</h3>

		<p>{translate key="installer.administratorAccountInstructions"}</p>

		{fbvFormArea id="administratorAccountFormArea"}
			{fbvFormSection title="user.username"}
				{fbvElement type="text" id="adminUsername" value=$adminUsername|escape maxlength="32" size=$fbvStyles.size.LARGE}
			{/fbvFormSection}
			{fbvFormSection title="user.password"}
				{fbvElement type="text" password=true id="adminPassword" value=$adminPassword|escape maxlength="32" size=$fbvStyles.size.LARGE}
			{/fbvFormSection}
			{fbvFormSection title="user.repeatPassword"}
				{fbvElement type="text" password=true id="adminPassword2" value=$adminPassword2|escape maxlength="32" size=$fbvStyles.size.LARGE}
			{/fbvFormSection}
			{fbvFormSection title="user.email"}
				{fbvElement type="text" id="adminEmail" value=$adminEmail|escape maxlength="90" size=$fbvStyles.size.LARGE}
			{/fbvFormSection}
		{/fbvFormArea}
	</div>

	<div class="separator"></div>

	<!-- Database configuration -->
	<div id="databaseSettings">
		<h3>{translate key="installer.databaseSettings"}</h3>

		<p>{translate key="installer.databaseSettingsInstructions"}</p>

		{fbvFormArea id="databaseSettingsFormArea"}
			{fbvFormSection title="installer.databaseDriver"}
				{fbvElement type="select" id="databaseDriver" from=$databaseDriverOptions selected=$databaseDriver translate=false}
				<br />
				<span class="instruct">{translate key="installer.databaseDriverInstructions"}</span>
			{/fbvFormSection}
			{fbvFormSection title="installer.databaseHost"}
				{fbvElement type="text" id="databaseHost" value=$databaseHost|escape maxlength="60" size=$fbvStyles.size.LARGE}
			{/fbvFormSection}
			{fbvFormSection title="installer.databaseUsername"}
				{fbvElement type="text" id="databaseUsername" value=$databaseUsername|escape maxlength="60" size=$fbvStyles.size.LARGE}
			{/fbvFormSection}
			{fbvFormSection title="installer.databasePassword"}
				{fbvElement type="text" id="databasePassword" value=$databasePassword|escape maxlength="60" size=$fbvStyles.size.LARGE}
			{/fbvFormSection}
			{fbvFormSection title="installer.databaseName"}
				{fbvElement type="text" id="databaseName" value=$databaseName|escape maxlength="60" size=$fbvStyles.size.LARGE}
				{fbvElement type="checkbox" id="createDatabase" value="1" checked=$createDatabase label="installer.createDatabase"}
			{/fbvFormSection}
		{/fbvFormArea}
	</div>

	<div class="separator"></div>

	{if !$skipMiscSettings}
	<div id="miscSettings">
		<h3>{translate key="installer.miscSettings"}</h3>

		{fbvFormArea id="miscSettingsFormArea"}
			{fbvFormSection title="installer.oaiRepositoryId"}
				{fbvElement type="text" id="oaiRepositoryId" value=$oaiRepositoryId|escape maxlength="60" size=$fbvStyles.size.LARGE}
				<br />
				<span class="instruct">{translate key="installer.oaiRepositoryIdInstructions"}</span>
			{/fbvFormSection}
		{/fbvFormArea}

		<div class="separator"></div>
	</div>
	{/if}{* !$skipMiscSettings *}

	<p><input type="submit" id="installButton" value="{translate key="installer.installApplication"}" class="button defaultButton" /> <input type="submit" name="manualInstall" value="{translate key="installer.manualInstall"}" class="button" /></p>

</form>

{include file="common/footer.tpl"}

