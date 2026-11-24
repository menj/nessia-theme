# NESSIA Setup (companion plugin)

Creates the NESSIA category structure in one go.

## Install
1. Copy the `NessiaSetup` folder into your Omeka `plugins/` directory.
2. Admin → Plugins → **NESSIA Setup** → Install.

On install it creates, skipping anything that already exists:
- **Collections**: from `categories.php`, public. Sub-collections are nested if the **Collection Tree** plugin is active (install Collection Tree first).
- **Subject vocabulary**: added to Dublin Core *Subject* if the **Simple Vocab** plugin is active, so editors pick subjects from a list.
- **Tags**: created in the tag list, ready to attach to items.

## Change the categories
Edit `categories.php`, then Admin → Plugins → NESSIA Setup → Configure → tick **Run setup again** → Save. Nothing is deleted or renamed.

The NESSIA theme reads the same file for its homepage **Explore by topic** section.

## Reorganise existing content
1. **Back up the database.**
2. Admin → Plugins → NESSIA Setup → Configure. The **Current categories** list shows every existing collection, tag and subject with its item count.
3. In `categories.php`, fill in `migrate` with old name → new name (or `null` to remove).
4. Reload the Configure page and read the **Preview**. Nothing has changed yet.
5. Tick **Apply reorganisation** → Save.
6. Settings → Search → **Index Records** so search reflects the new categories.

## Readable addresses (Clean Url)
Turns `/items/show/24` into `/comparative-religion/al-tawhid-its-implications-for-thought-and-life`.

1. Install the **Clean Url** plugin for Omeka Classic (Admin → Plugins).
2. **Back up the database.**
3. NESSIA Setup → Configure → **Readable addresses**: check the preview, tick **Write Identifiers** and **Name new records automatically**, Save.
4. Clean Url → Configure: use **Dublin Core: Identifier**, no prefix, and choose the item form you prefer (for example *collection / item*). Old `/items/show/…` addresses keep working.

The NESSIA theme prints a `<link rel="canonical">` with the record's address, so search engines index the clean form once Clean Url is active.
