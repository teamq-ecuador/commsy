//>>built
define("ckeditor/_source/core/editor_basic",["dijit","dojo","dojox"],function(){if(!CKEDITOR.editor)CKEDITOR.ELEMENT_MODE_NONE=0,CKEDITOR.ELEMENT_MODE_REPLACE=1,CKEDITOR.ELEMENT_MODE_APPENDTO=2,CKEDITOR.editor=function(a,c,b,d){this._={instanceConfig:a,element:c,data:d};this.elementMode=b||CKEDITOR.ELEMENT_MODE_NONE;CKEDITOR.event.call(this);this._init()},CKEDITOR.editor.replace=function(a,c){var b=a;if("object"!=typeof b){(b=document.getElementById(a))&&b.tagName.toLowerCase()in{style:1,script:1,
base:1,link:1,meta:1,title:1}&&(b=null);if(!b)for(var d=0,e=document.getElementsByName(a);(b=e[d++])&&"textarea"!=b.tagName.toLowerCase(););if(!b)throw'[CKEDITOR.editor.replace] The element with id or name "'+a+'" was not found.';}b.style.visibility="hidden";return new CKEDITOR.editor(c,b,CKEDITOR.ELEMENT_MODE_REPLACE)},CKEDITOR.editor.appendTo=function(a,c,b){var d=a;if("object"!=typeof d&&(d=document.getElementById(a),!d))throw'[CKEDITOR.editor.appendTo] The element with id "'+a+'" was not found.';
return new CKEDITOR.editor(c,d,CKEDITOR.ELEMENT_MODE_APPENDTO,b)},CKEDITOR.editor.prototype={_init:function(){(CKEDITOR.editor._pending||(CKEDITOR.editor._pending=[])).push(this)},fire:function(a,c){return CKEDITOR.event.prototype.fire.call(this,a,c,this)},fireOnce:function(a,c){return CKEDITOR.event.prototype.fireOnce.call(this,a,c,this)}},CKEDITOR.event.implementOn(CKEDITOR.editor.prototype,!0)});