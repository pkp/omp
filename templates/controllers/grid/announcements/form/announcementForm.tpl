{**
 * templates/controllers/grid/announcements/form/announcementForm.tpl
 *
 * Copyright (c) 2003-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Announcement form to read/create/edit announcements.
 *}
 
<script type="text/javascript">
	$(function() {ldelim}
		// Attach the form handler.
		$('#announcementForm').pkpHandler('$.pkp.controllers.form.AjaxFormHandler');
	{rdelim});
</script>

<form class="pkp_form" id="announcementForm" method="post" action="{url router=$smarty.const.ROUTE_COMPONENT component="grid.announcements.AnnouncementGridHandler" op="updateAnnouncement"}">
	{if $readOnly}
		{* Read only announcement *}
	
		{fbvFormArea id="announcementInfo"}
			{fbvFormSection title="manager.announcements.form.title"}
				{$announcement->getLocalizedTitleFull()|escape}
			{/fbvFormSection}
			{fbvFormSection title="manager.announcements.datePublish"}
				{$announcement->getDatePosted()|escape}
			{/fbvFormSection}
			{fbvFormSection title="manager.announcements.form.description"}
				{$announcement->getLocalizedDescription()|strip_unsafe_html}
			{/fbvFormSection}
		{/fbvFormArea}
	{else}
		{* Editable announcement *}
	
		{include file="controllers/notification/inPlaceNotification.tpl" notificationId="announcementFormNotification"}
		{fbvFormArea id="announcementInfo"}
			{if $announcementId}
				<input type="hidden" name="announcementId" value="{$announcementId|escape}" />
			{/if}
			{fbvElement type="select" id="typeId" from=$announcementTypes selected=$selectedTypeId label="manager.announcements.form.typeId"}
			{fbvFormSection title="manager.announcements.form.title" for="title" required="true"}
				{fbvElement type="text" multilingual="true" id="title" value=$title maxlength="255"}
			{/fbvFormSection}
			{fbvFormSection title="manager.announcements.form.descriptionShort" for="descriptionShort" required="true"}
				{fbvElement type="textarea" multilingual="true" id="descriptionShort" value=$descriptionShort label="manager.announcements.form.descriptionShortInstructions" rich=true height=$fbvStyles.height.SHORT}
			{/fbvFormSection}
			{fbvFormSection title="manager.announcements.form.description" for="description" required="true"}
				{fbvElement type="textarea" multilingual="true" id="description" value=$description label="manager.announcements.form.descriptionInstructions" rich=true}
			{/fbvFormSection}
			<script type="text/javascript">
				$("#dateExpire").datepicker({ldelim} dateFormat: 'yy-mm-dd' {rdelim});
			</script>
			{fbvFormSection title="manager.announcements.form.dateExpire" for="dataExpire" required="true"}
				{fbvElement type="text" id="dateExpire" value=$dateExpire label="manager.announcements.form.dateExpireInstructions" size=$fbvStyles.size.MEDIUM}
			{/fbvFormSection}			
		{/fbvFormArea}
	{/if}
</form>
