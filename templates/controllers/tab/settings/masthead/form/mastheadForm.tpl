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

<form id="mastheadForm" class="pkp_controllers_form" method="post"
      action="{url router=$smarty.const.ROUTE_COMPONENT component="tab.settings.PressSettingsTabHandler" op="saveFormData" tab="masthead"}">
{include file="common/formErrors.tpl"}
{include file="controllers/tab/settings/wizardMode.tpl" wizardMode=$wizardMode}

    <fieldset>
        <legend>Group Title</legend>
        <div>
            <label>Single Label With Multiple Inline Inputs</label>

            <div class="inline half">
                <input name="" type="text" id="" class=""/>
                <span><label class="sub_label">Inline Field One</label></span>
            </div>
            <div class="inline quarter">
                <input name="" type="text" id="" class="small"/>
                <span><label class="sub_label">Inline Field Two</label></span>
            </div>
        </div>
        <div class="inline half">
            <span id="localization-container"
                  class="localization_popover_container localization_popover_container_focus">
            <label>Two Column Layout</label>
            <input name="" type="text" id="" class=""/>
                <span>
					<div id="lang" class="localization_popover">
                        <input type="text" placeholder="Francais" class="field text large flag flag_fr"/>
                        <label class="locale">(FR)</label>
                        <input id="last" type="text" placeholder="Deutch" class="field text large flag flag_de"/>
                        <label class="locale">(DE)</label>
                    </div>
				</span>
            </span>
        </div>
        <div class="inline half">
            <label>2nd Column</label>
            <input name="" type="text" id="" class="small"/>
        </div>
        <div>
            <span id="localization-container"
                  class="localization_popover_container localization_popover_container_focus">
            <label for="the_press_name">
                Full Width Input (Please don't use this unless you need to...) <span class="req">*</span>
            </label>
            <input name="" type="text" id="" class=""/>
                <span>
					<div id="lang" class="localization_popover">
                        <input type="text" placeholder="Francais" class="field text large flag flag_fr"/>
                        <label class="locale">(FR)</label>
                        <input id="last" type="text" placeholder="Deutch" class="field text large flag flag_de"/>
                        <label class="locale">(DE)</label>
                    </div>
				</span>
            </span>
        </div>
        <div>
            <label for="the_press_name">
                Select <span class="req">*</span>
            </label>
            <select name="" type="text" id="" class="">
                <option>One</option>
                <option>Two</option>
            </select>
        </div>
        <div>
            <label>Mailing Address</label>
            <textarea></textarea>
                <span>
                    <label class="sub_label">
                        The press's physical location and mailing address.
                    </label>
                </span>
        </div>
        <div>
            <label>Checkboxes/Radiobuttons</label>
            <ul class="checkbox_and_radiobutton">
                <li><input name="radio-01" type="radio"/><label>Option 01</label></li>
                <li><input name="radio-01" type="radio"/><label>Option 02</label></li>
                <li><input name="radio-01" type="radio"/><label>Option 03</label></li>
                <li><input name="radio-01" type="radio"/><label>Option 04</label></li>
            </ul>
            <ul class="checkbox_and_radiobutton">
                <li><input name="radio-01" type="checkbox"/><label>Option 01</label></li>
                <li><input name="radio-01" type="checkbox"/><label>Option 02</label></li>
                <li><input name="radio-01" type="checkbox"/><label>Option 03</label></li>
                <li><input name="radio-01" type="checkbox"/><label>Option 04</label></li>
            </ul>
        </div>
    </fieldset>

    <h3>{translate key="manager.setup.generalInformation"}</h3>
    <ul>
        <li class="section">
            <h3>
            {translate key="manager.setup.generalInformation"}</h3>
        </li>
        <li class="leftHalf">
            <label class="desc" for="the_press_name">
                Press Name <span class="req">*</span>
            </label>
            <input name="the_press_name" type="text" id="the_press_name" class="field text large"/>
        </li>
        <li class="rightHalf">
            <label class="desc" for="the_press_initials">
                Press Initials <span class="req">*</span>
            </label>
            <input name="the_press_initials" type="text" id="the_press_initials" class="field text"/>
        </li>
        <li>
            <label class="desc">Press Description</label>
            <textarea class="textarea medium"></textarea>
        </li>
        <li>
            <label class="desc">Mailing Address</label>
            <textarea class="textarea medium"></textarea>
                <span>
                    <label>The press's physical location and mailing address.</label>
                </span>
        </li>
        <li>
                <span class="field clear_choice">
                    <input id="press_visibility" type="checkbox" name="press_visibility"/>
                    <label for="press_visibility">Enable this press to appear publicly on the site</label>
                </span>
        </li>
    </ul>
    <hr/>
{fbvFormArea id="generalInformation"}
    {fbvFormSection title="manager.setup.pressName" for="name" required=true}
        {fbvElement type="text" multilingual=true name="name" id="name" value=$name maxlength="120" }
    {/fbvFormSection}
    {fbvFormSection title="manager.setup.pressInitials" for="initials" required=true}
        {fbvElement type="text" multilingual=true name="initials" id="initials" value=$initials maxlength="16" size=$fbvStyles.size.SMALL}
    {/fbvFormSection}
    {fbvFormSection title="manager.setup.pressDescription" for="description" float=$fbvStyles.float.LEFT}
        {fbvElement type="textarea" multilingual=true name="description" id="description" value=$description size=$fbvStyles.size.MEDIUM measure=$fbvStyles.measure.3OF4 rich=true}
    {/fbvFormSection}
    {fbvFormSection layout=$fbvStyles.layout.ONE_COLUMN}
        {fbvElement type="checkbox" id="pressEnabled" value="1" checked=$pressEnabled label="manager.setup.enablePressInstructions"}
    {/fbvFormSection}
{/fbvFormArea}

    <div class="separator"></div>

{url|assign:mastheadGridUrl router=$smarty.const.ROUTE_COMPONENT component="grid.settings.masthead.mastheadGridHandler" op="fetchGrid"}
{load_url_in_div id="mastheadGridDiv" url=$mastheadGridUrl}

    <div class="separator"></div>

    <div {if $wizardMode}class="pkp_form_hidden"{/if}>
        <h3>{translate key="common.mailingAddress"}</h3>
    {fbvFormArea id="mailingAddressInformation"}
        {fbvFormSection title="common.mailingAddress" for="mailingAddress" group=true}
            {fbvElement type="textarea" id="mailingAddress" value=$mailingAddress size=$fbvStyles.size.SMALL measure=$fbvStyles.measure.1OF1}
            <p>{translate key="manager.setup.mailingAddressDescription"}</p>
        {/fbvFormSection}
    {/fbvFormArea}
    </div>

    <div class="separator"></div>

    <p><span class="formRequired">{translate key="common.requiredField"}</span></p>
{include file="form/formButtons.tpl" submitText="common.save"}
</form>
