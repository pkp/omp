// @ts-check
/**
 * @file playwright/fixtures/bootstrap.js
 *
 * Copyright (c) 2014-2026 Simon Fraser University
 * Copyright (c) 2003-2026 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * The OMP base seed, as data.
 *
 * `POST /api/v1/_test/bootstrap` walks the application to this state through its
 * real services, so what follows is the whole definition of the world every OMP
 * test starts in. Two rules govern it:
 *
 * 1. **The base press is READ-ONLY.** No test may change its settings, series,
 *    categories or the 18 seeded users — a test that needs press-level
 *    mutations creates a scratch press instead. That is what makes parallel
 *    workers safe.
 * 2. **Richer defaults are deliberate.** The seed enables what most real
 *    presses enable, so tests exercise representative configuration rather than
 *    an empty install. A change here means re-checking every implemented spec
 *    against the new defaults.
 */

const {users, byUsername} = require('../../lib/pkp/playwright/data/users.js');

/**
 * How each seeded user is enrolled in `publicknowledge`.
 *
 * Keys are OMP user-group name keys (`registry/userGroups.xml`, minus the
 * `default.groups.name.` prefix) — resolution is by that key, not by role id or
 * translated name. The KEY is shared with OJS; the LABEL is OMP's own, which is
 * why `sectionEditor` is what OMP calls "Series editor" and `manager`/`editor`
 * are "Press manager"/"Press editor".
 *
 * CAUTION: `editor` resolves to the "Press editor" group, which carries
 * ROLE_ID_MANAGER — NOT sub-editor. A test that needs a non-manager editorial
 * actor wants `sectionEditor`.
 *
 * REVIEWERS ARE SPLIT, and this is the OMP divergence most likely to bite. OMP
 * ships TWO reviewer groups: `internalReviewer` reaches stage 2 (Internal
 * Review) only and `externalReviewer` reaches stage 3 (External Review) only.
 * Julia and Paul are external; Amara and Adam are internal. Nobody holds both,
 * on purpose — a reviewer in both groups would make every "can this person be
 * assigned here?" assertion vacuous.
 *
 * `series` assigns the user as a series editor of those series, exactly as the
 * Series settings form does; it is what makes them a participant on new
 * submissions in that series.
 *
 * `admin` is absent on purpose: the installer creates it, and creating the
 * press enrols the creating user as its manager.
 *
 * @type {Record<string, {roles: string[], series?: string[]}>}
 */
const enrolments = {
	'manager.maya': {roles: ['manager']},
	'editor.diana': {roles: ['editor'], series: ['monographs', 'textbooks']},
	'sectioneditor.ana': {roles: ['sectionEditor'], series: ['monographs']},
	'sectioneditor.ravi': {roles: ['sectionEditor'], series: ['textbooks']},
	'sectioneditor.omar': {roles: ['sectionEditor'], series: ['monographs']},
	'reviewer.julia': {roles: ['externalReviewer']},
	'reviewer.paul': {roles: ['externalReviewer']},
	'reviewer.amara': {roles: ['internalReviewer']},
	'reviewer.adam': {roles: ['internalReviewer']},
	'copyeditor.carla': {roles: ['copyeditor']},
	'copyeditor.sam': {roles: ['copyeditor']},
	'layouteditor.leo': {roles: ['layoutEditor']},
	'proofreader.pia': {roles: ['proofreader']},
	'author.alex': {roles: ['author']},
	'author.bea': {roles: ['author']},
	// The Funding coordinator group is the one default assistant group with
	// review-stage access — stages 1, 2 and 3, so in OMP it reaches BOTH review
	// stages, which is the whole point of this account.
	'assistant.rita': {roles: ['funding']},
	'reader.rosa': {roles: ['reader']},
};

/** The 17 seeded accounts, in roster order. */
function bootstrapUsers() {
	return users
		.filter((user) => enrolments[user.username])
		.map((user) => ({
			username: user.username,
			givenName: user.givenName,
			familyName: user.familyName,
			email: user.email,
			affiliation: 'Public Knowledge Project',
			country: 'CA',
			...enrolments[user.username],
		}));
}

/**
 * The full bootstrap payload.
 *
 * @returns {object}
 */
function bootstrapPayload() {
	return {
		context: {
			urlPath: 'publicknowledge',
			name: 'Public Knowledge Press',
			acronym: 'PKP',
			description:
				'Public Knowledge Press is the test fixture press every OMP end-to-end test starts from.',
			primaryLocale: 'en',
			// Multilingual on purpose: a bare front-end URL 302s to the
			// locale-prefixed form only on a multi-locale context, and that
			// difference has bitten enough probes to be worth having in the base.
			supportedLocales: ['en', 'fr_CA'],

			// A press with no contact address cannot accept a submission: the
			// acknowledgement mail fails for want of a From header AFTER the
			// submission is marked submitted.
			contactName: 'Ramiro Vaca',
			contactEmail: 'rvaca@mailinator.com',
			supportName: 'Ramiro Vaca',
			supportEmail: 'rvaca@mailinator.com',
			mailingAddress: '123 456th Street\nBurnaby, British Columbia\nCanada',

			copyrightNotice:
				'Authors who publish with this press agree to the terms of the test fixture licence.',

			enableAnnouncements: true,
			enablePublicComments: true,
			disableSubmissions: false,

			// Double-anonymous review with the default deadlines most presses
			// configure, so review-stage tests see realistic dates. These apply
			// to BOTH of OMP's review stages.
			defaultReviewMode: 2,
			numWeeksPerResponse: 4,
			numWeeksPerReview: 4,

			keywords: 'request',
			citations: 'request',

			// --- OMP overlay (api/v1/_test/PressScenarioController.php) ---

			// The press identity ONIX metadata is built from. A press with no
			// publisher/location exports incomplete ONIX, which is not the
			// configuration a real press runs.
			publisher: 'Public Knowledge Press',
			location: 'Burnaby, BC',
			codeType: '01',
			codeValue: 'PKPTEST',

			// The catalog is OMP's counterpart to the OJS issue archive; both
			// its blocks are on so catalog tests have something to assert
			// against without configuring the press first.
			displayNewReleases: true,
			displayFeaturedBooks: true,
			catalogSortOption: 'datePublished-DESC',
			restrictMonographAccess: false,

			// OMP-unique: the Internal Review stage has its own guidelines
			// field, with no OJS counterpart.
			internalReviewGuidelines:
				'Internal reviewers assess the proposal for fit with the press list before external peer review.',

			series: [
				{
					path: 'monographs',
					title: 'Monographs',
					description: 'Single-authored scholarly monographs.',
					// The catalog's Featured block needs something featured.
					featured: true,
					editorRestricted: false,
					sortOption: 'datePublished-DESC',
				},
				{
					// Deliberately editor-restricted: authors cannot choose this
					// series in the submission wizard, which is a real OMP
					// configuration and the base seed's one asymmetry between the
					// two series. Seed submissions into `monographs` unless the
					// test is about the restriction itself.
					path: 'textbooks',
					title: 'Textbooks',
					description: 'Course texts and teaching material.',
					featured: false,
					editorRestricted: true,
					printIssn: '0378-5946',
					onlineIssn: '0378-5955',
				},
			],

			// Parents first: a child names its parent by path. Categories carry
			// more weight in OMP than in OJS — the catalog browses by them.
			categories: [
				{
					path: 'applied-science',
					title: 'Applied Science',
					description: 'Applied science research.',
				},
				{
					path: 'comp-sci',
					title: 'Computer Science',
					parentPath: 'applied-science',
				},
				{
					path: 'computer-vision',
					title: 'Computer Vision',
					parentPath: 'comp-sci',
				},
				{
					path: 'eng',
					title: 'Engineering',
					parentPath: 'applied-science',
				},
				{
					path: 'social-sciences',
					title: 'Social Sciences',
					description: 'Social science research.',
				},
				{
					path: 'sociology',
					title: 'Sociology',
					parentPath: 'social-sciences',
				},
				{
					path: 'anthropology',
					title: 'Anthropology',
					parentPath: 'social-sciences',
				},
			],
		},

		users: bootstrapUsers(),
	};
}

module.exports = {bootstrapPayload, bootstrapUsers, enrolments, byUsername};
