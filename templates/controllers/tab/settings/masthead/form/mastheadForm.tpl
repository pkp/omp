{**
 * controllers/tab/settings/masthead/form/mastheadForm.tpl
 *
 * Copyright (c) 2003-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Masthead management form.
 *
 *}

<script type="text/javascript">
    $(function() {ldelim}
        // Attach the form handler.
        $('#mastheadForm').pkpHandler('$.pkp.controllers.form.AjaxFormHandler');
    {rdelim});
</script>

<form class="pkp_form pkp_controllers_form" id="mastheadForm" method="post"
      action="{url router=$smarty.const.ROUTE_COMPONENT component="tab.settings.PressSettingsTabHandler" op="saveFormData" tab="masthead"}">

	{include file="common/formErrors.tpl"}
	{include file="controllers/tab/settings/wizardMode.tpl" wizardMode=$wizardMode}

	{fbvFormArea id="generalInformation"}
	    {fbvFormSection title="manager.setup.pressName" for="name" required=true}
	        {fbvElement type="text" multilingual=true name="name" id="name" value=$name maxlength="120" }
	    {/fbvFormSection}
	    {fbvFormSection title="manager.setup.pressInitials" for="initials" required=true}
	        {fbvElement type="text" multilingual=true name="initials" id="initials" value=$initials maxlength="16" size=$fbvStyles.size.SMALL}
	    {/fbvFormSection}
	    {fbvFormSection title="manager.setup.pressDescription" for="description"}
	        {fbvElement type="textarea" multilingual=true name="description" id="description" value=$description size=$fbvStyles.size.MEDIUM  rich=true}
	    {/fbvFormSection}
	    {fbvFormSection}
	        {fbvElement type="checkbox" id="pressEnabled" value="1" checked=$pressEnabled label="manager.setup.enablePressInstructions"}
	    {/fbvFormSection}
	{/fbvFormArea}
	{fbvFormArea id="mastheadInfo"}
		{fbvFormSection title="manager.masthead.title" for="masthead"}
			{fbvElement type="textarea" multilingual=true id="masthead" value=$masthead rich=true}
		{/fbvFormSection}
	{/fbvFormArea}

	<div {if $wizardMode}class="pkp_form_hidden"{/if}>
		<h3>{translate key="common.mailingAddress"}</h3>
		{fbvFormArea id="mailingAddressInformation"}
			{fbvFormSection title="common.mailingAddress" for="mailingAddress" group=true}
				{fbvElement type="textarea" id="mailingAddress" value=$mailingAddress size=$fbvStyles.size.SMALL}
				<p>{translate key="manager.setup.mailingAddressDescription"}</p>
			{/fbvFormSection}
		{/fbvFormArea}
		{fbvFormArea id="additionalAboutItems"}
			{fbvSection}
				<h4>{translate key="manager.setup.addItemtoAboutPress"}</h4>
			{/fbvSection}
		{/fbvFormArea}
	</div>

    <div class="separator"></div>

    <p><span class="formRequired">{translate key="common.requiredField"}</span></p>
	{include file="form/formButtons.tpl" submitText="common.save"}
</form>
