<?php
/**
 * @file classes/components/form/publication/ForTheEditors.php
 *
 * Copyright (c) 2014-2021 Simon Fraser University
 * Copyright (c) 2000-2021 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class ForTheEditors
 *
 * @ingroup classes_controllers_form
 *
 * @brief A form during the For the Editors step in the submission wizard
 */

namespace APP\components\forms\submission;

use APP\publication\Publication;
use APP\section\Section;
use APP\submission\Submission;
use Illuminate\Support\LazyCollection;
use PKP\components\forms\FieldOptions;
use PKP\context\Context;

class ForTheEditors extends \PKP\components\forms\submission\ForTheEditors
{
    /**
     * @param Section[] $series
     */
    public function __construct(string $action, array $locales, Publication $publication, Submission $submission, Context $context, string $suggestionUrlBase, array $series, LazyCollection $categories)
    {
        parent::__construct($action, $locales, $publication, $submission, $context, $suggestionUrlBase, $categories);

        $this->addSeriesField($series, $publication);
    }

    protected function addSeriesField(array $series, Publication $publication): void
    {
        if (empty($series)) {
            return;
        }
        $seriesOptions[] = [
            'value' => '',
            'label' => __('common.none'),
        ];
        /** @var Section $iSeries */
        foreach ($series as $iSeries) {
            $seriesOptions[] = [
                'value' => $iSeries->getId(),
                'label' => $iSeries->getLocalizedFullTitle(),
            ];
        }
        $this->addField(new FieldOptions('seriesId', [
            'label' => __('series.series'),
            'type' => 'radio',
            'options' => $seriesOptions,
            'value' => $publication->getData('seriesId') ?? '',
        ]));
    }
}
