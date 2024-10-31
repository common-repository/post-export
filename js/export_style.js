var getCheckBoxesIDArray	= new Array();
var getSelectedIDArray		= new Array();
var getCheckedBoxNameArray	= new Array();
var postAlreadyAdded		= 0;

function insertInArray(getPostID)
{
    var getArrayLength	= getSelectedIDArray.length;
    if(getArrayLength == 0)
    {
    }
    else
    {
        for(var i = 0; i < getArrayLength; i++)
        {
            if(getSelectedIDArray[i] == getPostID)
            {
                return false;
            }
        }
    }
    getSelectedIDArray[getArrayLength]	= getPostID;
}

function inArray(value, getArray)
{
    if(getArray.length == 0)
    {
        return false;
    }
    else
    {
        for(var i = 0; i < getArray.length; i++)
        {
            if(getArray[i] == value)
            {
                return true;
            }
        }
    }
    return false;
}
function checkAllPostFields(isChecked)
{
    if(isChecked)
    {
        jQuery("#exportActionDiv input[type='checkbox']").each(function()
        {
            jQuery(this).attr('checked', 'checked');
        });
    }
    else
    {
        jQuery("#exportActionDiv input[type='checkbox']").each(function()
        {
            jQuery(this).attr('checked', false);
        });
    }
}
function changeExportOptions(getValue)
{
	if(getValue == 1)
	{
		jQuery('#exportPostIDDiv').css('display', 'block');
		jQuery('#exportPageIDDiv').css('display', 'none');
	}
	else
	{
		jQuery('#exportPageIDDiv').css('display', 'block');
		jQuery('#exportPostIDDiv').css('display', 'none');
	}
}
function showBulkExportPopup()
{
	jQuery('#exportPostIDDiv').html('');
    getCheckBoxesIDArray	= new Array();
    getSelectedIDArray		= new Array();
    getCheckedBoxNameArray	= new Array();
    
    jQuery('input[name="post[]"]').each(function()
    {
        getCheckBoxesIDArray[getCheckBoxesIDArray.length]	= jQuery(this).val();
        getCheckedBoxNameArray[jQuery(this).val()]			= jQuery(this).parent().find('label').html();
        if(jQuery(this).attr('checked') == "checked")
        {
            insertInArray(jQuery(this).val());
        }
        else
        {
        }
    });
    if(getSelectedIDArray.length == 0)
    {
        alert("please select atleast one post to export.");
    }
    else
    {
        if(postAlreadyAdded == 0)
        {
            //postAlreadyAdded	= 1;
            for(var i = 0; i < getCheckBoxesIDArray.length; i++)
            {
                var checked	= "";
				//alert(getSelectedIDArray);
                if(inArray(getCheckBoxesIDArray[i], getSelectedIDArray))
                {
                    checked	= "checked='checked'";
                }
                
                var getHTML	= '<div><input type="checkbox" id="bulk_export_post_id'+getCheckBoxesIDArray[i]+'" name="bulk_export_post_id[]" '+checked+' value="'+getCheckBoxesIDArray[i]+'" /><label for="bulk_export_post_id'+getCheckBoxesIDArray[i]+'"> '+getCheckedBoxNameArray[getCheckBoxesIDArray[i]].replace('Select ', '')+'</label></div>';
                jQuery('#exportPostIDDiv').append(getHTML);
            }
        }
        
        jQuery('#bulk-edit-export').css('display', 'block');
        jQuery('#bulk-edit-option-screen').css('display', 'block');
    }
}
jQuery(document).ready(function()
{
    //alert("fdsf");

    jQuery("select[name='action']").after('<input type="button" id="doaction-option" name="doaction-option" value="Apply" class="button action" style="display:none; margin:1px 8px 0px 0px;" />');
    jQuery("select[name='action2']").after('<input type="button" id="doaction2-option" name="doaction2-option" value="Apply" class="button action" style="display:none; margin:1px 8px 0px 0px;" />');
    
    jQuery('<option>').val('export_option').text('Export Posts').addClass('hide-if-no-js').appendTo("select[name='action']");
    jQuery('<option>').val('export_option').text('Export Posts').appendTo("select[name='action2']");
    
    jQuery("select[name='action']").change(function()
    {
        if(jQuery(this).val() == "export_option")
        {
            jQuery('#doaction-option').css('display', 'inline');
            jQuery('#doaction').css('display', 'none');
        }
        else
        {
            jQuery('#doaction-option').css('display', 'none');
            jQuery('#doaction').css('display', 'inline');
        }
    });
    
    jQuery("select[name='action2']").change(function()
    {
        if(jQuery(this).val() == "export_option")
        {
            jQuery('#doaction2-option').css('display', 'inline');
            jQuery('#doaction2').css('display', 'none');
        }
        else
        {
            jQuery('#doaction2-option').css('display', 'none');
            jQuery('#doaction2').css('display', 'inline');
        }
    });
    
    jQuery('#doaction2-option').click(function()
    {
        if(jQuery("select[name='action2']").val() == "export_option")
        {
            showBulkExportPopup();
        }
    });
    
    jQuery('#doaction-option').click(function()
    {
        if(jQuery("select[name='action']").val() == "export_option")
        {
            showBulkExportPopup();
        }
    });
    
    jQuery('#cancelExportButton').click(function()
    {
        jQuery('#bulk-edit-export').css('display', 'none');
        jQuery('#bulk-edit-option-screen').css('display', 'none');
    });
    
    jQuery('#submitExportButton').click(function()
    {
        var totalPostChecked	= 0;
        var totalPageChecked	= 0;
        if(document.getElementById('export_post_options').checked == true)
        {
            for(var i = 0; i < getCheckBoxesIDArray.length; i++)
            {
                if(jQuery('#bulk_export_post_id'+getCheckBoxesIDArray[i]).attr('checked') == 'checked')
                {
                    totalPostChecked++;
                }
            }
            if(totalPostChecked == 0)
            {
                alert("please select atleast one post to export.");
                return false;
            }
        }
        
        var totalFieldSelected	= 0;
        jQuery('#exportActionDiv input[type="checkbox"]').each(function()
        {
            if(jQuery(this).attr('checked') == "checked" || jQuery(this).attr('checked') == true)
            {
                totalFieldSelected++;
            }
        });
        
        if(totalFieldSelected == 0)
        {
            alert("Please select atleast one field to export.");
            return false;
        }
        jQuery('#bulk-edit-export').css('display', 'none');
        jQuery('#bulk-edit-option-screen').css('display', 'none');
        jQuery('#bulkExportForm').submit();
    });
});