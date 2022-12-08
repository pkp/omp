{**
 * templates/submission/review-chapters.tpl
 *
 * Copyright (c) 2014-2022 Simon Fraser University
 * Copyright (c) 2003-2022 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * Template which adds chapter details to the review step of the submission wizard
 *}
<div class="submissionWizard__reviewPanel">
    <div class="submissionWizard__reviewPanel__header">
        <h3 id="review-chapters">
            {translate key="submission.chapters"}
        </h3>
        <pkp-button
            aria-describedby="review-chapters"
            class="submissionWizard__reviewPanel__edit"
            @click="openStep('{$step.id}')"
        >
            {translate key="common.edit"}
        </pkp-button>
    </div>
    <div class="submissionWizard__reviewPanel__body">
        <div
            v-for="chapter in chapters"
            :key="chapter.id"
            class="submissionWizard__reviewPanel__item"
        >
            <h4 class="submissionWizard__reviewPanel__item__header">
                {{ chapter.title }}
            </h4>
            <div v-if="chapter.authors" class="submissionWizard__reviewPanel__item__value">
                {{ chapter.authors }}
            </div>
        </div>
    </div>
</div>