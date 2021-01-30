{**
 * plugins/paymethod/manual/templates/settingsForm.tpl
 *
 * Copyright (c) 2014-2021 Simon Fraser University
 * Copyright (c) 2003-2021 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * Form for manual payment settings.
 *}

{fbvFormSection title="plugins.paymethod.manual.settings"}
	{fbvElement type="textarea" name="manualInstructions" id="manualInstructions" value=$manualInstructions}
{/fbvFormSection}
