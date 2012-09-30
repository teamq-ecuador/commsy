//>>built
define("commsy/popups/TogglePersonalConfiguration","dojo/_base/declare,commsy/TogglePopupHandler,dojo/query,dojo/dom-class,dojo/dom-attr,dojo/dom-construct,dojo/on,dijit/Tooltip,dojo/_base/lang,dojo/i18n!./nls/tooltipErrors".split(","),function(g,h,c,e,i,f,b,j,a,k){return g(h,{sendImages:[],constructor:function(a,c){this.popup_button_node=a;this.contentNode=c;this.module="profile";this.button=this.dialog=null;this.features=["editor","upload-single"];this.registerPopupClick()},onTogglePopup:function(){!0===
this.is_open?(e.add(this.popup_button_node,"tm_user_hover"),e.remove(this.contentNode,"hidden")):(e.remove(this.popup_button_node,"tm_user_hover"),e.add(this.contentNode,"hidden"))},setupSpecific:function(){dojo.ready(a.hitch(this,function(){this.featureHandles["upload-single"][0].setCallback(a.hitch(this,function(a){var d=c("div.filePreview",this.featureHandles["upload-single"][0].uploader.form)[0];f.empty(d);f.create("img",{src:"commsy.php?cid="+this.uri_object.cid+"&mod=picture&fct=getTemp&fileName="+
a.file},d,"last");this.sendImages.push({part:"user_picture",fileInfo:a})}));b(c("input#delete",this.contentNode)[0],"click",a.hitch(this,function(){e.remove(c("div#delete_options",this.contentNode)[0],"hidden");b(c("input#lock_room",this.contentNode)[0],"click",a.hitch(this,function(){this.onPopupSubmit({part:"account_lock_room"})}));b(c("input#delete_room",this.contentNode)[0],"click",a.hitch(this,function(){this.onPopupSubmit({part:"account_delete_room"})}));b(c("input#lock_portal",this.contentNode)[0],
"click",a.hitch(this,function(){this.onPopupSubmit({part:"account_lock_portal"})}));b(c("input#delete_portal",this.contentNode)[0],"click",a.hitch(this,function(){this.onPopupSubmit({part:"account_delete_portal"})}))}))}));var d=c("#submit_delete_wordpress",this.contentNode)[0];d&&b(d,"click",a.hitch(this,function(){this.button_delete=new dijit.form.Button({label:"Blog endg&uuml;ltig l&ouml;schen",onClick:a.hitch(this,function(){this.onPopupSubmit({part:"cs_bar",action:"delete_wordpress"});this.dialog.destroyRecursive()})});
this.button_cancel=new dijit.form.Button({label:"Abbrechen",onClick:a.hitch(this,function(){this.dialog.destroyRecursive()})});this.dialog=new dijit.Dialog({title:"Wordpress l&ouml;schen",content:"<b style='color:#ff0000;'>Achtung: Alle Daten im Blog werden gel&ouml;scht. Dieser Vorgang kann nicht r&uuml;ckg&auml;ngig gemacht werden!</b><br/><br/>"});dojo.place(this.button_delete.domNode,this.dialog.containerNode);dojo.place(this.button_cancel.domNode,this.dialog.containerNode);this.dialog.show()}));
(d=c("#submit_delete_wiki",this.contentNode)[0])&&b(d,"click",a.hitch(this,function(){this.button_delete=new dijit.form.Button({label:"Wiki endg&uuml;ltig l&ouml;schen",onClick:a.hitch(this,function(){this.onPopupSubmit({part:"cs_bar",action:"delete_wiki"});this.dialog.destroyRecursive()})});this.button_cancel=new dijit.form.Button({label:"Abbrechen",onClick:a.hitch(this,function(){this.dialog.destroyRecursive()})});this.dialog=new dijit.Dialog({title:"Wiki l&ouml;schen",content:"<b style='color:#ff0000;'>Ein gel&ouml;schtes Wiki kann nicht wieder rekonstruiert werden. M&ouml;chten Sie dieses Wiki endg&uuml;ltig l&ouml;schen?</b><br/><br/>"});
dojo.place(this.button_delete.domNode,this.dialog.containerNode);dojo.place(this.button_cancel.domNode,this.dialog.containerNode);this.dialog.show()}))},createConfirmBox:function(){this.button=new dijit.form.Button({label:"delete",onClick:a.hitch(this,function(){this.onPopupSubmit({part:"account_delete"});this.dialog.destroyRecursive()})});this.dialog=new dijit.Dialog({title:""});dojo.place(this.button.domNode,this.dialog.containerNode,"last");this.dialog.show()},onPopupSubmit:function(a){var b=a.part,
a=a.action;dojo.forEach(this.featureHandles.editor,function(a){a.getInstance();var b=a.getNode().parentNode;i.set(c("input[type='hidden']",b)[0],"value",a.getInstance().getData())});this.submit({tabs:[{id:b}],nodeLists:[]},{part:b,action:a})},onPopupSubmitSuccess:function(){0<this.sendImages.length?this.AJAXRequest("popup","save",{module:"profile",additional:{part:this.sendImages[0].part,fileInfo:this.sendImages[0].fileInfo}},function(){location.reload()}):location.reload()},onPopupSubmitError:function(a){this.inherited(arguments);
switch(a.code){case "1011":var b=c("input[name='form_data[user_id]']",this.contentNode)[0];j.show(k.personalPopup1011,b);this.errorNodes.push(b)}}})});