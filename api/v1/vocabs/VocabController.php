<?php

/**
 * @file api/v1/vocabs/VocabController.php
 *
 * Copyright (c) 2026 Simon Fraser University
 * Copyright (c) 2026 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class VocabController
 *
 * @ingroup api_v1_vocab
 *
 * @brief Handle API requests for controlled vocab operations, adding Thema
 *   subject categories to the subjects suggestions when the press uses Thema.
 */

namespace APP\API\v1\vocabs;

use APP\codelist\Thema;
use APP\core\Application;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use PKP\API\v1\vocabs\PKPVocabController;
use PKP\controlledVocab\ControlledVocab;
use PKP\controlledVocab\ControlledVocabEntry;
use PKP\facades\Locale;

class VocabController extends PKPVocabController
{
    /**
     * Get the controlled vocab entries available in this context, appending
     * matching Thema subject categories to the subjects vocabulary when the
     * press has enabled Thema. Thema entries already stored on a publication
     * are returned once, from the stored entries.
     */
    public function getMany(Request $illuminateRequest): JsonResponse
    {
        $response = parent::getMany($illuminateRequest);
        $context = Application::get()->getRequest()->getContext();
        $vocab = $illuminateRequest->query('vocab', '');

        if (
            ($response->getStatusCode() !== Response::HTTP_OK) ||
            ($vocab !== ControlledVocab::CONTROLLED_VOCAB_SUBMISSION_SUBJECT) ||
            !Thema::isEnabled($context)
        ) {
            return $response;
        }

        $data = $response->getData(true);
        $term = (string) $illuminateRequest->query('term', '');
        $locale = $illuminateRequest->query('locale', Locale::getLocale());
        $storedCodes = collect($data)
            ->filter(fn (array $entry): bool => ($entry[ControlledVocabEntry::CONTROLLED_VOCAB_ENTRY_SOURCE] ?? null) === Thema::SOURCE)
            ->pluck(ControlledVocabEntry::CONTROLLED_VOCAB_ENTRY_IDENTIFIER);

        foreach ((new Thema())->search($term, $locale) as $entry) {
            if ($storedCodes->doesntContain($entry[ControlledVocabEntry::CONTROLLED_VOCAB_ENTRY_IDENTIFIER])) {
                $data[] = $entry;
            }
        }

        return response()->json($data, Response::HTTP_OK);
    }
}
