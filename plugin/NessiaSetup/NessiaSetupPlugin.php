<?php
/**
 * NESSIA Setup — seeds collections, subject vocabulary and tags.
 *
 * Runs on install, and again whenever "Save Changes" is pressed on the
 * plugin's Configure page. Every step is idempotent: nothing that already
 * exists is changed or duplicated.
 *
 * @package NessiaSetup
 */
class NessiaSetupPlugin extends Omeka_Plugin_AbstractPlugin
{
    protected $_hooks = array('install', 'config_form', 'config');

    public function hookInstall()
    {
        $this->_seed();
    }

    public function hookConfigForm()
    {
        $categories = $this->_categories();
        $report = get_option('nessia_setup_report');
        include dirname(__FILE__) . '/views/admin/config-form.php';
    }

    public function hookConfig($args)
    {
        if (!empty($args['post']['nessia_setup_run'])) {
            $this->_seed();
        }
    }

    // ------------------------------------------------------------------

    protected function _categories()
    {
        return include dirname(__FILE__) . '/categories.php';
    }

    protected function _seed()
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

        set_option('nessia_setup_report', json_encode(array(
            'time'   => date('c'),
            'counts' => $report,
        )));
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
}
