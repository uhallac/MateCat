let AppDispatcher = require('../dispatcher/AppDispatcher');
let CattolConstants = require('../constants/CatToolConstants');


let CatToolActions = {

    openQaIssues: function () {
        AppDispatcher.dispatch({
            actionType: CattolConstants.SHOW_CONTAINER,
            container: "qaComponent"
        });
    },
    openSearch: function () {
        AppDispatcher.dispatch({
            actionType: CattolConstants.SHOW_CONTAINER,
            container: "search"
        });
    },
    openSegmentFilter: function () {
        AppDispatcher.dispatch({
            actionType: CattolConstants.SHOW_CONTAINER,
            container: "segmentFilter"
        });
    },
    toggleQaIssues: function () {
        AppDispatcher.dispatch({
            actionType: CattolConstants.TOGGLE_CONTAINER,
            container: "qaComponent"
        });
    },
    toggleSearch: function () {
        AppDispatcher.dispatch({
            actionType: CattolConstants.TOGGLE_CONTAINER,
            container: "search"
        });
    },
    toggleSegmentFilter: function () {
        AppDispatcher.dispatch({
            actionType: CattolConstants.TOGGLE_CONTAINER,
            container: "segmentFilter"
        });
    },
    closeSubHeader: function (  ) {
        $('.mbc-history-balloon-outer').removeClass('mbc-visible');
        AppDispatcher.dispatch({
            actionType: CattolConstants.CLOSE_SUBHEADER
        });
    },
    qaComponentSetTagIssues: function ( issues ) {
        AppDispatcher.dispatch({
            actionType: CattolConstants.QA_SET_TAG_ISSUES,
            data: issues
        });
    },
    qaComponentSetGlossaryIssues: function ( issues ) {
        AppDispatcher.dispatch({
            actionType: CattolConstants.QA_SET_GLOSSARY_ISSUES,
            data: issues
        });
    },
    qaComponentsetTranslationConflitcts: function ( issues ) {
        AppDispatcher.dispatch({
            actionType: CattolConstants.QA_SET_TRANSLATION_CONFLICTS,
            data: issues
        });
    },
    qaComponentsetLxqIssues: function ( issues ) {
        AppDispatcher.dispatch({
            actionType: CattolConstants.QA_LEXIQA_ISSUES,
            data: issues
        });
    },
    setSegmentFilter: function ( segments ) {
        AppDispatcher.dispatch({
            actionType: CattolConstants.SET_SEGMENT_FILTER,
            data: segments
        });
    },
};

module.exports = CatToolActions;