<?php
/**
 * NESSIA — site search form (overrides Omeka core partial).
 *
 * Replaces the earlier regex patching in header.php: accessible labels,
 * a real toggle button and a hidden advanced panel are rendered directly.
 *
 * Variables provided by Omeka_View_Helper_SearchForm:
 * $options, $filters, $query_types, $record_types
 */
$searchLabel = __('Search');
?>
<?php echo $this->form('search-form', $options['form_attributes']); ?>
    <label class="sr-only" for="query"><?php echo $searchLabel; ?></label>
    <?php echo $this->formText('query', $filters['query'], array(
        'id'          => 'query',
        'placeholder' => __('Search the archive…'),
    )); ?>

    <?php if ($options['show_advanced']): ?>
    <button type="button" class="advanced-toggle" id="advanced-toggle"
            aria-controls="advanced-form" aria-expanded="false"><?php echo __('Advanced search'); ?></button>

    <div id="advanced-form" role="group" aria-labelledby="advanced-toggle" hidden>
        <fieldset id="query-types">
            <legend><?php echo __('Search using this query type:'); ?></legend>
            <?php echo $this->formRadio('query_type', $filters['query_type'], null, $query_types); ?>
        </fieldset>

        <?php if ($record_types): ?>
        <fieldset id="record-types">
            <legend><?php echo __('Search only these record types:'); ?></legend>
            <?php foreach ($record_types as $key => $value): ?>
            <div class="nessia-check">
                <?php echo $this->formCheckbox('record_types[]', $key, array(
                    'id'      => 'record_types-' . $key,
                    'checked' => in_array($key, $filters['record_types']),
                )); ?>
                <?php echo $this->formLabel('record_types-' . $key, $value); ?>
            </div>
            <?php endforeach; ?>
        </fieldset>
        <?php endif; ?>

        <p class="nessia-items-search-link"><?php echo link_to_item_search(__('Advanced item search')); ?></p>
    </div>
    <?php else: ?>
        <?php echo $this->formHidden('query_type', $filters['query_type']); ?>
        <?php foreach ($filters['record_types'] as $type): ?>
        <?php echo $this->formHidden('record_types[]', $type); ?>
        <?php endforeach; ?>
    <?php endif; ?>

    <button type="submit" name="submit_search" id="submit_search" aria-label="<?php echo html_escape($searchLabel); ?>"><?php echo $searchLabel; ?></button>
</form>
