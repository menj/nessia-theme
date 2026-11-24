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
