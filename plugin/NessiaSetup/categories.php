<?php
/**
 * NESSIA default category structure.
 * Edit this file to change what the plugin creates.
 */
return array(
    // Collections. 'children' need the Collection Tree plugin to be nested;
    // without it they are still created as ordinary collections.
    'collections' => array(
        array(
            'title'       => 'Comparative Religion',
            'description' => 'Studies comparing the doctrines, scriptures and histories of the Abrahamic and wider Near Eastern religious traditions.',
            'children'    => array(
                array('title' => 'Christology', 'description' => 'Sources on the nature and person of Jesus in Christian and Islamic thought.'),
                array('title' => 'Early Christianity', 'description' => 'Material on the first centuries of Christianity, Paul and the formation of doctrine.'),
                array('title' => 'Islamic Studies', 'description' => 'Qur\'anic, hadith and theological studies.'),
            ),
        ),
        array(
            'title'       => 'Semitic Languages',
            'description' => 'Arabic, Hebrew, Aramaic, Syriac and related languages: grammar, lexicography and philology.',
            'children'    => array(
                array('title' => 'Arabic', 'description' => ''),
                array('title' => 'Hebrew', 'description' => ''),
                array('title' => 'Aramaic and Syriac', 'description' => ''),
            ),
        ),
        array(
            'title'       => 'History and Culture',
            'description' => 'The history, heritage and cultures of the Near East.',
            'children'    => array(
                array('title' => 'Syria', 'description' => ''),
                array('title' => 'Arabia', 'description' => 'Including the history of Makkah and the Hijaz.'),
                array('title' => 'Levant', 'description' => ''),
            ),
        ),
        array(
            'title'       => 'Political Affairs',
            'description' => 'Historical and contemporary political affairs of the Near East.',
        ),
        array(
            'title'       => 'Manuscripts and Archival Material',
            'description' => 'Manuscripts, scans, recordings and other primary archival material.',
        ),
    ),

    // Dublin Core Subject terms. Used as a controlled vocabulary when the
    // Simple Vocab plugin is active; otherwise shown as theme topic links.
    'subjects' => array(
        'Biblical Criticism',
        'Christology',
        'Early Christianity',
        'Trinity',
        'Pauline Studies',
        'Qur\'anic Studies',
        'Hadith Studies',
        'Islamic History',
        'Makkah',
        'Orientalism',
        'Interfaith Dialogue',
        'Arabic Language',
        'Hebrew Language',
        'Aramaic Language',
        'Syriac Language',
        'Manuscripts',
    ),

    // Tags (lower case, matching the existing archive style).
    'tags' => array(
        'early christianity',
        'history',
        'paul of tarsus',
        'christology',
        'trinity',
        'quran',
        'hadith',
        'makkah',
        'orientalism',
        'syriac',
        'aramaic',
        'hebrew',
        'arabic',
        'manuscript',
        'audio',
    ),

    // ------------------------------------------------------------------
    // Reorganisation: map OLD names (left) to NEW names (right).
    // Fill these in from the "Current categories" list on the plugin's
    // Configure page, check the preview, then tick "Apply reorganisation".
    //
    //   collections: items in the old collection move to the new one.
    //   tags:        tags are merged (items keep one copy of the new tag).
    //   subjects:    Dublin Core Subject values are rewritten on items.
    //
    // Map an old name to null to remove it without a replacement
    // (items lose that tag/subject; items in a removed collection become
    // "no collection").
    // ------------------------------------------------------------------
    'migrate' => array(
        'collections' => array(
            // 'Old Collection Title' => 'Comparative Religion',
        ),
        'tags' => array(
            // 'old tag' => 'new tag',
        ),
        'subjects' => array(
            // 'Old Subject' => 'New Subject',
        ),
        // Delete old collections and tags once they are empty.
        'delete_old' => true,
    ),
);
