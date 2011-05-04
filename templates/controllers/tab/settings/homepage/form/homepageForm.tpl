{**
 * controllers/tab/settings/homepage/form/homepageForm.tpl
 *
 * Copyright (c) 2003-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Homepage information and settings management form.
 *
 *}

<script type="text/javascript">
	$(function() {ldelim}
		// Attach the form handler.
		$('#homepageForm').pkpHandler('$.pkp.controllers.form.AjaxFormHandler');
	{rdelim});
</script>

<form id="homepageForm" class="pkp_controllers_form" method="post" action="{url router=$smarty.const.ROUTE_COMPONENT component="tab.settings.WebsiteSettingsTabHandler" op="saveFormData" tab="homepage"}">
	{include file="common/formErrors.tpl"}

	<h3>4.2 {translate key="manager.setup.announcements"}</h3>

	<p>{translate key="manager.setup.announcementsDescription"}</p>

	<script type="text/javascript">
		{literal}
		<!--
			function toggleEnableAnnouncementsHomepage(form) {
				form.numAnnouncementsHomepage.disabled = !form.numAnnouncementsHomepage.disabled;
			}
		// -->
		{/literal}
	</script>

	<p>
		<input type="checkbox" name="enableAnnouncements" id="enableAnnouncements" value="1" {if $enableAnnouncements} checked="checked"{/if} />&nbsp;
		<label for="enableAnnouncements">{translate key="manager.setup.enableAnnouncements"}</label>
	</p>

	<p>
		<input type="checkbox" name="enableAnnouncementsHomepage" id="enableAnnouncementsHomepage" value="1" onclick="toggleEnableAnnouncementsHomepage(this.form)"{if $enableAnnouncementsHomepage} checked="checked"{/if} />&nbsp;
		<label for="enableAnnouncementsHomepage">{translate key="manager.setup.enableAnnouncementsHomepage1"}</label>
		<select name="numAnnouncementsHomepage" size="1" class="selectMenu" {if not $enableAnnouncementsHomepage}disabled="disabled"{/if}>
			{section name="numAnnouncementsHomepageOptions" start=1 loop=11}
				<option value="{$smarty.section.numAnnouncementsHomepageOptions.index}"{if $numAnnouncementsHomepage eq $smarty.section.numAnnouncementsHomepageOptions.index or ($smarty.section.numAnnouncementsHomepageOptions.index eq 1 and not $numAnnouncementsHomepage)} selected="selected"{/if}>{$smarty.section.numAnnouncementsHomepageOptions.index}</option>
			{/section}
		</select>
		{translate key="manager.setup.enableAnnouncementsHomepage2"}
	</p>

	{fbvFormArea id="announcementsIntroductionContainer"}
		{fbvFormSection title="manager.setup.announcementsIntroduction"}
			<p>{translate key="manager.setup.announcementsIntroductionDescription"}</p>
			{fbvTextArea multilingual="true" name="announcementsIntroduction" id="announcementsIntroduction" value=$announcementsIntroduction size=$fbvStyles.size.MEDIUM measure=$fbvStyles.measure.3OF4 rich=true}
		{/fbvFormSection}
	{/fbvFormArea}

	{include file="form/formButtons.tpl" submitText="common.save"}
</form>
