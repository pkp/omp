{**
 * templates/user/profile.tpl
 *
 * Copyright (c) 2003-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * User profile form.
 *}
{strip}
{assign var="pageTitle" value="user.profile.editProfile"}
{url|assign:"url" op="profile"}
{include file="common/header.tpl"}
{/strip}

<form class="pkp_form" id="profile" method="post" action="{url op="saveProfile"}" enctype="multipart/form-data">

{fbvFormArea id="profileForm"}

	{fbvFormSection title="user.username"}
		{$username|escape}
	{/fbvFormSection}

	{fbvFormSection title="user.password"}
		<a href="{url op='changePassword'}">{translate key="user.changePassword"}</a>
	{/fbvFormSection}

	{fbvFormSection title="common.name"}
		{fbvElement type="text" label="user.salutation" id="salutation" value=$salutation size=$fbvStyles.size.SMALL inline="true"}
		{fbvElement type="text" label="user.firstName" id="firstName" required="true" value=$firstName size=$fbvStyles.size.SMALL inline="true"}
		{fbvElement type="text" label="user.middleName" id="middleName" value=$middleName size=$fbvStyles.size.SMALL inline="true"}
		{fbvElement type="text" label="user.lastName" id="lastName" required="true" value=$lastName size=$fbvStyles.size.SMALL inline="true"}
		{fbvElement type="text" label="user.suffix" id="suffix" value=$suffix  size=$fbvStyles.size.SMALL inline="true"}
		{fbvElement type="text" label="user.initials" id="initials" value=$initials size=$fbvStyles.size.SMALL inline="true"}
	{/fbvFormSection}

	{fbvFormSection title="user.gender" for="gender"  size=$fbvStyles.size.SMALL}
		{fbvElement type="select" from=$genderOptions selected=$gender|escape id="gender" translate=true}
	{/fbvFormSection}

	{fbvFormSection title="user.affiliation" for="affiliation"}
		{fbvElement type="textarea" id="affiliation" multilingual=true value=$affiliation|escape label="user.affiliation.description" size=$fbvStyles.size.MEDIUM}<br/>
	{/fbvFormSection}

	{fbvFormSection title="user.biography" for="biography"}
		{fbvElement type="textarea" id="biography" name="biography" multilingual=true value=$biography|escape rich=true size=$fbvStyles.size.MEDIUM}
	{/fbvFormSection}

	{fbvFormSection title="user.signature" for="signature"}
		{fbvElement type="textarea" id="signature" name="signature" multilingual=true value=$signature|escape size=$fbvStyles.size.MEDIUM}
	{/fbvFormSection}

	{fbvFormSection title="user.email" for="email" required="true"}
		{fbvElement type="text" id="email" value=$email|escape size=$fbvStyles.size.MEDIUM}
	{/fbvFormSection}

	{fbvFormSection title="user.url" for="userUrl"}
		{fbvElement type="text" id="userUrl" value=$userUrl|escape size=$fbvStyles.size.MEDIUM}
	{/fbvFormSection}

	{fbvFormSection title="user.phone" for="phone"}
		{fbvElement type="text" id="phone" value=$phone|escape size=$fbvStyles.size.MEDIUM}
	{/fbvFormSection}

	{fbvFormSection title="user.fax" for="fax"}
		{fbvElement type="text" id="fax" value=$fax|escape size=$fbvStyles.size.MEDIUM}
	{/fbvFormSection}

	{fbvFormSection title="user.mailingAddress" for="mailingAddress"}
		{fbvElement type="textarea" id="mailingAddress" value=$mailingAddress|escape rich=true size=$fbvStyles.size.MEDIUM}
	{/fbvFormSection}

	{fbvFormSection title="common.country" for="country" size=$fbvStyles.size.SMALL required="true"}
		{fbvElement type="select" from=$countries selected=$country translate=false id="country" defaultValue="" defaultLabel=""}
	{/fbvFormSection}

	{if $currentPress && ($allowRegAuthor || $allowRegReviewer)}
		{fbvFormSection label="user.register.registerAs" list="true"}
			{if $allowRegAuthor}
				{iterate from=authorUserGroups item=userGroup}
					{assign var="userGroupId" value=$userGroup->getId()}
					{if in_array($userGroup->getId(), $userGroupIds)}
						{assign var="checked" value=true}
					{else}
						{assign var="checked" value=false}
					{/if}
					{fbvElement type="checkbox" id="readerGroup-$userGroupId" name="authorGroup[$userGroupId]" checked=$checked label=$userGroup->getLocalizedName() translate=false}
				{/iterate}
			{/if}
			{if $allowRegReviewer}
				{iterate from=reviewerUserGroups item=userGroup}
					{assign var="userGroupId" value=$userGroup->getId()}
					{if in_array($userGroup->getId(), $userGroupIds)}
						{assign var="checked" value=true}
					{else}
						{assign var="checked" value=false}
					{/if}
					{fbvElement type="checkbox" id="reviewerGroup-$userGroupId" name="reviewerGroup[$userGroupId]" checked=$checked label=$userGroup->getLocalizedName() translate=false}
				{/iterate}
			{/if}
		{/fbvFormSection}
		{if $allowRegReviewer}
			{fbvFormSection id="reviewerInterestsContainer" label="user.register.reviewerInterests"}
				{fbvElement type="interests" id="interests" interestKeywords=$interestsKeywords interestsTextOnly=$interestsTextOnly}
			{/fbvFormSection}
		{/if}
	{/if}

	{** FIXME 6760: Fix profile image uploads
	{fbvFormSection id="profileImage" label="user.profile.form.profileImage"}
		{fbvFileInput id="profileImage" submit="uploadProfileImage"}
		{if $profileImage}
			{translate key="common.fileName"}: {$profileImage.name|escape} {$profileImage.dateUploaded|date_format:$datetimeFormatShort} <input type="submit" name="deleteProfileImage" value="{translate key="common.delete"}" class="button" />
			<br />
			<img src="{$sitePublicFilesDir}/{$profileImage.uploadName|escape:"url"}" width="{$profileImage.width|escape}" height="{$profileImage.height|escape}" style="border: 0;" alt="{translate key="user.profile.form.profileImage"}" />
		{/if}
	{/fbvFormSection}**}

	<br /><br />
	{url|assign:cancelUrl page="dashboard"}
	{fbvFormButtons submitText="common.save" cancelUrl=$cancelUrl}
{/fbvFormArea}
</form>

<p><span class="formRequired">{translate key="common.requiredField"}</span></p>

{include file="common/footer.tpl"}

