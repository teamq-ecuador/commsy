//>>built
define("commsy/popups/ToggleBreadcrumb","dojo/_base/declare,commsy/TogglePopupHandler,dojo/query,dojo/dom-class,dojo/dom-attr,dojo/dom-construct,dojo/on,dojo/_base/lang,dojo/dnd/Source".split(","),function(m,n,d,g,j,c,h,e,o){return m(n,{constructor:function(a,b){this.popup_button_node=a;this.contentNode=b;this.module="breadcrumb";this.features=[];this.registerPopupClick()},onTogglePopup:function(){!0===this.is_open?(g.add(this.popup_button_node,"tm_user_hover"),g.remove(this.contentNode,"hidden")):
(g.remove(this.popup_button_node,"tm_user_hover"),g.add(this.contentNode,"hidden"))},setupSpecific:function(){var a=d("a#edit_roomlist",this.contentNode)[0];if(a)h.once(a,"click",e.hitch(this,function(){this.setupEditMode()}));dojo.forEach(d("div.room_change_item",this.contentNode),e.hitch(this,function(a){var d=this.getAttrAsObject(a,"data-custom").href;h(a,"click",function(){location.href=d})}))},onPopupSubmit:function(a){a=a.part;this.submit({tabs:[{id:a}],nodeLists:[]},{part:a})},setupEditMode:function(){var a=
d("div#profile_content_row_three, div#profile_content_row_four",this.contentNode);g.remove(a[1],"hidden");dojo.forEach(d("div.room_block",this.contentNode),e.hitch(this,function(a){var b=d("div.breadcrumb_room_area",a),f=null,h=null;dojo.forEach(b,e.hitch(this,function(a,b){0===b?(f=a,h=d("div.clear",f)[0]):(dojo.forEach(d("div.room_change_item",a),function(a){c.place(a,h,"before")}),c.destroy(a))}));var k=-1,i=0;dojo.forEach(d("div.room_change_item, div.room_dummy",f),e.hitch(this,function(a,b){g.contains(a,
"room_dummy")?g.remove(a,"room_dummy_no_border"):k=b;i++}));b=0;0!==i%4?b=8-i%4:k>i-3&&(b=4);for(var l=0;l<b;l++)c.create("div",{className:"room_dummy"},h,"before");dojo.forEach(d("> h3",a),function(b){c.destroy(b,a)});dojo.forEach(d("> h2",a),function(a){c.create("input",{value:j.get(a,"innerHTML")},a,"replace")})}));var b=c.create("div",{className:"roomlist_append_block"}),f=c.create("a",{id:"roomlist_append_block",href:"#",innerHTML:this.from_php.i18n.COMMON_NEW_BLOCK},b,"last");c.place(b,d("div#profile_content_row_three div.room_block:last-child",
this.contentNode)[0],"after");h(f,"click",e.hitch(this,function(a){this.appendNewBlock();a.preventDefault()}));b=c.create("div",{className:"roomlist_save"});f=c.create("a",{id:"roomlist_save",href:"#",innerHTML:this.from_php.i18n.COMMON_SAVE_BUTTON},b,"last");c.place(b,d("div#profile_content_row_three",this.contentNode)[0],"last");c.create("div",{className:"clear"},b,"after");h(f,"click",e.hitch(this,function(a){this.saveRoomList();a.preventDefault()}));this.setupSortables(a)},setupSortables:function(a){var b=
[];dojo.forEach(a,function(a){dojo.forEach(d("div.breadcrumb_room_area",a),function(a){b.push(a)})});var c=[];dojo.forEach(b,e.hitch(this,function(a,b){c.push(new o(a,{singular:!0}));var e=d("div.room_change_item, div.room_dummy",a);c[b].insertNodes(!1,e,d("div.clear",a)[0])}))},appendNewBlock:function(){var a=c.create("div",{className:"room_block"},d("div#profile_content_row_three div.roomlist_append_block",this.contentNode)[0],"before");c.create("input",{value:this.from_php.i18n.COMMON_NEW_BLOCK},
a,"last");for(var b=c.create("div",{className:"breadcrumb_room_area"},a,"last"),f=0;8>f;f++)c.create("div",{className:"room_dummy"},b,"last");c.create("div",{className:"clear"},b,"last");this.setupSortables(new dojo.NodeList(a))},onPopupSubmitSuccess:function(){this.close()},saveRoomList:function(){var a={module:"breadcrumb",form_data:[]},b=[];dojo.forEach(d("div#profile_content_row_three div.room_block"),function(a){b.push({type:"title",value:j.get(d(">input",a)[0],"value")});dojo.forEach(d("div.breadcrumb_room_area div.room_change_item, div.breadcrumb_room_area div.room_dummy",
a),function(a){var c="room",e="";g.contains(a,"room_dummy")?c="dummy":e=j.get(d("input[name='hidden_item_id']",a)[0],"value");b.push({type:c,value:e})})});a.form_data.push({name:"room_config",value:b});this.AJAXRequest("popup","save",a,e.hitch(this,function(){this.close()}))}})});