'use strict';

var editedObjectType;
var editedEntityId;
var editForm;
var formButtons;
var triggerValidation = true;

function mUCommentsTriggerFormValidation() {
    mUCommentsExecuteCustomValidationConstraints(editedObjectType, editedEntityId);

    if (!editForm.get(0).checkValidity()) {
        // This does not really submit the form,
        // but causes the browser to display the error message
        editForm.find(':submit').first().click();
    }
}

function mUCommentsHandleFormSubmit(event) {
    if (triggerValidation) {
        mUCommentsTriggerFormValidation();
        if (!editForm.get(0).checkValidity()) {
            event.preventDefault();
            return false;
        }
    }

    // hide form buttons to prevent double submits by accident
    formButtons.each(function (index) {
        jQuery(this).addClass('hidden');
    });

    return true;
}

/**
 * Initialises an entity edit form.
 */
function mUCommentsInitEditForm(mode, entityId) {
    if (jQuery('.mucomments-edit-form').length < 1) {
        return;
    }

    editForm = jQuery('.mucomments-edit-form').first();
    editedObjectType = editForm.attr('id').replace('EditForm', '');
    editedEntityId = entityId;

    if (jQuery('#moderationFieldsSection').length > 0) {
        jQuery('#moderationFieldsContent').addClass('hidden');
        jQuery('#moderationFieldsSection legend').addClass('pointer').click(function (event) {
            if (jQuery('#moderationFieldsContent').hasClass('hidden')) {
                jQuery('#moderationFieldsContent').removeClass('hidden');
                jQuery(this).find('i').removeClass('fa-expand').addClass('fa-compress');
            } else {
                jQuery('#moderationFieldsContent').addClass('hidden');
                jQuery(this).find('i').removeClass('fa-compress').addClass('fa-expand');
            }
        });
    }

    var allFormFields = editForm.find('input, select, textarea');
    allFormFields.change(function (event) {
        mUCommentsExecuteCustomValidationConstraints(editedObjectType, editedEntityId);
    });

    formButtons = editForm.find('.form-buttons input');
    if (editForm.find('.btn-danger').length > 0) {
        editForm.find('.btn-danger').first().bind('click keypress', function (event) {
            if (!window.confirm(Translator.__('Do you really want to delete this entry?'))) {
                event.preventDefault();
            }
        });
    }
    editForm.find('button[type=submit]').bind('click keypress', function (event) {
        triggerValidation = !jQuery(this).attr('formnovalidate');
    });
    editForm.submit(mUCommentsHandleFormSubmit);

    if (mode != 'create') {
        mUCommentsTriggerFormValidation();
    }
}

/**
 * Initialises a relation field section with autocompletion and optional edit capabilities.
 */
function mUCommentsInitRelationHandling(objectType, alias, idPrefix, includeEditing, inputType, createUrl) {
    if (inputType == 'autocomplete') {
        mUCommentsInitAutoCompletion(objectType, alias, idPrefix, includeEditing);
    }
    if (includeEditing) {
        mUCommentsInitInlineEditingButtons(objectType, alias, idPrefix, inputType, createUrl);
    }
}

