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
$err_user_name = "Tobin";
$err_email = "tobin.bradley@mecklenburgcountync.gov";

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

# Performs the query and returns XML or JSON gid,st_asgeojson(st_transform(the_geom,4617)) as geojson
try {
  if($source=="" && $target==""){
	$sql = "select " . $fields . "   from  ". $geotable."  limit 10000";
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

    $sql = "select ".$fields." from  ".$geotable."
            join (select * from shortest_path('".$innerSql."', ".$source.", ".$target.", false, false)) as route
            on ".$geotable.".gid = route.edge_id";


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