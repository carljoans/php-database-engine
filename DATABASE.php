<?
/*
 * PHP Database Engine
 *
 * Copyright (C) 2015 Charles Johannisen
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA
 *
 */

include( dirname(__FILE__)."/RESULTSET.php" );
include( dirname(__FILE__)."/SQL.php" );

class DATABASE {

	const ALL = 1;
	const DEL = 2;
	const UPD = 2;
	const NUM = 2;
	const PUT = 3;
	const INS = 3;
	const FET = 4;
	const VOID = 5;

	const TRA = 1;
	const COM = 2;
	const ROL = 3;
	
	const MYSQL = "mysql";
	const SQLITE = "sqlite";
	const ORACLE = "oracle";
	
	const SEQ = "_SEQ";
	
	const YEARFULL = 1;
	const YY = 2;
	const M = 3;
	const MM = 4;
	const MMM = 5;
	const MONTHFULL = 6;
	const D = 7;
	const DD = 8;
	const DDD = 9;
	const DAYFULL = 10;
	const WEEKS = 11;
	
	public static $LOGCALLER = null;
	public static $CASESENSITIVE = false;
	public static $PREFIX = "";
	public static $USE_PDO = true;
	public static $ob = array();
	public static $transaction = array();
	public static $dbtype = "mysql";
	public static $make_table = array();
	
	public static $HOST = "";
	public static $DB = "";
	public static $USER = "";
	public static $PASSWORD = "";
	public static $PORT = "";
	
	public static $TMP = "";
	
	public static $ERROR = "";
	public static $LASTQUERY = array();
	public static $LASTQUERYTIME = array();
	
	public static $defaults = array();
	public static $functions = array(
		"oracle"=>array(
			"TO_DATE",
		),
		"mysql"=>array(
			"DATE_FORMAT",
		),
		"sqlite"=>array(
		),
	);
	public static $reserved = array(
		"oracle"=>array(
			"DATE", "ORDER", "FROM", "TO", "PASSWORD",
		),
		"mysql"=>array(
			"DATE", "ORDER", "FROM", "TO", "PASSWORD",
		),
		"sqlite"=>array(
		),
	);
	
	public static $databases = array();
	public static $database_data = array();
	public static $database_in_use = "";
	
	public static function register_database( $name,  array $config=array() ){
		
		if( empty( self::$defaults ) ){
			self::defaults();
		}
		
		if( isset($config['type']) && isset($config['host']) && isset($config['db']) && isset($config['user']) && isset($config['password']) ){
			$config['usePDO'] = ( isset($config['usePDO']) )? $config['usePDO'] : true ;
			$config['casesensitive'] = ( isset($config['casesensitive']) )? $config['casesensitive'] : true ;
			$config['tmp'] = ( isset($config['tmp']) )? $config['tmp'] : "/tmp" ;
			$config['logcaller'] = ( isset($config['logcaller']) )? $config['logcaller'] : null ;
			$config['prefix'] = ( isset($config['prefix']) )? $config['prefix'] : null ;
			self::$databases[$name] = $config;
			self::load_class($name);
		}
		
	}
	
	public static function get_databases(){
		
		return self::$databases;
		
	}	
	
	public static function use_database(){
		
		if( self::$database_in_use != get_called_class() ){
			
			self::$database_in_use = get_called_class();
			
			if( !isset( self::$databases[self::$database_in_use] ) ){
				print "DB CLASS ERROR!"; exit;
			}
				
			self::initDB();
			
			if( self::$dbtype==self::ORACLE ){
				
				$dbOptsSetter = self::query( "ALTER SESSION SET NLS_DATE_FORMAT='yyyy-mm-dd hh24:mi:ss'", self::DEL );
				$dbOptsSetter = self::query( "ALTER SESSION SET NLS_TIMESTAMP_FORMAT='yyyy-mm-dd hh24:mi:ss'", self::DEL );
				
			}
			
		}
		
	}	
	
	public static function initDB(){
		
		self::$dbtype = self::$databases[self::$database_in_use]['type'];
		self::$HOST = self::$databases[self::$database_in_use]['host'];
		self::$DB = self::$databases[self::$database_in_use]['db'];
		self::$USER = self::$databases[self::$database_in_use]['user'];
		self::$PASSWORD = self::$databases[self::$database_in_use]['password'];
		self::$USE_PDO = self::$databases[self::$database_in_use]['usePDO'];
		self::$CASESENSITIVE = self::$databases[self::$database_in_use]['casesensitive'];
		self::$TMP = self::$databases[self::$database_in_use]['tmp'];
		self::$LOGCALLER = self::$databases[self::$database_in_use]['logcaller'];
		self::$PREFIX = self::$databases[self::$database_in_use]['prefix'];
		
		self::setob();
		
	}
	
	public static function reset(){
		
		$do_not_reset = array( "databases","database_data", "ob", "transaction" );
		
		foreach ( self::$defaults as $name=>$value ) { 
			if( !in_array( $name, $do_not_reset ) ){
				self::$$name = $value;
			}
		}
	}
	
	public static function defaults(){
		
		$reflection = new ReflectionClass(__CLASS__);
        self::$defaults = $reflection->getStaticProperties();
        unset( self::$defaults['defaults'] );
		
	}
	
	public static function connect( $host="", $db="", $user="", $password="" ) {
		
		$ob = isset(self::$ob[self::$database_in_use]) ? self::$ob[self::$database_in_use] : NULL ;
		
		if( $ob == NULL ){
			
			$host = ( $host == "" )? self::$HOST : $host;
			$db = ( $db == "" )? self::$DB : $db;
			$user = ( $user == "" )? self::$USER : $user;
			$password = ( $password == "" )? self::$PASSWORD : $password;
			
			if( self::$USE_PDO ){
				try {
					
					if( self::$dbtype == self::MYSQL ){
						$ob = new PDO( "mysql:host=".$host.";dbname=".$db, $user, $password );
					}
					
					if( self::$dbtype == self::SQLITE ){
						//$db = /path/to/dbfile.db
						$ob = new PDO( "sqlite:".$db );
					}
					
					if( self::$dbtype == self::ORACLE ){
						/*$db = "  
									(DESCRIPTION =
										(ADDRESS_LIST =
										  (ADDRESS = (PROTOCOL = TCP)(HOST = yourip)(PORT = 1521))
										)
										(CONNECT_DATA =
										  (SERVICE_NAME = orcl)
										)
									  )
										   ";*/
						
						$ob = new PDO( "oci:dbname=".$db, $user, $password  );
					}
					
					$ob->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION ); 
					self::$ob[self::$database_in_use] = $ob;
					
				}
				catch(PDOException $e) {
					self::pdo_debug( $e );
				}
				
			}else{
				
				if( self::$dbtype == self::ORACLE ){
					/*$db = "  
								(DESCRIPTION =
									(ADDRESS_LIST =
									  (ADDRESS = (PROTOCOL = TCP)(HOST = yourip)(PORT = 1521))
									)
									(CONNECT_DATA =
									  (SERVICE_NAME = orcl)
									)
								  )
									   ";*/
					
					$ob = oci_connect( $user, $password, $db);
					self::$ob[self::$database_in_use] = $ob;
					
					if (!$ob) {
						self::oci8_debug( $ob, $str_sql );
					}
					
				}				
				
			}
			
		}

		return $ob;
	}
	
	public static function connection( $host="", $db="", $user="", $password="" ){
		
		$ob = self::connect( $host, $db, $user, $password );
		$return = true;
		
		if( self::$USE_PDO ){
			if( $ob instanceof PDOException ){
				$return = false;
			}
		}else{
			if( $ob === false ){
				$return = false;
			}
		}
		
		$ob = null;		
		return $return;
		
	}

	public static function setob( $host="", $db="", $user="", $password="" ) {
		
		return self::connection( $host, $db, $user, $password );
        
	}
	
	public static function log_error($class){
		if( self::$LOGCALLER != null ){
			call_user_func( self::$LOGCALLER, __CLASS__ );
		}
	}
	
	public static function oci8_debug( $handle, $text="" ){
		
		ob_start();
		print "\n>>>>>>>>>>>>>>>>>>>>>>>> ERROR >>>\n";
		print_r( oci_error($handle) );
		print "\n>>>>>>>>>>>>>>>>>>>>>>>> CONTEXT >>>\n";
		print "\n";
		print $text;
		print "\n";
		print "\n>>>>>>>>>>>>>>>>>>>>>>>> BACKTRACE >>>\n";
		debug_print_backtrace();
		$backtrace = ob_get_contents(); 
		ob_end_clean();
		
		self::$ERROR = array( $backtrace );
		self::log_error(__CLASS__);
		
	}
	
	public static function pdo_debug( $e ){
		
		self::$ERROR = $e;
		self::log_error(__CLASS__);
		
	}
	
	/*
	 * The important thing about load_class is that it creates a new child class from this one
	 * so that when connecting to different databases in one scope it will use the proper platform ( oracle, mysql, etc )
	 * formatting where platform formatting is applied.
	 * calling use_database() tells us everything we need to know about the platform we're using.
	 * 
	 * */
	
	public static function load_class($classname){	
		
		if( !file_exists(self::$databases[$classname]['tmp']) ){
			mkdir( self::$databases[$classname]['tmp'], 0777, true );
		}
		
		$filename = self::$databases[$classname]['tmp']."/__autogen_by_database_engine_".$classname;
		
		if( !file_exists( $filename ) ){
			
			file_put_contents( $filename, "<? class ".$classname." extends DATABASE{}" );	
				
		}
		
		if( !class_exists($classname) ){
			include($filename);		
		}
			
	}
	
	public static function numrows( $str_sql ){
		
		return self::query( $str_sql, self::NUM );
		
	}
	
	public static function begintransaction(){
		return self::query( "", 1, self::TRA );
	}
	
	public static function commit(){
		return self::query( "", 1, self::COM );
	}
	
	public static function rollback(){
		return self::query( "", 1, self::ROL );
	}
	
	public static function lastInsertId(){
		
		if( self::$USE_PDO ){
			if( self::$dbtype == self::MYSQL || self::$dbtype == self::SQLITE ){
				return self::$ob[self::$database_in_use]->lastInsertId();
			}
		}
		
		return 0;
		
	}
	
	public static function getrowcount( $str_sql, $bindings=NULL ){
		$rowcount = 1;
		$query = self::sql();
		$query->compiled_str( $str_sql );
		if( $query->isSelect() ){
			$str_sql = "SELECT COUNT( * ) AS rowcount FROM ( ".$str_sql." ) DBtblcount";
			$count = self::query( $str_sql, self::FET, $bindings );
			$rowcount = $count->rowcount;
		}
		return $rowcount;
	}
    
    public static function query( $str_sql, $expect=1, $transaction=NULL, $ob=NULL ) {
		
		self::$LASTQUERYTIME[md5($str_sql)]["start"] = microtime(true);
		
		self::use_database();
		self::$ERROR = "";
		
		if( !self::connection() ){
			return array();
		}
		
		if( $ob == NULL && self::$ob == NULL ){
			self::setob();
		}else if( $ob != NULL && ( !isset( self::$ob[self::$database_in_use] ) || ( isset( self::$ob[self::$database_in_use] ) && self::$ob[self::$database_in_use] == NULL ) ) ){
			self::$ob[self::$database_in_use] = $ob;
		}
		
		if( self::$USE_PDO ){	
			
			try {
				
				if( is_array( $transaction ) ){
					
					self::interpolateQuery( $str_sql, $transaction );
					
					$rowcount = 1;
					
					if( $expect===self::ALL && ( self::$dbtype == self::SQLITE || self::$dbtype == self::ORACLE ) ){
						$rowcount = self::getrowcount( $str_sql, $transaction );
					}
					
					$result = self::$ob[self::$database_in_use]->prepare( $str_sql );
					
					if( $result->execute( $transaction ) ){
						
						if( $expect===self::DEL || $expect===self::UPD || $expect===self::NUM ){
							$status = ( $expect===self::DEL || $expect===self::UPD )? $result : true ;
							self::$LASTQUERYTIME[md5($str_sql)]["end"] = microtime(true);
							return new RESULTSET( null, $result->rowCount(), self::NUM, $status );
						}else if( $expect===self::PUT || $expect===self::INS ){
							self::$LASTQUERYTIME[md5($str_sql)]["end"] = microtime(true);
							return new RESULTSET( null, self::lastInsertId(), self::INS, true );
						}else if( $expect===self::FET ){
							self::$LASTQUERYTIME[md5($str_sql)]["end"] = microtime(true);
							return new RESULTSET( $result->fetch(), 1, self::FET, true );
						}else{
							self::$LASTQUERYTIME[md5($str_sql)]["end"] = microtime(true);
							if( self::$dbtype != self::SQLITE && self::$dbtype != self::ORACLE ){
								$rowcount = $result->rowCount();
							}
							return new RESULTSET( $result, $rowcount, self::ALL, true );
						}
						
					}
					
				}else if( $transaction === self::TRA ){
					if( !isset( self::$transaction[self::$database_in_use] ) || ( isset(self::$transaction[self::$database_in_use]) && !self::$transaction[self::$database_in_use] ) ){
						self::$transaction[self::$database_in_use] = self::$ob[self::$database_in_use]->beginTransaction();
						return self::$transaction;	
					}
				}else if( $transaction === self::COM ){
					return self::$ob[self::$database_in_use]->commit();
				}else if( $transaction === self::ROL ){
					return self::$ob[self::$database_in_use]->rollBack();
				}else{
					
					self::interpolateQuery( $str_sql, array() );
					
					$rowcount = 1;
					
					if( $expect===self::ALL && ( self::$dbtype == self::SQLITE || self::$dbtype == self::ORACLE ) ){
						$rowcount = self::getrowcount( $str_sql, $transaction );
					}
					
					if( $expect===self::DEL || $expect===self::UPD || $expect===self::NUM ){
						$result = self::$ob[self::$database_in_use]->query( $str_sql );
						$status = ( $expect===self::DEL || $expect===self::UPD )? $result : true ;
						self::$LASTQUERYTIME[md5($str_sql)]["end"] = microtime(true);
						return new RESULTSET( null, $result->rowCount(), self::NUM, $status );
					}else if( $expect===self::PUT || $expect===self::INS ){
						$result = self::$ob[self::$database_in_use]->query( $str_sql );
						self::$LASTQUERYTIME[md5($str_sql)]["end"] = microtime(true);
						return new RESULTSET( null, self::lastInsertId(), self::INS, true );
					}else if( $expect===self::FET ){
						$result = self::$ob[self::$database_in_use]->query( $str_sql );
						self::$LASTQUERYTIME[md5($str_sql)]["end"] = microtime(true);
						return new RESULTSET( $result->fetch(), 1, self::FET, true );
					}else{
						$result = self::$ob[self::$database_in_use]->query( $str_sql );
						self::$LASTQUERYTIME[md5($str_sql)]["end"] = microtime(true);
						if( self::$dbtype != self::SQLITE && self::$dbtype != self::ORACLE ){
							$rowcount = $result->rowCount();
						}		
						return new RESULTSET( $result, $rowcount, self::ALL, true );
					}
					
				}
				
				
				
			} catch(PDOException $e) {
				
				self::pdo_debug($e);
				
			}
			
		}else{
			
			if( is_array( $transaction ) ){
				
				self::interpolateQuery( $str_sql, $transaction );
				
				if( self::$dbtype == self::ORACLE ){
					
					$rowcount = 1;
					
					if( $expect===self::ALL ){
						$rowcount = self::getrowcount( $str_sql, $transaction );
					}
					
					$parse = oci_parse( self::$ob[self::$database_in_use], $str_sql );
					
					foreach( $transaction as $bindkey=>$bindvalue ){
						oci_bind_by_name( $parse, $bindkey, $transaction[$bindkey]);
					}
					
					$execute = ( isset(self::$transaction[self::$database_in_use]) && self::$transaction[self::$database_in_use] )? oci_execute( $parse, OCI_NO_AUTO_COMMIT ) : oci_execute($parse) ;		
					
					if( $execute ){
						
						if( $expect===self::DEL || $expect===self::UPD || $expect===self::NUM ){
							self::$LASTQUERYTIME[md5($str_sql)]["end"] = microtime(true);
							return new RESULTSET( null, oci_num_rows($parse), self::NUM, true, true );
						}else if( $expect===self::PUT || $expect===self::INS ){
							self::$LASTQUERYTIME[md5($str_sql)]["end"] = microtime(true);
							return new RESULTSET( null, self::lastInsertId(), self::INS, true, true );
						}else if( $expect===self::FET ){
							$result = oci_fetch_array( $parse );
							oci_free_statement($parse);
							self::$LASTQUERYTIME[md5($str_sql)]["end"] = microtime(true);
							return new RESULTSET( $result, 1, self::FET, true, true );
						}else{
							self::$LASTQUERYTIME[md5($str_sql)]["end"] = microtime(true);
							return new RESULTSET( $parse, $rowcount, self::ALL, true, true );
						}
						
					}else{						
						
						ob_start();
						print_r( $transaction );
						$backtrace = ob_get_contents(); 
						ob_end_clean();
						
						$backtrace = "SQL:\n".$str_sql."\nVALUES:\n".$backtrace;
						
						self::oci8_debug( $parse, $backtrace );
												
					}
					
				}
				
			}else if( $transaction === self::TRA ){
				if( !isset( self::$transaction[self::$database_in_use] ) || ( isset(self::$transaction[self::$database_in_use]) && !self::$transaction[self::$database_in_use] ) ){
					self::$transaction[self::$database_in_use] = true;
					return self::$transaction[self::$database_in_use];	
				}
			}else if( $transaction === self::COM ){
				self::$transaction[self::$database_in_use] = false;
				return oci_commit(self::$ob[self::$database_in_use]);
			}else if( $transaction === self::ROL ){
				self::$transaction[self::$database_in_use] = false;
				return oci_rollback(self::$ob[self::$database_in_use]);
			}else{
				
				self::interpolateQuery( $str_sql, array() );
				
				if( self::$dbtype == self::ORACLE ){
					
					$rowcount = 1;
					
					if( $expect===self::ALL ){
						$rowcount = self::getrowcount( $str_sql, $transaction );
					}
					
					$parse = oci_parse( self::$ob[self::$database_in_use], $str_sql );
					
					$execute = ( isset(self::$transaction[self::$database_in_use]) && self::$transaction[self::$database_in_use] ) ? oci_execute( $parse, OCI_NO_AUTO_COMMIT ) : oci_execute($parse) ;		
					
					if( $execute ){
						
						if( $expect===self::DEL || $expect===self::UPD || $expect===self::NUM ){
							self::$LASTQUERYTIME[md5($str_sql)]["end"] = microtime(true);
							return new RESULTSET( null, oci_num_rows($parse), self::NUM, true, true );
						}else if( $expect===self::PUT || $expect===self::INS ){
							self::$LASTQUERYTIME[md5($str_sql)]["end"] = microtime(true);
							return new RESULTSET( null, self::lastInsertId(), self::INS, true, true );
						}else if( $expect===self::FET ){
							$result = oci_fetch_array( $parse );
							oci_free_statement($parse);
							self::$LASTQUERYTIME[md5($str_sql)]["end"] = microtime(true);
							return new RESULTSET( $result, 1, self::FET, true, true );
						}else{
							self::$LASTQUERYTIME[md5($str_sql)]["end"] = microtime(true);
							return new RESULTSET( $parse, $rowcount, self::ALL, true, true );
						}
						
					}else{
						
						self::oci8_debug( $parse, $str_sql );
												
					}
					
				}
				
			}
			
			
		}       

		return false;
		
	}
	
	/*
	 USED ONLY ON NON Table names and aliases.
	 DBO::NF("TableName") -- NO
	 DBO::NF("TableName.id") -- NO
	 DBO::NF("TN.id") -- YES on table alias, i.e select DBO::NF("TN.id") from DBO::TF("TableName") DBO::NF("TN.id")
	 DBO::NF("TN") -- YES
	 DBO::NF("id") -- YES
	 * */
	
	public static function NF($name) {
		
		return self::nameformat($name);
		
	}

	/*
	 This is oracle specific. When selecting a clob field in a groupby. So it converts the clob field to a varchar.
	 This will cut off the clob to fit in a varchar size if it exceeds varchar length. So if you don't want your clob cut off don't use it in the select/groupby. 
	 * 
	 group by
	 DBO::ora_to_char("paymentDetail") == to_char(paymentDetail)
	 * */

	public static function ora_to_char($name) {	
		if( preg_match('/\./',$name) ){
			$name = self::TF($name);
		}
		return self::nameformat( $name, false, false, true );
	}

	public static function MAXF($name) {	
		if( preg_match('/\./',$name) ){
			$name = self::TF($name);
		}
		return self::nameformat( $name, false, true );
	}

	/*
	 USED ONLY ON Full Table names not aliases.
	 DBO::TF("TableName") -- YES
	 DBO::TF("TableName.id") -- YES
	 DBO::TF("TN.id") -- NO
	 DBO::NF("TN.id") -- YES
	 * */

	public static function TF($name) {		
		return self::nameformat($name,true);
	}	
	
	public static function sql($tablename=""){
		
		self::use_database();
		$sql = new SQL($tablename);
		$sql->use_database( self::$database_in_use );
		return $sql;
		
	}
	
	public static function quote_name( $name ){
		
		if( preg_match( '/\./', $name ) ){
			$name = explode(".", $name);
			$name[0] = self::quote_name( $name[0] );
			$name[1] = self::quote_name( $name[1] );
			return implode(".", $name);
		}
		
		self::use_database();
		if( self::$CASESENSITIVE || in_array( strtoupper($name), self::$reserved[self::$databases[self::$database_in_use]['type']] ) ){
			
			if( self::$dbtype == self::MYSQL ){
				$name = "`".$name."`";
			}
			
			if( self::$dbtype == self::ORACLE ){
				$name = '"'.$name.'"';
			}
			
			if( self::$dbtype == self::SQLITE ){
				$name = '"'.$name.'"';
			}
			
		}
		
		return $name;
		
	}
	
	private static function nameformat( $name, $table=false, $max=false, $clobtochar=false ) {	
	
		self::use_database();
		$name = str_replace( array( "`", '"' ), "", trim( $name ) );		
		
		if( $table ){
			$name = self::quote_name( self::$PREFIX.$name );
		}else if( $max ){
			$name = ( self::$dbtype == self::ORACLE )? 'MAX('.self::quote_name( $name ).')' : $name;
		}else if( $clobtochar ){
			$name = ( self::$dbtype == self::ORACLE )? 'to_char('.self::quote_name( $name ).')' : $name;
		}else{
			$name = self::quote_name( $name );
		}
		
		return $name;	
			
	}
	
	public static function YEAR( $fill ){
		return self::datesub( self::YEARFULL, $fill );
	}
	
	public static function MONTH( $fill ){
		return self::datesub( self::MM, $fill );
	}
	
	public static function DAY( $fill, $format = "" ){
		return self::datesub( self::DD, $fill, $format );
	}
	
	public static function WEEK( $fill, $weeks=1 ){
		
		self::use_database();
		$week = ($weeks*7);
		
		if ( self::$dbtype == self::MYSQL ) {
			
			$week -= 1;
			
			return " TO_CHAR(TRUNC( ".$fill.", 'DAY' ), '".CORE::$conf['general']['dateFormat2']."')  || ' - ' || TO_CHAR(TRUNC( ".$fill.", 'DAY' )+".$week.", '".CORE::$conf['general']['dateFormat2']."') ";
			
		}
		
		if( self::$dbtype == self::ORACLE ){
			
			return " concat( DATE_FORMAT(from_days(to_days(".$fill.")-dayofweek(".$fill.")+1), '".CORE::$conf['general']['dateFormat2']."'), ' - ', DATE_FORMAT(from_days(to_days(".$fill.")-dayofweek(".$fill.")+".$week."), '".CORE::$conf['general']['dateFormat2']."') ) ";
			
		}
		
	}
	
	public static function dateformatstring(){
	
		self::use_database();
		$str = array();
		
		if( self::$dbtype == self::MYSQL ){
			$str = array( self::YEARFULL=>"%Y", self::MM=>"%m", self::DD=>"%d" );
		}
		
		if( self::$dbtype == self::ORACLE ){
			$str = array( self::YEARFULL=>"YYYY", self::MM=>"MM", self::DD=>"DD" );
		}
		
		$args = func_get_args();
		$format = array();
		
		foreach( $args as $index=>$arg ){
			if( is_int( $arg ) ){
				$args[$index] = $str[$arg];
			}
		}
		
		return implode( $args );
		
	}
	
	public static function datesub( $part, $fill, $format = "" ){
		
		self::use_database();
		$part = strtolower( $part );
		
		if( $part == self::YEARFULL ){
			if( self::$dbtype == self::MYSQL ){
				return "YEAR( ".$fill." )";
			}
			
			if( self::$dbtype == self::ORACLE ){
				return "TO_CHAR( ".$fill.", 'YYYY' )";
			}
		}
		
		if( $part == self::MM ){
			if( self::$dbtype == self::MYSQL ){
				return "MONTH( ".$fill." )";
			}
			
			if( self::$dbtype == self::ORACLE ){
				return " TO_CHAR( ".$fill.", 'MM' ) ";
			}
		}
		
		if( $part == self::DD ){
			
			if( self::$dbtype == self::MYSQL ){
				if( $format != "" ){
					return " DATE_FORMAT( ".self::DATE_COL($fill).", '".$format."' ) ";
				}else{
					return " DAY( ".$fill." ) ";
				}
			}
			
			if( self::$dbtype == self::ORACLE ){
				if( $format != "" ){
					return " TO_CHAR( ".$fill.", '".$format."' ) ";
				}else{
					return " TO_CHAR( ".$fill.", 'DD' ) ";
				}
			}			
			
		}
		
	}
	
	public static function DAY_INTERVAL( $fill, $format='Y-m-d H:i:s' ){
		
		return self::datesub( self::DD, $fill, $format );
		
	}
	
	public static function datetime_val_format(){
		
		self::use_database();
		if( self::$dbtype == self::MYSQL ){
			return date('Y-m-d H:i:s');
		}
		
		if( self::$dbtype == self::ORACLE ){			
			return self::DATE_FORMAT_VAL( date('Y-m-d H:i:s'), "yyyy-mm-dd hh24:mi:ss" );
		}
		
	}
	
	public static function date_val_format(){
		
		self::use_database();
		if( self::$dbtype == self::MYSQL ){
			return date('Y-m-d');
		}
		
		if( self::$dbtype == self::ORACLE ){			
			return self::DATE_FORMAT_VAL( date('Y-m-d'), "yyyy-mm-dd" );
		}
		
	}
	
	public static function dateformat( $item, $field=true, $format="YYYYMMDD" ){
		
		self::use_database();
		$item = ( $field )? self::NF($item) : "'".$item."'" ;
		
		if( self::$dbtype == self::MYSQL ){
			return "DATE_FORMAT( ".$item.", '".$format."' )";
		}
		
		if( self::$dbtype == self::ORACLE ){			
			return "TO_DATE( ".$item.", '".$format."' )";
		}
		
	}
	
	public static function SUBSTRING( $item, $from, $to = 0, $field = true ){
		
		self::use_database();
		$item = ( $field )? self::NF($item) : "'".$item."'" ;
		
		if( self::$dbtype == self::MYSQL || self::$dbtype == self::SQLITE ){
			$to = ( $to > 0 )? ", ".$to."" : "" ;
			return "SUBSTR( ".$item.", ".$from."".$to." )";
		}
		
		if( self::$dbtype == self::ORACLE ){		
			$to = ( $to > 0 )? " TO ".$to." " : "" ;
			return "SUBSTR( ".$item.", ".$from."".$to." )";
		}
		
	}
	
	public static function SUBSTRING_COL( $item, $from, $to = 0 ){
		
		return self::SUBSTRING( $item, $from, $to, true );
		
	}
	
	public static function SUBSTRING_VAL( $item, $from, $to = 0 ){
		
		return self::SUBSTRING( $item, $from, $to, false );
		
	}
	
	/*
	 DATE_FORMAT('VALUE', '%Y-%m-%d') 
	 DBO::DATE_FORMAT_COL( 'VALUE', DBO::dateformatstring( DBO::YEARFULL, "-", DBO::MM, "-", DBO::DD ) )	 
	 */
	
	public static function DATE_FORMAT_VAL( $item, $format="YYYYMMDD" ){
		
		return self::dateformat( $item, false, $format );
		
	}
	
	/*
	 mysql's DATE_FORMAT(TableName.expiryDate, '%Y-%m-%d') 
	 DBO::DATE_FORMAT_COL( "TableName.expiryDate", DBO::dateformatstring( DBO::YEARFULL, "-", DBO::MM, "-", DBO::DD ) )
	 */
	
	public static function DATE_FORMAT_COL( $item, $format="YYYYMMDD" ){
		
		return self::dateformat( $item, true, $format );
		
	}
	
	public static function DATE_ADD_MONTH_COL( $item, $interval ){
		
		return self::adddate( $item, $interval, "MONTH", 1 );
		
	}
	
	public static function DATE_ADD_MONTH_VAL( $item, $interval ){
		
		return self::adddate( $item, $interval, "MONTH", 2 );
		
	}
	
	public static function DATE_ADD_MONTH( $item, $interval ){
		
		return self::adddate( $item, $interval, "MONTH", 3 );
		
	}
	
	/*
	 Used where you have DATE_ADD with and first arg is a column name
	 MYSQL implementation: DATE_ADD( $date, INTERVAL $int DAY) 
	 DBO implementation: DBO::DATE_ADD_DAY_COL( $date, $int )
	 * */
	
	public static function DATE_ADD_DAY_COL( $item, $interval ){
		
		return self::adddate( $item, $interval, "DAY", 1 );
		
	}
	
	/*
	 Used where you have DATE_ADD with and first arg is a column name
	 MYSQL implementation: DATE_ADD( '$date', INTERVAL $int DAY) 
	 DBO implementation: DBO::DATE_ADD_DAY_VAL( $date, $int )
	 * */
	
	public static function DATE_ADD_DAY_VAL( $item, $interval ){
		
		return self::adddate( $item, $interval, "DAY", 2 );
		
	}
	
	/*
	 Used where you have DATE_ADD with and first arg is a function or you don't want to format the first arg
	 MYSQL implementation: DATE_ADD(DATE('{$date}'), INTERVAL $int DAY) 
	 DBO implementation: DBO::DATE_ADD_DAY( DATE('{$date}'), $int )
	 * */
	
	public static function DATE_ADD_DAY( $item, $interval ){
		
		return self::adddate( $item, $interval, "DAY", 3 );
		
	}
	
	public static function adddate( $item, $interval, $period, $field=true, $format="YYYYMMDD" ){
		
		self::use_database();
		$item = ( $field == 1 )? self::NF($item) : ( ( $field == 2 )? "'".$item."'" : $item ) ;
		$period = strtoupper($period);
		
		if( self::$dbtype == self::MYSQL ){
			return "DATE_ADD( ".$item.", INTERVAL ".$interval." ".$period." )";
		}
		
		if( self::$dbtype == self::ORACLE ){	
			
			$adddate = "";
			
			if( $period == "SECOND" ){
				//$adddate = "ADD_MONTHS( ".$item.", ".$interval." )";
			}
			
			if( $period == "MINUTE" ){
				//$adddate = "ADD_MONTHS( ".$item.", ".$interval." )";
			}
			
			if( $period == "HOUR" ){
				//$adddate = "ADD_MONTHS( ".$item.", ".$interval." )";
			}
			
			if( $period == "DAY" ){
				
				$interval = ( $interval > -1 )? " + ".$interval : $interval ;
				
				$adddate = "TO_DATE( ".$item.", '".$format."' ) ".$interval."";
			}
			
			if( $period == "MONTH" ){
				$adddate = "ADD_MONTHS( ".$item.", ".$interval." )";
			}
			
					
			return $adddate;
			
		}
		
	}
	
	public static function todate( $item, $field=true, $format="YYYYMMDD" ){
		
		self::use_database();
		$item = ( $field )? self::NF($item) : "'".$item."'" ;
		
		if( self::$dbtype == self::MYSQL ){
			return "DATE( ".$item." )";
		}
		
		if( self::$dbtype == self::ORACLE ){			
			return "TO_DATE( ".$item.", '".$format."' )";
		}
		
	}
	
	/*
	 mysql's DATE('VAL')
	 * */
	
	public static function DATE_VAL( $item, $format="YYYYMMDD" ){
		
		return self::todate( $item, false, $format );
		
	}
	
	/*
	 mysql's DATE(TableName.Column)
	 mysql's DATE(Column)
	 * */
	 
	public static function DATE_COL( $item, $format="YYYYMMDD" ){
		
		return self::todate( $item, true, $format );
		
	}
	
	// mysql's CONCAT(User.name, ' ', User.surname)
	// usage DBO::concat( DBO::TF("User.name"), "' '", DBO::TF("User.surname") )
	
	public static function concat(){
		
		self::use_database();
		$args = func_get_args();
		
		$concat = "";
		$func = "";
		
		if( self::$dbtype == self::MYSQL ){
			$concat = ", ";
			$func = "CONCAT";
		}
		
		if( self::$dbtype == self::ORACLE || self::$dbtype == self::SQLITE ){
			$concat = " || ";
		}
		
		$str = $func."( ".implode( $concat, $args )." )";
		
		
		return $str;
		
	}
	
	// MySQL IFNULL(Table.column,retunvalue)
	// usage DBO::ifnull()."(Table.column,retunvalue)"
	
	public static function ifnull($NOT=""){
		
		self::use_database();
		if( self::$dbtype == self::MYSQL || self::$dbtype == self::SQLITE ){
			return "IFNULL";
		}
		
		if( self::$dbtype == self::ORACLE ){
			return "NVL";
		}
		
	}
	
	public static function REGEXP( $column, $regexp, $NOT=false, $casesensitive=false ){
		
		self::use_database();
		// TODO casesensitive
		
		$NOT = ( $NOT )? "NOT" : "" ;
		
		if( self::$dbtype == self::SQLITE ){
			return ""; ### todo
		}
		
		if( self::$dbtype == self::MYSQL ){
			return "".$column." ".$NOT." REGEXP '".$regexp."'";
		}
		
		if( self::$dbtype == self::ORACLE ){
			return "".$NOT." REGEXP_LIKE ( ".$column.", '".$regexp."' )";
		}
		
	}
	
	/*
	 * Accepts 3 mandatory args as strings DBO::ifthen( $when, $then, $else )
	 * or
	 * Accepts 2 args as 
	 * $whenthen = array();
	 * $whenthen[] = array( $when1, $then1 );
	 * $whenthen[] = array( $when2, $then2 );
	 * ...
	 * $else as optional
	 * DBO::ifthen( $whenthen [, $else] )
	 * */
	
	public static function ifthen(){
		
		self::use_database();
		
		$endcase = "";
		$whenthen = array();
		$else = "";
		$args = func_get_args();
		
		if( self::$dbtype == self::MYSQL ){
			$endcase = " CASE";
		}
		
		if( isset($args[0]) && is_array($args[0]) && !empty($args[0]) ){
			
			foreach( $args[0] as $arg ){
				if( is_array($arg) && count($arg) == 2 ){
					$whenthen[] = "WHEN ".$arg[0]." THEN ".$arg[1];
				}
			}
			
			if( isset($args[1]) && is_string($args[1]) ){
				$else = "ELSE ".$args[1]."";
			}
			
		}elseif( count($args) == 3 && is_string($args[0]) && is_string($args[1]) && is_string($args[2]) ){
			
			$whenthen[] = "WHEN ".$args[0]." THEN ".$args[1];
			$else = "ELSE ".$args[2]."";
			
		}
		
		if( !empty($whenthen) ){
			return "CASE 
					  ".implode("\n",$whenthen)."
					  ".$else."
					END".$endcase;
		}
		
		return "";
		
	}
	
	public static function mysqllimitdeconstuct( $limit ){
		$limit = trim( str_replace( "limit", "", strtolower( $limit ) ) );
		if( preg_match( '/,/', $limit ) ){
			$limit = explode( ",", $limit );
			$limit[0] = trim( $limit[0] );
			$limit[1] = trim( $limit[1] );
			return array( "from"=>$limit[0], "count"=>$limit[1] );
		}else{
			return array( "from"=>0, "count"=>$limit );
		}
	}
	
	public static function limit( $from, $count=0 ){
		
		self::use_database();
		if( $count == 0 ){
			
			$count = $from;
			
			if( self::$dbtype == self::MYSQL || self::$dbtype == self::SQLITE ){
				return "LIMIT ".$count;
			}
			
			if( self::$dbtype == self::ORACLE ){
				return '"nums" <= '.$count;
			}
			
		}
		
		if( $count > 0 ){
			
			if( self::$dbtype == self::MYSQL || self::$dbtype == self::SQLITE ){
				return "LIMIT ".$from.", ".$count;
			}
			
			if( self::$dbtype == self::ORACLE ){
				$count = $from+$count;
				return '"nums" <= '.$count.' AND "nums" > '.$from.'';
			}
			
		}
				
	}	
	
	public static function formatlimit( $sql, $count = array( "from"=>0, "count"=>0 ) ){
		
		self::use_database();
		if( self::$dbtype == self::MYSQL || self::$dbtype == self::SQLITE ){
			return $sql." ".self::limit( $count['from'], $count['count'] );
		}
		
		if( self::$dbtype == self::ORACLE ){
			
			if( strtolower( substr( trim($sql), 0, strlen("select ") ) ) == "select " ){
				$sql1 = trim($sql);
				
				if( !preg_match( '/DBTBLLIMIT\.\*/', $sql1 ) ){
					if( preg_match( '/GROUP BY/', $sql1 ) ){
						$sql1 = "SELECT DBTBLLIMIT.* FROM( ".$sql1." ) DBTBLLIMIT";
					}
				}
				
				$sql2 = ltrim( $sql1, "select " );
				if( $sql1 == $sql2 ){
					$sql2 = ltrim( $sql1, "SELECT " );
				}
				$sql = 'SELECT ROWNUM "nums", '.$sql2;
				return 'SELECT * FROM ( '.$sql.' ) WHERE '.self::limit( $count['from'], $count['count'] );
			}
			
			return $sql;
			
		}
		
	}
	
	public static function SEQ_NEXT( $table ){	
		
		return self::SequenceValue( $table, true, true );
		
	}
	
	public static function SEQ_CUR( $table ){	
		
		return self::SequenceValue( $table );
		
	}
	
	public static function SEQ_SYNC( $table ){	
		
		return self::SequenceValue( $table, false, true );
		
	}
	
	public static function SequenceValue( $table, $next=false, $sync=false ){	
		
		$sql = "select last_number from user_sequences where sequence_name='".self::TF(strtoupper($table)).self::SEQ."'";
		
		$query = self::sql();
		$query->compiled_str( $sql );
		$query = self::exec_statement( $query );
		$query->fetch();
		$last_number = 0;
		
		if( $query->count() > 0 ){
			
			$last_number = $query->last_number;
			
			if( $sync ){
				
				$max = self::getMaxValue( $table );
				
				if( $last_number < $max ){
					
					$increment = $max - $last_number;					
					
					$sql = "declare
								max_id number;
								cur_seq number;
							begin
								cur_seq := ".$last_number.";
								max_id := ".$max.";
								while cur_seq < max_id
								loop
									select ".self::TF($table).self::SEQ.".nextval into cur_seq from dual;
								end loop;
									
							end;";
					
					
					$query = self::sql();
					$query->compiled_str( $sql, true );
					$query = self::exec_statement( $query );
					
					$sql = "select last_number from user_sequences where sequence_name='".self::TF(strtoupper($table)).self::SEQ."'";
		
					$query = self::sql();
					$query->compiled_str( $sql );
					$query = self::exec_statement( $query );
					$query->fetch();
					$last_number = $query->last_number;
				
				}
				
			}
			
			if( $next ){
				
				$sql = "SELECT ".self::TF($table).self::SEQ.".nextval AS last_number FROM dual";
				$query = self::sql();
				$query->compiled_str( $sql, true );
				$query = self::exec_statement( $query );
				$query->fetch();
				$last_number = $query->last_number;
				
			}			
			
		}
		
		return $last_number;
		
	}
				
	public static function getMaxValue( $table ){				
		
		$id = self::getPrimaryKey( $table );
		
		if( $id != "" ){
				
			$sql = "SELECT MAX(".$id.") AS MAXID FROM ".self::TF($table)."";
			
			$tbl = self::query( $sql, self::FET );
			return $tbl->MAXID;
			
		}
		
		return 0;
		
	}
	
	public static function getPrimaryKey( $table ){				
		
		self::use_database();
		
		if( self::$dbtype == self::SQLITE ){
			$sql = "PRAGMA table_info(".self::TF($table).")";
		}
		
		if( self::$dbtype == self::MYSQL ){
			$sql = "SHOW COLUMNS FROM ".self::TF($table)." WHERE `Key` = 'PRI'";
		}
		
		if( self::$dbtype == self::ORACLE ){
			
			$sql = "SELECT cols.column_name AS Field, cols.position, cons.status, cons.owner
				FROM all_constraints cons, all_cons_columns cols
				WHERE ( cols.table_name = '".self::TF($table)."' OR cols.table_name = '".self::TF(strtolower($table))."' OR cols.table_name = '".self::TF(strtoupper($table))."' )
				AND cons.constraint_type = 'P'
				AND cons.constraint_name = cols.constraint_name
				AND cons.owner = cols.owner
				ORDER BY cols.position";
			
		}
		
		$tbl = self::query( $sql, self::VOID );
		$key = "";
		
		if( self::$dbtype == self::SQLITE ){
			while( $tbl->fetch() ){
				if( $tbl->pk == '1' ){
					$key = $tbl->name;
					break;
				}
			}
		}
		
		if( self::$dbtype == self::MYSQL || self::$dbtype == self::ORACLE ){
			$tbl->fetch();
			$key = $tbl->Field;
		}
		
		return $key;	
		
	}

	public static function valueescape( $value ) {
		
		self::use_database();
		if( self::$dbtype == self::MYSQL ){
			return str_replace( "'", "\\'", $value );
		}
		
		if( self::$dbtype == self::ORACLE || self::$dbtype == self::SQLITE ){
			return str_replace( "'", "''", $value );
		}	
		
		return $name;	
			
	}
	
	public static function error( $tostring = true ){
		
		if( $tostring ){
			ob_start();
			print_r( self::$ERROR );
			$html = ob_get_contents(); 
			ob_end_clean();
			return $html;
		}
		
		return self::$ERROR;
		
	}
	
	public static function interpolateQuery( $query, $params ) {
		
		$keys = array();
		$id = md5($query);
		# build a regular expression for each parameter
		foreach ($params as $key => $value) {
			$query = str_replace( $key, "'".$params[$key]."'", $query );
		}

		self::$LASTQUERY[$id] = $query;
		
	}
	
	public static function exec_prepared( $sql ){
		$sql->output();
		if($sql->isVoid()){
			return self::query( $sql->prepared(), self::VOID, $sql->values() );
		}
		if($sql->isSelect()){
			return self::query( $sql->prepared(), self::ALL, $sql->values() );
		}
		if($sql->isDelete()){
			$return = self::query( $sql->prepared(), self::DEL, $sql->values() );
			return $return->count() > 0;
		}
		if($sql->isUpdate()){
			$return = self::query( $sql->prepared(), self::UPD, $sql->values() );
			return $return->count() > 0;
		}
		if($sql->isInsert()){
			$return = self::query( $sql->prepared(), self::INS, $sql->values() );
			return ( self::$dbtype == self::ORACLE )? $sql->__INSERTID__ : $return;
		}
	}
	
	public static function exec_statement( $sql ){
		if($sql->isVoid()){
			return self::query( $sql->output_str(), self::VOID );
		}
		if($sql->isSelect()){
			return self::query( $sql->output_str(), self::ALL );
		}
		if($sql->isDelete()){
			$return = self::query( $sql->output_str(), self::DEL );
			return $return->count() > 0;
		}
		if($sql->isUpdate()){
			$return = self::query( $sql->output_str(), self::UPD );
			return $return->count() > 0;
		}
		if($sql->isInsert()){
			$return = self::query( $sql->output_str(), self::INS );
			return ( self::$dbtype == self::ORACLE )? $sql->__INSERTID__ : $return;
		}
	}
	
	public static function count( $sql, $countWhat = false, $whereAddOnly = false, $str = false ){
		
		$sql->count( $countWhat, $whereAddOnly, $str );
		if( $str ){
			$sql = self::query( $sql->prepared(), self::FET, $sql->values() );
		}else{
			$sql = self::query( $sql->prepared(), self::FET );
		}
		return $sql->rowcount;
		
	}
	
	public static function startswith( $str, $prefix ){
		return strpos( $str, $prefix ) === 0;
	}
	
	public static function endswith( $str, $suffix ){
		$pos = strlen( $str ) - strlen( $suffix );
		return strrpos( $str, $suffix ) == $pos;
	}
	
}
