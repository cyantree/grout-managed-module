/*! cyantree grout ManagedModule - v0.0.0 - 2013-09-03 00:59:59
* Copyright (c) 2013 cyantree - Christoph Schreiber */
var initCallbacks=[];!function(a){a(document).ready(function(){for(var a in initCallbacks)initCallbacks[a]()}),a.extend({app:{urlPrefix:null},service:{url:null,call:function(b,c,d){a.ct.callService(a.service.url,b,c,d)}},initContainer:function(b){b.find(".CT_LayerLink").each(function(b,c){a(c).CT_LayerLink()}),b.find("form.CT_LayerForm").each(function(c,d){a(d).CT_LayerForm({layer:b.data("CT_Layer")})})}})}(jQuery);