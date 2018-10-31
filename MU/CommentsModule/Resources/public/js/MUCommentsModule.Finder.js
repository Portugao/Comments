'use strict';

var currentMUCommentsModuleEditor = null;
var currentMUCommentsModuleInput = null;

/**
 * Returns the attributes used for the popup window. 
 * @return {String}
 */
function getMUCommentsModulePopupAttributes() {
    var pWidth, pHeight;

    pWidth = screen.width * 0.75;
    pHeight = screen.height * 0.66;

    return 'width=' + pWidth + ',height=' + pHeight + ',location=no,menubar=no,toolbar=no,dependent=yes,minimizable=no,modal=yes,alwaysRaised=yes,resizable=yes,scrollbars=yes';
}

/**
 * Open a popup window with the finder triggered by an editor button.
 */
function MUCommentsModuleFinderOpenPopup(editor, editorName) {
    var popupUrl;

    // Save editor for access in selector window
    currentMUCommentsModuleEditor = editor;

    popupUrl = Routing.generate('mucommentsmodule_external_finder', { objectType: 'comment', editor: editorName });

    if (editorName == 'ckeditor') {
        editor.popup(popupUrl, /*width*/ '80%', /*height*/ '70%', getMUCommentsModulePopupAttributes());
    } else {
        window.open(popupUrl, '_blank', getMUCommentsModulePopupAttributes());
    }
}


var mUCommentsModule = {};

mUCommentsModule.finder = {};

mUCommentsModule.finder.onLoad = function (baseId, selectedId) {
    if (jQuery('#mUCommentsModuleSelectorForm').length < 1) {
        return;
    }
    jQuery('select').not("[id$='pasteAs']").change(mUCommentsModule.finder.onParamChanged);
    
    jQuery('.btn-default').click(mUCommentsModule.finder.handleCancel);

    var selectedItems = jQuery('#mucommentsmoduleItemContainer a');
    selectedItems.bind('click keypress', function (event) {
        event.preventDefault();
        mUCommentsModule.finder.selectItem(jQuery(this).data('itemid'));
    });
};

mUCommentsModule.finder.onParamChanged = function () {
    jQuery('#mUCommentsModuleSelectorForm').submit();
};

mUCommentsModule.finder.handleCancel = function (event) {
    var editor;

    event.preventDefault();
    editor = jQuery("[id$='editor']").first().val();
    if ('ckeditor' === editor) {
        mUCommentsClosePopup();
    } else if ('quill' === editor) {
        mUCommentsClosePopup();
    } else if ('summernote' === editor) {
        mUCommentsClosePopup();
    } else if ('tinymce' === editor) {
        mUCommentsClosePopup();
    } else {
        alert('Close Editor: ' + editor);
    }
};


function mUCommentsGetPasteSnippet(mode, itemId) {
    var quoteFinder;
    var itemPath;
    var itemUrl;
    var itemTitle;
    var itemDescription;
    var pasteMode;

    quoteFinder = new RegExp('"', 'g');
    itemPath = jQuery('#path' + itemId).val().replace(quoteFinder, '');
    itemUrl = jQuery('#url' + itemId).val().replace(quoteFinder, '');
    itemTitle = jQuery('#title' + itemId).val().replace(quoteFinder, '').trim();
    itemDescription = jQuery('#desc' + itemId).val().replace(quoteFinder, '').trim();
    if (!itemDescription) {
        itemDescription = itemTitle;
    }
    pasteMode = jQuery("[id$='pasteAs']").first().val();

    // item ID
    if (pasteMode === '3') {
        return '' + itemId;
    }

    // relative link to detail page
    if (pasteMode === '1') {
        return mode === 'url' ? itemPath : '<a href="' + itemPath + '" title="' + itemDescription + '">' + itemTitle + '</a>';
    }
    // absolute url to detail page
    if (pasteMode === '2') {
        return mode === 'url' ? itemUrl : '<a href="' + itemUrl + '" title="' + itemDescription + '">' + itemTitle + '</a>';
    }

    return '';
}


// User clicks on "select item" button
mUCommentsModule.finder.selectItem = function (itemId) {
    var editor, html;

    html = mUCommentsGetPasteSnippet('html', itemId);
    editor = jQuery("[id$='editor']").first().val();
    if ('ckeditor' === editor) {
        if (null !== window.opener.currentMUCommentsModuleEditor) {
            window.opener.currentMUCommentsModuleEditor.insertHtml(html);
        }
    } else if ('quill' === editor) {
        if (null !== window.opener.currentMUCommentsModuleEditor) {
            window.opener.currentMUCommentsModuleEditor.clipboard.dangerouslyPasteHTML(window.opener.currentMUCommentsModuleEditor.getLength(), html);
        }
    } else if ('summernote' === editor) {
        if (null !== window.opener.currentMUCommentsModuleEditor) {
            html = jQuery(html).get(0);
            window.opener.currentMUCommentsModuleEditor.invoke('insertNode', html);
        }
    } else if ('tinymce' === editor) {
        window.opener.currentMUCommentsModuleEditor.insertContent(html);
    } else {
        alert('Insert into Editor: ' + editor);
    }
    mUCommentsClosePopup();
};

function mUCommentsClosePopup() {
    window.opener.focus();
    window.close();
}

jQuery(document).ready(function () {
    mUCommentsModule.finder.onLoad();
});
