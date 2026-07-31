/**
 * @file playwright/fixtures/bootstrap.js
 *
 * Static seed data for the OMP base install (press `publicknowledge`).
 * READ-ONLY for tests — see the OJS twin for the policy notes.
 *
 * One shared roster, subset-enrolled: the four reviewers split across OMP's
 * two reviewer groups (julia/paul → External, amara/adam → Internal); series
 * `monographs`/`textbooks` are identified by path (no abbrev).
 */
const {getEmail} = require('../../lib/pkp/playwright/data/users.js');

const user = (username, givenName, familyName, roles, extra = {}) => ({
    username,
    givenName,
    familyName,
    email: getEmail(username),
    roles,
    ...extra,
});

module.exports = {
    context: {
        path: 'publicknowledge',
        name: {
            en: 'Public Knowledge Press',
            fr_CA: 'Presse de la connaissance du public',
        },
        acronym: {en: 'PKP'},
        primaryLocale: 'en',
        supportedLocales: ['en', 'fr_CA'],
    },
    series: [
        {path: 'monographs', title: {en: 'Monographs'}},
        {path: 'textbooks', title: {en: 'Textbooks'}},
    ],
    categories: [
        {
            path: 'applied-science',
            title: {en: 'Applied Science'},
            children: [
                {
                    path: 'comp-sci',
                    title: {en: 'Computer Science'},
                    children: [{path: 'computer-vision', title: {en: 'Computer Vision'}}],
                },
                {path: 'eng', title: {en: 'Engineering'}},
            ],
        },
        {
            path: 'social-sciences',
            title: {en: 'Social Sciences'},
            children: [
                {path: 'sociology', title: {en: 'Sociology'}},
                {path: 'anthropology', title: {en: 'Anthropology'}},
            ],
        },
    ],
    users: [
        user('manager.maya', 'Maya', 'Manager', ['manager']),
        user('editor.diana', 'Diana', 'Editor', ['editor'], {series: ['monographs', 'textbooks']}),
        user('sectioneditor.ana', 'Ana', 'Section Editor', ['sectionEditor'], {series: ['monographs']}),
        user('sectioneditor.ravi', 'Ravi', 'Section Editor', ['sectionEditor'], {series: ['textbooks']}),
        user('sectioneditor.omar', 'Omar', 'Section Editor', ['sectionEditor'], {series: ['monographs']}),
        user('reviewer.julia', 'Julia', 'Reviewer', ['externalReviewer']),
        user('reviewer.paul', 'Paul', 'Reviewer', ['externalReviewer']),
        user('reviewer.amara', 'Amara', 'Reviewer', ['internalReviewer']),
        user('reviewer.adam', 'Adam', 'Reviewer', ['internalReviewer']),
        user('copyeditor.carla', 'Carla', 'Copyeditor', ['copyeditor']),
        user('copyeditor.sam', 'Sam', 'Copyeditor', ['copyeditor']),
        user('layouteditor.leo', 'Leo', 'Layout Editor', ['layoutEditor']),
        user('proofreader.pia', 'Pia', 'Proofreader', ['proofreader']),
        user('author.alex', 'Alex', 'Author', ['author']),
        user('author.bea', 'Bea', 'Author', ['author']),
        user('assistant.rita', 'Rita', 'Assistant', ['funding']),
        user('reader.rosa', 'Rosa', 'Reader', ['reader']),
    ],
};
