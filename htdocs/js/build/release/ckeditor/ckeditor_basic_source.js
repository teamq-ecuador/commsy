//>>built
define("ckeditor/ckeditor_basic_source",["dijit","dojo","dojox"],function(){if(!window.CKEDITOR)window.CKEDITOR=function(){var b={timestamp:"",version:"3.6.4",revision:"7575",rnd:Math.floor(900*Math.random())+100,_:{},status:"unloaded",basePath:function(){var a=window.CKEDITOR_BASEPATH||"";if(!a)for(var b=document.getElementsByTagName("script"),d=0;d<b.length;d++){var c=b[d].src.match(/(^|.*[\\\/])ckeditor(?:_basic)?(?:_source)?.js(?:\?.*)?$/i);if(c){a=c[1];break}}-1==a.indexOf(":/")&&(a=0===a.indexOf("/")?
location.href.match(/^.*?:\/\/[^\/]*/)[0]+a:location.href.match(/^[^\?]*\/(?:)/)[0]+a);if(!a)throw'The CKEditor installation path could not be automatically detected. Please set the global variable "CKEDITOR_BASEPATH" before creating editor instances.';return a}(),getUrl:function(a){-1==a.indexOf(":/")&&0!==a.indexOf("/")&&(a=this.basePath+a);this.timestamp&&"/"!=a.charAt(a.length-1)&&!/[&?]t=/.test(a)&&(a+=(0<=a.indexOf("?")?"&":"?")+"t="+this.timestamp);return a}},c=window.CKEDITOR_GETURL;if(c){var e=
b.getUrl;b.getUrl=function(a){return c.call(b,a)||e.call(b,a)}}return b}();CKEDITOR._autoLoad="core/ckeditor_basic";document.write('<script type="text/javascript" src="'+CKEDITOR.getUrl("_source/core/loader.js")+'"><\/script>')});