
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
        var este_canvas = this;
        
        jQuery('#' + this.id).find('.recebeDrag').each(function() {
            este_canvas.columns.push(new eletroColumn(this.id, este_canvas));
        });
        
        //behavior do botao add
        jQuery('#' + this.id).find('#eletro_widgets_add_button').click(function() {
            este_canvas.add();
        });
        
        //ajax form
        jQuery('#eletro_widgets_form_'+this.index).ajaxForm(function() { 

            //recarregar widget
            este_canvas.refreshItem(refreshWidget);

        }); 
    
    },
    
    save: function() {
        
        //save canvas
        var save_este = this;
        
        values = this.getCurrentWidgets();
        for (var i=0; i<values.length; i++) {
        
            if (values[i].constructor.toString().indexOf("Array")) values[i] = values[i].join('X|X');
        
        }
        values = values.join('X||X');
        
        debug = jQuery.ajax({
            type: 'POST',
            dataType: 'html',
            url: eletro_widgets_ajax_url,
             data: 
            {
                action: 'save',
                value: values,
                id: save_este.index
            },
            complete: function() {jQuery("#debug").append(debug.responseText)}
        });
    
    },
    
    add: function() {
        var este_add = this;
        widgetContent = jQuery.ajax({
                type: 'POST',
                url: eletro_widgets_ajax_url,
                dataType: 'html',
                data: 
                {
                    action: 'add',
                    name: jQuery('#' + este_add.id).find('#eletro_widgets_add').val()
                },
                complete: function() 
                {
                    jQuery('#' + este_add.id).find('#eletro_widgets_col_0').prepend(widgetContent.responseText);
                    new eletroItem(jQuery('#' + este_add.id).find('#eletro_widgets_add').val(), este_add);  
                    jQuery('#eletro_widgets_add').val('');
                    este_add.save();
                }
            });
            
        
    },
    
    getCurrentWidgets: function() {
    
        var col = 0;
        var values = Array();
        var save_este = this;
        
        jQuery('#' + this.id).find('.recebeDrag').each(function() {
            var thisItems = new Array();
            jQuery(this).find('.itemDrag:not(".ui-sortable-helper")').each(function() {
                thisItems.push(this.id);
            });            
            values.push(thisItems);
        });
        
        return values;
    
    },
    
    updateControl: function(widget, disable) {
    
        var wOption = jQuery('#' + this.id).find('option[value="'+widget+'"]');
        if (disable) {
            wOption.attr('disabled', 'disabled');
        } else {
            wOption.removeAttr('disabled');
        }
        /*
        if (wOption.attr('disabled')==true) {
            wOption.removeAttr('disabled');
        } else {
            wOption.attr('disabled', 'disabled');
        }
        */
    },
    
    refreshItem: function(widget) {
    
        var este_reload = this;
        widgetContent = jQuery.ajax({
                type: 'POST',
                url: eletro_widgets_ajax_url,
                dataType: 'html',
                data: 
                {
                    action: 'add',
                    refresh: 1,
                    name: widget
                },
                complete: function() 
                {
                    jQuery('#' + este_reload.id).find('#' + widget).html(widgetContent.responseText);
                    new eletroItem(widget, este_reload);
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
     
     
     //inicia as caixas q existem
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
        // do initialization here
        this.id = id;
        var este_item = this;
        
        //adicionar controles e behaviors
        jQuery('#' + canvas.id).find('#' + id).children('.eletro_widgets_control').hide();
        jQuery('#' + canvas.id).find('#' + id).find('h2.itemDrag').append('<a alt="edit" class="edit"></a>').append('<a alt="remove" class="remove"></a>');
        jQuery('#' + canvas.id).find('#' + id).find('.eletro_widgets_control').append('<input class="save" type="submit" value="Save">');


        jQuery('#' + canvas.id).find('#' + id).find('h2 a.edit').click(function() {
            jQuery(this).parents('.eletro_widgets_content').children(':not("h2")').toggle();
            jQuery(this).parents('.eletro_widgets_content').siblings('.eletro_widgets_control').toggle();                    
        });

        jQuery('#' + canvas.id).find('#' + id).find('h2 a.remove').click(function() {
            este_item.remove(id, canvas);
        });
        
        jQuery('#' + canvas.id).find('#' + id).find('input.save').click(function() {
            refreshWidget = este_item.id;
            canvas.updateControl(id, false);
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

