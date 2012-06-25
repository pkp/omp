{**
 * templates/install/install.tpl
 *
 * Copyright (c) 2000-2012 John Willinsky
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
		<span class="pkp_form_error">{translate key="installer.installErrorsOccurred"}:</span>
		<ul class="pkp_form_error_list">
			<li class="error">{if $dbErrorMsg}{translate key="common.error.databaseError" error=$dbErrorMsg}{else}{translate key=$errorMsg}{/if}</li>
		</ul>
	</p>
	{/if}

	<!-- OMP requires XSL or an XSL parser engine installed -->
	<h3>{translate key="installer.xslPresent"}</h3>
	{if $xslEnabled}
		<p>{translate key="installer.xslPresentString"}</p>
	{else}
		<span class="pkp_form_error">{translate key="installer.configureXSLMessage"}</span>
	{/if}

	<!-- Locale configuration -->
	{fbvFormArea id="localeSettingsFormArea" border=true title="installer.localeSettings" title="installer.localeSettings"}
		<p>{translate key="installer.localeSettingsInstructions" supportsMBString=$supportsMBString}</p>
		{fbvFormSection title="locale.primary" description="installer.localeInstructions"}
			{fbvElement type="select" name="locale" id="localeOptions" from=$localeOptions selected=$locale translate=false size=$fbvStyles.size.SMALL}
		{/fbvFormSection}
		{fbvFormSection list="true" title="installer.additionalLocales" description="installer.additionalLocalesInstructions"}
			{foreach from=$localeOptions key=localeKey item=localeName}
				{assign var=localeKeyEscaped value=$localeKey|escape}
				{if !$localesComplete[$localeKey]}
					{assign var=localeName value=$localeName|concat:"*"}
				{/if}
				{fbvElement type="checkbox" name="additionalLocales[]" id="additionalLocales-$localeKeyEscaped" value=$localeKeyEscaped translate=false label="manager.people.createUserSendNotify" checked=$sendNotify label=$localeName|escape}
			{/foreach}
		{/fbvFormSection}

		{fbvFormSection title="installer.clientCharset" description="installer.clientCharsetInstructions"}
			{fbvElement type="select" id="clientCharset" from=$clientCharsetOptions selected=$clientCharset translate=false size=$fbvStyles.size.SMALL}
		{/fbvFormSection}

		{fbvFormSection title="installer.connectionCharset" description="installer.connectionCharsetInstructions"}
			{fbvElement type="select" id="connectionCharset" from=$connectionCharsetOptions selected=$connectionCharset translate=false size=$fbvStyles.size.SMALL}
		{/fbvFormSection}

		{fbvFormSection title="installer.databaseCharset" description="installer.databaseCharsetInstructions"}
			{fbvElement type="select" id="databaseCharset" from=$databaseCharsetOptions selected=$databaseCharset translate=false size=$fbvStyles.size.SMALL}
		{/fbvFormSection}
	{/fbvFormArea}

	<!-- Files directory configuration -->
	{if !$skipFilesDirSection}
		{fbvFormArea id="fileSettingsFormArea" border=true title="installer.fileSettings"}
			{fbvFormSection title="installer.filesDir" description="installer.filesDirInstructions"}
				{fbvElement type="text" id="filesDir" value=$filesDir|escape maxlength="255" size=$fbvStyles.size.LARGE}
			{/fbvFormSection}
			<p>{translate key="installer.allowFileUploads" allowFileUploads=$allowFileUploads}</p>
			<p>{translate key="installer.maxFileUploadSize" maxFileUploadSize=$maxFileUploadSize}</p>
			{fbvFormSection list="true"}
				{fbvElement type="checkbox" id="skipFilesDir" value="1" checked=$skipFilesDir label="installer.skipFilesDir"}
			{/fbvFormSection}
		{/fbvFormArea}
	{/if}{* !$skipFilesDirSection *}

	<!-- Security configuration -->
	{fbvFormArea id="securityFormArea" title="installer.securitySettings" border=true}
		{fbvFormSection title="installer.encryption" description="installer.encryptionInstructions"}
			{fbvElement type="select" id="encryption" from=$encryptionOptions selected=$encryption translate=false size=$fbvStyles.size.SMALL}
		{/fbvFormSection}
	{/fbvFormArea}

	<!-- Administrator username, password, and email -->
	{fbvFormArea id="administratorAccountFormArea" title="installer.administratorAccount" border=true}
		<p>{translate key="installer.administratorAccountInstructions"}</p>
		{fbvFormSection title="user.username"}
			{fbvElement type="text" id="adminUsername" value=$adminUsername|escape maxlength="32" size=$fbvStyles.size.MEDIUM}
		{/fbvFormSection}
		{fbvFormSection title="user.password"}
			{fbvElement type="text" password=true id="adminPassword" value=$adminPassword|escape maxlength="32" size=$fbvStyles.size.MEDIUM}
		{/fbvFormSection}
		{fbvFormSection title="user.repeatPassword"}
			{fbvElement type="text" password=true id="adminPassword2" value=$adminPassword2|escape maxlength="32" size=$fbvStyles.size.MEDIUM}
		{/fbvFormSection}
		{fbvFormSection title="user.email"}
			{fbvElement type="text" id="adminEmail" value=$adminEmail|escape maxlength="90" size=$fbvStyles.size.MEDIUM}
		{/fbvFormSection}
	{/fbvFormArea}

	<!-- Database configuration -->
	{fbvFormArea id="databaseSettingsFormArea" title="installer.databaseSettings"}
		<p>{translate key="installer.databaseSettingsInstructions"}</p>
		{fbvFormSection title="installer.databaseDriver" description="installer.databaseDriverInstructions"}
			{fbvElement type="select" id="databaseDriver" from=$databaseDriverOptions selected=$databaseDriver translate=false size=$fbvStyles.size.SMALL}
		{/fbvFormSection}
		{fbvFormSection title="installer.databaseHost"}
			{fbvElement type="text" id="databaseHost" value=$databaseHost|escape maxlength="60" size=$fbvStyles.size.MEDIUM}
		{/fbvFormSection}
		{fbvFormSection title="installer.databaseUsername"}
			{fbvElement type="text" id="databaseUsername" value=$databaseUsername|escape maxlength="60" size=$fbvStyles.size.MEDIUM}
		{/fbvFormSection}
		{fbvFormSection title="installer.databasePassword"}
			{fbvElement type="text" id="databasePassword" value=$databasePassword|escape maxlength="60" size=$fbvStyles.size.MEDIUM}
		{/fbvFormSection}
		{fbvFormSection title="installer.databaseName"}
			{fbvElement type="text" id="databaseName" value=$databaseName|escape maxlength="60" size=$fbvStyles.size.MEDIUM}
		{/fbvFormSection}
		{fbvFormSection list="true"}
			{fbvElement type="checkbox" id="createDatabase" value="1" checked=$createDatabase label="installer.createDatabase"}
		{/fbvFormSection}
	{/fbvFormArea}

	{if !$skipMiscSettings}
		 {fbvFormArea id="miscSettingsFormArea" title="installer.miscSettings"}
			{fbvFormSection title="installer.oaiRepositoryId" description="installer.oaiRepositoryIdInstructions"}
				{fbvElement type="text" id="oaiRepositoryId" value=$oaiRepositoryId|escape maxlength="60" size=$fbvStyles.size.LARGE}
			{/fbvFormSection}
		{/fbvFormArea}
	{/if}{* !$skipMiscSettings *}

	{fbvFormButtons id="appearanceFormSubmit" submitText="common.save" hideCancel=true submitText="installer.installApplication"}
</form>

{include file="common/footer.tpl"}

