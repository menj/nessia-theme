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
);
