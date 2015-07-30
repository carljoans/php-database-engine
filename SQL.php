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
 
/**
 * @package PHP Database Engine
 */
 
class SQL{

	public $__TABLE__ = "";
	public $__IS_SELECT__ = false;
	public $__IS_UPDATE__ = false;
	public $__IS_INSERT__ = false;
	public $__IS_DELETE__ = false;
	public $__IS_VOID__ = false;
	public $__COLUMNS__ = array();
	public $__AVAILABLE_COLUMNS__ = array();
	public $__LINKS__ = array();
	public $__KEYS__ = array();
	public $__ROW__ = 0;
	public $__ROWARRAY__ = array();
	public $__ROWARRAY_ADD__ = array();
	public $__ORDERBY__ = "";
	public $__ORDERDIRECTION__ = "ASC";
	public $__LIMIT__ = array( "from"=>0, "count"=>0 );
	public $__SELECTADD__ = array();
	public $__SELECTADD_GROUPBY__ = array();
	public $__SELECTAS__ = array();
	public $__SELECTAS_GROUPBY__ = array();
	public $__OTHERSELECTAS__ = array();
	public $__OTHERSELECTAS_GROUPBY__ = array();
	public $__JOIN__ = array();
	public $__JOINWHERE__ = array();
	public $__JOINSELECTADD__ = array();
	public $__JOINON__ = 0;
	public $__JOINONADD__ = array();
	public $__GROUPBY__ = array();
	public $__WHERE__ = array();
	public $__WHERELOGIC__ = array();
	public $__WHERECOUNT__ = 1;
	public $__INSERTID__ = 0;
	public $__COUNT__ = false;
	public $__TABLEALIAS__ = "";
	public $__USE_DB = null;
	public $__STATEMENT__ = null;
	public $__VALUES__ = null;

	/**
	 * constructor
	 * 
	 * @param string $__TABLE__ optional name of the database table being queried. 
	 * Mandatory when inserting, updating, deleting or selecting from a table using
	 * __select(), __insert(), __update() or __delete(). These 3 functions should be
	 * used first when chaining additional values onto your query.
	 * i.e. $sql->__select("MyTable")->__where("x=4")->MyColumn("username123");
	 * The "where" or "join" functions make use of $__TABLE__ to build the sql statement or
	 * to query columns values in the table, etc.
	 * 
	 * @return new class
	 * 
	 */

	public function __construct( $__TABLE__ = "", $__USE_DB = "" ) {
		$this->__TABLE__ = $__TABLE__;
		$this->__USE_DB = $__USE_DB;
		$this->__checkTablename( $__TABLE__, false );
	}

	/**
	 * inaccessible functions
	 * 
	 * used to assign columns for use in an sql statment
	 * i.e. $sql->__selectFrom("MyTable")->MyColumn("username123");
	 * 
	 * @param string $name column name only
	 * @param string $arg column value only. Only the first argument will be used.
	 * 
	 * @return the class object
	 */
	 
	public function __call($name, $arg){
		if( count($arg) == 1 ){
			$this->__COLUMNS__[$name] = $arg[0];
		}
		return $this;
	}

	/**
	 * sets a hint to DATABASE and RESULTSET of the type of sql statement being used
	 * 
	 * @param void
	 * 
	 * @return void
	 */

	public function __setIsSelect(){
		$this->__IS_SELECT__ = true;
		$this->__IS_DELETE__ = false;
		$this->__IS_UPDATE__ = false;
		$this->__IS_INSERT__ = false;
		$this->__IS_VOID__ = false;
	}

	/**
	 * sets a hint to DATABASE and RESULTSET of the type of sql statement being used
	 * 
	 * @param void
	 * 
	 * @return void
	 */

	public function __setIsDelete(){
		$this->__IS_SELECT__ = false;
		$this->__IS_DELETE__ = true;
		$this->__IS_UPDATE__ = false;
		$this->__IS_INSERT__ = false;
		$this->__IS_VOID__ = false;
	}

	/**
	 * sets a hint to DATABASE and RESULTSET of the type of sql statement being used
	 * 
	 * @param void
	 * 
	 * @return void
	 */

	public function __setIsUpdate(){
		$this->__IS_SELECT__ = false;
		$this->__IS_DELETE__ = false;
		$this->__IS_UPDATE__ = true;
		$this->__IS_INSERT__ = false;
		$this->__IS_VOID__ = false;
	}

	/**
	 * sets a hint to DATABASE and RESULTSET of the type of sql statement being used
	 * 
	 * @param void
	 * 
	 * @return void
	 */

	public function __setIsInsert(){
		$this->__IS_SELECT__ = false;
		$this->__IS_DELETE__ = false;
		$this->__IS_UPDATE__ = false;
		$this->__IS_INSERT__ = true;
		$this->__IS_VOID__ = false;
	}

	/**
	 * sets a hint to DATABASE and RESULTSET of the type of sql statement being used
	 * 
	 * @param void
	 * 
	 * @return void
	 */

	public function __setIsVoid(){
		$this->__IS_SELECT__ = false;
		$this->__IS_DELETE__ = false;
		$this->__IS_UPDATE__ = false;
		$this->__IS_INSERT__ = false;
		$this->__IS_VOID__ = true;
	}

	/**
	 * return a hint of the type of sql statement being used
	 * 
	 * @param void
	 * 
	 * @return void
	 */

	public function __isSelect(){
		return $this->__IS_SELECT__;
	}

	/**
	 * return a hint of the type of sql statement being used
	 * 
	 * @param void
	 * 
	 * @return void
	 */

	public function __isDelete(){
		return $this->__IS_DELETE__;
	}

	/**
	 * return a hint of the type of sql statement being used
	 * 
	 * @param void
	 * 
	 * @return void
	 */

	public function __isUpdate(){
		return $this->__IS_UPDATE__;
	}

	/**
	 * return a hint of the type of sql statement being used
	 * 
	 * @param void
	 * 
	 * @return void
	 */

	public function __isInsert(){
		return $this->__IS_INSERT__;
	}

	/**
	 * return a hint of the type of sql statement being used
	 * 
	 * @param void
	 * 
	 * @return void
	 */

	public function __isVoid(){
		return $this->__IS_VOID__;
	}

	/**
	 * check if table name is set for  __selectFrom(), __deleteFrom(), __insertInto() and __updateSet()
	 * 
	 * @param void
	 * 
	 * @return void
	 */

	public function __checkTablename( $__TABLE__, $exit=true ){
		
		if( $__TABLE__ == "" && $this->__TABLE__ == "" && $exit ){
			print "Table name must be set.";
			exit;
		}
		
		if( $__TABLE__ != "" && $this->__TABLE__ == "" ){
			$this->__TABLE__ = $__TABLE__;
		}
		
		if( $this->__TABLE__ != "" && $this->__USE_DB != "" ){
			$this->__getColumns();
		}
		
	}

	/**
	 * creates context for the sql statement being constructed
	 * 
	 * @param string $__TABLE__ optional table name
	 * 
	 * @return the class object
	 */

	public function __selectFrom( $__TABLE__ = "" ){
		$this->__setIsSelect();
		$this->__checkTablename( $__TABLE__ );
		return $this;
	}

	/**
	 * creates context for the sql statement being constructed
	 * 
	 * @param string $__TABLE__ optional table name
	 * 
	 * @return the class object
	 */

	public function __deleteFrom( $__TABLE__ = "" ){
		$this->__setIsDelete();
		$this->__checkTablename( $__TABLE__ );
		return $this;
	}

	/**
	 * creates context for the sql statement being constructed
	 * 
	 * @param string $__TABLE__ optional table name
	 * 
	 * @return the class object
	 */

	public function __insertInto( $__TABLE__ = "" ){
		$this->__setIsInsert();
		$this->__checkTablename( $__TABLE__ );
		return $this;
	}

	/**
	 * creates context for the sql statement being constructed
	 * 
	 * @param string $__TABLE__ optional table name
	 * 
	 * @return the class object
	 */

	public function __updateSet( $__TABLE__ = "" ){
		$this->__setIsUpdate();
		$this->__checkTablename( $__TABLE__ );
		return $this;
	}

	/**
	 * adds options to be added to the where claus of the sql statement
	 * 
	 * @param string $add
	 * @param string $logic optional. either "OR" or "AND"
	 * 
	 * @return the class object
	 */

	public function __where( $add, $logic = "AND" ){
		
		if( !empty($add) ){
			$this->__WHERECOUNT__++;
			$this->__WHERE__[$this->__WHERECOUNT__] = $add;
			$this->__WHERELOGIC__[$this->__WHERECOUNT__] = strtoupper($logic);
		}
		
		return $this;
			
	}

	/**
	 * sets the database configuration name
	 * 
	 * Normally the name registered through DATABASE and also set by DATABASE.
	 * 
	 * @see DATABASE::sql($tablename)
	 * 
	 * @param string $__USE_DB the database configuration name
	 * 
	 * @return void
	 */

	public function __use_database($__USE_DB) {
		$this->__USE_DB = $__USE_DB;
		$this->__get_database_data();
	}

	/**
	 * creates the sql statement
	 * 
	 * @param boolean $str optional. sets whether or not to return the finalised sql string
	 * or the class object where $this->__STATEMENT__ and $this->__VALUES__ has been set to
	 * be passed as an sql statement with bindings.
	 * 
	 * @return the class object or sql string
	 */

	public function __output( $str=false ) {
		
		if( trim($this->__TABLE__) != "" ){
			if($this->__isSelect()){
				return $this->__select( $str );
			}
			if($this->__isDelete()){
				return $this->__delete( $str );
			}
			if($this->__isUpdate()){
				return $this->__update( $str );
			}
			if($this->__isInsert()){
				return $this->__insert( $str );
			}		
		}
		
		return ( $str )? $this->__STATEMENT__ : $this ;
				
	}

	/**
	 * creates a select statement
	 * 
	 * @param boolean $str optional. sets whether or not to set the sql as a finalised or a prepared string
	 * 
	 * @return the class object
	 */

	public function __select( $str=false ) {

		$DBOVAR = $this->__USE_DB;

		$columns = array();
		$columns_str = array();
		$columnvalues = array();
		
		$selectadd__ADD = ( count( $this->__SELECTADD__ ) > 0 );
		$selectadd__AS = ( count( $this->__SELECTAS__ ) > 0 || count( $this->__OTHERSELECTAS__ ) > 0 );
		$group_BY = ( count( $this->__GROUPBY__ ) > 0 );
		$select_count_set = isset( $this->__SELECTADD__['count'] );
		$select_count = ($select_count_set)?$this->__SELECTADD__['count']:"";
		
		if( !$group_BY ){
			$this->__SELECTADD_GROUPBY__ = array();
		}

		foreach( $this->__COLUMNS__ as $column=>$value ){
			$__iddot = $this->__TABLE__.count($columns);
			$columns[] = $DBOVAR::TF( $this->__TABLE__ ).".".$DBOVAR::NF( $column )." = :".$__iddot;
			$columns_str[] = $DBOVAR::TF( $this->__TABLE__ ).".".$DBOVAR::NF( $column )." = '".$value."'";
			$columnvalues[":".$__iddot] = $value;			
		}
		
		$orderby = ( $this->__ORDERBY__ != "" ) ? " ORDER BY ".$this->__ORDERBY__." ".$this->__ORDERDIRECTION__ : "";
		
		$select = "";
		
		$selectas = array();
		$joins = array();
		$join = "";		
		
		if( count( $this->__GROUPBY__ ) == 0 && count( $this->__SELECTADD_GROUPBY__ ) == 0 && ( $selectadd__ADD || $selectadd__AS ) ){
			
			if( count($this->__OTHERSELECTAS__) > 0 ){
				
				foreach( $this->__OTHERSELECTAS__ as $table=>$as ){
					$selectas[] = implode( ", ", $as );
				}
				$this->__selectAs();
				
			}
			
			if( count($this->__SELECTAS__) > 0 ){				
				$selectas[] = implode( ", ",  $this->__SELECTAS__ );
				$select = implode( ", ", $selectas );
			}
			
		}elseif( ( count( $this->__GROUPBY__ ) > 0 || count( $this->__SELECTADD_GROUPBY__ ) > 0 ) && !$selectadd__ADD ){
			
			if( count($this->__OTHERSELECTAS_GROUPBY__) > 0 ){
				
				foreach( $this->__OTHERSELECTAS_GROUPBY__ as $table=>$as ){
					$selectas[] = implode( ", ", $as );
				}
				$this->__selectAs();
			
			}
			
			if( count($this->__SELECTAS_GROUPBY__) > 0 ){
				$selectas[] = implode( ", ", $this->__SELECTAS_GROUPBY__ );
				$select = implode( ", ", $selectas );
			}			
			
		}	
		
		if( !$group_BY ){
			$this->__SELECTADD_GROUPBY__ = array();
		}	
		
		if( count( $this->__JOINWHERE__ ) > 0 ){
			
			foreach( $this->__JOINWHERE__ as $table=>$as ){
				foreach( $as as $column=>$value ){
					$__iddot = $this->__TABLE__.count($columns);
					$columns[] = $table.".".$DBOVAR::NF( $column )." = :".$__iddot;
					$columns_str[] = $table.".".$DBOVAR::NF( $column )." = '".$value."'";
					$columnvalues[":".$__iddot] = $value;
				}
			}
			
		}
		
		if( count( $this->__JOIN__ ) > 0 ){
			
			foreach( $this->__JOIN__ as $table=>$as ){
				$joins[] = $as;
			}			
			
		}
		
		$join = implode( chr(10), $joins );
		
		if( $this->__COUNT__ ){
			unset( $this->__SELECTADD__["count"] );
		}
		
		$selectadd = ( count( $this->__SELECTADD__ ) > 0 ) ? implode( ", ", $this->__SELECTADD__ ) : "";
		$selectadd = ( $select_count_set ) ? $select_count : $selectadd ;
		$select = ( $this->__COUNT__ || ( $selectadd__ADD && $group_BY ) ) ? "" : $select ;
		
		$__select = array();
		
		if( $select != "" ){
			$__select[] = $select;
		}
		
		if( $selectadd != "" ){
			$__select[] = " ".$selectadd;
		}
		
		if( !empty( $__select ) ){
			
			if( !$this->__COUNT__ ){
				$colcount = 0;
				
				foreach( $__select as $value ){
					
					if( preg_match( '/,/', $value ) ){
						$__cols = explode( ",", $value );
						
						foreach( $__cols as $column ){
							$column = trim( $column );
							$column = strtolower(str_replace( $DBOVAR::TF( $this->__TABLE__ ).".", "", $column )); 
							foreach( $this->__AVAILABLE_COLUMNS__ as $col ){
								$col = strtolower($col);
								if( $col == $column || DATABASE::startswith( $column, $DBOVAR::NF( $col )." " ) || ( $this->__is_function( $column ) && preg_match( '/'.$DBOVAR::NF( $col ).'/i', $column ) ) ){
									$colcount++;
								}
							}
						}
						
					}else{
						$column = trim( $value );
						$column = strtolower(str_replace( $DBOVAR::TF( $this->__TABLE__ ).".", "", $column )); 
						foreach( $this->__AVAILABLE_COLUMNS__ as $col ){
							$col = strtolower($col);
							if( $col == $column || DATABASE::startswith( $column, $DBOVAR::NF( $col )." " ) || ( $this->__is_function( $column ) && preg_match( '/'.$DBOVAR::NF( $col ).'/i', $column ) ) ){
								$colcount++;
							}
						}
					}
				}
				
				if( $colcount == 0 ){
					array_unshift($__select, $DBOVAR::TF( $this->__TABLE__ ).".*");
				} 
			} 
			
		}else{
			$__select[] = $DBOVAR::TF( $this->__TABLE__ ).".*";
		}
		
		$AND = "AND";
		
		if( count( $this->__WHERE__ ) > 0 ){
			
			$columns_ = array();
			
			if( $str ){
				if( !empty( $columns_str ) ){
					$columns_[] = "( ".implode( " AND ", $columns_str )." )";
				}
			}else{
				if( !empty( $columns ) ){
					$columns_[] = "( ".implode( " AND ", $columns )." )";
				}
			}
			
			$columns = array();
			
			foreach( $this->__WHERE__ as $id=>$column ){					
				$columns[$this->__WHERELOGIC__[$id]][] = "( ".$column." )";					
			}
			
			$columns_and = "";
			$columns_or = "";
			
			if( isset( $columns['AND'] ) ){
				$columns_[] = "( ".implode( " AND ", $columns['AND'] )." )";
			}
			
			if( isset( $columns['OR'] ) ){
				$AND = "OR";
				$columns_[] = "( ".implode( " OR ", $columns['OR'] )." )";
			}
			
			$columns = $columns_;
			
		}
		
		$this->__COUNT__ = false;
		
		if( $str ){
			$where = ( count( $columns_str ) > 0 ) ? " WHERE ".implode( " ".$AND." ", $columns_str ) : "";
		}else{
			$where = ( count( $columns ) > 0 ) ? " WHERE ".implode( " ".$AND." ", $columns ) : "";
		}
		
		$group__By = ( !$selectadd__ADD )? array_unique( array_merge( $this->__GROUPBY__,$this->__SELECTADD_GROUPBY__ ) ) : $this->__GROUPBY__ ;
		
		$groupby = ( count( $group__By ) > 0 ) ? " GROUP BY ".implode( ", ", $group__By ) : "";
		
		$sql = "SELECT ".implode( ",", $__select ).chr(10)." FROM ".$DBOVAR::TF($this->__TABLE__).chr(10).$join.chr(10).$where.$groupby.$orderby ;
		
		if( $this->__LIMIT__["from"] > 0 || $this->__LIMIT__["count"] > 0 ){
			$sql = $DBOVAR::formatlimit( $sql, $this->__LIMIT__ );
		}
		
		$this->__STATEMENT__ = $sql;
		$this->__VALUES__ = $columnvalues;
		$this->__fix_where();
		return $this;

	}

	/**
	 * creates an insert statement
	 * 
	 * @param boolean $str optional. sets whether or not to set the sql as a finalised or a prepared string
	 * 
	 * @return the class object
	 */

	public function __insert( $str=false ){	
		
		$DBOVAR = $this->__USE_DB;
		
		$insert_columns = array();
		$insert_preps = array();
		$insert_preps_str = array();
		$insert_columnvalues = array();

		foreach( $this->__COLUMNS__ as $column=>$value ){
			
			$insert_columns[] = $DBOVAR::NF( $column );				
				
			if( $this->__is_function( $value ) ){
				$insert_preps[] = $value;
				$insert_preps_str[] = $value;
			}else{
				$insert_preps[] = ":".$column;
				$insert_preps_str[] = "'".$value."'";
				$insert_columnvalues[":".$column] = $value ;
			}
			
		}
		$primaryKey_str = "";
		$primaryKeyValue_str = "";	
					
		$primaryKey = "";
		$primaryKeyValue = "";	
		$this->__INSERTID__ = 0;	
		
		$primaryKeySet = false;
				
		if( DATABASE::$databases[$this->__USE_DB]['type'] == DATABASE::ORACLE ){
			$primaryKey = $this->__getfirstkey( true );
		}
		
		foreach( $insert_columns as $columnname ){
			if( strtolower($primaryKey) == strtolower($columnname) ){
				$primaryKeySet = true;
			}
		}
		
		if( DATABASE::$databases[$this->__USE_DB]['type'] == DATABASE::ORACLE ){
			if( !$primaryKeySet ){
				
				$this->__INSERTID__ = $DBOVAR::SEQ_NEXT( $this->__tablename() );	
				$insert_columnvalues[":".$primaryKey] = $this->__INSERTID__;
				$primaryKeyValue = ":".$primaryKey;
				$primaryKeyValue_str = $primaryKeyValue.",";
				$primaryKey_str = $primaryKey.",";
				
			}
		}
		
		$__insert = ( $str )? $insert_preps_str : $insert_preps;
		
		$sql = "INSERT INTO ".$DBOVAR::TF($this->__TABLE__)." ( ::__ID__::, ".implode( ", ", $insert_columns )." ) VALUES ( ::__ID__VAL::, ".implode( ", ", $__insert )." )";
		
		$sql = str_replace( "::__ID__::,", $primaryKey_str, $sql );
		$sql = str_replace( "::__ID__VAL::,", $primaryKeyValue_str, $sql );
		
		$this->__STATEMENT__ = $sql;
		$this->__VALUES__ = $insert_columnvalues;
		$this->__fix_where();
		$this->__check_string_literal();
		
		return $this;
			
	}

	/**
	 * creates a delete statement
	 * 
	 * @param boolean $str optional. sets whether or not to set the sql as a finalised or a prepared string
	 * 
	 * @return the class object
	 */

	public function __delete($str=false){	
		
		$DBOVAR = $this->__USE_DB;
		$columns = array();
		$columnvalues = array();
		$columns_str = array();
		
		$primaryKey = $this->__getfirstkey( true );
		$primaryKey_found = false;
		foreach( $this->__COLUMNS__ as $column=>$value ){
			if( !empty($primaryKey) && strtolower($column) == strtolower($primaryKey) ){
				$primaryKey = $column;
				$primaryKey_found = true;
				break;
			}
		}
		
		if( $primaryKey_found ){
			$columns[] = $DBOVAR::NF( $primaryKey )." = :".$primaryKey;
			$columns_str[] = $DBOVAR::NF( $primaryKey )." = '".$this->__COLUMNS__[$primaryKey]."'";
			$columnvalues[":".$primaryKey] = $this->__COLUMNS__[$primaryKey];
		}else{
			foreach( $this->__COLUMNS__ as $column=>$value ){
				$columns[] = $DBOVAR::NF( $column )." = :".$column;
				$columns_str[] = $DBOVAR::NF( $column )." = '".$value."'";
				$columnvalues[":".$column] = $value;
			}
			
			if( count( $this->__WHERE__ ) > 0 ){
				foreach( $this->__WHERE__ as $column ){					
					$columns[] = "( ".$column." )";					
					$columns_str[] = "( ".$column." )";					
				}
			}
		}
		
		if( $str ){
			$where = ( count( $columns_str ) > 0 ) ? " WHERE ".implode( " AND ", $columns_str ) : "";
		}else{
			$where = ( count( $columns ) > 0 ) ? " WHERE ".implode( " AND ", $columns ) : "";
		}
		
		if( $where != "" ){
			
			$sql = "DELETE FROM ".$DBOVAR::TF($this->__TABLE__).$where ;
			$this->__STATEMENT__ = $sql;
			$this->__VALUES__ = $columnvalues;
			$this->__fix_where();
			
		}
		
		return $this;
			
	}

	/**
	 * creates an update statement
	 * 
	 * @param boolean $str optional. sets whether or not to set the sql 
	 * as a finalised or a prepared string
	 * 
	 * @return the class object
	 */

	public function __update($str=false){	
		
		$DBOVAR = $this->__USE_DB;
		$where_columns = array();
		$where_columns_str = array();
		$where_columnvalues = array();
		
		$update_columns = array();
		$update_columns_str = array();
		$update_columnvalues = array();
		
		$primaryKey = $DBOVAR::getPrimaryKey( $this->__tablename() );

		foreach( $this->__COLUMNS__ as $column=>$value ){
			if( strtolower($column) == strtolower($primaryKey) ){
				if( count( $this->__WHERE__ ) == 0 ){						
					$where_columns[] = $DBOVAR::NF( $column )." = :".$column;
					$where_columns_str[] = $DBOVAR::NF( $column )." = '".$value."'";
					$where_columnvalues[":".$column] = $value;
				}
			}else{
				
				if( $this->__is_function( $value ) ){
					$update_columns[] = $DBOVAR::NF( $column )." = ".$value;
				}else{
					$update_columns[] = $DBOVAR::NF( $column )." = :".$column;
					$update_columns_str[] = $DBOVAR::NF( $column )." = '".$value."'";
					$update_columnvalues[":".$column] = $value ;
				}				
				
			}
		}

		if( count( $this->__WHERE__ ) == 0 ){
			
			foreach( $where_columnvalues as $key=>$column ){
				$update_columnvalues[$key] = $column;
			}
			
			if( $str ){
				$where = ( count( $where_columns_str ) > 0 ) ? " WHERE ".implode( " AND ", $where_columns_str ) : "";
				$update_columns = $update_columns_str;
			}else{
				$where = ( count( $where_columns ) > 0 ) ? " WHERE ".implode( " AND ", $where_columns ) : "";
			}
			
		}else{
			
			$where = " WHERE ".implode( " AND ", $this->__WHERE__ );
			
		}
		
		$data = 0;
		
		if( $where != "" ){
			$sql = "UPDATE ".$DBOVAR::TF($this->__TABLE__)." SET ".implode( ", ", $update_columns )." ".$where ;
			$this->__STATEMENT__ = $sql;
			$this->__VALUES__ = $update_columnvalues;
			$this->__fix_where();
			$this->__check_string_literal();
		}
			
	}

	/**
	 * returns the last id inserted after an insert statement
	 * 
	 * @param void
	 * 
	 * @return integer
	 */

	public function __lastInsertId(){
		return $this->__INSERTID__;
	}

	/**
	 * checks string literal
	 * 
	 * checks whether the character length does not exceed the maximum 
	 * for the database platform string literal limit and prepares the
	 * bound values accordingly.
	 * 
	 * @param void
	 * 
	 * @return void
	 */

	public function __check_string_literal(){
		
		$str_sql = $this->__STATEMENT__;
		$values = $this->__VALUES__;
		
		## ORACLE VARS
		$declaretxt = array();
		$valuestxt = array();
		$valuesbound = array();
		$replacetxt = array();
		$subvaluestxt = array();
		
		## MYSQL VARS
		//$declaretxt = array();
		//$valuestxt = array();
		//$valuesbound = array();
		//$replacetxt = array();
		//$subvaluestxt = array();
		
		foreach( $values as $bindname=>$bindvalue ){

			if( DATABASE::$databases[$this->__USE_DB]['type'] == DATABASE::MYSQL ){
				
				if( strlen( $bindvalue ) > 65535 ){
					## TODO
				}						
				
			}
			
			if( DATABASE::$databases[$this->__USE_DB]['type'] == DATABASE::ORACLE ){
				
				if( strlen( $bindvalue ) > 4000 ){					
					
					$bindbasename = "BIND".ltrim( $bindname, ":" )."0";
					$declaretxt[] = $bindbasename;
					$replacetxt[$bindname] = $bindbasename;
					
					if( strlen( $bindvalue ) > 30000 ){
						
						$subvaluestxt_ = array();
						
						$a = 1;
						$substr = "";
						for( $i=0; $i<strlen( $bindvalue ); $i++ ){
							
							$substr .= substr( $bindvalue, $i, 1 );
							if( $i>0 && $i%30000==0 ){
								
								$valuestxt[ ":".$bindbasename.$a ] = $substr;
								$subvaluestxt_[] = $bindbasename.$a;
								$declaretxt[] = $bindbasename.$a;
								
								$substr = "";
								$a++;
								
							}
							
						}
						
						$subvaluestxt[ ":".$bindbasename ] = implode( " || ", $subvaluestxt_ );
						
					}else{
						
						$valuestxt[ ":".$bindbasename ] = $bindvalue;
						
					}							
					
				}	
				
			}
			
		}
		
		if( DATABASE::$databases[$this->__USE_DB]['type'] == DATABASE::ORACLE && !empty( $replacetxt ) ){
			
			$__sql = "DECLARE\n".
			implode( " CLOB;\n", $declaretxt )." CLOB;\n".
			"BEGIN\n";
			
			foreach( $declaretxt as $txt ){
				$__sql .=  $txt." := :".$txt.";\n";
			}
			
			if( !empty( $subvaluestxt ) ){
				foreach( $subvaluestxt as $bindvar=>$bindval ){
					$__sql .=  $bindvar." := ".$bindval.";\n";
				}	
			}
			
			foreach( $replacetxt as $bindname=>$bindbasename ){
				unset( $values[$bindname] );
				$str_sql = str_replace( $bindname, ":".$bindbasename, $str_sql );
			}
			
			$values = array_merge( $subvaluestxt, $valuestxt );
			
			$__sql .=  $str_sql."\n";
			$__sql .= ";\nEND;";
			
			$str_sql = $__sql;
			
		}
		
		$this->__STATEMENT__ = $str_sql;
		$this->__VALUES__ = $values;
		
	}

	/**
	 * returns the sql statement with bindings
	 * 
	 * @param void
	 * 
	 * @return string
	 */

	public function __prepared(){
		return $this->__STATEMENT__;
	}

	/**
	 * returns the sql bindings
	 * 
	 * @param void
	 * 
	 * @return associative array
	 */

	public function __values(){
		return $this->__VALUES__;
	}

	/**
	 * returns the finalised sql statement
	 * 
	 * @param void
	 * 
	 * @return string
	 */

	public function __output_str(){
		$this->__output( true );
		return $this->__STATEMENT__;
	}

	/**
	 * returns the finalised sql statement
	 * 
	 * @see __count()
	 * 
	 * @param boolean $countWhat optional
	 * @param boolean $whereAddOnly optional 
	 * 
	 * @return string
	 */

	public function __count_str( $countWhat = false, $whereAddOnly = false ){
		$this->__count( $countWhat, $whereAddOnly, true );
		return $this->__STATEMENT__;
	}

	/**
	 * returns the finalised sql statement
	 * 
	 * @param boolean $countWhat optional
	 * @param boolean $whereAddOnly optional 
	 * @param boolean $str optional. sets whether or not to set the sql 
	 * as a finalised or a prepared string 
	 * 
	 * @return string or the class object
	 */

	public function __count( $countWhat = false, $whereAddOnly = false, $str=false ){
		
		$DBOVAR = $this->__USE_DB;
		if (is_bool($countWhat)) {
			$whereAddOnly = $countWhat;
		}
		
		$table = $this->__TABLE__;
		$key = $this->__getfirstkey();        
		
		// support distinct on default keys.
		$countWhat = (strtoupper($countWhat) == 'DISTINCT') ? "DISTINCT ".$DBOVAR::TF( $table.".".$key ) : $countWhat;        
		$countWhat = is_string($countWhat) ? $countWhat : $DBOVAR::TF( $table.".".$key );
		$as = "rowcount";
		$this->__COUNT__ = true;
		$this->__selectAdd( "COUNT( ".$countWhat." ) AS ".$DBOVAR::NF( $as ) ); 
		
		return $this->__output($str);
		
	}

	/**
	 * fetches column data
	 * 
	 * @param void 
	 * 
	 * @return void
	 */

	private function __get_database_data(){
		$DBOVAR = $this->__USE_DB;
		if( DATABASE::$databases[$this->__USE_DB]['type'] == DATABASE::ORACLE ){
			
			if( !isset(DATABASE::$database_data[$this->__USE_DB]) ){
				
				$db_data = $DBOVAR::query( "select * from all_tab_columns where lower(owner)='".strtolower(DATABASE::$databases[$this->__USE_DB]['user'])."'", DATABASE::VOID );
				DATABASE::$database_data[$this->__USE_DB] = array();
				
				while( $db_data->fetch() ){
					
					$entry = array();
					foreach( $db_data as $column=>$value ){
						$entry[strtolower($column)] = $value ;
					}
					
					if( !isset( DATABASE::$database_data[$this->__USE_DB][strtolower($entry["table_name"])] ) ){
						DATABASE::$database_data[$this->__USE_DB][strtolower($entry["table_name"])] = array();
					}
					
					DATABASE::$database_data[$this->__USE_DB][strtolower($entry["table_name"])][] = $entry;
					
				}
				
			}
						
		}
	}

	/**
	 * returns column type
	 * 
	 * @param string $column 
	 * 
	 * @return string
	 */

	private function __find_column_datatype_ora( $column ){
		
		if( DATABASE::$databases[$this->__USE_DB]['type'] == DATABASE::ORACLE ){
			
			if( preg_match( '/\./', $column ) ){
				$column = explode( ".", $column );
				$column = array_reverse($column);
				$column = $column[0];
			}
			
			foreach( DATABASE::$database_data[$this->__USE_DB][strtolower( $this->__prependTableName( $this->__TABLE__ ) )] as $row ){
				
				if( strtolower( $row['column_name'] ) == strtolower( $column ) ){
					return $row['data_type'];
				}
				
			}
		}
		return "";
		
	}

	/**
	 * returns all columns in the Table
	 * 
	 * @param void  
	 * 
	 * @return array
	 */

	public function __getColumns(){
		
		$DBOVAR = $this->__USE_DB;
		
		$this->__AVAILABLE_COLUMNS__ = ( $this->__tablename() != "" && empty($this->__AVAILABLE_COLUMNS__) ) ? $DBOVAR::getColumns( $this->__tablename() ) : $this->__AVAILABLE_COLUMNS__ ;
		
		return $this->__AVAILABLE_COLUMNS__;
		
	}

	/**
	 * returns first key
	 * 
	 * @param boolean $primary optional. sets whether to return
	 * the primary key or one specified by the user through $__KEYS__  
	 * 
	 * @return string
	 */

	public function __getfirstkey( $primary=false ){
		
		$DBOVAR = $this->__USE_DB;
		$primaryKey = $DBOVAR::getPrimaryKey( $this->__tablename() );
		
		if( $primaryKey != "" || $primary ){
			return $primaryKey;
		}
		
		if( count( $this->__KEYS__ ) > 0 ){
			return $this->__KEYS__[0];
		}
		
		return "";
		
	}

	/**
	 * this function is redundant. 
	 * 
	 * @ignore
	 * @param string $name  
	 * 
	 * @return string
	 */

	public function __replaceTableName( $name ){
		
		$tempname = $name;
		
		if( strpos( $this->__PREPEND_TABLENAME, $name ) === 0 ){
			$name = ltrim( $name, $this->__PREPEND_TABLENAME );
		}
		
		$column = "";
		
		if( preg_match( '/\./', $name ) ){
			$name = explode(".", $name);
			$column = ".".$name[1];
			$name = $name[0];
		}
		
		if( strpos( $this->__PREPEND_TABLENAME, $tempname ) === 0 ){
			$name = $this->__PREPEND_TABLENAME.$name;
		}
		
		return $name.$column;
		
	}

	/**
	 * adds the defined prefix to table name 
	 * 
	 * @param string $name Table name 
	 * 
	 * @return string
	 */

	public function __prependTableName( $name ){
		
		$name = $this->__replaceTableName( $name );
		
		if( $this->__PREPEND_TABLENAME != "" ){
			if( strpos( $this->__PREPEND_TABLENAME, $name ) !== 0 ){
				$name = $this->__PREPEND_TABLENAME.$name;
			}
		}
		
		return $name;
		
	}

	/**
	 * sets the sort order for returned results
	 * 
	 * @param string $orderby optional
	 * 
	 * @return the class object
	 */
		
	public function __orderBy( $orderby="" ){
		
		$DBOVAR = $this->__USE_DB;
		$orderby = str_replace( array( "`",'"' ), "", $orderby );
		$orderby = trim( $orderby );
		$direction = "ASC";

		if( preg_match( '/DESC/i', $orderby ) ){
			$direction = "DESC";
		}
		
		$orderby = trim( preg_replace( '/'.$direction.'/i', "", $orderby ) );
		
		if( preg_match( '/,/i', $orderby ) ){
			
			$orderby = explode( ",", $orderby );
			
			$columns = array();
			
			foreach( $orderby as $order_by ){
				$columns[] = preg_match('/\./',$order_by) ? $DBOVAR::TF(trim($order_by)) : $DBOVAR::NF(trim($order_by));
			}
			
			$orderby = implode( ",", $columns );
			
		}else{
			$orderby = preg_match('/\./',$orderby) ? $DBOVAR::TF(trim($orderby)) : $DBOVAR::NF(trim($orderby));
		}

		$this->__ORDERDIRECTION__ = $direction;
		$this->__ORDERBY__ = $orderby;
		
		return $this;
		
	}

	/**
	 * sets the limit for returned results 
	 * 
	 * @param integer $from if $count=0 $from is count else it is offset
	 * @param integer $count optional
	 * 
	 * @return the class object
	 */

	public function __limit( $from, $count=0 ){
		
		$this->__LIMIT__["from"] = $from; 
		$this->__LIMIT__["count"] = $count; 
		
		return $this;
				
	}

	/**
	 * adds columns to be returned in a select statement 
	 * 
	 * FOR Oracle:
	 * If using a groupBy, the selectAdd must contain the same columns 
	 * as the groupBy. The developer must see to this manually.
	 * If only using the selectAdd, go nuts.
	 * If using only a groupBy and not using the selectAdd SQL will 
	 * automatically balance out the selected columns with the groupBy.
	 * 
	 * @param string $add 
	 * 
	 * @return the class object
	 */

	public function __selectAdd( $add = "" ){	
		
		if( $add != "" ){
			if( $this->__COUNT__ ){
				$this->__SELECTADD__["count"] = $add;
			}else{
				$this->__SELECTADD__[] = $add;
			}
		}
		
		return $this;
			
	}

	/**
	 * adds GROUP BY clause to sql statement 
	 * 
	 * FOR Oracle:
	 * If using a selectAdd the groupBy must contain the same columns 
	 * as the selectAdd. The developer must see to this manually.
	 * If only using a groupBy and not using the selectAdd SQL will 
	 * automatically balance out the selected columns with the groupBy.
	 * 
	 * @param string $groupBy 
	 * @param boolean $selectAdd used privately by SQL
	 * 
	 * @return the class object
	 */

	public function __groupBy( $groupBy = "", $selectAdd=false ){	
		
		$DBOVAR = $this->__USE_DB;
		$group__BY = ($selectAdd)? "__SELECTADD_GROUPBY__" : "__GROUPBY__" ;
		
		
		if( $groupBy != "" ){
			if( preg_match( '/,/', $groupBy ) ){
				$groupBy = explode( ",", $groupBy );
				$this->$group__BY = array_merge ( $groupBy, $this->$group__BY );
			}else{
				if( !preg_match( '/\./', $groupBy ) ){
					$groupBy = $DBOVAR::TF($this->__tablename()).".".$groupBy;
				}
				$array = $this->$group__BY;
				$array[] = $groupBy;
				$this->$group__BY = $array;
			}
			$this->$group__BY = array_unique($this->$group__BY);
		}
		
		return $this;
			
	}

	/**
	 * adds columns to be returned in a select statement 
	 * 
	 * @param string $groupBy 
	 * 
	 * @return void
	 */

	private function __add_selectAs_groupBy( $groupBy ){
		
		if( count( $this->__SELECTADD_GROUPBY__ ) > 0 ){
			$group = $this->__SELECTADD_GROUPBY__;
			foreach( $group as $element  ){
				if( $groupBy == $element ){
					return;
				}
			}
		}
		
		$this->__groupBy( $groupBy, true );
		
	}

	/**
	 * creates column aliases in a select statement 
	 * 
	 * @param string $table optional 
	 * @param string $format optional 
	 * 
	 * @return the class object
	 */

	public function __selectAs( $table=NULL, $format="%s" ){
		
		$DBOVAR = $this->__USE_DB;
		if( $table == NULL ){
			
			foreach( $this->__AVAILABLE_COLUMNS__ as $column ){
				
				$table_column_pair = $DBOVAR::TF( $this->__TABLE__ ).".".$DBOVAR::NF( $column );
				$column_name = ( strtolower( $this->__find_column_datatype_ora( $column ) ) == "clob" )? "to_char(".$table_column_pair.")" : $table_column_pair;
				$this->__SELECTAS__[$column] = $table_column_pair." AS ".$DBOVAR::NF( sprintf( $format , $column ) );
				
				if( DATABASE::$databases[$this->__USE_DB]['type'] == DATABASE::ORACLE ){
					$this->__SELECTAS_GROUPBY__[$column] = $column_name." AS ".$DBOVAR::NF( sprintf( $format , $column ) );
					$this->__add_selectAs_groupBy( $column_name );		
				}
				
			}
			
		}else if( $table instanceof SQL  ){
			
			$format = ( $format == "%s" )? $table->__TABLE__."_%s" : $format;
			
			$this->__OTHERSELECTAS__[$table->__TABLE__] = array();
			$this->__OTHERSELECTAS_GROUPBY__[$table->__TABLE__] = array();
			
			foreach( $table->__AVAILABLE_COLUMNS__ as $column ){
				
				$table_column_pair = $DBOVAR::TF( $table->__TABLE__ ).".".$DBOVAR::NF( $column );
				$column_name = ( strtolower( $this->__find_column_datatype_ora( $column ) ) == "clob" )? "to_char(".$table_column_pair.")" : $table_column_pair;
				
				$this->__OTHERSELECTAS__[$table->__TABLE__][] = $table_column_pair." AS ".$DBOVAR::NF( sprintf( $format , $column ) );	
				if( DATABASE::$databases[$this->__USE_DB]['type'] == DATABASE::ORACLE ){
					$this->__OTHERSELECTAS_GROUPBY__[$table->__TABLE__][] = $column_name." AS ".$DBOVAR::NF( sprintf( $format , $column ) );
					$this->__add_selectAs_groupBy( $column_name );				 
				}	
				
			}
			
		}else{
			
			$this->__TABLEALIAS__ = $table;
			
			foreach( $this->__AVAILABLE_COLUMNS__ as $column ){
				$table_column_pair = $DBOVAR::TF( $table ).".".$DBOVAR::NF( $column );
				$column_name = ( strtolower( $this->__find_column_datatype_ora( $column ) ) == "clob" )? "to_char(".$table_column_pair.")" : $table_column_pair;
				$this->__SELECTAS__[$column] = $table_column_pair." AS ".$DBOVAR::NF( sprintf( $format , $column ) );	
				if( DATABASE::$databases[$this->__USE_DB]['type'] == DATABASE::ORACLE ){
					$this->__SELECTAS_GROUPBY__[$column] = $column_name." AS ".$DBOVAR::NF( sprintf( $format , $column ) );
					$this->__add_selectAs_groupBy( $DBOVAR::TF( $table ).".".$DBOVAR::NF( $column ) );			
				}	 			
			}
			
		}
		
		return $this;
		
	}

	/**
	 * this function is not used. 
	 * 
	 * @ignore
	 */

	public function __joinOnAdd( $tablename, $join, $joinType='INNER' ){
		$DBOVAR = $this->__USE_DB;
		$this->__JOINONADD__[$tablename] = $joinType." JOIN ".$DBOVAR::NF($tablename)." ON ".$join;
	}

	/**
	 * creates link between tables 
	 * 
	 * @param string $table the table to link to 
	 * @param string $column optional only to pass $table as array 
	 * @param string $linkto optional only to pass $table as array 
	 * 
	 * @return the class object
	 */

	public function __createLink( $table, $column=null, $linkto=null ){
		
		if( $table instanceof SQL  ){
			$this->__LINKS__[$linkto] = $table->__TABLE__.":".$column;
		}elseif( is_array( $table ) ){
			$this->__LINKS__ = $table;
		}
		
		return $this;
		
	}

	/**
	 * checks whether a column exists in a table
	 * 
	 * __AVAILABLE_COLUMNS__ is set by the user. This a list of all
	 * columns in the table.
	 * 
	 * @param string $field
	 * 
	 * @return boolean
	 */

	public function __hasColumn( $field ) {

		return in_array( trim( $field ), $this->__AVAILABLE_COLUMNS__ );
		
	}

	/**
	 * adds join clause to sql statement
	 * 
	 * @param string $table
	 * @param string $joinType optional
	 * @param string $tableAs optional
	 * @param string $columnAs optional
	 * 
	 * @return the class object
	 */
		
	public function __joinAdd( $table, $joinType='INNER', $tableAs="", $columnAs="" ){	
		$DBOVAR = $this->__USE_DB;		
		
		if( $table instanceof SQL  ){	
			
			$link_found = false;
			$table->__selectAs( $tableAs );
			$this->__JOINSELECTADD__[$table->__TABLE__] = $table->__SELECTAS__;
			if( count( $table->__JOINSELECTADD__ ) > 0 ){
				foreach( $table->__JOINSELECTADD__ as $jsat=>$joinselectadd ){
					$this->__JOINSELECTADD__[$jsat] = $joinselectadd;
				}
			}
			
			if( count( $table->__GROUPBY__ ) > 0 ){
				foreach( $table->__GROUPBY__ as $groupBy ){
					$this->__add_selectAs_groupBy( $groupBy );
				}
			}
			
			$__use_table = ( $table->__TABLEALIAS__ != "" )? $table->__TABLEALIAS__ : $table->__TABLE__;
			$__use_table_formatted = ( $__use_table == $table->__TABLE__ )? $DBOVAR::TF( $__use_table ) : $DBOVAR::NF( $__use_table );
			
			if( isset( $this->__JOINONADD__[$table->__TABLE__] ) ){
				
				$link_found = true;
					
				$__table = $table->__TABLE__;
				$__column = "joinonadd";
				$__link = $this->__JOINON__;
				$this->__JOINON__++;
				
				
				$this->__JOIN__[$__table.".".$__column.".".$__link] = $this->__JOINONADD__[$table->__TABLE__];
				
				foreach( $table->__COLUMNS__ as $column=>$value ){
					$column = strtolower($column);
					$this->__JOINWHERE__[ $__use_table_formatted ][$column] = $value;					
				}
				
				if( count( $table->__JOIN__ ) > 0 ){
					foreach( $table->__JOIN__ as $joinname=>$join ){
						$this->__JOIN__[$joinname] = $join;
					}
				}
				
				if( count( $table->__JOINWHERE__ ) > 0 ){
					foreach( $table->__JOINWHERE__ as $joinwherename=>$joinwhere ){
						$this->__JOINWHERE__[$joinwherename] = $joinwhere;
					}
				}				
				
			}	
			
			if( !$link_found && $tableAs != "" && $columnAs != "" ){
				
				if( $this->__hasColumn( $columnAs ) && $table->__hasColumn( $columnAs ) ){
					
					$link_found = true;
					
					$__table = $table->__TABLE__;
					$__column = $columnAs;
					$__link = $columnAs;
					
					$this->__JOIN__[$__table.".".$__column.".".$__link] = $joinType." JOIN ".$DBOVAR::TF( $__table )." ".( ( $tableAs != $__table )? $DBOVAR::NF( $tableAs ) : "" )." ON ".$DBOVAR::TF( $this->__TABLE__ ).".".$DBOVAR::NF( $__link )." = ". $__use_table_formatted .".".$DBOVAR::NF( $__column )."";
					
					foreach( $table->__COLUMNS__ as $column=>$value ){
						$column = strtolower($column);
						$this->__JOINWHERE__[$__use_table_formatted][$column] = $value;						
					}
					
					if( count( $table->__JOIN__ ) > 0 ){
						foreach( $table->__JOIN__ as $joinname=>$join ){
							$this->__JOIN__[$joinname] = $join;
						}
					}
					
					if( count( $table->__JOINWHERE__ ) > 0 ){
						foreach( $table->__JOINWHERE__ as $joinwherename=>$joinwhere ){
							$this->__JOINWHERE__[$joinwherename] = $joinwhere;
						}
					}
					
				}
				
				
			}			
			
			
			if( !$link_found ){
				
				foreach( $table->__LINKS__ as $columnname=>$link  ){
		
					$linkers = $link;
		
					if( is_array( $linkers ) ){
						
						continue;					
						
					}
					
					$links = array($linkers);
			
					if( preg_match( '/,/', $linkers ) ){
						$links = explode( ",", $linkers );
					}
					foreach( $links as $linked ){
						
						$linked = explode( ":", $linked );
						$table__ = trim( $linked[0] );
						$table_link = trim( $linked[1] );
						
						if( $table__ == $this->__TABLE__ ){
							
							$__table = $table->__TABLE__;
							$__column = $columnname;
							$__link = $table_link;
							
							
							$this->__JOIN__[$__table.".".$__column.".".$__link] = $joinType." JOIN ".$DBOVAR::TF( $__table )." ".( ( $tableAs != "" && $tableAs != $__table )? $DBOVAR::NF( $tableAs ) : "" )." ON ".$DBOVAR::TF( $this->__TABLE__ ).".".$DBOVAR::NF( $__link )." = ". $__use_table_formatted .".".$DBOVAR::NF( $__column )."";
							
							foreach( $table->__COLUMNS__ as $column=>$value ){
								$column = strtolower($column);
								$this->__JOINWHERE__[ $__use_table_formatted ][$column] = $value;
							}
							
							if( count( $table->__JOIN__ ) > 0 ){
								foreach( $table->__JOIN__ as $joinname=>$join ){
									$this->__JOIN__[$joinname] = $join;
								}
							}
							
							if( count( $table->__JOINWHERE__ ) > 0 ){
								foreach( $table->__JOINWHERE__ as $joinwherename=>$joinwhere ){
									$this->__JOINWHERE__[$joinwherename] = $joinwhere;
								}
							}			
							
							$link_found = true;
							
							break;
							
						}	
						
					}
					
				}		
				
			}		
			
			if( !$link_found ){
				
				foreach( $this->__LINKS__ as $columnname=>$link  ){
		
					$linkers = $link;
		
					if( is_array( $linkers ) ){
						
						continue;					
						
					}
					
					$links = array($linkers);
			
					if( preg_match( '/,/', $linkers ) ){
						$links = explode( ",", $linkers );
					}
					
					foreach( $links as $linked ){
						
						$linked = explode( ":", $linked );
						$table__ = trim( $linked[0] );
						$table_link = trim( $linked[1] );
						
						if( $table__ == $table->__TABLE__ ){
							
							$__table = $table->__TABLE__;
							$__column = $table_link;
							$__link = $columnname;
							
							
							$this->__JOIN__[$__table.".".$__column.".".$__link] = $joinType." JOIN ".$DBOVAR::TF( $__table )." ".( ( $tableAs != "" && $tableAs != $__table )? $DBOVAR::NF( $tableAs ) : "" )." ON ".$DBOVAR::TF( $this->__TABLE__ ).".".$DBOVAR::NF( $__link )." = ". $__use_table_formatted.".".$DBOVAR::NF( $__column )."";
							
							foreach( $table->__COLUMNS__ as $column=>$value ){
								$column = strtolower($column);
								$this->__JOINWHERE__[$__use_table_formatted][$column] = $value;
							}
							
							if( count( $table->__JOIN__ ) > 0 ){
								foreach( $table->__JOIN__ as $joinname=>$join ){
									$this->__JOIN__[$joinname] = $join;
								}
							}
							
							if( count( $table->__JOINWHERE__ ) > 0 ){
								foreach( $table->__JOINWHERE__ as $joinwherename=>$joinwhere ){
									$this->__JOINWHERE__[$joinwherename] = $joinwhere;
								}
							}					
							
							break;
							
						}	
						
					}
					
				}		
				
			}
			
		}
		
		return $this;
			
	}

	/**
	 * returns the table name being used
	 * 
	 * @param void
	 * 
	 * @return string
	 */		

	public function __tablename(){
		##TODO USING TABLENAME
		return $this->__TABLE__;
	}

	/**
	 * checks whether the bound value is a function
	 * 
	 * @param string $value
	 * 
	 * @return boolean
	 */		

	public function __is_function( $value ){
		
		$is_function = false;
		foreach( DATABASE::$functions[DATABASE::$databases[$this->__USE_DB]['type']] as $functions ){
			if( preg_match('/'.$functions.'\(/i', $value ) ){
				$is_function = true;
			}
		}
		return $is_function;
	}

	/**
	 * accepts a finalised sql statement and prepares SQL for DATABASE
	 * 
	 * @param string $sql
	 * @param boolean $void set whether or not the statement is
	 * an insert, update, delete or typical select statement,
	 * i.e. select * from dual
	 * 
	 * @return the class object
	 */	

	public function __compiled_str( $sql, $void=false ) {
		
		$this->__setIsVoid();
		
		if( is_bool($void) ){
			
			if( !$void ){
				
				if( DATABASE::startswith( strtoupper($sql), "SELECT" ) ){
					$this->__setIsSelect();
				}
				
				if( DATABASE::startswith( strtoupper($sql), "UPDATE" ) ){
					$this->__setIsUpdate();
				}
				
				if( DATABASE::startswith( strtoupper($sql), "DELETE" ) ){
					$this->__setIsDelete();
				}
				
				if( DATABASE::startswith( strtoupper($sql), "INSERT" ) ){
					$this->__setIsInsert();
				}
				
			}
			
		}elseif( is_int($void) ){
			
			if( $void === DATABASE::ALL ){
				$this->__setIsSelect();
			}
			
			if( $void === DATABASE::UPD ){
				$this->__setIsUpdate();
			}
			
			if( $void === DATABASE::DEL ){
				$this->__setIsDelete();
			}
			
			if( $void === DATABASE::INS ){
				$this->__setIsInsert();
			}
			
		}
		
		
		$this->__STATEMENT__ = $sql;
		$this->__fix_where();
		return $this;
		
	}

	/**
	 * fixes the user's where 1 mistake
	 * 
	 * usually something that would surface in __compiled_str where
	 * the user uses WHERE 1 as default when building an sql statement.
	 * example:
	 * $sql = "select * from table where 1";
	 * 
	 * if( $s == 2 ){
	 * 		$sql .= " and column = 3";
	 * }
	 * 
	 * etc. WHERE 1 works fine in MySQL, but not in Oracle.
	 * 
	 * @param void
	 * 
	 * @return void
	 */	

	public function __fix_where(){		
		
		$str = $this->__STATEMENT__;
		preg_match_all( '/WHERE(\s+)1(\s+)AND/i', $str, $matches );
		
		if( count($matches) > 0 ){
			if( DATABASE::$databases[$this->__USE_DB]['type'] == DATABASE::ORACLE ){
				foreach ( $matches[0] as $i=>$match ) {
					$str = str_replace( $matches[0][$i], "WHERE", $str );
				}
			}
		}
		$this->__STATEMENT__ = $str;
		
	}

}
