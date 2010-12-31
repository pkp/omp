{**
 * pressSettings.tpl
 *
 * Copyright (c) 2003-2010 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Basic press settings under site administration.
 *}
{strip}
{assign var="pageTitle" value="admin.presses.pressSettings"}
{include file="common/header.tpl"}
{/strip}

<br />

<script type="text/javascript">
{literal}
<!--
// Ensure that the form submit button cannot be double-clicked
function doSubmit() {
	if (document.press.submitted.value != 1) {
		document.press.submitted.value = 1;
		document.press.submit();
	}
	return true;
}
// -->
{/literal}
</script>

<form id="press" method="post" action="{url op="updatePress"}">
<input type="hidden" name="submitted" value="0" />
{if $pressId}
<input type="hidden" name="pressId" value="{$pressId|escape}" />
{/if}

{include file="common/formErrors.tpl"}

{if not $pressId}
<p><span class="instruct">{translate key="admin.presses.createInstructions"}</span></p>
{/if}

<table class="data" width="100%">
{if count($formLocales) > 1}
	<tr valign="top">
		<td width="20%" class="label">{fieldLabel name="formLocale" key="form.formLanguage"}</td>
		<td width="80%" class="value">
			{url|assign:"settingsUrl" op="editPress" path=$pressId}
			{form_language_chooser form="press" url=$settingsUrl}
			<span class="instruct">{translate key="form.formLanguage.description"}</span>
		</td>
	</tr>
{/if}
	<tr valign="top">
		<td width="20%" class="label">{fieldLabel name="name" key="manager.setup.pressName" required="true"}</td>
		<td width="80%" class="value"><input type="text" id="name" name="name[{$formLocale|escape}]" value="{$name[$formLocale]|escape}" size="40" maxlength="120" class="textField" /></td>
	</tr>
	<tr valign="top">
		<td class="label">{fieldLabel name="description" key="admin.presses.pressDescription"}</td>
		<td class="value"><textarea name="description[{$formLocale|escape}]" id="description" cols="40" rows="10" class="textArea">{$description[$formLocale]|escape}</textarea></td>
	</tr>
	<tr valign="top">
		<td class="label">{fieldLabel name="path" key="press.path" required="true"}</td>
		<td class="value">
			<input type="text" id="path" name="path" value="{$path|escape}" size="16" maxlength="32" class="textField" />
			<br />
			{url|assign:"sampleUrl" press="path"}
			<span class="instruct">{translate key="admin.presses.urlWillBe" sampleUrl=$sampleUrl}</span>
		</td>
	</tr>
	<tr valign="top">
		<td colspan="2" class="label">
			<input type="checkbox" name="enabled" id="enabled" value="1"{if $enabled} checked="checked"{/if} /> <label for="enabled">{translate key="admin.presses.enablePressInstructions"}</label>
		</td>
	</tr>
</table>

<p><input type="button" id="savePress" value="{translate key="common.save"}" class="button defaultButton" onclick="doSubmit()" /> <input type="button" value="{translate key="common.cancel"}" class="button" onclick="document.location.href='{url op="presses" escape=false}'" /></p>

</form>

<p><span class="formRequired">{translate key="common.requiredField"}</span></p>

{include file="common/footer.tpl"}

