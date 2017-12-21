'use strict';

function mUCommentsValidateNoSpace(val) {
    var valStr;
    valStr = new String(val);

    return (valStr.indexOf(' ') === -1);
}

/**
 * Runs special validation rules.
 */
function mUCommentsExecuteCustomValidationConstraints(objectType, currentEntityId) {
}
