{* Purpose of this template: edit view of generic item list content type *}
<div class="form-group">
    {gt text='Object type' domain='mucommentsmodule' assign='objectTypeSelectorLabel'}
    {formlabel for='mUCommentsModuleObjectType' text=$objectTypeSelectorLabel cssClass='col-sm-3 control-label'}
    <div class="col-sm-9">
        {mucommentsmoduleObjectTypeSelector assign='allObjectTypes'}
        {formdropdownlist id='mUCommentsModuleObjectType' dataField='objectType' group='data' mandatory=true items=$allObjectTypes cssClass='form-control'}
        <span class="help-block">{gt text='If you change this please save the element once to reload the parameters below.' domain='mucommentsmodule'}</span>
    </div>
</div>

<div class="form-group">
    {gt text='Sorting' domain='mucommentsmodule' assign='sortingLabel'}
    {formlabel text=$sortingLabel cssClass='col-sm-3 control-label'}
    <div class="col-sm-9">
        {formradiobutton id='mUCommentsModuleSortRandom' value='random' dataField='sorting' group='data' mandatory=true}
        {gt text='Random' domain='mucommentsmodule' assign='sortingRandomLabel'}
        {formlabel for='mUCommentsModuleSortRandom' text=$sortingRandomLabel}
        {formradiobutton id='mUCommentsModuleSortNewest' value='newest' dataField='sorting' group='data' mandatory=true}
        {gt text='Newest' domain='mucommentsmodule' assign='sortingNewestLabel'}
        {formlabel for='mUCommentsModuleSortNewest' text=$sortingNewestLabel}
        {formradiobutton id='mUCommentsModuleSortDefault' value='default' dataField='sorting' group='data' mandatory=true}
        {gt text='Default' domain='mucommentsmodule' assign='sortingDefaultLabel'}
        {formlabel for='mUCommentsModuleSortDefault' text=$sortingDefaultLabel}
    </div>
</div>

<div class="form-group">
    {gt text='Amount' domain='mucommentsmodule' assign='amountLabel'}
    {formlabel for='mUCommentsModuleAmount' text=$amountLabel cssClass='col-sm-3 control-label'}
    <div class="col-sm-9">
        {formintinput id='mUCommentsModuleAmount' dataField='amount' group='data' mandatory=true maxLength=2 cssClass='form-control'}
    </div>
</div>

<div class="form-group">
    {gt text='Template' domain='mucommentsmodule' assign='templateLabel'}
    {formlabel for='mUCommentsModuleTemplate' text=$templateLabel cssClass='col-sm-3 control-label'}
    <div class="col-sm-9">
        {mucommentsmoduleTemplateSelector assign='allTemplates'}
        {formdropdownlist id='mUCommentsModuleTemplate' dataField='template' group='data' mandatory=true items=$allTemplates cssClass='form-control'}
    </div>
</div>

<div id="customTemplateArea" class="form-group"{* data-switch="mUCommentsModuleTemplate" data-switch-value="custom"*}>
    {gt text='Custom template' domain='mucommentsmodule' assign='customTemplateLabel'}
    {formlabel for='mUCommentsModuleCustomTemplate' text=$customTemplateLabel cssClass='col-sm-3 control-label'}
    <div class="col-sm-9">
        {formtextinput id='mUCommentsModuleCustomTemplate' dataField='customTemplate' group='data' mandatory=false maxLength=80 cssClass='form-control'}
        <span class="help-block">{gt text='Example' domain='mucommentsmodule'}: <em>itemlist_[objectType]_display.html.twig</em></span>
    </div>
</div>

<div class="form-group">
    {gt text='Filter (expert option)' domain='mucommentsmodule' assign='filterLabel'}
    {formlabel for='mUCommentsModuleFilter' text=$filterLabel cssClass='col-sm-3 control-label'}
    <div class="col-sm-9">
        {formtextinput id='mUCommentsModuleFilter' dataField='filter' group='data' mandatory=false maxLength=255 cssClass='form-control'}
    </div>
</div>

<script type="text/javascript">
    (function($) {
    	$('#mUCommentsModuleTemplate').change(function() {
    	    $('#customTemplateArea').toggleClass('hidden', $(this).val() != 'custom');
	    }).trigger('change');
    })(jQuery)
</script>
