
var refreshWidget;

/************** ClassCanvas ****************/
eletroCanvas = function(id) {
  this.init(id);
}

jQuery.extend(eletroCanvas.prototype, {

    id: '',
    columns: new Array(),
    index: '',
    
    init: function(id) {
        this.id = 'eletro_widgets_container_' + id;
        this.index = id;
        this.columns = new Array()
        var this_canvas = this;
        
        jQuery('#' + this.id).find('.recebeDrag').each(function() {
            this_canvas.columns.push(new eletroColumn(this.id, this_canvas));
        });
        
        //behavior do botao add
        jQuery('#' + this.id).find('.eletro_widgets_add_button').click(function() {
            this_canvas.add(jQuery(this));
        });
        
        // select behaviour 
        jQuery('#' + this.id).find('#eletro_widgets_add').change(function() {
        	jQuery('#' + this_canvas.id).find('.widget_add_control').hide();
        	if (jQuery(this).val()) {
        		jQuery('#' + this_canvas.id).find('#widget_add_control_' + jQuery(this).val()).show();
        	}
        });
    },
    
    save: function() {
        
        //save canvas
        var save_this = this;
        
        values = this.getCurrentWidgets();
        
        debug = jQuery.ajax({
            type: 'POST',
            dataType: 'html',
            url: eletro_widgets_ajax_url,
             data: 
            {
                action: 'save',
                'value[]': values,
                id: save_this.index
            },
            complete: function() {jQuery("#debug").append(debug.responseText)}
        });    
    },
    
    add: function(button) {
        var this_add = this;
        var widget_type = button.siblings('.add').val();
        if (widget_type == 'multi') {
        	
            // This is what we are going to post
            var number = button.siblings('.multi_number').val();
        	var id = button.siblings('.widget-id').val();
        	
            // This is used to know th ID of the new Widget and create the new Instance
        	var id_base = button.siblings('.id_base').val();
        	var newName = id_base + '-' + number;
        	
            // This increments multi-number value so the next instance will have another number
        	button.siblings('.multi_number').val( parseInt(button.siblings('.multi_number').val()) + 1 );
        } else {
            // When it is a single widget, all we want is its id
        	var id = button.siblings('.widget-id').val();
            var newName = id;
        }
        widgetContent = jQuery.ajax({
                type: 'POST',
                url: eletro_widgets_ajax_url,
                dataType: 'html',
                data: 
                {
                    action: 'add',
                    number: number,
                    id: id
                },
                complete: function() 
                {
                    jQuery('#' + this_add.id).find('#eletro_widgets_col_0').prepend(widgetContent.responseText);
                    new eletroItem(newName, this_add);  
                    this_add.save();
                    jQuery('#' + this_add.id).find('#eletro_widgets_add').val('');
                }
            });
    },
    
    getCurrentWidgets: function() {
    
        var col = 0;
        var values = Array();
        var save_save_this = this;
        
        jQuery('#' + this.id).find('.recebeDrag').each(function() {
            var thisItems = new Array();
            jQuery(this).find('div.itemDrag:not(".ui-sortable-helper")').each(function() {
                var number = jQuery(this).children('input[name=widget-number]').val();
                var id = jQuery(this).children('input[name=widget-id]').val();
                
                var widget = id + 'X|X' + number;
            	thisItems.push(widget);
            });            
            values.push(thisItems);
        });
        
        return values;   
    },
    
    updateControl: function(widget, disable) {
        var wOption = jQuery('#' + this.id).find('option[value="'+widget+'"]');
        if (disable  ) {
            wOption.attr('disabled', 'disabled');
        } else {
            wOption.removeAttr('disabled');
        }

    },
    
    refreshItem: function(instanceID) {
    
        var this_reload = this;
        var widgetID = jQuery('#' + this_reload.id).find('#' + instanceID).children('input[name=widget-id]').val();
        var widgetNumber = jQuery('#' + this_reload.id).find('#' + instanceID).children('input[name=widget-number]').val();
        widgetContent = jQuery.ajax({
                type: 'POST',
                url: eletro_widgets_ajax_url,
                dataType: 'html',
                data: 
                {
                    action: 'add',
                    refresh: 1,
                    id: widgetID,
                    number: widgetNumber
                },
                complete: function() 
                {
                    jQuery('#' + this_reload.id).find('#' + instanceID).html(widgetContent.responseText);
                    new eletroItem(instanceID, this_reload);
                }
            });
    }
});

/************** END Canvas ****************/

/************** Class Column ****************/
eletroColumn = function(id, canvas) {
  this.init(id, canvas);
}

jQuery.extend(eletroColumn.prototype, {
   // object variables
   id: '',
   items: new Array(),

   init: function(id, canvas) {
     // do initialization here
     this.id = id;
     
     //inicia o sortable
     jQuery('#' + canvas.id).find('#'+id).sortable(
			{
				accept			: 'itemDrag',
				placeholder		: 'dragAjuda',
				activeclass 	: 'dragAtivo',
				hoverclass 		: 'dragHover',
				handle			: 'h2.itemDrag',
				opacity			: 0.7,
				connectWith     : ['#' + canvas.id + ' .recebeDrag'],
				update 		    : function() {
                      canvas.save();          
                },
				onStart         : function()
				{
					jQuery.iAutoscroller.start(this, document.getElementsByTagName('body'));
				},
				onStop          : function()
				{
					jQuery.iAutoscroller.stop();
				}
			});
     
     
     //initialize all existent canvas
     jQuery('#' + canvas.id).find('#'+id).children('.itemDrag').each(function() {
         new eletroItem(this.id, canvas);
     });
   }
});
/************** END Column ****************/

/************** Class Item ****************/
eletroItem = function(id, canvas) {
  this.init(id, canvas);
}

jQuery.extend(eletroItem.prototype, {
   // object variables
   id: '',
   container: '',

    init: function(id, canvas) {

        this.id = id;
        var this_item = this;

        //add controls and behaviors
        jQuery('#' + canvas.id).find('#' + id).children('.eletro_widgets_control').hide();
        jQuery('#' + canvas.id).find('#' + id).find('h2.itemDrag').append('<a alt="edit" class="edit"></a>').append('<a alt="remove" class="remove"></a>');

        jQuery('#' + canvas.id).find('#' + id).find('h2 a.edit').click(function() {
            jQuery(this).parents('.eletro_widgets_content').children(':not("h2")').toggle();
            jQuery(this).parents('.eletro_widgets_content').siblings('.eletro_widgets_control').toggle();                    
        });

        jQuery('#' + canvas.id).find('#' + id).find('h2 a.remove').click(function() {
            this_item.remove(id, canvas);
        });
        
        jQuery('#' + canvas.id).find('#' + id).find('.save').click(function() {
        	
            canvas.updateControl(id, false);
            var data = jQuery(this).parents('div.itemDrag').find('input').serialize();
        	
        	debug = jQuery.ajax({
                type: 'POST',
                dataType: 'html',
                url: eletro_widgets_ajax_url,
                data: data,
                complete: function() {
                    jQuery("#debug").append(debug.responseText);
                    canvas.refreshItem(id);
                }
            });
        });
        canvas.updateControl(id, true);
    },
    
    remove: function(id, canvas) {
        jQuery('#' + canvas.id).find('#' + id).remove();
        canvas.save();
        canvas.updateControl(id, false);
    }

});
/************** END Item ****************/

jQuery(document).ready(function() {
    // loop through the containers
    jQuery('.eletro_widgets_container').each(function() {
        new eletroCanvas(jQuery(this).find('#eletro_widgets_id').val());
    });
});
