{**
 * templates/controllers/grid/users/author/form/authorForm.tpl
 *
 * Copyright (c) 2014-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Submission Contributor grid form
 *
 *}
{if $submission->getData('workType') === $smarty.const.WORK_TYPE_EDITED_VOLUME}
  {capture assign="additionalCheckboxes"}
    {fbvElement type="checkbox" label="author.isVolumeEditor" id="isVolumeEditor" checked=$isVolumeEditor}
  {/capture}
{/if}

{include file="core:controllers/grid/users/author/form/authorForm.tpl" additionalCheckboxes=$additionalCheckboxes}
