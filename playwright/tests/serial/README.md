# Serial specs

Globally-scanning work runs here, alone, after every parallel project: scheduled
tasks, site-level plugin toggles, site-settings mutations, cache clears, and the
one infrastructure spec allowed to clear Mailpit.

These affect state across every press and every worker, so they cannot live in a
parallel spec. The `omp-serial` project depends on the parallel projects, which
is what guarantees nothing is seeding while they run.
