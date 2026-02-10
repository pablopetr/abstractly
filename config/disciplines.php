<?php

// Canonical list shown in the UI.
// "ready" controls whether a discipline is selectable and included in digests.
return [
    'all' => [
        // Natural & Physical
        'math'         => ['label' => 'Mathematics',                         'ready' => true],
        'earth'        => ['label' => 'Earth & Environmental Sciences',      'ready' => false],

        // Life & Health
        'neuroscience' => ['label' => 'Neuroscience',                        'ready' => false],
        'agriculture'  => ['label' => 'Agriculture & Food Science',          'ready' => false],
        'psychology'   => ['label' => 'Psychology & Cognitive Science',      'ready' => false],
        'pharmacology' => ['label' => 'Pharmacology & Drug Discovery',       'ready' => false],

        // Eng/Computing
        'cs'           => ['label' => 'Computer Science & AI',               'ready' => false],
        'informatics'  => ['label' => 'Bioinformatics & Computational Biology','ready' => false],
        'engineering'  => ['label' => 'Engineering (General)',               'ready' => false],

        // Social/Humanities
        'economics'    => ['label' => 'Economics & Finance',                 'ready' => false],
        'linguistics'  => ['label' => 'Linguistics & Language Science',      'ready' => false],
        'law'          => ['label' => 'Law & Legal Studies',                 'ready' => false],
        'arts'         => ['label' => 'Arts & Cultural Studies',             'ready' => false],
        'education'    => ['label' => 'Education & Learning Sciences',       'ready' => false],
        'communication'=> ['label' => 'Communication & Media Studies',       'ready' => false],
    ],

    // Initial selection — keep only 'math' on to avoid it appearing unchecked,
    // and to ensure non-ready disciplines are excluded from generated digests.
    'enabled_by_default' => [
        'math',
    ],

    // Optional: map common typos/synonyms → canonical slugs
    'aliases' => [
        'neruoscience'     => 'neuroscience',
        'neuro'            => 'neuroscience',
        'informations'     => 'informatics',
        'info'             => 'informatics',
        'comp_sci'         => 'cs',
        'computer_science' => 'cs',
    ],
];
