//>>built
define("commsy/popups/ClickDiscarticlePopup","dojo/_base/declare,commsy/ClickPopupHandler,dojo/query,dojo/dom-class,dojo/_base/lang,dojo/dom-construct,dojo/dom-attr,dojo/on".split(","),function(d,e,c,h,f,i,g){return d(e,{answerTo:null,constructor:function(){this.answerTo=null},init:function(b,a){this.triggerNode=b;this.item_id=a.iid;this.module="discarticle";this.contextId=a.contextId;if(a.answerTo)this.answerTo=a.answerTo;this.features=["editor","tree","upload","netnavigation","calendar"];this.registerPopupClick()},
setupSpecific:function(){},onPopupSubmit:function(){dojo.forEach(this.featureHandles.editor,function(b){b.getInstance();var a=b.getNode().parentNode;g.set(c("input[type='hidden']",a)[0],"value",b.getInstance().getData())});this.submit({tabs:[{id:"rights_tab"},{id:"buzzwords_tab",group:"buzzwords"},{id:"tags_tab",group:"tags"}],nodeLists:[{query:c("div#files_attached",this.contentNode)},{query:c("div#files_finished",this.contentNode),group:"files"},{query:c("input[name='form_data[description]']",this.contentNode)},
{query:c("input[name='form_data[title]']",this.contentNode)}]},{answerTo:this.answerTo,discussionId:this.uri_object.iid,contextId:this.contextId})},onPopupSubmitSuccess:function(b){if("NEW"===this.item_id)this.featureHandles.netnavigation[0].afterItemCreation(b,f.hitch(this,function(){if(this.contextId){this.close();var a=c("a#listItem"+b)[0];a&&a.click()}else this.reload(b)}));else if(this.contextId){this.close();var a=c("a#listItem"+b)[0];a&&a.click()}else this.reload(b)}})});