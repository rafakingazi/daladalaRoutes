<?php
/**
 * Geo Attribute Query
 * Performs attribute query on a geotable.
 *
 * @param 		string 		$fields 		Fields to be returned
 * @param 		string		$geotable		The PostGIS layer name.
 * @param 		string		$parameters		SQL where clause parameters
 * @param 		string		$format			format of output, either json or xml
 * @return 		string						resulting json or xml string
 */

# Includes
require_once("../inc/error.inc.php");
require_once("../inc/database.inc.php");
require_once("../inc/security.inc.php");

# Set arguments for error email
$err_user_name = "raphael martin";
$err_email = "rafakingazi@gmail.com";

# Retrive URL arguments
try {
	$fields = $_REQUEST['fields'];
	$geotable = $_REQUEST['geotable'];
	$parameters = $_REQUEST['parameters'];
	$format = trim($_REQUEST['format']);
	$hassource=trim($_REQUEST['hassource']);
	$source=trim($_REQUEST['source']);
	$target=trim($_REQUEST['target']);
	$log=$fields."_".$geotable."_".$parameters."_".$format."\n";
	if(file_exists("er.log")){
	$fd = fopen("er.log", "a+");
	fwrite($fd,$log);
	 fclose($fd);
	}
}
catch (Exception $e) {
    trigger_error("Caught Exception: " . $e->getMessage(), E_USER_ERROR);
}

// Performs the query and returns XML or JSON gid,st_asgeojson(st_transform(the_geom,4617)) as geojson
try {
  if($source=="" && $target==""){
	$sql = "select ".$fields.",st_asgeojson(st_transform(the_geom,4617)) as geojson  from  ". $geotable;
	if (strlen(trim($parameters)) > 0) {$sql .= " where " . $parameters;}

	$sql = sanitizeSQL($sql);
	//echo $sql;
	$pgconn = pgConnection();
    /*** fetch into an PDOStatement object ***/
    $recordSet = $pgconn->prepare($sql);
    $recordSet->execute();
	//echo $sql;
	require_once("../inc/format.inc.php");
	}else{
	 $innerSql = "select gid as id, source::integer, target::integer, length::double precision as cost from ".$geotable;

    //This is the SQL query that joins the results of the shortest_path() query with the roads table to get the
    //associated geometries that comprise our shortest path
      /*  $sql="select gid from  newroads
            join (select * from shortest_path('select gid as id, source::integer, target::integer, length::double precision as cost from newroads', 128, 315, false, false)) as route
            on newroads.gid = route.edge_id"*/

   /* $sql = "select st_asgeojson(st_transform(the_geom,4617)) as geojson from  ".$geotable."
            join (select seq, id1 AS node, id2 AS edge, cost from pgr_dijkstra('".$innerSql."', ".$source.", ".$target.", false, false)) as route
            on ".$geotable.".gid = route.id2";*/

/*$sql="select  seq, id1 AS node, id2 AS edge, cost,".$fields.",st_asgeojson(st_transform(".$geotable.".the_geom,4617)) as geojson from pgr_dijkstra('".$innerSql."', ".$source.", ".$target.", false, false) as route ";
$sql.="  left ".$geotable." on (route.id2=".$geotable.".gid)";*/
$sql="SELECT ".$fields.",st_asgeojson(st_transform(the_geom,4326)) as geojson FROM pgr_dijkstra('
                SELECT gid AS id,
                         source::integer,
                         target::integer,
                         length::double precision AS cost
                        FROM ".$geotable."',
                ".$source.", ".$target.", true, false) a   JOIN ".$geotable." b ON (a.id2 = b.gid) order by b.gid asc";
/*$sql=" EXPLAIN ANALYZE select * from ";
$sql.="shortest_path_astar ('SELECT gid as id, source, target, cost, x1, y1,x2, y2 FROM ways ',
                6261, 6262, false, false)";*/
/*$sql.="driving_distance('SELECT gid AS id,source,target,
             length::double precision AS cost FROM ways',30,0.01,false,false);";*/



    $sql = sanitizeSQL($sql);
	//echo $sql;
	$pgconn = pgConnection();
    /*** fetch into an PDOStatement object ***/
    $recordSet = $pgconn->prepare($sql);
    $recordSet->execute();
	//echo $sql;
	require_once("../inc/format.inc.php");
	}
}
catch (Exception $e) {
	trigger_error("Caught Exception: " . $e->getMessage(), E_USER_ERROR);
}

?>