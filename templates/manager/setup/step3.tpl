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
{assign var="pageTitle" value="manager.setup.preparingWorkflow"}
{include file="manager/setup/setupHeader.tpl"}

<script type="text/javascript">
{literal}
<!--

function addWorkflowRole(fromSelect, toElementId, prefix) {
  fromSelectElement=document.setupForm.elements[fromSelect];
  role=fromSelectElement.options[fromSelectElement.selectedIndex];
  roleText=role.text;
  roleId=role.value;

  fromSelectElement.removeChild(role);

  //create elements
  toElement=document.getElementById(toElementId);
  var roleDiv = document.createElement('div');
  roleDiv.id=prefix+'-'+roleId;

  var removeButton = document.createElement('input');
  removeButton.type='button';
  removeButton.className='button';
  removeButton.value='X';
  removeButton.setAttribute('onclick', 'removeWorkflowRole(\''+fromSelect+'\',\''+prefix+'\',\''+roleId+'\',\''+roleText+'\')');

  var roleInfo = document.createElement('input');
  roleInfo.type='hidden';
  roleInfo.name=prefix+'['+roleId+']';
  roleInfo.value=roleId;

  //create tree
  var roleRow = document.createElement('p');
  roleRow.appendChild(removeButton);
  roleRow.appendChild(document.createTextNode(roleText));
  roleDiv.appendChild(roleInfo);
  roleDiv.appendChild(roleRow);
  toElement.appendChild(roleDiv);
}

function removeWorkflowRole(toName, prefix, roleId, roleName) {
  var toElement=document.setupForm.elements[toName];
  var fromElement=document.getElementById(prefix+'-'+roleId);

  fromElement.parentNode.removeChild(fromElement);

  var option=document.createElement('option');
  option.value=roleId;
  option.appendChild(document.createTextNode(roleName));

  toElement.appendChild(option);
}

// -->
{/literal}
</script>

<form name="setupForm" method="post" action="{url op="saveSetup" path="3"}" enctype="multipart/form-data">
{include file="common/formErrors.tpl"}

{if count($formLocales) > 1}
{fbvFormArea id="locales"}
{fbvFormSection title="form.formLanguage" for="languageSelector"}
	{fbvCustomElement}
		{url|assign:"setupFormUrl" op="setup" path="1"}
		{form_language_chooser form="setupForm" url=$setupFormUrl}
		<span class="instruct">{translate key="form.formLanguage.description"}</span>
	{/fbvCustomElement}
{/fbvFormSection}
{/fbvFormArea}
{/if} {* count($formLocales) > 1*}

<h3>3.1 {translate key="manager.setup.pressRoles"}</h3>

<p>{translate key="manager.setup.pressRolesDescription"}</p>

<table border="0" align="center">
	<tr>
		<td>
			{assign var="flexRoleAuthorId" value=$smarty.const.FLEXIBLE_ROLE_CLASS_AUTHOR}
			{assign var="flexRolePressId" value=$smarty.const.FLEXIBLE_ROLE_CLASS_PRESS}

			<p>{translate key="manager.setup.roleName"}</p>
			<input type="text" name="newRole[name]" class="textField" />
			<p>{translate key="manager.setup.roleAbbrev"}</p>
			<input type="text" name="newRole[abbrev]" class="textField" />
			<p>{translate key="manager.setup.roleType"}</p>
			<input type="radio" name="newRole[type]" checked="checked" value="{$flexRoleAuthorId}" /> {translate key="manager.setup.authorRole"}
			<input type="radio" name="newRole[type]" value="{$flexRolePressId}" /> {translate key="manager.setup.pressRole"}
		</td>
		<td valign="center">
			<input class="button defaultButton" name="addRole" style="width:100px;" type="submit" value="&rarr; {translate key="common.add"}" />
			<input type="hidden" name="deletedFlexibleRoles" value="{$deletedFlexibleRoles|escape}" />
		</td>
		<td>
			<p><strong>{translate key="manager.setup.authorRoles"}</strong></p>
			<div id="authorRoles" class="flexibleRolesList">
			{foreach from=$additionalRoles.$flexRoleAuthorId key=key item=additionalRole}
				<input type="hidden" name="additionalRoles[{$flexRoleAuthorId}][{$key|escape}][flexibleRoleId]" value="{$additionalRole.flexibleRoleId|escape}" />
				<input type="hidden" name="additionalRoles[{$flexRoleAuthorId}][{$key|escape}][name][{$formLocale|escape}]" value="{$additionalRole.name.$formLocale|escape}"/>
				<input type="hidden" name="additionalRoles[{$flexRoleAuthorId}][{$key|escape}][abbrev][{$formLocale|escape}]" value="{$additionalRole.abbrev.$formLocale|escape}"/>
				<p><input type="submit" class="button" name="removeRole[{$flexRoleAuthorId}][{$key|escape}]" value="X" />&nbsp;{$additionalRole.name.$formLocale|escape}&nbsp;({$additionalRole.abbrev.$formLocale|escape})</p>
			{/foreach}
			</div>
			<p><strong>{translate key="manager.setup.pressRoles"}</strong></p>
			<div id="pressRoles" class="flexibleRolesList">
			{foreach from=$additionalRoles.$flexRolePressId key=key item=additionalRole}
				<input type="hidden" name="additionalRoles[{$flexRolePressId}][{$key|escape}][flexibleRoleId]" value="{$additionalRole.flexibleRoleId|escape}" />
				<input type="hidden" name="additionalRoles[{$flexRolePressId}][{$key|escape}][name][{$formLocale|escape}]" value="{$additionalRole.name.$formLocale|escape}"/>
				<input type="hidden" name="additionalRoles[{$flexRolePressId}][{$key|escape}][abbrev][{$formLocale|escape}]" value="{$additionalRole.abbrev.$formLocale|escape}"/>
				<p><input type="submit" class="button" name="removeRole[{$flexRolePressId}][{$key|escape}]" value="X" />&nbsp;{$additionalRole.name.$formLocale|escape}&nbsp;({$additionalRole.abbrev.$formLocale|escape})</p>
			{/foreach}
			</div>
			<input type="hidden" name="nextRoleId" value="{$nextRoleId|escape}" />
		</td>
	</tr>
</table>

<div class="separator"></div>

<h3>3.2 {translate key="manager.setup.submissionRoles"}</h3>

<p>{translate key="manager.setup.submissionRolesDescription"}</p>

<table border="0" align="center">
<tr>
	<td>
		<p><strong>{translate key="manager.setup.availableRoles"}</strong></p>
		<select name="availableSubmissionRoles">
		{foreach from=$additionalRoles.$flexRoleAuthorId key=key item=additionalRole}
			{if !isset($submissionRoles.$key)}<option value="{$key|escape}">{$additionalRole.name.$formLocale|escape} ({$additionalRole.abbrev.$formLocale|escape})</option>{/if}
		{/foreach}
		</select>
	</td>
	<td valign="center" style="width:7em">
		<input class="button defaultButton" type="button" value="&rarr; {translate key="common.add"}" onclick="addWorkflowRole('availableSubmissionRoles','currentSubmissionRoles','submissionRoles');" />
	</td>
	<td>
		<p><strong>{translate key="manager.setup.currentRoles"}</strong></p>
		<div id="currentSubmissionRoles" class="flexibleRolesList">
		{foreach from=$submissionRoles key=key item=currentRole}
		{assign var="roleName" value=$additionalRoles.$flexRoleAuthorId.$key.name.$formLocale|cat:" ("|cat:$additionalRoles.$flexRoleAuthorId.$key.abbrev.$formLocale|cat:")"}
		<div id="submissionRoles-{$key|escape}">
			<input type="hidden" name="submissionRoles[{$key|escape}]" value=""/>
			<p><input type="button" class="button" onclick="removeWorkflowRole('availableSubmissionRoles','submissionRoles','{$key|escape}','{$roleName|escape}')" value="X" />{$roleName|escape}</p>
		</div>
		{/foreach}
		</div>
	</td>
</tr>
</table>

<div class="separator"></div>

<h3>3.3 {translate key="manager.setup.bookFileTypes"}</h3>

<p>{translate key="manager.setup.bookFileTypesDescription"}</p>

{foreach name=bookFileTypes from=$bookFileTypes item=fileTypeItem}
	{if !$notFirstFileTypeItem}
		{assign var=notFirstFileTypeItem value=1}
		<table width="100%" class="data">
			<tr valign="top">
				<td width="5%">&nbsp;</td>
				<td width="30%">{translate key="common.name"}</td>
				<td width="70%">{translate key="common.designation"}</td>
			</tr>
	{/if}

	<tr valign="top">
		<td><input type="checkbox" name="bookFileTypeSelect[]" value="{$fileTypeItem->getId()|escape}" /></td>
		<td>
			{if $fileTypeItem->getName($formLocale)}
				{$fileTypeItem->getName($formLocale)|escape}
			{else}
				<input type="text" name="bookFileTypeUpdate[{$fileTypeItem->getId()|escape}][name]" value="{$fileTypeItem->getName($primaryLocale)|escape}"/>
			{/if}
		</td>
		<td>
			{if $fileTypeItem->getSortable()}
				{$smarty.const.BOOK_FILE_TYPE_SORTABLE_DESIGNATION}
			{elseif $fileTypeItem->getDesignation($formLocale)}
				{$fileTypeItem->getDesignation($formLocale)|escape}
			{else}
				<input type="text" name="bookFileTypeUpdate[{$fileTypeItem->getId()|escape}][designation]" value="{$fileTypeItem->getDesignation($primaryLocale)|escape}"/>
			{/if}
		</td>
	</tr>
	{if !$fileTypeItem->getName($formLocale) or (!$fileTypeItem->getDesignation($formLocale) and !$fileTypeItem->getSortable())}
	<tr valign="top">
		<td colspan="3">
			<input type="submit" name="updateBookFileType[{$fileTypeItem->getId()|escape}]" value="{translate key="common.update"}" class="button" />
		</td>
	</tr>
	{/if}
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
	<td>{translate key="common.name"}</td><td><input type="text" name="newBookFileName[{$formLocale|escape}]" class="textField" /></td>
</tr>
<tr>
	<td>{translate key="common.designation"}</td><td><input type="text" name="newBookFileDesignation[{$formLocale|escape}]" class="textField" /></td>
</tr>
<tr>
	<td>&nbsp;</td><td><input type="checkbox" name="newBookFileSortable" class="textField" /> {translate key="manager.setup.sortableByComponent"}</td>
</tr>
<tr>
	<td>&nbsp;</td><td><input type="submit" name="addBookFileType" value="{translate key="common.create"}" class="button" /></td>
</tr>
</table>
</div>

<div class="separator"></div>

<h3>3.4 {translate key="manager.setup.submissionLibrary"}</h3>
{url|assign:submissionLibraryUrl router=$smarty.const.ROUTE_COMPONENT component="grid.library.LibraryFileGridHandler" op="fetchGrid" fileType=$smarty.const.LIBRARY_FILE_TYPE_SUBMISSION}
{load_url_in_div id="submissionLibraryGridDiv" url=$submissionLibraryUrl}

<div class="separator"></div>

<h3>3.5 {translate key="manager.setup.internalReviewRoles"}</h3>

<p>{translate key="manager.setup.internalReviewRolesDescription"}</p>

<table border="0" align="center">
<tr>
	<td>
		<p><strong>{translate key="manager.setup.availableRoles"}</strong></p>
		<select name="availableInternalReviewRoles">
		{foreach from=$additionalRoles.$flexRolePressId key=key item=additionalRole}
			{if !isset($internalReviewRoles.$key)}<option value="{$key|escape}">{$additionalRole.name.$formLocale|escape} ({$additionalRole.abbrev.$formLocale|escape})</option>{/if}
		{/foreach}
		</select>
	</td>
	<td valign="center" style="width:7em">
		<input class="button defaultButton" type="button" value="&rarr; {translate key="common.add"}" onclick="addWorkflowRole('availableInternalReviewRoles','currentInternalReviewRoles','internalReviewRoles');" />
	</td>
	<td>
		<p><strong>{translate key="manager.setup.currentRoles"}</strong></p>
		<div id="currentInternalReviewRoles" class="flexibleRolesList">
		{foreach from=$internalReviewRoles key=key item=currentRole}
		{assign var="roleName" value=$additionalRoles.$flexRolePressId.$key.name.$formLocale|cat:" ("|cat:$additionalRoles.$flexRolePressId.$key.abbrev.$formLocale|cat:")"}
		<div id="internalReviewRoles-{$key|escape}">
			<input type="hidden" name="internalReviewRoles[{$key|escape}]" value=""/>
			<p><input type="button" class="button" onclick="removeWorkflowRole('availableInternalReviewRoles','internalReviewRoles','{$key|escape}','{$roleName|escape}')" value="X" />{$roleName|escape}</p>
		</div>
		{/foreach}
		</div>
	</td>
</tr>
</table>

<div class="separator"></div>

<h3>3.6 {translate key="manager.setup.externalReviewRoles"}</h3>

<p>{translate key="manager.setup.externalReviewRolesDescription"}</p>

<table border="0" align="center">
<tr>
	<td>
		<p><strong>{translate key="manager.setup.availableRoles"}</strong></p>
		<select name="availableExternalReviewRoles">
		{foreach from=$additionalRoles.$flexRolePressId key=key item=additionalRole}
			{if !isset($externalReviewRoles.$key)}<option value="{$key|escape}">{$additionalRole.name.$formLocale|escape} ({$additionalRole.abbrev.$formLocale|escape})</option>{/if}
		{/foreach}
		</select>
	</td>
	<td valign="center" style="width:7em">
		<input class="button defaultButton" type="button" value="&rarr; {translate key="common.add"}" onclick="addWorkflowRole('availableExternalReviewRoles','currentExternalReviewRoles','externalReviewRoles');" />
	</td>
	<td>
		<p><strong>{translate key="manager.setup.currentRoles"}</strong></p>
		<div id="currentExternalReviewRoles" class="flexibleRolesList">
		{foreach from=$externalReviewRoles key=key item=currentRole}
		{assign var="roleName" value=$additionalRoles.$flexRolePressId.$key.name.$formLocale|cat:" ("|cat:$additionalRoles.$flexRolePressId.$key.abbrev.$formLocale|cat:")"}
		<div id="externalReviewRoles-{$key|escape}">
			<input type="hidden" name="externalReviewRoles[{$key|escape}]" value=""/>
			<p><input type="button" class="button" onclick="removeWorkflowRole('availableExternalReviewRoles','externalReviewRoles','{$key|escape}','{$roleName|escape}')" value="X" />{$roleName|escape}</p>
		</div>
		{/foreach}
		</div>
	</td>
</tr>
</table>

<div class="separator"></div>

<h3>3.7 {translate key="manager.setup.reviewLibrary"}</h3>

<div class="separator"></div>

<h3>3.8 {translate key="manager.setup.reviewForms"}</h3>

<div class="separator"></div>

<h3>3.9 {translate key="manager.setup.editorialRoles"}</h3>

<p>{translate key="manager.setup.editorialRolesDescription"}</p>

<table border="0" align="center">
<tr>
	<td>
		<p><strong>{translate key="manager.setup.availableRoles"}</strong></p>
		<select name="availableEditorialRoles">
		{foreach from=$additionalRoles.$flexRolePressId key=key item=additionalRole}
			{if !isset($editorialRoles.$key)}<option value="{$key|escape}">{$additionalRole.name.$formLocale|escape} ({$additionalRole.abbrev.$formLocale|escape})</option>{/if}
		{/foreach}
		</select>
	</td>
	<td valign="center" style="width:7em">
		<input class="button defaultButton" type="button" value="&rarr; {translate key="common.add"}" onclick="addWorkflowRole('availableEditorialRoles','currentEditorialRoles','editorialRoles');" />
	</td>
	<td>
		<p><strong>{translate key="manager.setup.currentRoles"}</strong></p>
		<div id="currentEditorialRoles" class="flexibleRolesList">
		{foreach from=$editorialRoles key=key item=currentRole}
		{assign var="roleName" value=$additionalRoles.$flexRolePressId.$key.name.$formLocale|cat:" ("|cat:$additionalRoles.$flexRolePressId.$key.abbrev.$formLocale|cat:")"}
		<div id="editorialRoles-{$key|escape}">
			<input type="hidden" name="editorialRoles[{$key|escape}]" value=""/>
			<p><input type="button" class="button" onclick="removeWorkflowRole('availableEditorialRoles','editorialRoles','{$key|escape}','{$roleName|escape}')" value="X" />{$roleName|escape}</p>
		</div>
		{/foreach}
		</div>
	</td>
</tr>
</table>

<div class="separator"></div>

<h3>3.10 {translate key="manager.setup.editorialLibrary"}</h3>

<div class="separator"></div>

<h3>3.11 {translate key="manager.setup.productionRoles"}</h3>

<p>{translate key="manager.setup.productionRolesDescription"}</p>

<table border="0" align="center">
<tr>
	<td>
		<p><strong>{translate key="manager.setup.availableRoles"}</strong></p>
		<select name="availableProductionRoles">
		{foreach from=$additionalRoles.$flexRolePressId key=key item=additionalRole}
			{if !isset($productionRoles.$key)}<option value="{$key|escape}">{$additionalRole.name.$formLocale|escape} ({$additionalRole.abbrev.$formLocale|escape})</option>{/if}
		{/foreach}
		</select>
	</td>
	<td valign="center" style="width:7em">
		<input class="button defaultButton" type="button" value="&rarr; {translate key="common.add"}" onclick="addWorkflowRole('availableProductionRoles','currentProductionRoles','productionRoles');" />
	</td>
	<td>
		<p><strong>{translate key="manager.setup.currentRoles"}</strong></p>
		<div id="currentProductionRoles" class="flexibleRolesList">
		{foreach from=$productionRoles key=key item=currentRole}
		{assign var="roleName" value=$additionalRoles.$flexRolePressId.$key.name.$formLocale|cat:" ("|cat:$additionalRoles.$flexRolePressId.$key.abbrev.$formLocale|cat:")"}
		<div id="productionRoles-{$key|escape}">
			<input type="hidden" name="productionRoles[{$key|escape}]" value=""/>
			<p><input type="button" class="button" onclick="removeWorkflowRole('availableProductionRoles','productionRoles','{$key|escape}','{$roleName|escape}')" value="X" />{$roleName|escape}</p>
		</div>
		{/foreach}
		</div>
	</td>
</tr>
</table>

<div class="separator"></div>

<h3>3.12 {translate key="manager.setup.productionLibrary"}</h3>

<div class="separator"></div>

<h3>3.13 {translate key="manager.setup.productionTemplates"}</h3>

<div class="separator"></div>

<h3>3.14 {translate key="manager.setup.publicationFormats}</h3>

<p>{translate key="manager.setup.publicationFormatsDescription"}</p>

{foreach name=publicationFormats from=$publicationFormats item=fileTypeItem}
	{if !$notFirstFormatItem}
		{assign var=notFirstFormatItem value=1}
		<table width="100%" class="data">
			<tr valign="top">
				<td width="5%">&nbsp;</td>
				<td width="30%">{translate key="common.name"}</td>
				<td width="70%">{translate key="common.designation"}</td>
			</tr>
	{/if}

	<tr valign="top">
		<td><input type="checkbox" name="publicationFormatSelect[]" value="{$fileTypeItem->getId()|escape}" /></td>
		<td>
			{if $fileTypeItem->getName($formLocale)}
				{$fileTypeItem->getName($formLocale)|escape}
			{else}
				<input type="text" name="publicationFormatUpdate[{$fileTypeItem->getId()|escape}][name]" value="{$fileTypeItem->getName($primaryLocale)|escape}"/>
			{/if}
		</td>
		<td>
			{if $fileTypeItem->getDesignation($formLocale)}
				{$fileTypeItem->getDesignation($formLocale)|escape}
			{else}
				<input type="text" name="publicationFormatUpdate[{$fileTypeItem->getId()|escape}][designation]" value="{$fileTypeItem->getDesignation($primaryLocale)|escape}"/>
			{/if}
		</td>
	</tr>
	{if !$fileTypeItem->getName($formLocale) or !$fileTypeItem->getDesignation($formLocale)}
	<tr valign="top">
		<td colspan="3">
			<input type="submit" name="updatePublicationFormat[{$fileTypeItem->getId()|escape}]" value="{translate key="common.update"}" class="button" />
		</td>
	</tr>
	{/if}
{/foreach}
{if $notFirstFormatItem}
	</table>
{/if}
<p>
<input type="submit" name="deleteSelectedPublicationFormats" value="{translate key="manager.setup.deleteSelected"}" class="button" />
<input type="submit" name="restoreDefaultPublicationFormats" value="{translate key="manager.setup.restoreDefaults"}" class="button" />
</p>

<div class="newItemContainer">
<h3>{translate key="manager.setup.newPublicationFormat"}</h3>
<p>{translate key="manager.setup.newPublicationFormatDescription"}</p>
<table>
<tr>
	<td>{translate key="common.name"}</td><td><input type="text" name="newPublicationFormatName[{$formLocale|escape}]" class="textField" /></td>
</tr>
<tr>
	<td>{translate key="common.designation"}</td><td><input type="text" name="newPublicationFormatDesignation[{$formLocale|escape}]" class="textField" /></td>
</tr>
<tr>
	<td>&nbsp;</td><td><input type="submit" name="addPublicationFormat" value="{translate key="common.create"}" class="button" /></td>
</tr>
</table>
</div>

<div class="separator"></div>

<p><input type="submit" value="{translate key="common.saveAndContinue"}" class="button defaultButton" /> <input type="button" value="{translate key="common.cancel"}" class="button" onclick="document.location.href='{url op="setup" escape=false}'" /></p>

<p><span class="formRequired">{translate key="common.requiredField"}</span></p>

</form>

{include file="common/footer.tpl"}
