'use strict';

var mUCommentsModule = {};

mUCommentsModule.itemSelector = {};
mUCommentsModule.itemSelector.items = {};
mUCommentsModule.itemSelector.baseId = 0;
mUCommentsModule.itemSelector.selectedId = 0;

mUCommentsModule.itemSelector.onLoad = function (baseId, selectedId)
{
    mUCommentsModule.itemSelector.baseId = baseId;
    mUCommentsModule.itemSelector.selectedId = selectedId;

    // required as a changed object type requires a new instance of the item selector plugin
    jQuery('#mUCommentsModuleObjectType').change(mUCommentsModule.itemSelector.onParamChanged);

    jQuery('#' + baseId + '_catidMain').change(mUCommentsModule.itemSelector.onParamChanged);
    jQuery('#' + baseId + '_catidsMain').change(mUCommentsModule.itemSelector.onParamChanged);
    jQuery('#' + baseId + 'Id').change(mUCommentsModule.itemSelector.onItemChanged);
    jQuery('#' + baseId + 'Sort').change(mUCommentsModule.itemSelector.onParamChanged);
    jQuery('#' + baseId + 'SortDir').change(mUCommentsModule.itemSelector.onParamChanged);
    jQuery('#mUCommentsModuleSearchGo').click(mUCommentsModule.itemSelector.onParamChanged);
    jQuery('#mUCommentsModuleSearchGo').keypress(mUCommentsModule.itemSelector.onParamChanged);

    mUCommentsModule.itemSelector.getItemList();
};

mUCommentsModule.itemSelector.onParamChanged = function ()
{
    jQuery('#ajaxIndicator').removeClass('hidden');

    mUCommentsModule.itemSelector.getItemList();
};

mUCommentsModule.itemSelector.getItemList = function ()
{
    var baseId;
    var params;

    baseId = mUCommentsModule.itemSelector.baseId;
    params = {
        ot: baseId,
        sort: jQuery('#' + baseId + 'Sort').val(),
        sortdir: jQuery('#' + baseId + 'SortDir').val(),
        q: jQuery('#' + baseId + 'SearchTerm').val()
    }
    if (jQuery('#' + baseId + '_catidMain').length > 0) {
        params[catidMain] = jQuery('#' + baseId + '_catidMain').val();
    } else if (jQuery('#' + baseId + '_catidsMain').length > 0) {
        params[catidsMain] = jQuery('#' + baseId + '_catidsMain').val();
    }

    jQuery.getJSON(Routing.generate('mucommentsmodule_ajax_getitemlistfinder'), params, function( data ) {
        var baseId;

        baseId = mUCommentsModule.itemSelector.baseId;
        mUCommentsModule.itemSelector.items[baseId] = data;
        jQuery('#ajaxIndicator').addClass('hidden');
        mUCommentsModule.itemSelector.updateItemDropdownEntries();
        mUCommentsModule.itemSelector.updatePreview();
    });
};

mUCommentsModule.itemSelector.updateItemDropdownEntries = function ()
{
    var baseId, itemSelector, items, i, item;

    baseId = mUCommentsModule.itemSelector.baseId;
    itemSelector = jQuery('#' + baseId + 'Id');
    itemSelector.length = 0;

    items = mUCommentsModule.itemSelector.items[baseId];
    for (i = 0; i < items.length; ++i) {
        item = items[i];
        itemSelector.get(0).options[i] = new Option(item.title, item.id, false);
    }

    if (mUCommentsModule.itemSelector.selectedId > 0) {
        jQuery('#' + baseId + 'Id').val(mUCommentsModule.itemSelector.selectedId);
    }
};

mUCommentsModule.itemSelector.updatePreview = function ()
{
    var baseId, items, selectedElement, i;

    baseId = mUCommentsModule.itemSelector.baseId;
    items = mUCommentsModule.itemSelector.items[baseId];

    jQuery('#' + baseId + 'PreviewContainer').addClass('hidden');

    if (items.length === 0) {
        return;
    }

    selectedElement = items[0];
    if (mUCommentsModule.itemSelector.selectedId > 0) {
        for (var i = 0; i < items.length; ++i) {
            if (items[i].id == mUCommentsModule.itemSelector.selectedId) {
                selectedElement = items[i];
                break;
            }
        }
    }

    if (null !== selectedElement) {
        jQuery('#' + baseId + 'PreviewContainer')
            .html(window.atob(selectedElement.previewInfo))
            .removeClass('hidden');
    }
};

mUCommentsModule.itemSelector.onItemChanged = function ()
{
    var baseId, itemSelector, preview;

    baseId = mUCommentsModule.itemSelector.baseId;
    itemSelector = jQuery('#' + baseId + 'Id').get(0);
    preview = window.atob(mUCommentsModule.itemSelector.items[baseId][itemSelector.selectedIndex].previewInfo);

    jQuery('#' + baseId + 'PreviewContainer').html(preview);
    mUCommentsModule.itemSelector.selectedId = jQuery('#' + baseId + 'Id').val();
};

jQuery(document).ready(function() {
    var infoElem;

    infoElem = jQuery('#itemSelectorInfo');
    if (infoElem.length == 0) {
        return;
    }

    mUCommentsModule.itemSelector.onLoad(infoElem.data('base-id'), infoElem.data('selected-id'));
});
