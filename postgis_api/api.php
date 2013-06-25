<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
        <title>Routing Example</title>
        <meta name="description" content="Quality of Life Dashboard">

        <!-- Mobile viewport optimized: j.mp/bplateviewport -->
        <meta name="viewport" content="width=device-width,initial-scale=1">
        <meta name="apple-mobile-web-app-capable" content="yes" />
        <meta name="apple-mobile-web-app-status-bar-style" content="black" />

        <!-- Stylesheets -->
        <link rel="stylesheet" href="css/bootstrap.css">
        <link rel="stylesheet" href="css/bootstrap-responsive.css">
         <link href="http://cdn.leafletjs.com/leaflet-0.5.1/leaflet.css" rel="stylesheet">
         <style>
         .map {
    height: 100%;
    border: 1px solid #a5a5a5;
    -moz-box-shadow: 5px 5px 5px #d2d2d2;
    -webkit-box-shadow: 5px 5px 5px #d2d2d2;
    box-shadow: 5px 5px 5px #d2d2d2;
}
demo {
    height: 450px;
    position: relative;
}
.demo .height-controlled {
    height: 100%;
}
         </style>
    </head>
    <body>

<div class='header'>
<h1>Routing Examples</h1>
</div>
<div class="container">
<div class="row-fluid" >
<div class="span2">
<div id='clocation'>
</div>
<div id='destination'>
</div>
<p id='tester'></p>
</div>
<div class="span10  demo">
<p>Habari vijana wetu</p>
  <div id="map-container-1" class="map" style='height:500px !important;'></div>
    <div class="layer-buttons">
 <div class="row layer-button-row"><a href="javascript:prwsf_bus.setMap(map1);" class="btn success">Layer On</a> <a href="javascript:prwsf_bus.setMap(null);" class="btn danger">Layer Off</a></div>
    </div>
</div>
</div>
</div>

<script src="http://cdn.leafletjs.com/leaflet-0.5.1/leaflet.js"type="text/javascript"></script>
<script src="js/prettify.js" type="text/javascript"></script>
<script src='js/jquery-1.8.1.min.js' ></script>
<script src="js/lvector.js" type="text/javascript"></script>


<script>
/*
 $(document).ready(function() {
$.ajax({
    type: "GET",
    url: "http://localhost/postgis_api/v1/ws_geo_attributequery.php",
    data: {
        "srid": 4617,
        "distance": 20000,
        "geotable": "nrnbc90roadseg",
        "fields": "gid",
        "source":"",
        "target":"",
        "hasSource":false,
        "parameters":"",
        "format":"json",
        "limit": 10
    },
    dataType: "json",
    success: function(data) {
        $.each(data, function(i, row) {
            console.log(row);
        });
    },
    error: function(error, status, desc) {
        console.log(status, desc);
    }
});
});*/
function getGeoJSON(srid,geotable,fields,source,target,hassource,parameters,format,limit){
$.ajax({
    type: "GET",
    url: "http://localhost/postgis_api/v1/ws_geo_attributequery.php",
    data: {
        "srid":srid,
        "geotable":geotable,
        "fields": fields,
        "source":source,
        "target":target,
        "hassource":hassource,
        "parameters":parameters,
        "format":format,
        "limit": limit
    },
    dataType: "json",
    success: function(data) {
        $.each(data, function(i, mydatas) {

            console.log(mydatas);
        });
        return data;
    },
    error: function(error, status, desc) {
        console.log(status, desc);
        return status+"@"+desc;
    }
});
}

var ids=new Array(),mytarget=new Array(),mysource=new Array();

var i=0,text="<p>hapo vipi  ",tag=0,sos=0,updator="",k=0;

 $(document).ready(function() {

//var map = L.map('map-container-1').setView([52.9089, -124.4531], 13);
//var geos=getGeoJSON(4617,"nrnbc90roadseg","gid,length","","",false,"","json","");
   //document.getElementById("tester").innerHTML=geos.row.geojson;
                map1 = new L.Map("map-container-1", {
                    center: new L.LatLng(54.5721,-124.4531),
                    zoom: 10,
                    minZoom: 4,
                    layers: [
                        new L.TileLayer("http://tile.stamen.com/tanzania/{z}/{x}/{y}.jpg", {
                            maxZoom: 20,
                            attribution: 'Map data (c) <a href="http://www.openstreetmap.org/" target="_blank">OpenStreetMap</a> contributors, CC-BY-SA.'
                        })
                    ]
                });
           //prwsf_bus.setMap(map1);

            kino.setMap(map1);
            //teme.setMap(map1);
                });
 prwsf_bus = new lvector.PRWSF({
                    url: "http://localhost/postgis_api",
                    geotable: "nrnbc90roadseg",
                    fields: "gid",
                    uniqueField: "gid",
                    srid:4617,
                    showAll: true,
                    symbology: {
                        type: "single",
                        vectorOptions: {

                            weight: 8,
                            color: "#000000",
                            opacity: 1,
                            clickable: false
                        }
                    }
                });
         kino = new lvector.PRWSF({
                    url: "http://localhost/postgis_api",
                    geotable: "nrnbc90roadseg",
                    fields: "gid,r_placenam,speed,r_stname_c,source,target",
                    uniqueField: "gid",
                    srid:4629,
                    showAll: true,
                     symbology: {
                        type: "single",
                        vectorOptions: {
                             strokeWeight: 1.8,
                          strokeColor: "#2f4a00",
                                strokeOpacity: 1,
                            weight: 5,
                            color: "#edffcc",
                            opacity: 1,
                            clickable: true
                        }
                    },
                    clickEvent: function (feature, event) {

        alert("gid is"+feature.properties.source+"_"+feature.properties.target);
        var street=feature.properties.r_stname_c;
          var place=feature.properties.r_placenam;
          var showinfo="<ul><li>StreetName:"+street+"</li><li>PlaceName:"+place+"</li></ul>"
        if(i==0){
        document.getElementById("clocation").innerHTML="<h6>Source Info</h6>"+showinfo;
       sos=feature.properties.source;
       text+="init_sou_"+sos+"<br/>";
        text+="init_tag_"+tag+"<br/>";

       }
       if(i==1){
        i=-1;
        tag=feature.properties.target;
          getShortestPath(sos,tag);
       getGeoJSON(4617,"nrnbc90roadseg","gid,length",sos,tag,false,"","json","");
       document.getElementById("destination").innerHTML="<h6>Destination Info</h6>"+showinfo;
        text+="finaal_sou_"+sos+"<br/>";
        text+="final_tag_"+tag+"<br/>";
        sos="";
        tag="";
        }
      ///clocation


        // document.getElementById("tester").innerHTML=text+"</p>";
        i++; k++;
                        }
                });

            teme = new lvector.PRWSF({
                    url: "http://localhost/postgis_api",
                    geotable: "temekedistrict",
                    fields: "gid",
                    uniqueField: "gid",
                    srid:4617,
                    showAll: true,
                    symbology: {
                        type: "single",
                        vectorOptions: {
                            fillColor: "#304567",
                            fillOpacity: 0.4,
                            weight: 6,
                            color: "#000000",
                            opacity: 1,
                            clickable: false
                        }
                    }
                });

                function getShortestPath(source,target){
                       if(k==3){
                     shorter.setMap(null);
                     k=1;
                     }
                 shorter = new lvector.PRWSF({
                    url: "http://localhost/postgis_api",
                    geotable: "nrnbc90roadseg",
                    fields: "gid",
                    uniqueField: "gid",
                    source:sos,
                    target:tag,
                    srid:4617,
                    showAll: true,
                    dynamic: true,
                     autoUpdate: true,
                    symbology: {
                        type: "single",
                        vectorOptions: {
                              strokeWeight: 1.8,
                             strokeColor: "#2f4a00",
                              strokeOpacity: 1,
                            weight: 4,
                            color: "#D93600",
                            opacity: 1,
                            clickable: false
                        }
                    }
                });
                if(source=="" || target==""){

                }else{

                 shorter.setMap(map1);
                 }
                }




</script>

    </body>
</html>
