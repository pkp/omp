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
 <table width="100%" class="data">

	<tr valign="top">
		<td width="20%" class="label">
			<input type="hidden" name="author[authorId]" value="{$author.authorId|escape}" />
			{if $smarty.foreach.authors.total <= 1}
				<input type="hidden" name="primaryContact" value="{$authorIndex|escape}" />
			{/if}
			{fieldLabel name="authors-$authorIndex-firstName" required="true" key="user.firstName"}
		</td>
		<td width="80%" class="value"><input type="text" name="author[firstName]" id="authors-{$authorIndex|escape}-firstName" value="{$firstName|escape}" size="20" maxlength="40" class="textField" /></td>
	</tr>
	<tr valign="top">
		<td class="label">{fieldLabel name="authors-$authorIndex-middleName" key="user.middleName"}</td>
		<td class="value"><input type="text" name="author[middleName]" id="authors-{$authorIndex|escape}-middleName" value="{$middleName|escape}" size="20" maxlength="40" class="textField" /></td>
	</tr>
	<tr valign="top">
		<td class="label">{fieldLabel name="authors-$authorIndex-lastName" required="true" key="user.lastName"}</td>
		<td class="value"><input type="text" name="author[lastName]" id="authors-{$authorIndex|escape}-lastName" value="{$lastName|escape}" size="20" maxlength="90" class="textField" /></td>
	</tr>
	<tr valign="top">
		<td class="label">{fieldLabel name="authors-$authorIndex-affiliation" key="user.affiliation"}</td>
		<td class="value"><input type="text" name="author[affiliation]" id="authors-{$authorIndex|escape}-affiliation" value="{$affiliation|escape}" size="30" maxlength="255" class="textField" /></td>
	</tr>
	<tr valign="top">
		<td class="label">{fieldLabel name="authors-$authorIndex-country" key="common.country"}</td>
		<td class="value">
			<select name="author[country]" id="authors-{$authorIndex|escape}-country" class="selectMenu">
				<option value=""></option>
				{html_options options=$countries selected=$country|escape}
			</select>
		</td>
	</tr>
	<tr valign="top">
		<td class="label">{fieldLabel name="authors-$authorIndex-email" required="true" key="user.email"}</td>
		<td class="value"><input type="text" name="author[email]" id="authors-{$authorIndex|escape}-email" value="{$email|escape}" size="30" maxlength="90" class="textField" /></td>
	</tr>
	<tr valign="top">
		<td class="label">{fieldLabel name="authors-$authorIndex-url" key="user.url"}</td>
		<td class="value"><input type="text" name="author[url]" id="authors-{$authorIndex|escape}-url" value="{$url|escape}" size="30" maxlength="90" class="textField" /></td>
	</tr>
	<tr valign="top">
		<td class="label">{fieldLabel name="authors-$authorIndex-biography" key="user.biography"}<br />{translate key="user.biography.description"}</td>
		<td class="value"><textarea name="author[biography][{$formLocale|escape}]" id="authors-{$authorIndex|escape}-biography" rows="5" cols="40" class="textArea">{$biography[$formLocale]|escape}</textarea></td>
	</tr>

</table>

<input type="submit" value="{translate key="author.updateInfo"}" /><input value="{translate key="common.cancel"}" class="button" onclick="history.go(-1)" type="button">

</form>

{include file="common/footer.tpl"}
