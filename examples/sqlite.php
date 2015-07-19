<?
include( dirname(dirname(__FILE__))."/DATABASE.php" );

$config = array();
$config['type'] = DATABASE::SQLITE;
$config['host'] = "";
$config['db'] = dirname(__FILE__)."/temp.db"; #database name
$config['user'] = "";
$config['password'] = "";
$config['casesensitive'] = true; #optional - default true
$config['usePDO'] = true; #optional - default true, for Oracle use false PDO::OCI is unstable
$config['tmp'] = "/tmp"; #optional - default "/tmp" directory must be writable and accessible by php/apache
$config['logcaller'] = null; #optional - default null. the function used to handle errors.
## eg. "handler" or "Myclass::handler" by default no error output.
$config['prefix'] = ""; #optional - default null. the prefix used for all tables.

DATABASE::register_database( "SQLITEDB",  $config );

$create_table = "CREATE TABLE IF NOT EXISTS users (
  id integer NOT NULL PRIMARY KEY AUTOINCREMENT,
  username varchar(255) NOT NULL,
  password varchar(255) NOT NULL,
  type int(11) DEFAULT 0,
  date int(11) DEFAULT CURRENT_TIMESTAMP,
  lastedited int(11) DEFAULT 0,
  lasteditedby int(11) DEFAULT 0
);";

$create = SQLITEDB::sql()->compiled_str( $create_table, DATABASE::VOID );
SQLITEDB::exec_statement( $create );  
### exec_statement executes the final sql string, 
### as opposed to one with value binding when using exec_prepared

$insert = SQLITEDB::sql()
	->insertInto("users")	// needs a table name first before any other parts of the statement.
	->password("pass123")	// set password = 'pass123'
	->username("bobby");	// set username = 'bobby'
	
$result = SQLITEDB::exec_prepared( $insert );
print $result->insertid().": bobby<br>";

$insert = SQLITEDB::sql()
	->insertInto("users")	// needs a table name first before any other parts of the statement.
	->password("pass123")	// set password = 'pass123'
	->username("tommy");	// set username = 'tommy'
	
$result = SQLITEDB::exec_statement( $insert );
print $result->insertid().": tommy<br>";

$insert = SQLITEDB::sql()
	->insertInto("users")	// needs a table name first before any other parts of the statement.
	->password("pass123")	// set password = 'pass123'
	->username("cathy");	// set username = 'cathy'
	
$result = SQLITEDB::exec_prepared( $insert );
print $result->insertid().": cathy<br>";

$select = SQLITEDB::sql()
	->selectFrom("users")	// needs a table name first before any other parts of the statement.
	->orderBy("id DESC");		// the order of ORDER BY, WHERE, GROUP BY after selectFrom does not matter

$data = SQLITEDB::exec_statement( $select );
#fetch 1 row
print "=======[ 1 ]<br>";

while( $data->fetch() ){
	print $data->id.": ".$data->username."<br>";
}
