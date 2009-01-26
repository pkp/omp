{**
 * authorMetadataEdit.tpl
 *
 * Copyright (c) 2003-2008 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Form for changing metadata of an article.
 *
 * $Id: 
 *}
{strip}
{assign var="pageTitle" value="submission.editMetadata"}
{include file="common/header.tpl"}
{/strip}
<form action="" method="post">
<input type="hidden" name="monographId" value="{$monographId}" />
<input type="hidden" name="authorId" value="{$authorId|escape}" />

 <table width="100%" class="data">

	<tr valign="top">
		<td width="20%" class="label">
			{fieldLabel name="firstName" required="true" key="user.firstName"}
		</td>
		<td width="80%" class="value">
			<input type="text" name="firstName" value="{$author->getFirstName()|escape}" size="20" maxlength="40" class="textField" />
		</td>
	</tr>
	<tr valign="top">
		<td class="label">{fieldLabel name="middleName" key="user.middleName"}</td>
		<td class="value"><input type="text" name="middleName" value="{$author->getMiddleName|escape}" size="20" maxlength="40" class="textField" /></td>
	</tr>
	<tr valign="top">
		<td class="label">{fieldLabel name="lastName" required="true" key="user.lastName"}</td>
		<td class="value"><input type="text" name="lastName" value="{$author->getLastName|escape}" size="20" maxlength="90" class="textField" /></td>
	</tr>
	<tr valign="top">
		<td class="label">{fieldLabel name="affiliation" key="user.affiliation"}</td>
		<td class="value"><input type="text" name="affiliation" value="{$author->getAffiliation|escape}" size="30" maxlength="255" class="textField" /></td>
	</tr>
	<tr valign="top">
		<td class="label">{fieldLabel name="country" key="common.country"}</td>
		<td class="value">
			<select name="country" class="selectMenu">
				<option value=""></option>
				{html_options options=$countries selected=$country|escape}
			</select>
		</td>
	</tr>
	<tr valign="top">
		<td class="label">{fieldLabel name="email" required="true" key="user.email"}</td>
		<td class="value"><input type="text" name="email" value="{$author->getEmail()|escape}" size="30" maxlength="90" class="textField" /></td>
	</tr>
	<tr valign="top">
		<td class="label">{fieldLabel name="url" key="user.url"}</td>
		<td class="value"><input type="text" name="url" value="{$author->getUrl()|escape}" size="30" maxlength="90" class="textField" /></td>
	</tr>
	<tr valign="top">
		<td class="label">{fieldLabel name="biography" key="user.biography"}<br />{translate key="user.biography.description"}</td>
		<td class="value"><textarea name="biography[{$formLocale|escape}]" rows="5" cols="40" class="textArea">{$author->getBiography()|escape}</textarea></td>
	</tr>

</table>

<input type="submit" value="{translate key="author.updateInfo"}" class="button defaultButton" /> <input value="{translate key="common.cancel"}" class="button" onclick="history.go(-1)" type="button">


</form>

{include file="common/footer.tpl"}
