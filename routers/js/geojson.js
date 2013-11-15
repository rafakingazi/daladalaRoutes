var myLayer = L.geoJson();
var shortpath="";
var ids=new Array(),mytarget=new Array(),mysource=new Array();
var marker1,marker2,marker3;
var i=0,text="<p>hapo vipi  ",tag=0,sos=0,updator="",k=0,startpoint="",endpoint="";
var bus_stops=[];
var coordinates=[];


/**
*This function is used to return layer styles
*/
   function myStyle(routes,feature) {
       // console.log(sn);
       //console.log("My Data");
       if (routes=="short") {
            return {
                weight: 5,
                opacity: 1,
                color: '#8C6900',
                fillOpacity: 0.9,
                fillColor: '#ababab'
            };
       } else {
            return {
                weight: 5,
                opacity: 0.5,
                color: '#bebebe',
                fillOpacity: 0.0,
                fillColor: '#666666'
            };
        }
    }

/**
*Initialize the main map and other component on window.ready event
*/
$(document).ready(function() {
var geos=getGeoJSON(4617,"ways","gid,length,via as maxspeed_forward,route as name,source,target","","",false,"","json","","full");
 getGeoJSON(4326,"bus_stop","gid,name","","",false,"","json","","bus_stop");

                map1 = new L.Map("map-container-1", {
                    center: new L.LatLng(-6.83553, 39.265),
                    zoom: 11,
                    minZoom: 4,
                    layers: [
                        new L.TileLayer("http://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png", {
                            maxZoom: 20,
                            attribution: 'Map data (c) <a href="http://www.openstreetmap.org/" target="_blank">OpenStreetMap</a> contributors, CC-BY-SA.'
                        })
                    ]
                });
                myLayer.addTo(map1);

 marker1 = new L.marker(map1.getCenter());
marker2 = new L.marker(map1.getCenter());
marker3=new L.marker(map1.getCenter());

          myLayer.on('click', onMapClick);

                });
/*
*function used to get geojson data from the server and add the vector layer to the map.
*/
function getGeoJSON(srid,geotable,fields,source,target,hassource,parameters,format,limit,routing){
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
    console.log(data);
    if(data.total_rows!=0){
      switch(routing){
      case "full":
        $.each(data.rows, function(i, mydatas) {
        var cords=mydatas.row.geojson.coordinates;

        var prop={"source":mydatas.row.source,"target":mydatas.row.target,"via":mydatas.row.name,"route":mydatas.row.maxspeed_forward};
        var geometry='{"type":'+mydatas.row.geojson.type+',"coordinates":'+mydatas.row.geojson.coordinates+'}';
       var jsoner=propertyBaseLayers(prop,mydatas.row.geojson.type,mydatas.row.geojson.coordinates,data.total_rows,i,routing);

       var pointscords={"slng1":cords[0][0],"slng2":cords[1][0],"slat1":cords[0][1],"slat2":cords[1][1],"s":mydatas.row.source,"t":mydatas.row.target};
       coordinates.push(pointscords);
        });
       break;
       case "short":
       console.log(data);
        $.each(data.rows, function(i, mydatas) {

        var proper={"source":mydatas.row.source,"target":mydatas.row.target,"via":mydatas.row.name,"route":mydatas.row.maxspeed_forward};
        var geometries='{"type":'+mydatas.row.geojson.type+',"coordinates":'+mydatas.row.geojson.coordinates+'}';
         console.log(geometries);
       var jsoner=propertyShortPath(proper,mydatas.row.geojson.type,mydatas.row.geojson.coordinates,data.total_rows,i,routing);

       if(routing=="short" && (i<=(parseInt(data.total_rows)-2)) && (i>=(parseInt(data.total_rows)/2)-1)){

       /*endpoint=mydatas.row.name;
       var ends=endpoint.split("-");
       var starts=startpoint.split("-");
       console.log("ends:"+ends[0]+",starts"+starts[0]);*/
        document.getElementById("router").innerHTML="<p>Route"+mydatas.row.maxspeed_forward+" <br/> via "+mydatas.row.name+"<br/>"+data.total_rows+"<br/>"+router(startpoint,endpoint)+"</p>";
       }
        });
       break;
       case "bus_stop":

       $.each(data.rows, function(i,mydatas){
       var stops={"busstop":mydatas.row.name,"coordinates":mydatas.row.geojson.coordinates};
       bus_stops.push(stops);
       });
       break;
       }
      }else{
        document.getElementById("clocation").innerHTML="<p>Sorry We don't have route on those locations</p>";

      }
        return data;
    },
    error: function(error, status, desc) {
        console.log(desc);
        console.log(error);
        return error+"@"+desc;
    }
});
}

/**
*Function used to build together the geojson data and adding the layer to map
*/
function propertyBaseLayers(mypro,type,mygeo,totalrows,currentrow,routes){
var jsons={"property":mypro,"type":"LineString","coordinates":mygeo};

  myLayer.addData(jsons);
  if(routes!="short" && (totalrows-1)==currentrow){
  var styles=myStyle(routes,jsons);
   myLayer.setStyle(styles);
}


}

/**
*This method is used to get the properties requiered to build a layer for routed path
*/
function propertyShortPath(mypro,type,mygeo,totalrows,currentrow,routes){
var jsons={"property":mypro,"type":"LineString","coordinates":mygeo};
console.log(jsons);
 map1.removeLayer(shortpath);
 shortpath=L.geoJson();
  shortpath.addData(jsons);

  if((totalrows-1)==currentrow){
    var styles=myStyle(routes,jsons);
    shortpath.setStyle(styles);
     console.log("Im here tell me");
    shortpath.addTo(map1);
    }

}

/**
*this method is used get the required route
*/
function router(startpoint,endpoint){
var myroute="";
var starts=startpoint.split("-");
var ends=startpoint.split("-");
if(starts[0]==ends[0]){
myroute=starts[0]+"-"+ends[1];
}else if(starts[0]==ends[1]){
myroute=starts[0]+"-"+ends[0];
}else if(starts[1]==ends[0]){
myroute=starts[1]+"-"+ends[1];
}else if(starts[1]==ends[1]){
myroute=starts[1]+"-"+ends[0];
}
return myroute;
}

/**
*This function is used to called when the point is clicked on map(road point)
*/
 function onMapClick(e) {

  var street=e.layer.feature.property.route;

  var place=e.layer.feature.property.via;
          var showinfo="<ul><li>StreetName:"+street+"</li><li>PlaceName:"+place+"</li></ul>";
              markTile(e,e);
        if(i==0){
        startpoint=e.layer.feature.property.route;
       sos=e.layer.feature.property.source;
       text+="init_sou_"+sos+"<br/>";
        text+="init_tag_"+tag+"<br/>";
 document.getElementById("clocation").innerHTML="<h6>Source Info</h6>"+showinfo+"<br/>";
       }
       if(i==1){
        i=-1;
        tag=e.layer.feature.property.target;
     console.log("source:"+sos+":Target:"+tag);
         // getShortestPath(sos,tag);
     getGeoJSON(4617,"ways","gid,length,via as maxspeed_forward,route as name,source,target",sos,tag,false,"","json","","short");

        text+="finaal_sou_"+sos+"<br/>";
        text+="final_tag_"+tag+"<br/>";
          document.getElementById("destination").innerHTML="<h6>Destination Info</h6>"+showinfo+"<br/>";
        sos="";
        tag="";
        }
      i++; k++;
}

/**
*This function is used for adding the marker to clicked point on the map, for this project, source and destination
*/
function markTile(feature,event,content){
     if(i==0){
     if(k==3){
     }
        map1.removeLayer(shortpath);
        map1.removeLayer(marker1);
        marker1.setLatLng(event.latlng);
        console.log(event.latlng);
        map1.addLayer(marker1);
        }
        if(i==1){
       map1.removeLayer(marker2);
       marker2.setLatLng(event.latlng);
        map1.addLayer(marker2);

        }
}



/**
*Typehead event
*/
function searchOptions(targetClass,hiddenInputClass){

$('.'+targetClass).typeahead({
    source: function (query, process) {
        // implementation
          states = [];
    map = {};



    $.each(bus_stops, function (i, state) {
        map[state.busstop] = state;
        states.push(state.busstop);
    });

    process(states);
    },
    updater: function (item) {
        // implementation
 selectedState = map[item].busstop;
 $("."+hiddenInputClass).val(map[item].coordinates);

    return item;
    },
    matcher: function (item) {
        // implementation
             if (item.toLowerCase().indexOf(this.query.trim().toLowerCase()) != -1) {
        return true;
    }
    },
    sorter: function (items) {
        // implementation
         return items.sort();
    },
    highlighter: function (item) {
       // implementation
        var regex = new RegExp( '(' + this.query + ')', 'gi' );
    return item.replace( regex, "<strong>$1</strong>" );
    },
});
}

searchOptions("source_option","source_point");
searchOptions("destiny_option","destiny_point");


/**
*This event get user options
*/
$(".find_route").click(function(e){
e.preventDefault();
var sourcefound=0;
var targetfound=0;
var sosValue="";
var targetValue="";
var source_point=$("input.source_point").val();
var destiny_point=$("input.destiny_point").val();
var source_name=$("input.source_option").val();
var destiny_name=$("input.destiny_option").val();
var split_source=source_point.split(",");
var split_destiny=destiny_point.split(",");
var source_lat=parseFloat(split_source[1]);
var source_lng=parseFloat(split_source[0]);
var destiny_lat=parseFloat(split_destiny[1]);
var destiny_lng=parseFloat(split_destiny[0]);
var source_latlang=new L.LatLng(split_source[1],split_source[0]);
var destiny_latlang=new L.LatLng(split_destiny[1],split_destiny[0]);
//console.log(coordinates);
       //add marker one
          map1.removeLayer(marker1);
       marker1.setLatLng(source_latlang);
        map1.addLayer(marker1);


        //add marker two
        map1.removeLayer(marker2);
       marker2.setLatLng(destiny_latlang);
        map1.addLayer(marker2);
       // marker2.bindPopup(destiny_name).openPopup();
       //  marker1.bindPopup(source_name).openPopup();
       console.log(coordinates.length);
       console.log(parseFloat(split_source[1])-0.1);
           $.each(coordinates, function (i, cords) {
       // if(split_source[1]!=cords.slat1 || split_source[1]!=cords.slat2){
       var composite=Math.abs(Math.abs(cords.slat1)+Math.abs(cords.slat2))/2;
       var compositeLng=Math.abs(Math.abs(cords.slng1)+Math.abs(cords.slng2))/2;
       var diff1=Math.abs(Math.abs(source_lat)-Math.abs(cords.slat2));
       var diff2=Math.abs(Math.abs(source_lat)-Math.abs(cords.slat1));
        var compDiff=Math.abs(Math.abs(composite)-Math.abs(source_lat));
         var compDiffTarget=Math.abs(Math.abs(composite)-Math.abs(destiny_lat));
         var difflng1=Math.abs(Math.abs(source_lng)-Math.abs(compositeLng));
         var difflng2=Math.abs(Math.abs(destiny_lng)-Math.abs(compositeLng));

       if(compDiff>=(0) && compDiff<(0.000001) && difflng1<0.00001 && sourcefound==0){
         sosValue=cords.s;
         ++sourcefound;
        }

        if(compDiffTarget>=(0) && compDiffTarget<(0.000001) && difflng2<0.00001 && targetfound==0){
         $("p#tester").append("compo:"+composite+":compoDiff:"+compDiffTarget+":differlng:"+difflng1+":differlng2:"+difflng2+"::cords"+cords.t+"sources::"+destiny_lat+"::::"+cords.slat1+"<br/>");
         targetValue=cords.t;

         ++targetfound;
        }

        if(sourcefound==1 && targetfound==1){
        return false;
        }

    });
    if(sosValue!="" && targetValue!=""){
getGeoJSON(4617,"ways","gid,length,via as maxspeed_forward,route as name,source,target",sosValue,targetValue,false,"","json","","short");
}else{
 $("p#tester").html("<h1>Data not found </h1>"+targetValue+"target "+sosValue+" sos");
}
});



