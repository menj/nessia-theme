<?php
/**
 * NESSIA Theme — built-in setup tools (formerly the NessiaSetup plugin).
 *
 * Creates the category structure from data/categories.php, gives records
 * readable Dublin Core Identifiers for the Clean Url plugin, and reorganises
 * collections, tags and subjects. Every write is idempotent.
 *
 * Runs from the public theme only: custom.php calls it once after activation
 * (seed), in small batches on page loads (identifiers), and from the private
 * tools page (?nessia-tools, super users only).
 *
 * @package NESSIA
 */
class NessiaSetup
{
    /**
     * Run $fn while holding a MySQL named lock, so only one request at a
     * time seeds categories or writes Identifiers. GET_LOCK is atomic across
     * connections. With $wait = 0 a busy lock means "someone else is on it":
     * the call returns false and does nothing.
     *
     * @param callable $fn
     * @param int $wait Seconds to wait for the lock.
     * @return mixed|false Result of $fn, or false if the lock was busy.
     */
    public function withLock($fn, $wait = 0)
    {
        $db = get_db();
        $name = 'nessia_setup_' . $db->prefix;
        if ((int) $db->fetchOne('SELECT GET_LOCK(?, ?)', array($name, (int) $wait)) !== 1) {
            return false;
        }
        try {
            return call_user_func($fn);
        } finally {
            $db->query('SELECT RELEASE_LOCK(?)', array($name));
        }
    }

    protected function _categories()
    {
        return include dirname(dirname(__FILE__)) . '/data/categories.php';
    }

    public function seed()
    {
        $categories = $this->_categories();
        $report = array('collections' => 0, 'nested' => 0, 'subjects' => 0, 'tags' => 0);

        foreach ($categories['collections'] as $parentDef) {
            $parent = $this->_ensureCollection($parentDef, $report);
            if (!empty($parentDef['children'])) {
                foreach ($parentDef['children'] as $childDef) {
                    $child = $this->_ensureCollection($childDef, $report);
                    if ($parent && $child && $this->_nest($parent->id, $child->id)) {
                        $report['nested']++;
                    }
                }
            }
        }

        $report['subjects'] = $this->_ensureSubjectVocab($categories['subjects']);

        foreach ($categories['tags'] as $name) {
            if ($this->_ensureTag($name)) {
                $report['tags']++;
            }
        }

        $report['pages'] = $this->_ensurePages();

        set_option('nessia_setup_report', json_encode(array(
            'time'   => date('c'),
            'counts' => $report,
        )));
    }

    /**
     * Landing pages built from theme shortcodes (Simple Pages plugin only).
     * A page whose slug already exists is left exactly as it is.
     *
     * @return int Pages created.
     */
    protected function _ensurePages()
    {
        if (!plugin_is_active('SimplePages') || !class_exists('SimplePagesPage')) {
            return 0;
        }
        $pages = array(
            'overview' => array(
                'title' => 'Archive Overview',
                'text'  => "[nessia_overview]",
            ),
            'start-here' => array(
                'title' => 'Start Here',
                'text'  => implode("\n\n", array(
                    '[nessia_hero kicker="Start here" title="A guide to the archive" text="Primary sources, scholarship and recordings on Semitic religion, language and heritage, arranged by topic." button="Browse every item" link="/items/browse"]',
                    '[nessia_stats]',
                    '[nessia_collections title="Choose a topic"]',
                    '[nessia_featured limit="3"]',
                    '[nessia_gallery title="From the collections" limit="12"]',
                    '[nessia_items title="Recently added" limit="8" more="/items/browse" more_label="Browse all items"]',
                )),
            ),
        );
        $table = get_db()->getTable('SimplePagesPage');
        $user = current_user();
        $owner = $user ? (int) $user->id : (int) get_db()->fetchOne("SELECT id FROM " . get_db()->User . " WHERE role = 'super' ORDER BY id LIMIT 1");
        $created = 0;
        foreach ($pages as $slug => $def) {
            if ($table->findBySql('slug = ?', array($slug), true)) {
                continue;
            }
            $page = new SimplePagesPage;
            $page->title = $def['title'];
            $page->slug = $slug;
            $page->text = $def['text'];
            $page->is_published = 1;
            $page->use_tiny_mce = 0;
            $page->parent_id = 0;
            $page->order = 0;
            $page->created_by_user_id = $owner;
            $page->modified_by_user_id = $owner;
            $page->save();
            $created++;
        }
        return $created;
    }

    /**
     * Find a collection by exact Dublin Core Title, or create it.
     */
    protected function _ensureCollection(array $def, array &$report)
    {
        $existing = $this->_findCollectionByTitle($def['title']);
        if ($existing) {
            return $existing;
        }

        $collection = new Collection;
        $collection->public = 1;
        $collection->featured = 0;
        $user = current_user();
        if ($user) {
            $collection->owner_id = $user->id;
        }

        $texts = array('Title' => array(array('text' => $def['title'], 'html' => false)));
        if (!empty($def['description'])) {
            $texts['Description'] = array(array('text' => $def['description'], 'html' => false));
        }
        $collection->addElementTextsByArray(array('Dublin Core' => $texts));
        $collection->save();

        $report['collections']++;
        return $collection;
    }

    protected function _findCollectionByTitle($title)
    {
        $db = get_db();
        $titleElement = $db->getTable('Element')->findByElementSetNameAndElementName('Dublin Core', 'Title');
        if (!$titleElement) {
            return null;
        }
        $id = $db->fetchOne(
            "SELECT record_id FROM {$db->ElementText}
             WHERE record_type = 'Collection' AND element_id = ? AND text = ?
             LIMIT 1",
            array($titleElement->id, $title)
        );
        return $id ? $db->getTable('Collection')->find($id) : null;
    }

    /**
     * Nest a collection under a parent (Collection Tree plugin only).
     *
     * @return bool True if a new relationship was created.
     */
    protected function _nest($parentId, $childId)
    {
        if (!plugin_is_active('CollectionTree')) {
            return false;
        }
        $db = get_db();
        $table = $db->prefix . 'collection_trees';
        $exists = $db->fetchOne("SELECT id FROM `$table` WHERE collection_id = ? LIMIT 1", array($childId));
        if ($exists) {
            return false;
        }
        $db->query(
            "INSERT INTO `$table` (parent_collection_id, collection_id) VALUES (?, ?)",
            array($parentId, $childId)
        );
        return true;
    }

    /**
     * Merge subject terms into the Simple Vocab list for Dublin Core Subject.
     *
     * @return int Number of terms added.
     */
    protected function _ensureSubjectVocab(array $terms)
    {
        if (!plugin_is_active('SimpleVocab')) {
            return 0;
        }
        $db = get_db();
        $subject = $db->getTable('Element')->findByElementSetNameAndElementName('Dublin Core', 'Subject');
        if (!$subject) {
            return 0;
        }
        $table = $db->prefix . 'simple_vocab_terms';
        $row = $db->fetchRow("SELECT id, terms FROM `$table` WHERE element_id = ? LIMIT 1", array($subject->id));

        $current = $row ? array_filter(array_map('trim', explode("\n", $row['terms']))) : array();
        $merged = $current;
        foreach ($terms as $term) {
            if (!in_array($term, $merged, true)) {
                $merged[] = $term;
            }
        }
        $added = count($merged) - count($current);
        if (!$added) {
            return 0;
        }

        if ($row) {
            $db->query("UPDATE `$table` SET terms = ? WHERE id = ?", array(implode("\n", $merged), $row['id']));
        } else {
            $db->query("INSERT INTO `$table` (element_id, terms) VALUES (?, ?)", array($subject->id, implode("\n", $merged)));
        }
        return $added;
    }

    /**
     * @return bool True if the tag was created.
     */
    protected function _ensureTag($name)
    {
        $db = get_db();
        $exists = $db->fetchOne("SELECT id FROM {$db->Tag} WHERE name = ? LIMIT 1", array($name));
        if ($exists) {
            return false;
        }
        $tag = new Tag;
        $tag->name = $name;
        $tag->save();
        return true;
    }

    // ------------------------------------------------------------------
    // Inventory & reorganisation
    // ------------------------------------------------------------------

    /**
     * Current collections, tags and subject values with item counts.
     */
    public function inventory()
    {
        $db = get_db();
        $out = array('collections' => array(), 'tags' => array(), 'subjects' => array());

        $title = $db->getTable('Element')->findByElementSetNameAndElementName('Dublin Core', 'Title');
        if ($title) {
            $out['collections'] = $db->fetchAll(
                "SELECT c.id, et.text AS name, COUNT(i.id) AS items
                 FROM {$db->Collection} c
                 LEFT JOIN {$db->ElementText} et
                   ON et.record_id = c.id AND et.record_type = 'Collection' AND et.element_id = ?
                 LEFT JOIN {$db->Item} i ON i.collection_id = c.id
                 GROUP BY c.id, et.text ORDER BY et.text",
                array($title->id)
            );
        }

        $out['tags'] = $db->fetchAll(
            "SELECT t.id, t.name, COUNT(rt.id) AS items
             FROM {$db->Tag} t
             LEFT JOIN {$db->RecordsTag} rt ON rt.tag_id = t.id AND rt.record_type = 'Item'
             GROUP BY t.id, t.name ORDER BY t.name"
        );

        $subject = $db->getTable('Element')->findByElementSetNameAndElementName('Dublin Core', 'Subject');
        if ($subject) {
            $out['subjects'] = $db->fetchAll(
                "SELECT text AS name, COUNT(DISTINCT record_id) AS items
                 FROM {$db->ElementText}
                 WHERE record_type = 'Item' AND element_id = ?
                 GROUP BY text ORDER BY text",
                array($subject->id)
            );
        }
        return $out;
    }

    /**
     * Apply (or preview) the 'migrate' mapping.
     *
     * @param bool $dryRun True to only describe what would happen.
     * @return array Human-readable lines.
     */
    public function migrate($dryRun)
    {
        $categories = $this->_categories();
        $map = isset($categories['migrate']) ? $categories['migrate'] : array();
        $deleteOld = !isset($map['delete_old']) || $map['delete_old'];
        $db = get_db();
        $lines = array();
        $verb = $dryRun ? __('Would move') : __('Moved');

        // --- Collections ---------------------------------------------
        if (!empty($map['collections'])) {
            foreach ($map['collections'] as $old => $new) {
                $oldC = $this->_findCollectionByTitle($old);
                if (!$oldC) {
                    $lines[] = __('Collection "%s" not found — skipped.', $old);
                    continue;
                }
                $newId = null;
                if ($new !== null) {
                    $newC = $this->_findCollectionByTitle($new);
                    if (!$newC && !$dryRun) {
                        $dummy = array('collections' => 0);
                        $newC = $this->_ensureCollection(array('title' => $new), $dummy);
                    }
                    $newId = $newC ? (int) $newC->id : null;
                    if ($newId === (int) $oldC->id) {
                        continue;
                    }
                }
                $count = (int) $db->fetchOne("SELECT COUNT(*) FROM {$db->Item} WHERE collection_id = ?", array($oldC->id));
                $lines[] = $new === null
                    ? __('%1$s %2$s item(s) out of collection "%3$s" (no collection).', $verb, $count, $old)
                    : __('%1$s %2$s item(s) from collection "%3$s" to "%4$s".', $verb, $count, $old, $new);

                if (!$dryRun) {
                    if ($newId) {
                        $db->query("UPDATE {$db->Item} SET collection_id = ? WHERE collection_id = ?", array($newId, $oldC->id));
                    } else {
                        $db->query("UPDATE {$db->Item} SET collection_id = NULL WHERE collection_id = ?", array($oldC->id));
                    }
                }
                if ($deleteOld) {
                    $lines[] = $dryRun
                        ? __('Would delete collection "%s".', $old)
                        : __('Deleted collection "%s".', $old);
                    if (!$dryRun) {
                        $this->_removeFromTree($oldC->id, $newId);
                        $oldC->delete();
                    }
                }
            }
        }

        // --- Tags ----------------------------------------------------
        if (!empty($map['tags'])) {
            foreach ($map['tags'] as $old => $new) {
                $oldId = $db->fetchOne("SELECT id FROM {$db->Tag} WHERE name = ? LIMIT 1", array($old));
                if (!$oldId) {
                    $lines[] = __('Tag "%s" not found — skipped.', $old);
                    continue;
                }
                $count = (int) $db->fetchOne("SELECT COUNT(*) FROM {$db->RecordsTag} WHERE tag_id = ?", array($oldId));
                $lines[] = $new === null
                    ? __('%1$s tag "%2$s" off %3$s record(s).', $dryRun ? __('Would remove') : __('Removed'), $old, $count)
                    : __('%1$s tag "%2$s" to "%3$s" on %4$s record(s).', $dryRun ? __('Would merge') : __('Merged'), $old, $new, $count);
                if ($dryRun) {
                    continue;
                }
                if ($new !== null) {
                    $this->_ensureTag($new);
                    $newId = $db->fetchOne("SELECT id FROM {$db->Tag} WHERE name = ? LIMIT 1", array($new));
                    // Re-point links, skipping records that already carry the new tag.
                    $db->query(
                        "UPDATE {$db->RecordsTag} rt
                         LEFT JOIN {$db->RecordsTag} dup
                           ON dup.tag_id = ? AND dup.record_id = rt.record_id AND dup.record_type = rt.record_type
                         SET rt.tag_id = ?
                         WHERE rt.tag_id = ? AND dup.id IS NULL",
                        array($newId, $newId, $oldId)
                    );
                }
                $db->query("DELETE FROM {$db->RecordsTag} WHERE tag_id = ?", array($oldId));
                if ($deleteOld) {
                    $db->query("DELETE FROM {$db->Tag} WHERE id = ?", array($oldId));
                }
            }
        }

        // --- Subjects ------------------------------------------------
        if (!empty($map['subjects'])) {
            $subject = $db->getTable('Element')->findByElementSetNameAndElementName('Dublin Core', 'Subject');
            foreach ($subject ? $map['subjects'] : array() as $old => $new) {
                $count = (int) $db->fetchOne(
                    "SELECT COUNT(*) FROM {$db->ElementText} WHERE record_type = 'Item' AND element_id = ? AND text = ?",
                    array($subject->id, $old)
                );
                if (!$count) {
                    $lines[] = __('Subject "%s" not used on any item — skipped.', $old);
                    continue;
                }
                $lines[] = $new === null
                    ? __('%1$s subject "%2$s" from %3$s item(s).', $dryRun ? __('Would remove') : __('Removed'), $old, $count)
                    : __('%1$s subject "%2$s" to "%3$s" on %4$s item(s).', $dryRun ? __('Would rename') : __('Renamed'), $old, $new, $count);
                if ($dryRun) {
                    continue;
                }
                if ($new === null) {
                    $db->query(
                        "DELETE FROM {$db->ElementText} WHERE record_type = 'Item' AND element_id = ? AND text = ?",
                        array($subject->id, $old)
                    );
                    continue;
                }
                // Drop the old value where the item already has the new one; rename the rest.
                $db->query(
                    "DELETE et FROM {$db->ElementText} et
                     JOIN {$db->ElementText} keep
                       ON keep.record_id = et.record_id AND keep.record_type = 'Item'
                      AND keep.element_id = et.element_id AND keep.text = ?
                     WHERE et.record_type = 'Item' AND et.element_id = ? AND et.text = ?",
                    array($new, $subject->id, $old)
                );
                $db->query(
                    "UPDATE {$db->ElementText} SET text = ? WHERE record_type = 'Item' AND element_id = ? AND text = ?",
                    array($new, $subject->id, $old)
                );
            }
        }

        if (!$lines) {
            $lines[] = __('No reorganisation mapping set in categories.php.');
        } elseif (!$dryRun) {
            $lines[] = __('Done. Rebuild the search index: Settings → Search → Index Records.');
        }
        return $lines;
    }

    /**
     * Collection Tree clean-up before a collection is deleted: children
     * of the old collection are re-parented to the new one (or the root).
     */
    protected function _removeFromTree($oldId, $newId)
    {
        if (!plugin_is_active('CollectionTree')) {
            return;
        }
        $db = get_db();
        $table = $db->prefix . 'collection_trees';
        if ($newId) {
            $db->query("UPDATE `$table` SET parent_collection_id = ? WHERE parent_collection_id = ?", array($newId, $oldId));
        } else {
            $db->query("DELETE FROM `$table` WHERE parent_collection_id = ?", array($oldId));
        }
        $db->query("DELETE FROM `$table` WHERE collection_id = ?", array($oldId));
    }

    // ------------------------------------------------------------------
    // Readable URL names (Dublin Core Identifier) for the Clean Url plugin
    // ------------------------------------------------------------------

    /**
     * Preview or write identifiers for every collection and item without one.
     *
     * @param bool $dryRun
     * @return array rows (type, id, title, slug) and skipped (records that already have an identifier)
     */
    public function slugs($dryRun, $limit = 0)
    {
        $db = get_db();
        $reserved = array();
        $ids = $this->_identifierElement();
        $titleEl = $db->getTable('Element')->findByElementSetNameAndElementName('Dublin Core', 'Title');
        $out = array('rows' => array(), 'skipped' => 0);
        if (!$ids || !$titleEl) {
            return $out;
        }
        $out['missing'] = 0;
        foreach (array('Collection', 'Item') as $type) {
            // Every Identifier already used by this record type, in one query,
            // so choosing unique names needs no further lookups.
            $taken = array();
            foreach ($db->fetchCol(
                "SELECT text FROM {$db->ElementText} WHERE record_type = ? AND element_id = ?",
                array($type, $ids->id)
            ) as $text) {
                $taken[(string) $text] = true;
            }
            $reserved[$type] = $taken;
            $table = $type === 'Item' ? $db->Item : $db->Collection;
            $rows = $db->fetchAll(
                "SELECT r.id, t.text AS title,
                        (SELECT COUNT(*) FROM {$db->ElementText} x
                          WHERE x.record_id = r.id AND x.record_type = ? AND x.element_id = ?) AS has_id
                 FROM `$table` r
                 LEFT JOIN {$db->ElementText} t
                   ON t.record_id = r.id AND t.record_type = ? AND t.element_id = ?
                 GROUP BY r.id, t.text
                 ORDER BY r.id",
                array($type, $ids->id, $type, $titleEl->id)
            );
            $seen = array();
            foreach ($rows as $row) {
                if (isset($seen[$row['id']])) {
                    continue; // several titles: use the first
                }
                $seen[$row['id']] = true;
                if ((int) $row['has_id'] > 0) {
                    $out['skipped']++;
                    continue;
                }
                $title = trim(strip_tags((string) $row['title']));
                if ($title === '') {
                    continue;
                }
                $out['missing']++;
                if ($limit && count($out['rows']) >= $limit) {
                    continue; // keep counting, stop listing/writing
                }
                $slug = $this->_uniqueSlug($title, $type, $reserved);
                $reserved[$type][$slug] = true;
                if (!$dryRun) {
                    // Re-check inside the lock: another request may have named it.
                    if ($this->_hasIdentifier($row['id'], $type, $ids->id)) {
                        continue;
                    }
                    $this->_writeIdentifier($row['id'], $type, $ids->id, $slug);
                }
                $out['rows'][] = array('type' => $type, 'id' => (int) $row['id'], 'title' => $title, 'slug' => $slug);
            }
        }
        return $out;
    }

    protected function _identifierElement()
    {
        return get_db()->getTable('Element')->findByElementSetNameAndElementName('Dublin Core', 'Identifier');
    }

    protected function _hasIdentifier($id, $type, $elementId)
    {
        $db = get_db();
        return (bool) $db->fetchOne(
            "SELECT id FROM {$db->ElementText} WHERE record_id = ? AND record_type = ? AND element_id = ? LIMIT 1",
            array($id, $type, $elementId)
        );
    }

    protected function _title($id, $type)
    {
        $db = get_db();
        $el = $db->getTable('Element')->findByElementSetNameAndElementName('Dublin Core', 'Title');
        return $el ? trim(strip_tags((string) $db->fetchOne(
            "SELECT text FROM {$db->ElementText} WHERE record_id = ? AND record_type = ? AND element_id = ? ORDER BY id LIMIT 1",
            array($id, $type, $el->id)
        ))) : '';
    }

    protected function _writeIdentifier($id, $type, $elementId, $slug)
    {
        $et = new ElementText;
        $et->record_id = (int) $id;
        $et->record_type = $type;
        $et->element_id = (int) $elementId;
        $et->text = $slug;
        $et->html = 0;
        $et->save();
    }

    /**
     * Lower-case, ASCII, hyphenated, at most 80 characters, unique among
     * identifiers of the same record type.
     */
    protected function _uniqueSlug($title, $type, array $reserved)
    {
        $base = $this->_slugify($title);
        if ($base === '') {
            $base = strtolower($type);
        }
        $slug = $base;
        $n = 2;
        while (!empty($reserved[$type][$slug])) {
            $slug = $base . '-' . $n++;
        }
        return $slug;
    }

    public static function slugify($text)
    {
        $text = html_entity_decode((string) $text, ENT_QUOTES, 'UTF-8');
        // Common transliteration marks (ʿ ʾ ā ī ū ḥ ṣ …) collapse to plain letters.
        if (function_exists('transliterator_transliterate')) {
            $ascii = transliterator_transliterate('Any-Latin; Latin-ASCII; Lower()', $text);
        } else {
            $ascii = strtolower((string) @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $text));
        }
        $ascii = str_replace(array("'", '`', '"'), '', (string) $ascii);
        $ascii = preg_replace('/[^a-z0-9]+/', '-', $ascii);
        $ascii = trim($ascii, '-');
        if (strlen($ascii) > 80) {
            $ascii = substr($ascii, 0, 80);
            $cut = strrpos($ascii, '-');
            if ($cut !== false && $cut > 40) {
                $ascii = substr($ascii, 0, $cut);
            }
        }
        return $ascii;
    }

    protected function _slugify($text)
    {
        return self::slugify($text);
    }
}
