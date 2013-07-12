<?php
/*
    Database Connections
*/

// Return database connection
function pgConnection() {
	$conn = new PDO ("pgsql:host=routes.zapto.org;dbname=dar_routes;port=5432","raphaelmartin","", array(PDO::ATTR_PERSISTENT => true));
    return $conn;
}

?>
