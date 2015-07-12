<?
include( dirname(__FILE__)."/DATABASE.php" );

$config = array();
$config['type'] = DATABASE::MYSQL;
$config['host'] = "localhost";
$config['db'] = "theworldwarriormc"; #database name
$config['user'] = "root";
$config['password'] = "dbpassword";
$config['casesensitive'] = true; #optional - default true
$config['usePDO'] = true; #optional - default true, for Oracle use false PDO::OCI is unstable
$config['tmp'] = "/tmp"; #optional - default "/tmp" directory must be writable and accessible by php/apache

DATABASE::register_database( "MYSQLDB",  $config );

$select = MYSQLDB::sql()
	->selectFrom("users")	// needs a table name first before any other parts of the statement.
	->type(1)				// where type = 1
	->orderBy("id");		// the order of ORDER BY, WHERE, GROUP BY after selectFrom does not matter

$data = MYSQLDB::exec_prepared( $select );
#fetch 1 row
print "=======[ 1 ]<br>";
while( $data->fetch(1) ){
	print $data->id.": ".$data->username."<br>";
}

print "=======[ 2 ]<br>";
#fetch 2 more rows
while( $data->fetch(2) ){
	print $data->id.": ".$data->username."<br>";
}

print "=======[ 2 ]<br>";
#fetch the rest
while( $data->fetch() ){
	print $data->id.": ".$data->username."<br>";
}

$update = MYSQLDB::sql()
	->updateSet("users")	// needs a table name first before any other parts of the statement.
	->id(4)					// where id = 1, if primary is assigned it will use as where, else where() must be used explicitly.
	->password("pass123");	// set password = 'pass123'
	
$isupdated = MYSQLDB::exec_prepared( $update );

$insert = MYSQLDB::sql()
	->insertInto("users")	// needs a table name first before any other parts of the statement.
	->password("pass123")	// set password = 'pass123'
	->username("bobby");	// set username = 'bobby'
	
$insert_id = MYSQLDB::exec_prepared( $insert );

$delete = MYSQLDB::sql()
	->deleteFrom("users")	// needs a table name first before any other parts of the statement.
	->password("pass123")	// where password = 'pass123'
	->username("bobby");	// where username = 'bobby'
	
$isdeleted = MYSQLDB::exec_prepared( $delete );
