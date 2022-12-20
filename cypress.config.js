const { defineConfig } = require('cypress')

module.exports = defineConfig({
  env: {
    contextTitles: {
      en_US: 'Public Knowledge Press',
      fr_CA: 'Press de la connaissance du public',
    },
    contextDescriptions: {
      en_US:
        'Public Knowledge Press is a publisher dedicated to the subject of public access to science.',
      fr_CA:
        "Le Press de Public Knowledge est une presse sur le thème de l'accès du public à la science.",
    },
    contextAcronyms: {
      en_US: 'PKP',
    },
    defaultGenre: 'Book Manuscript',
    authorUserGroupId: 13,
    volumeEditorUserGroupId: 14,
  },
  watchForFileChanges: false,
  defaultCommandTimeout: 10000,
  video: false,
  numTestsKeptInMemory: 0,
  e2e: {
    // We've imported your old cypress plugins here.
    // You may want to clean this up later by importing these.
    setupNodeEvents(on, config) {
      return require('./lib/pkp/cypress/plugins/index.js')(on, config)
    },
    specPattern: 'cypress/tests/**/*.cy.{js,jsx,ts,tsx}',
  },
})
