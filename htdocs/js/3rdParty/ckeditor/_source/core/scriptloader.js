//>>built
define("ckeditor/_source/core/scriptloader",["dijit","dojo","dojox"],function(){CKEDITOR.scriptLoader=function(){var k={},d={};return{load:function(b,e,f,l){var m="string"==typeof b;m&&(b=[b]);f||(f=CKEDITOR);var h=b.length,n=[],o=[],p=function(a){e&&(m?e.call(f,a):e.call(f,n,o))};if(0===h)p(!0);else{var q=function(a,g){(g?n:o).push(a);0>=--h&&(l&&CKEDITOR.document.getDocumentElement().removeStyle("cursor"),p(g))},i=function(a,g){k[a]=1;var c=d[a];delete d[a];for(var b=0;b<c.length;b++)c[b](a,g)},
r=function(a){if(k[a])q(a,!0);else{var b=d[a]||(d[a]=[]);b.push(q);if(!(1<b.length)){var c=new CKEDITOR.dom.element("script");c.setAttributes({type:"text/javascript",src:a});if(e)CKEDITOR.env.ie?c.$.onreadystatechange=function(){if("loaded"==c.$.readyState||"complete"==c.$.readyState)c.$.onreadystatechange=null,i(a,!0)}:(c.$.onload=function(){setTimeout(function(){i(a,!0)},0)},c.$.onerror=function(){i(a,!1)});c.appendTo(CKEDITOR.document.getHead());CKEDITOR.fire("download",a)}}};l&&CKEDITOR.document.getDocumentElement().setStyle("cursor",
"wait");for(var j=0;j<h;j++)r(b[j])}}}}()});