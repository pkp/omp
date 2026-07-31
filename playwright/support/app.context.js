/**
 * @file playwright/support/app.context.js
 *
 * OMP capability map + seeded-actor roster. Capability names are canonical in
 * lib/pkp/docs/product/APP-GLOSSARY.md §2 — verbatim. OMP splits the four
 * roster reviewers across its two reviewer groups (julia/paul → External,
 * amara/adam → Internal) and uses series (by path, no abbrev) instead of
 * sections.
 */
const bootstrap = require('../fixtures/bootstrap.js');

module.exports = {
    appName: 'omp',
    contextPath: 'publicknowledge',
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
    seed: {
        actors: {
            admin: 'admin',
            manager: 'manager.maya',
            editor: 'editor.diana',
            sectionEditor: 'sectioneditor.ana',
            reviewer: 'reviewer.julia',
            copyeditor: 'copyeditor.carla',
            layoutEditor: 'layouteditor.leo',
            proofreader: 'proofreader.pia',
            assistant: 'assistant.rita',
            author: 'author.alex',
            reader: 'reader.rosa',
        },
        bootstrap,
    },
};
