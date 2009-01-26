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
{include file="common/formErrors.tpl}

<input type="hidden" name="newContributor[authorId]" value="{$contributors|@count}" />

<table width="100%" class="data">

	<tr valign="top">
		<td width="20%" class="label">
			{fieldLabel name="firstName" required="true" key="user.firstName"}
		</td>
		<td width="80%" class="value">
			<input type="text" name="newContributor[firstName]" value="{$newContributor.firstName|escape}" size="20" maxlength="40" class="textField" />
		</td>
	</tr>
	<tr valign="top">
		<td class="label">{fieldLabel name="middleName" key="user.middleName"}</td>
		<td class="value"><input type="text" name="newContributor[middleName]" value="{$newContributor.middleName|escape}" size="20" maxlength="40" class="textField" /></td>
	</tr>
	<tr valign="top">
		<td class="label">{fieldLabel name="lastName" required="true" key="user.lastName"}</td>
		<td class="value"><input type="text" name="newContributor[lastName]" value="{$newContributor.lastName|escape}" size="20" maxlength="90" class="textField" /></td>
	</tr>
	<tr valign="top">
		<td class="label">{fieldLabel name="affiliation" key="user.affiliation"}</td>
		<td class="value"><input type="text" name="newContributor[affiliation]" value="{$newContributor.affiliation|escape}" size="30" maxlength="255" class="textField" /></td>
	</tr>
	<tr valign="top">
		<td class="label">{fieldLabel name="country" key="common.country"}</td>
		<td class="value">
			<select name="newContributor[country]" class="selectMenu">
				<option value=""></option>
				{html_options options=$countries selected=$newContributor.country|escape}
			</select>
		</td>
	</tr>
	<tr valign="top">
		<td class="label">{fieldLabel name="email" required="true" key="user.email"}</td>
		<td class="value"><input type="text" name="newContributor[email]" value="{$newContributor.email|escape}" size="30" maxlength="90" class="textField" /></td>
	</tr>
	<tr valign="top">
		<td class="label">{fieldLabel name="url" key="user.url"}</td>
		<td class="value"><input type="text" name="newContributor[url]" value="{$newContributor.url|escape}" size="30" maxlength="90" class="textField" /></td>
	</tr>
	<tr valign="top">
		<td class="label">{fieldLabel name="biography" key="user.biography"}<br />{translate key="user.biography.description"}</td>
		<td class="value"><textarea name="newContributor[biography][{$formLocale|escape}]" rows="5" cols="40" class="textArea">{$newContributor.biography.$formLocale|escape}</textarea></td>
	
	</tr>
	<tr valign="top">
		<td width="80%" class="value" colspan="2">
			<input type="checkbox" name="newContributor[contributionType]" id="authors-{$author.authorId}-contributionType" value="1"{if VOLUME_EDITOR == $author.contributionType} checked="checked"{/if} /> <label for="authors-{$author.authorId}-contributionType">This contributor is a volume editor.</label>
		</td>
	</tr>

</table>

<input type="submit" name="addContributor" value="{translate key="author.updateInfo"}" class="button defaultButton" />
