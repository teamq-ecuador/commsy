//>>built
define("commsy/popups/ToggleClipboard","dojo/_base/declare,commsy/TogglePopupHandler,dojo/query,dojo/dom-class,dojo/dom-attr,dojo/dom-construct,dojo/on,dojo/_base/lang".split(","),function(b,c,e,a,f,g,h,d){return b(c,{constructor:function(a,b){this.popup_button_node=a;this.contentNode=b;this.module="clipboard";this.features=[];this.registerPopupClick()},onTogglePopup:function(){!0===this.is_open?(a.add(this.popup_button_node,"tm_clipboard_hover"),a.remove(this.contentNode,"hidden")):(a.remove(this.popup_button_node,
"tm_clipboard_hover"),a.add(this.contentNode,"hidden"))},setupSpecific:function(){require(["commsy/Clipboard"],d.hitch(this,function(a){(new a).init(this.cid,this.from_php.template.tpl_path)}))},onPopupSubmit:function(){},onPopupSubmitSuccess:function(){}})});