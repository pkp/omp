{**
 * importUsersConfirm.tpl
 *
 * Copyright (c) 2003-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Show the results of importing users.
 *}
{strip}
{assign var="pageTitle" value="plugins.importexport.users.displayName"}
{/strip}

<script type="text/javascript">
	$(function() {ldelim}
		// Attach the form handler.
		$('#importUsersConfirmForm').pkpHandler('$.pkp.controllers.form.FormHandler');
	{rdelim});
</script>

{translate key="plugins.importexport.users.import.confirmUsers"}:
<form class="pkp_form" id="importUsersConfirmForm" action="{plugin_url path="import"}" method="post">
{if $sendNotify}
	<input type="hidden" name="sendNotify" value="{$sendNotify|escape}" />
{/if}
{if $continueOnError}
	<input type="hidden" name="continueOnError" value="{$continueOnError|escape}" />
{/if}

{if $errors}
	<p>
		<span class="pkp_form_error">{translate key="plugins.importexport.users.import.warning"}:</span>
		<ul class="pkp_form_error_list">
			{foreach key=field item=message from=$errors}
				<li>{$message}</li>
			{/foreach}
		</ul>
	</p>
{/if}

<table width="100%" class="pkp_listing">
	<tr>
		<td colspan="7" class="headseparator">&nbsp;</td>
	</tr>
	<tr class="heading" valign="bottom">
		<td width="5%">&nbsp;</td>
		<td width="10%">{translate key="user.firstName"}</td>
		<td width="10%">{translate key="user.middleName"}</td>
		<td width="15%">{translate key="user.lastName"}</td>
		<td width="15%">{translate key="user.username"}</td>
		<td width="20%">{translate key="user.email"}</td>
		<td width="25%">{translate key="user.roles"}</td>
	</tr>
	<tr>
		<td colspan="7" class="headseparator">&nbsp;</td>
	</tr>
{foreach name=users from=$users item=user key=userKey}
	<tr valign="top">
		<td>
			{fbvElement type="checkbox" name="userKeys[]" id=$userKeys|concat:"userKeys" value=$userKey checked="true"}
			{foreach from=$user->getBiography(null) key=locale item=value}
				<input type="hidden" name="{$userKey|escape}_biography[{$locale|escape}]" value="{$value|escape}" />
			{/foreach}
			{foreach from=$user->getSignature(null) key=locale item=value}
				<input type="hidden" name="{$userKey|escape}_signature[{$locale|escape}]" value="{$value|escape}" />
			{/foreach}
			<input type="hidden" name="{$userKey|escape}_interests" value="{$interestManager->getInterestsString($user)|escape}" />
			{foreach from=$user->getGossip(null) key=locale item=value}
				<input type="hidden" name="{$userKey|escape}_gossip[{$locale|escape}]" value="{$value|escape}" />
			{/foreach}
			{foreach name=locales from=$user->getLocales() item=locale}
				<input type="hidden" name="{$userKey|escape}_locales[]" value="{$locale|escape}" />
			{/foreach}
			<input type="hidden" name="{$userKey|escape}_country" value="{$user->getCountry()|escape}" />
			<input type="hidden" name="{$userKey|escape}_mailingAddress" value="{$user->getMailingAddress()|escape}" />
			<input type="hidden" name="{$userKey|escape}_fax" value="{$user->getFax()|escape}" />
			<input type="hidden" name="{$userKey|escape}_phone" value="{$user->getPhone()|escape}" />
			<input type="hidden" name="{$userKey|escape}_url" value="{$user->getUrl()|escape}" />
			{foreach from=$user->getAffiliation(null) key=locale item=value}
				<input type="hidden" name="{$userKey|escape}_affiliation[{$locale|escape}]" value="{$value|escape}" />
			{/foreach}
			<input type="hidden" name="{$userKey|escape}_gender" value="{$user->getGender()|escape}" />
			<input type="hidden" name="{$userKey|escape}_initials" value="{$user->getInitials()|escape}" />
			<input type="hidden" name="{$userKey|escape}_salutation" value="{$user->getSalutation()|escape}" />
			<input type="hidden" name="{$userKey|escape}_password" value="{$user->getPassword()|escape}" />
			<input type="hidden" name="{$userKey|escape}_unencryptedPassword" value="{$user->getUnencryptedPassword()|escape}" />
			<input type="hidden" name="{$userKey|escape}_mustChangePassword" value="{$user->getMustChangePassword()|escape}" />
		</td>
		<td>{fbvElement type="text" id=$userKey|concat:"_firstName" value=$user->getFirstName() size=$fbvStyles.size.SMALL}</td>
		<td>{fbvElement type="text" id=$userKey|concat:"_middeName" value=$user->getMiddleName() size=$fbvStyles.size.SMALL}</td>
		<td>{fbvElement type="text" id=$userKey|concat:"_lastName" value=$user->getLastName() size=$fbvStyles.size.SMALL}</td>
		<td>{fbvElement type="text" id=$userKey|concat:"_userName" value=$user->getUserName() size=$fbvStyles.size.SMALL}</td>
		<td>{fbvElement type="text" id=$userKey|concat:"_email" value=$user->getEmail() size=$fbvStyles.size.SMALL}</td>
		<td>
			{fbvElement type="select" id=$userKey|concat:"_roles" name=$userKey|concat:"_roles[]" from=$roleOptions selected=$usersRoles[$userKey] required="true" multiple="true"}
		</td>
	</tr>
	<tr>
		<td colspan="7" class="{if $smarty.foreach.users.last}end{/if}separator">&nbsp;</td>
	</tr>
	{foreachelse}
	<tr>
		<td colspan="7" class="nodata">{translate key="manager.people.noneEnrolled"}</td>
	</tr>
	<tr>
		<td colspan="7" class="endseparator">&nbsp;</td>
	</tr>
{/foreach}
</table>

{url|assign:cancelUrl router=$smarty.const.ROUTE_PAGE page="manager" op="importexport" path="plugin"|to_array:$plugin->getName()}
{fbvFormButtons cancelUrl=$cancelUrl submitText="plugins.importexport.users.import.importUsers"}

{if $isError}
<p>
	<span class="pkp_form_error">{translate key="plugins.importexport.users.import.errorsOccurred"}:</span>
	<ul class="pkp_form_error_list">
	{foreach key=field item=message from=$errors}
			<li>{$message}</li>
	{/foreach}
	</ul>
</p>
{/if}
</form>