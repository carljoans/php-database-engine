<?
include( dirname(dirname(__FILE__))."/DATABASE.php" );

$config = array();
$config['type'] = DATABASE::ORACLE;
$config['host'] = "";
$config['db'] = "(DESCRIPTION = (ADDRESS_LIST = (ADDRESS = (PROTOCOL = TCP)(HOST = localhost)(PORT = 1521)) ) (CONNECT_DATA = (SERVICE_NAME = dbase) ) )"; #database name
$config['user'] = "dbuser";
$config['password'] = "dbpassword";
$config['casesensitive'] = false; #optional - default true
$config['usePDO'] = false; #optional - default true, for Oracle use false PDO::OCI is unstable
$config['tmp'] = "/tmp"; #optional - default "/tmp" directory must be writable and accessible by php/apache
$config['logcaller'] = null; #optional - default null. the function used to handle errors.
## eg. "handler" or "Myclass::handler" by default no error output.
$config['prefix'] = ""; #optional - default null. the prefix used for all tables.

DATABASE::register_database( "ORACLEDB", $config );

$users = ORACLEDB::sql("users");
$blog = ORACLEDB::sql("blog")->__selectFrom();

$blog_links = array( "userid"=>"users:id" ); // create links between the tables
$blog->__createLink($blog_links);
$blog->__joinAdd($users);  // using the created links, create a join clause
$blog->__selectAs($users,"u_%s");  // prefix the users table columns with "u_" to avoid ambiguous column names

$data = ORACLEDB::exec_prepared( $blog );
print "=======[ joins ]<br>";
while( $data->fetch() ){
	print "\"".$data->subject."\" by ".$data->u_username."<br>";
}

$select = ORACLEDB::sql()
	->__selectFrom("users")	// needs a table name first before any other parts of the statement.
	->type(1)				// where type = 1
	->__orderBy("id");		// the order of ORDER BY, WHERE, GROUP BY after selectFrom does not matter

$data = ORACLEDB::exec_prepared( $select );
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

print "=======[ 3 ]<br>";
#fetch the rest
while( $data->fetch() ){
	print $data->id.": ".$data->username."<br>";
}

$update = ORACLEDB::sql()
	->__updateSet("users")	// needs a table name first before any other parts of the statement.
	->id(4)					// where id = 1, if primary is assigned it will use as where, else where() must be used explicitly.
	->password("pass123");	// set password = 'pass123'

$isupdated = ORACLEDB::exec_prepared( $update );

$insert = ORACLEDB::sql()
	->__insertInto("users")	// needs a table name first before any other parts of the statement.
	->password("pass123")	// set password = 'pass123'
	->username("bobby");	// set username = 'bobby'

$insert_id = ORACLEDB::exec_prepared( $insert );

$delete = ORACLEDB::sql()
	->__deleteFrom("users")	// needs a table name first before any other parts of the statement.
	->password("pass123")	// where password = 'pass123'
	->username("bobby");	// where username = 'bobby'

$isdeleted = ORACLEDB::exec_prepared( $delete );
