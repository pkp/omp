// @ts-check
/**
 * @file playwright/support/app.context.js
 *
 * Copyright (c) 2014-2026 Simon Fraser University
 * Copyright (c) 2003-2026 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * What the shared `lib/pkp/playwright` layer is allowed to know about OMP.
 *
 * Shared code gates on CAPABILITIES and resolves people through ARCHETYPES; it
 * never asks which app it is running in. OJS and OPS ship the same three keys
 * with their own values, so a shared spec written once runs in all three fleets
 * and skips itself where the capability does not hold.
 *
 * The capability names are canonical in `lib/pkp/docs/product/APP-GLOSSARY.md`
 * §2 and are spelled here VERBATIM from that table's OMP column. Adding a
 * capability means adding a glossary row first, then the same key in all three
 * app contexts.
 */

const {bootstrapPayload} = require('../fixtures/bootstrap.js');

const appContext = {
	app: 'omp',

	/** APP-GLOSSARY.md §2, OMP column. */
	capabilities: {
		hasReviewStage: true,
		hasInternalReview: true,
		hasCopyediting: true,
		hasProduction: true,
		hasIssues: false,
		hasGalleys: false,
		hasSubscriptions: false,
		hasSections: false,
		hasReviewerRoles: true,
	},

	/**
	 * APP-GLOSSARY.md §1, OMP column. Vocabulary never gates anything — it is
	 * what a shared spec puts in a label or a payload so the same test reads
	 * correctly in each app. `null` is the glossary's "—": the concept does not
	 * exist here, and the capability flag above is what a test gates on.
	 */
	vocab: {
		context: 'press',
		contextPlural: 'presses',
		submission: 'monograph',
		sectionGrouping: 'series',
		issue: null,
		galley: 'publication format',
		publishAction: 'Publish to Catalog',
	},

	seed: {
		/** The shared base context every fleet seeds at the same url path. */
		contextPath: 'publicknowledge',
		contextName: 'Public Knowledge Press',
		primaryLocale: 'en',
		supportedLocales: ['en', 'fr_CA'],

		/**
		 * Archetype → seeded username, or null where OMP has no such account.
		 *
		 * Shared code asks for `actors.reviewer`, not for `'reviewer.julia'`: the
		 * archetype exists in every app's vocabulary even when the account does
		 * not (OMP has no subscriptions, and its map says so with null). An app's
		 * OWN suite may name its usernames directly.
		 *
		 * REVIEWERS ARE SPLIT. OMP is the only app with two reviewer groups, one
		 * per review stage, and a reviewer enrolled in the wrong one cannot be
		 * assigned in the other stage. The generic `reviewer*` keys resolve to
		 * EXTERNAL reviewers — the stage every app with review has, and what a
		 * shared spec means — while `reviewer3`/`reviewer4` are the internal
		 * pair. An OMP spec should prefer the explicit `internalReviewer*` /
		 * `externalReviewer*` keys and say which stage it means.
		 */
		actors: {
			siteAdmin: 'admin',
			manager: 'manager.maya',
			editor: 'editor.diana',
			sectionEditor: 'sectioneditor.ana',
			sectionEditor2: 'sectioneditor.ravi',
			sectionEditor3: 'sectioneditor.omar',
			reviewer: 'reviewer.julia',
			reviewer2: 'reviewer.paul',
			reviewer3: 'reviewer.amara',
			reviewer4: 'reviewer.adam',
			copyeditor: 'copyeditor.carla',
			copyeditor2: 'copyeditor.sam',
			layoutEditor: 'layouteditor.leo',
			proofreader: 'proofreader.pia',
			author: 'author.alex',
			author2: 'author.bea',
			assistant: 'assistant.rita',
			reader: 'reader.rosa',
			// OJS-only: OMP sells publication formats directly and has no
			// subscription model at all.
			subscriptionManager: null,

			// OMP-only aliases. Same accounts as the reviewer* keys above, named
			// for the stage they can actually be assigned in.
			externalReviewer: 'reviewer.julia',
			externalReviewer2: 'reviewer.paul',
			internalReviewer: 'reviewer.amara',
			internalReviewer2: 'reviewer.adam',
		},

		/**
		 * Series paths the base seed creates. OMP series have no abbreviation —
		 * `path` is the identifier the catalog and the scenario endpoint use.
		 */
		series: ['monographs', 'textbooks'],
	},

	/** The base seed, as data. See playwright/fixtures/bootstrap.js. */
	bootstrapPayload,
};

module.exports = {appContext};
