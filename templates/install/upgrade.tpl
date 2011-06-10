{**
 * upgrade.tpl
 *
 * Copyright (c) 2003-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Upgrade form.
 *}
{strip}
{assign var="pageTitle" value="installer.ompUpgrade"}
{include file="common/header.tpl"}
{/strip}

{translate key="installer.upgradeInstructions" version=$version->getVersionString() baseUrl=$baseUrl}


<div class="separator"></div>


<form class="pkp_form" method="post" action="{url op="installUpgrade"}">
{include file="common/formErrors.tpl"}

{if $isInstallError}
<p>
	<span class="pkp_controllers_form_error">{translate key="installer.installErrorsOccurred"}:</span>
	<ul class="pkp_controllers_form_error_list">
		<li>{if $dbErrorMsg}{translate key="common.error.databaseError" error=$dbErrorMsg}{else}{translate key=$errorMsg}{/if}</li>
	</ul>
</p>
{/if}


<p><input type="submit" value="{translate key="installer.upgradeOMP"}" class="button defaultButton" /> <input type="submit" name="manualInstall" value="{translate key="installer.manualUpgrade"}" class="button" /></p>

</form>

{include file="common/footer.tpl"}

