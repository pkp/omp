{**
 * step3.tpl
 *
 * Copyright (c) 2003-2008 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Step 3 of press setup.
 *
 * $Id$
 *}
{assign var="pageTitle" value="manager.setup.guidingSubmissions"}
{include file="manager/setup/setupHeader.tpl"}

<form name="setupForm" method="post" action="{url op="saveSetup" path="3"}" enctype="multipart/form-data">
{include file="common/formErrors.tpl"}

{if count($formLocales) > 1}
<table width="100%" class="data">
	<tr valign="top">
		<td width="20%" class="label">{fieldLabel name="formLocale" key="form.formLanguage"}</td>
		<td width="80%" class="value">
			{url|assign:"setupFormUrl" op="setup" path="3"}
			{form_language_chooser form="setupForm" url=$setupFormUrl}
			<span class="instruct">{translate key="form.formLanguage.description"}</span>
		</td>
	</tr>
</table>
{/if}

<h3>3.2 {translate key="manager.setup.bookFileTypes}</h3>

<p>{translate key="manager.setup.bookFileTypesDescription"}</p>

{foreach name=bookFileTypes from=$bookFileTypes[$formLocale] key=fileTypeId item=fileTypeItem}
	{if !$notFirstFileTypeItem}
		{assign var=notFirstFileTypeItem value=1}
		<table width="100%" class="data">
			<tr valign="top">
				<td width="5%">&nbsp;</td>
				<td width="30%">{translate key="common.type"}</td>
				<td width="70%">{translate key="common.filePrefix"}</td>
			</tr>
	{/if}

	<tr valign="top">
		<td><input type="checkbox" name="bookFileTypeSelect[]" value="{$fileTypeId}" /></td>
		<td>{$fileTypeItem.type}</td>
		<td>{$fileTypeItem.prefix}</td>
	</tr>
{/foreach}

{if $notFirstFileTypeItem}
	</table>
{/if}
<p>
<input type="submit" name="deleteSelectedBookFileTypes" value="{translate key="manager.setup.deleteSelected"}" class="button" />
<input type="submit" name="restoreDefaultBookFileTypes" value="{translate key="manager.setup.restoreDefaults"}" class="button" />
</p>
<div class="newItemContainer">
<h3>{translate key="manager.setup.newBookFileType"}</h3>
<p>{translate key="manager.setup.newBookFileTypeDescription"}</p>
<table>
<tr>
	<td>{translate key="common.filePrefix"}</td><td><input type="text" name="newBookFileType[prefix]" class="textField" /></td>
</tr>
<tr>
	<td>{translate key="common.type"}</td><td><input type="text" name="newBookFileType[type]" class="textField" /></td>
</tr>
<tr>
	<td>{translate key="common.description"}</td><td><textarea name="newBookFileType[description]" rows="5" cols="30" class="textArea"></textarea></td>
</tr>
<tr>
	<td>{translate key="common.sortableByComponent"}</td><td><input type="checkbox" name="newBookFileType[sortable]" class="textField" /></td>
</tr>
<tr>
	<td>&nbsp;</td><td><input type="submit" name="addBookFileType" value="{translate key="common.create"}" class="button" /></td>
</tr>
</table>
</div>

<div class="separator"></div>

<p><input type="submit" value="{translate key="common.saveAndContinue"}" class="button defaultButton" /> <input type="button" value="{translate key="common.cancel"}" class="button" onclick="document.location.href='{url op="setup" escape=false}'" /></p>

<p><span class="formRequired">{translate key="common.requiredField"}</span></p>

</form>

{include file="common/footer.tpl"}
