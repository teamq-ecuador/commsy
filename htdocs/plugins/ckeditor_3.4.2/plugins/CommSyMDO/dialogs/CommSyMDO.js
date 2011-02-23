﻿/*
Copyright (c) 2003-2010, CKSource - Frederico Knabben. All rights reserved.
For licensing, see LICENSE.html or http://ckeditor.com/license
*/

CKEDITOR.dialog.add( 'CommSyMDO', function( editor )
{
	/**
	 * Simulate "this" of a dialog for non-dialog events.
	 * @type {CKEDITOR.dialog}
	 */
	var html_search = '';
	var html_integration = '';
	
	html_search += '<form id="commsy_mdo_search">';
	html_search += '  <table>'
	html_search += '    <tr>';
  	html_search += '      <td>';
  	html_search += ckeditor_mdo_search_label;
  	html_search += '      </td>';
  	html_search += '      <td>';
  	html_search += '        <input type="input" name="ckeditor_mdo_search" style="background-color: white; border: 1px solid black;"/>'
  	html_search += '      </td>';
  	html_search += '    </tr>';
  	html_search += '    <tr>';
  	html_search += '      <td>';
  	html_search += ckeditor_mdo_andor_label;
  	html_search += '      </td>';
  	html_search += '      <td>';
  	html_search += '        <select name="ckeditor_mdo_andor" style="background-color: white; border: 1px solid black;">';
  	html_search += '         <option value=""></option>';
  	html_search += '         <option value="AND">'
  	html_search += ckeditor_mdo_and_label;
  	html_search += '		 </option>';
  	html_search += '         <option value="OR">or';
  	html_search += ckeditor_mdo_or_label;
  	html_search += '		 </option>';
  	html_search += '        </select>';
  	html_search += '      </td>';
 	html_search += '    </tr>';
  	html_search += '    <tr>';
  	html_search += '      <td>';
  	html_search += ckeditor_mdo_wordbegin_label;
  	html_search += '      </td>';
  	html_search += '      <td>';
  	html_search += '        <select name="ckeditor_mdo_wordbegin" style="background-color: white; border: 1px solid black;">';
  	html_search += '         <option value=""></option>';
  	html_search += '         <option value="WORD">';
  	html_search += ckeditor_mdo_word_label;
  	html_search += '		 </option>';
  	html_search += '         <option value="BEGIN">';
  	html_search += ckeditor_mdo_begin_label;
  	html_search += '		 </option>';
  	html_search += '        </select>';
  	html_search += '      </td>';
  	html_search += '    </tr>';
  	html_search += '    <tr>';
  	html_search += '      <td>';
  	html_search += ckeditor_mdo_titletext_label;
  	html_search += '      </td>';
  	html_search += '      <td>';
  	html_search += '        <select name="ckeditor_mdo_titletext" style="background-color: white; border: 1px solid black;">';
  	html_search += '         <option value="titel_fields">';
  	html_search += ckeditor_mdo_title_label;
  	html_search += '		 </option>';
  	html_search += '         <option value="text_fiels">';
  	html_search += ckeditor_mdo_text_label;
  	html_search += '		 </option>';
  	html_search += '        </select>';
  	html_search += '      </td>';
  	html_search += '    </tr>';
  	html_search += '    <tr>';
  	html_search += '      <td>';
  	html_search += '      </td>';
  	html_search += '      <td>';
  	html_search += '        <input type="button" name="ckeditor_mdo_performsearch" value="' + ckeditor_mdo_submit_label + '" style="background-color: white; border: 1px solid black;"/>';
  	html_search += '      </td>';
  	html_search += '    </tr>';
	html_search += '  </table>';
	html_search += '</form>';
	html_search += '<hr style="color: black; background: black; height: 1px; margin-top: 5px; margin-bottom: 5px;"/>'
	html_search += '<div style="width: 800px; height: 300px; overflow: scroll;">';
	html_search += '  <span id="ckedtior_search_results_size">';
	html_search += '    0 ' + ckeditor_mdo_results;
	html_search += '  </span>';
	html_search += '  <table id="ckeditor_search_results" style="margin-top: 5px;"';
	html_search += '  </table>';
	html_search += '</div>';
	
	html_integration += '<form id="commsy_mdo_integration">';
	html_integration += '  <table>';
	html_integration += '    <tr>';
	html_integration += '      <td style="width:550px; white-space: normal;">';
	html_integration += ckeditor_mdo_integration_label;
	html_integration += '      </td>';
  	html_integration += '      <td style="width:250px;">';
  	html_integration += '       <select name="ckeditor_mdo_integration" style="background-color: white; border: 1px solid black;">';
  	html_integration += '         <option value="embedded">';
  	html_integration += ckeditor_mdo_embedded_label;
  	html_integration += '		  </option>';
  	html_integration += '         <option value="newpage">';
  	html_integration += ckeditor_mdo_newpage_label;
  	html_integration += '		  </option>';
  	html_integration += '       </select>';
  	html_integration += '      </td>';
  	html_integration += '    </tr>';
	html_integration += '  </table>';
	html_integration += '</form>';
	
	var commsyMDOSearch = {
		type : 'html',
		html : html_search,
		onLoad : function( event ) {
			// search handler
      jQuery('input[name="ckeditor_mdo_performsearch"]').click(function(object) {
        // perform ajax request
        var json_data = new Object();
        json_data.mdo_search      = jQuery('input[name="ckeditor_mdo_search"]').attr('value');
        json_data.mdo_andor       = jQuery('select[name="ckeditor_mdo_andor"]').attr('value');
        json_data.mdo_wordbegin   = jQuery('select[name="ckeditor_mdo_wordbegin"]').attr('value');
        json_data.mdo_titletext   = jQuery('select[name="ckeditor_mdo_titletext"]').attr('value');
        
        var cid = unescape((RegExp('cid=(.+?)(&|$)').exec(window.location.href)||[,null])[1]);
        
        jQuery.ajax({
          url:      'commsy.php?cid=' + cid + '&mod=ajax&fct=mdo_perform_search&output=json',
          data:     json_data,
          success:  function(message) {
            var result = eval('(' + message + ')');
            if(result.success === 'true' && result.results.length > 0) {
              // fill result table
              var table = '';
              
              // header
              table += '<tr>';
              table += '  <td style="width: 250px; white-space: normal; font-weigth: bold; border-bottom: 1px solid black;">Titel</td>';
              table += '  <td style="width: 550px; white-space: normal; font-weigth: bold; border-bottom: 1px solid black;">Text</td>';
              table += '</tr>';
              
              // content
              jQuery.each(result.results, function(i, item) {
                if(item.title !== null || item.text !== null) {
                  if(item.title === null) {
                    item.title = '-';
                  }
                  if(item.text === null) {
                    item.text = '-';
                  }
                  table += '<tr>';
                  table += '  <td style="width: 250px; padding: 3px;"><a id="ckeditor_mdo_link_' + item.identifier + '" href="#" style="white-space: normal; text-decoration: underline;">' + item.title + '</a></td>';
                  table += '  <td style="width: 550px; white-space: normal; padding: 3px;">' + item.text + '</td>';
                  table += '</tr>';
                }
              });
              
              // set content
              jQuery('table[id="ckeditor_search_results"]').html(table);
              
              // register click events
              jQuery('a[id^="ckeditor_mdo_link_"]').click(function(object) {
              	// remove all highlights
              	jQuery('table[id="ckeditor_search_results"] td').each(function(item) {
              		item.css('background-color', 'white');
              	});
              	
              	// highlight selection
              	object.currentTarget.css('background-color', 'Gainsboro');
              });
              
              // set number of results
              jQuery('span[id="ckedtior_search_results_size"]').html(result.results.length + ' ' + ckeditor_mdo_results);
            }
          }
        });
      });
		},
		style : 'width: 100%; height: 100%; border-collapse: separate;'
	};
	
	var commsyMDOIntegration = {
    type : 'html',
    html : html_integration
  	};
	
	if(ckeditor_mdo_access) {
		return {
			title : ckeditor_mdo_select,
			minWidth : 800,
			minHeight : 410,
			contents : [
				{
					id : 'tab1',
					label : ckeditor_mdo_tab_search,
					title : ckeditor_mdo_tab_search,
					expand : false,
					padding : 0,
					elements : [
				            commsyMDOSearch,
						]
				},
				{
	        id : 'tab2',
	        label : ckeditor_mdo_tab_integration,
	        title : ckeditor_mdo_tab_integration,
	        expand : false,
	        padding : 0,
	        elements : [
	                  commsyMDOIntegration,
	          ]
	      }
			],
			
			buttons : [ CKEDITOR.dialog.okButton ],
			onOk : function() {
				// close and add entry
				
				// find selected entry
				var text = '';
				jQuery('table[id="ckeditor_search_results"] td').each(function(item) {
              		if(item.css('background-color', 'Gainsboro')) {
              			text += '<span>(:mdo ' + item.id.substr(18);
              		}
              	});
                
                // embedded or new page
                if(jQuery('select[name="ckeditor_mdo_integration"]').attr('value') === 'newpage') {
                  text += ' target=new';
                }
                text += ':)</span>';
                editor.insertElement(CKEDITOR.dom.element.createFromHtml(text));
			},
			onCancel : function(){
				
			}
		};	
	}
});