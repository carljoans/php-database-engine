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

class SQL {

	public $__table__ = "";
	public $__is_select__ = false;
	public $__is_update__ = false;
	public $__is_insert__ = false;
	public $__is_delete__ = false;
	public $__is_void__ = false;
	public $__columns__ = array();
	public $__available_columns__ = array();
	public $__links__ = array();
	public $__keys__ = array();
	public $__row__ = 0;
	public $__rowarray__ = array();
	public $__rowarray_add__ = array();
	public $__orderby__ = "";
	public $__orderdirection__ = "ASC";
	public $__limit__ = array( "from"=>0, "count"=>0 );
	public $__selectadd__ = array();
	public $__selectadd_groupby__ = array();
	public $__selectas__ = array();
	public $__selectas_groupby__ = array();
	public $__otherselectas__ = array();
	public $__otherselectas_groupby__ = array();
	public $__join__ = array();
	public $__joinwhere__ = array();
	public $__joinselectadd__ = array();
	public $__joinon__ = 0;
	public $__joinonadd__ = array();
	public $__groupby__ = array();
	public $__where__ = array();
	public $__wherelogic__ = array();
	public $__wherecount__ = 1;
	public $__insertid__ = 0;
	public $__count__ = false;
	public $__tablealias__ = "";
	public $__use_db = null;
	public $__statement__ = null;
	public $__values__ = null;

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
		$this->__table__ = $__TABLE__;
		$this->__use_db = $__USE_DB;
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
			$this->__columns__[$name] = $arg[0];
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
		$this->__is_select__ = true;
		$this->__is_delete__ = false;
		$this->__is_update__ = false;
		$this->__is_insert__ = false;
		$this->__is_void__ = false;
	}

	/**
	 * sets a hint to DATABASE and RESULTSET of the type of sql statement being used
	 *
	 * @param void
	 *
	 * @return void
	 */

	public function __setIsDelete(){
		$this->__is_select__ = false;
		$this->__is_delete__ = true;
		$this->__is_update__ = false;
		$this->__is_insert__ = false;
		$this->__is_void__ = false;
	}

	/**
	 * sets a hint to DATABASE and RESULTSET of the type of sql statement being used
	 *
	 * @param void
	 *
	 * @return void
	 */

	public function __setIsUpdate(){
		$this->__is_select__ = false;
		$this->__is_delete__ = false;
		$this->__is_update__ = true;
		$this->__is_insert__ = false;
		$this->__is_void__ = false;
	}

	/**
	 * sets a hint to DATABASE and RESULTSET of the type of sql statement being used
	 *
	 * @param void
	 *
	 * @return void
	 */

	public function __setIsInsert(){
		$this->__is_select__ = false;
		$this->__is_delete__ = false;
		$this->__is_update__ = false;
		$this->__is_insert__ = true;
		$this->__is_void__ = false;
	}

	/**
	 * sets a hint to DATABASE and RESULTSET of the type of sql statement being used
	 *
	 * @param void
	 *
	 * @return void
	 */

	public function __setIsVoid(){
		$this->__is_select__ = false;
		$this->__is_delete__ = false;
		$this->__is_update__ = false;
		$this->__is_insert__ = false;
		$this->__is_void__ = true;
	}

	/**
	 * return a hint of the type of sql statement being used
	 *
	 * @param void
	 *
	 * @return void
	 */

	public function __isSelect(){
		return $this->__is_select__;
	}

	/**
	 * return a hint of the type of sql statement being used
	 *
	 * @param void
	 *
	 * @return void
	 */

	public function __isDelete(){
		return $this->__is_delete__;
	}

	/**
	 * return a hint of the type of sql statement being used
	 *
	 * @param void
	 *
	 * @return void
	 */

	public function __isUpdate(){
		return $this->__is_update__;
	}

	/**
	 * return a hint of the type of sql statement being used
	 *
	 * @param void
	 *
	 * @return void
	 */

	public function __isInsert(){
		return $this->__is_insert__;
	}

	/**
	 * return a hint of the type of sql statement being used
	 *
	 * @param void
	 *
	 * @return void
	 */

	public function __isVoid(){
		return $this->__is_void__;
	}

	/**
	 * check if table name is set for  __selectFrom(), __deleteFrom(), __insertInto() and __updateSet()
	 *
	 * @param void
	 *
	 * @return void
	 */

	public function __checkTablename( $__table__, $exit=true ){

		if( $__table__ == "" && $this->__table__ == "" && $exit ){
			print "Table name must be set.";
			exit;
		}

		if( $__table__ != "" && $this->__table__ == "" ){
			$this->__table__ = $__table__;
		}

		if( $this->__table__ != "" && $this->__use_db != "" ){
			$this->__columns__ = array_unique( $this->__available_columns__ );
		}

		if( $this->__table__ != "" && $this->__use_db != "" ){
			$this->__getColumns();
		}

	}

	/**
	 * creates context for the sql statement being constructed
	 *
	 * @param string $__table__ optional table name
	 *
	 * @return the class object
	 */

	public function __selectFrom( $__table__ = "" ){
		$this->__setIsSelect();
		$this->__checkTablename( $__table__ );
		return $this;
	}

	/**
	 * creates context for the sql statement being constructed
	 *
	 * @param string $__table__ optional table name
	 *
	 * @return the class object
	 */

	public function __deleteFrom( $__table__ = "" ){
		$this->__setIsDelete();
		$this->__checkTablename( $__table__ );
		return $this;
	}

	/**
	 * creates context for the sql statement being constructed
	 *
	 * @param string $__table__ optional table name
	 *
	 * @return the class object
	 */

	public function __insertInto( $__table__ = "" ){
		$this->__setIsInsert();
		$this->__checkTablename( $__table__ );
		return $this;
	}

	/**
	 * creates context for the sql statement being constructed
	 *
	 * @param string $__table__ optional table name
	 *
	 * @return the class object
	 */

	public function __updateSet( $__table__ = "" ){
		$this->__setIsUpdate();
		$this->__checkTablename( $__table__ );
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
			$this->__wherecount__++;
			$this->__where__[$this->__wherecount__] = $add;
			$this->__wherelogic__[$this->__wherecount__] = strtoupper($logic);
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
	 * @param string $__use_db the database configuration name
	 *
	 * @return void
	 */

	public function __use_database($__use_db) {
		$this->__use_db = $__use_db;
		$this->__get_database_data();
	}

	/**
	 * creates the sql statement
	 *
	 * @param boolean $str optional. sets whether or not to return the finalised sql string
	 * or the class object where $this->__statement__ and $this->__values__ has been set to
	 * be passed as an sql statement with bindings.
	 *
	 * @return the class object or sql string
	 */

	public function __output( $str=false ) {

		if( trim($this->__table__) != "" ){
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

		return ( $str )? $this->__statement__ : $this ;

	}

	/**
	 * creates a select statement
	 *
	 * @param boolean $str optional. sets whether or not to set the sql as a finalised or a prepared string
	 *
	 * @return the class object
	 */

	public function __select( $str=false ) {
		$DBOVAR = $this->__use_db;

		$columns = array();
		$columns_str = array();
		$columnvalues = array();

		$selectadd__ADD = ( count( $this->__selectadd__ ) > 0 );
		$selectadd__AS = ( count( $this->__selectas__ ) > 0 || count( $this->__otherselectas__ ) > 0 );
		$group_BY = ( count( $this->__groupby__ ) > 0 );
		$select_count_set = isset( $this->__selectadd__['count'] );
		$select_count = ($select_count_set)?$this->__selectadd__['count']:"";

		if( !$group_BY ){
			$this->__selectadd_groupby__ = array();
		}

		foreach( $this->__columns__ as $column=>$value ){
			$__iddot = $this->__table__.count($columns);
			$columns[] = $DBOVAR::TF( $this->__table__ ).".".$DBOVAR::NF( $column )." = :".$__iddot;
			$columns_str[] = $DBOVAR::TF( $this->__table__ ).".".$DBOVAR::NF( $column )." = '".$value."'";
			$columnvalues[":".$__iddot] = $value;
		}

		$orderby = ( $this->__orderby__ != "" ) ? " ORDER BY ".$this->__orderby__." ".$this->__orderdirection__ : "";

		$select = "";

		$selectas = array();
		$joins = array();
		$join = "";

		if( count( $this->__groupby__ ) == 0 && count( $this->__selectadd_groupby__ ) == 0 && ( $selectadd__ADD || $selectadd__AS ) ){
			if( count($this->__otherselectas__) > 0 ){
				foreach( $this->__otherselectas__ as $table=>$as ){
					$selectas[] = implode( ", ", $as );
				}
				$this->__selectAs();
			}

			if( count($this->__selectas__) > 0 ){
				$selectas[] = implode( ", ",  $this->__selectas__ );
				$select = implode( ", ", $selectas );
			}
		}elseif( ( count( $this->__groupby__ ) > 0 || count( $this->__selectadd_groupby__ ) > 0 ) && !$selectadd__ADD ){
			if( count($this->__otherselectas_groupby__) > 0 ){
				foreach( $this->__otherselectas_groupby__ as $table=>$as ){
					$selectas[] = implode( ", ", $as );
				}
				$this->__selectAs();
			}

			if( count($this->__selectas_groupby__) > 0 ){
				$selectas[] = implode( ", ", $this->__selectas_groupby__ );
				$select = implode( ", ", $selectas );
			}
		}

		if( !$group_BY ){
			$this->__selectadd_groupby__ = array();
		}

		if( count( $this->__joinwhere__ ) > 0 ){
			foreach( $this->__joinwhere__ as $table=>$as ){
				foreach( $as as $column=>$value ){
					$__iddot = $this->__table__.count($columns);
					$columns[] = $table.".".$DBOVAR::NF( $column )." = :".$__iddot;
					$columns_str[] = $table.".".$DBOVAR::NF( $column )." = '".$value."'";
					$columnvalues[":".$__iddot] = $value;
				}
			}
		}

		if( count( $this->__join__ ) > 0 ){
			foreach( $this->__join__ as $table=>$as ){
				$joins[] = $as;
			}
		}

		$join = implode( chr(10), $joins );

		if( $this->__count__ ){
			unset( $this->__selectadd__["count"] );
		}

		$selectadd = ( count( $this->__selectadd__ ) > 0 ) ? implode( ", ", $this->__selectadd__ ) : "";
		$selectadd = ( $select_count_set ) ? $select_count : $selectadd ;
		$select = ( $this->__count__ || ( $selectadd__ADD && $group_BY ) ) ? "" : $select ;

		$__select = array();

		if( $select != "" ){
			$__select[] = $select;
		}

		if( $selectadd != "" ){
			$__select[] = " ".$selectadd;
		}

		if( !empty( $__select ) ){
			if( !$this->__count__ ){
				$colcount = 0;

				foreach( $__select as $value ){
					if( preg_match( '/,/', $value ) ){
						$__cols = explode( ",", $value );

						foreach( $__cols as $column ){
							$column = trim( $column );
							$column = strtolower(str_replace( $DBOVAR::TF( $this->__table__ ).".", "", $column ));
							foreach( $this->__available_columns__ as $col ){
								$col = strtolower($col);
								if( $col == $column || DATABASE::startswith( $column, $DBOVAR::NF( $col )." " ) || ( $this->__is_function( $column ) && preg_match( '/'.$DBOVAR::NF( $col ).'/i', $column ) ) ){
									$colcount++;
								}
							}
						}

					}else{
						$column = trim( $value );
						$column = strtolower(str_replace( $DBOVAR::TF( $this->__table__ ).".", "", $column ));
						foreach( $this->__available_columns__ as $col ){
							$col = strtolower($col);
							if( $col == $column || DATABASE::startswith( $column, $DBOVAR::NF( $col )." " ) || ( $this->__is_function( $column ) && preg_match( '/'.$DBOVAR::NF( $col ).'/i', $column ) ) ){
								$colcount++;
							}
						}
					}
				}

				if( $colcount == 0 ){
					array_unshift($__select, $DBOVAR::TF( $this->__table__ ).".*");
				}
			}

		}else{
			$__select[] = $DBOVAR::TF( $this->__table__ ).".*";
		}

		$AND = "AND";

		if( count( $this->__where__ ) > 0 ){
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

			foreach( $this->__where__ as $id=>$column ){
				$columns[$this->__wherelogic__[$id]][] = "( ".$column." )";
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

		$this->__count__ = false;

		if( $str ){
			$where = ( count( $columns_str ) > 0 ) ? " WHERE ".implode( " ".$AND." ", $columns_str ) : "";
		}else{
			$where = ( count( $columns ) > 0 ) ? " WHERE ".implode( " ".$AND." ", $columns ) : "";
		}

		$group__By = ( !$selectadd__ADD )? array_unique( array_merge( $this->__groupby__,$this->__selectadd_groupby__ ) ) : $this->__groupby__ ;

		$groupby = ( count( $group__By ) > 0 ) ? " GROUP BY ".implode( ", ", $group__By ) : "";

		$sql = "SELECT ".implode( ",", $__select ).chr(10)." FROM ".$DBOVAR::TF($this->__table__).chr(10).$join.chr(10).$where.$groupby.$orderby ;

		if( $this->__limit__["from"] > 0 || $this->__limit__["count"] > 0 ){
			$sql = $DBOVAR::formatlimit( $sql, $this->__limit__ );
		}

		$this->__statement__ = $sql;
		$this->__values__ = $columnvalues;
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
		$DBOVAR = $this->__use_db;

		$insert_columns = array();
		$insert_preps = array();
		$insert_preps_str = array();
		$insert_columnvalues = array();

		foreach( $this->__columns__ as $column=>$value ){

			$insert_columns[] = $DBOVAR::NF( $column );

			if( $this->__is_function( $value ) ){
				$insert_preps[] = $value;
				$insert_preps_str[] = $value;
			}else{
				$insert_preps[] = ":".$DBOVAR::rename_bindname( $column );
				$insert_preps_str[] = "'".$value."'";
				$insert_columnvalues[":".$DBOVAR::rename_bindname( $column )] = $value ;
			}

		}
		$primaryKey_str = "";
		$primaryKeyValue_str = "";

		$primaryKey = "";
		$primaryKeyValue = "";
		$this->__insertid__ = 0;

		$primaryKeySet = false;

		if( DATABASE::$databases[$this->__use_db]['type'] == DATABASE::ORACLE ){
			$primaryKey = $this->__getfirstkey( true );
		}

		foreach( $insert_columns as $columnname ){
			if( strtolower($primaryKey) == strtolower($columnname) ){
				$primaryKeySet = true;
			}
		}

		if( DATABASE::$databases[$this->__use_db]['type'] == DATABASE::ORACLE ){
			if( !$primaryKeySet ){

				$this->__insertid__ = $DBOVAR::SEQ_NEXT( $this->__tablename() );
				$insert_columnvalues[":".$primaryKey] = $this->__insertid__;
				$primaryKeyValue = ":".$primaryKey;
				$primaryKeyValue_str = $primaryKeyValue.",";
				$primaryKey_str = $primaryKey.",";

			}
		}

		$__insert = ( $str )? $insert_preps_str : $insert_preps;

		$sql = "INSERT INTO ".$DBOVAR::TF($this->__table__)." ( ::__ID__::, ".implode( ", ", $insert_columns )." ) VALUES ( ::__ID__VAL::, ".implode( ", ", $__insert )." )";

		$sql = str_replace( "::__ID__::,", $primaryKey_str, $sql );
		$sql = str_replace( "::__ID__VAL::,", $primaryKeyValue_str, $sql );

		$this->__statement__ = $sql;
		$this->__values__ = $insert_columnvalues;
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

		$DBOVAR = $this->__use_db;
		$columns = array();
		$columnvalues = array();
		$columns_str = array();

		$primaryKey = $this->__getfirstkey( true );
		$primaryKey_found = false;
		foreach( $this->__columns__ as $column=>$value ){
			if( !empty($primaryKey) && strtolower($column) == strtolower($primaryKey) ){
				$primaryKey = $column;
				$primaryKey_found = true;
				break;
			}
		}

		if( $primaryKey_found ){
			$columns[] = $DBOVAR::NF( $primaryKey )." = :".$primaryKey;
			$columns_str[] = $DBOVAR::NF( $primaryKey )." = '".$this->__columns__[$primaryKey]."'";
			$columnvalues[":".$primaryKey] = $this->__columns__[$primaryKey];
		}else{
			foreach( $this->__columns__ as $column=>$value ){
				$columns[] = $DBOVAR::NF( $column )." = :".$DBOVAR::rename_bindname( $column );
				$columns_str[] = $DBOVAR::NF( $column )." = '".$value."'";
				$columnvalues[":".$DBOVAR::rename_bindname( $column )] = $value;
			}

			if( count( $this->__where__ ) > 0 ){
				foreach( $this->__where__ as $column ){
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

			$sql = "DELETE FROM ".$DBOVAR::TF($this->__table__).$where ;
			$this->__statement__ = $sql;
			$this->__values__ = $columnvalues;
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
		$DBOVAR = $this->__use_db;
		$where_columns = array();
		$where_columns_str = array();
		$where_columnvalues = array();

		$update_columns = array();
		$update_columns_str = array();
		$update_columnvalues = array();

		$primaryKey = $DBOVAR::getPrimaryKey( $this->__tablename() );

		foreach( $this->__columns__ as $column=>$value ){
			if( strtolower($column) == strtolower($primaryKey) ){
				if( count( $this->__where__ ) == 0 ){
					$where_columns[] = $DBOVAR::NF( $column )." = :".$DBOVAR::rename_bindname( $column );
					$where_columns_str[] = $DBOVAR::NF( $column )." = '".$value."'";
					$where_columnvalues[":".$DBOVAR::rename_bindname( $column )] = $value;
				}
			}else{

				if( $this->__is_function( $value ) ){
					$update_columns[] = $DBOVAR::NF( $column )." = ".$value;
				}else{
					$update_columns[] = $DBOVAR::NF( $column )." = :".$DBOVAR::rename_bindname( $column );
					$update_columns_str[] = $DBOVAR::NF( $column )." = '".$value."'";
					$update_columnvalues[":".$DBOVAR::rename_bindname( $column )] = $value ;
				}

			}
		}

		if( count( $this->__where__ ) == 0 ){

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

			$where = " WHERE ".implode( " AND ", $this->__where__ );

		}

		$data = 0;

		if( $where != "" ){
			$sql = "UPDATE ".$DBOVAR::TF($this->__table__)." SET ".implode( ", ", $update_columns )." ".$where ;
			$this->__statement__ = $sql;
			$this->__values__ = $update_columnvalues;
			$this->__fix_where();
			$this->__check_string_literal();
		}

		return $this;
	}

	/**
	 * returns the last id inserted after an insert statement
	 *
	 * @param void
	 *
	 * @return integer
	 */

	public function __lastInsertId(){
		return $this->__insertid__;
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
		$DBOVAR = $this->__use_db;

		$str_sql = $this->__statement__;
		$values = $this->__values__;

		$___values = $values;

		## ORACLE VARS
		$declaretxt = array();
		$valuestxt = array();
		$valuesbound = array();
		$replacetxt = array();
		$subvaluestxt = array();

		foreach( $___values as $bindname=>$bindvalue ){

			if( DATABASE::$databases[$this->__use_db]['type'] == DATABASE::MYSQL ){

				if( strlen( $bindvalue ) > 65535 ){
					## TODO
				}

			}

			if( DATABASE::$databases[$this->__use_db]['type'] == DATABASE::ORACLE ){

				if( strlen( $bindvalue ) > 4000 ){

					if( DATABASE::$databases[$this->__use_db]['use_descriptor'] ){

						unset( $values[$bindname] );
						$values["clob:".$bindname] = $bindvalue;

					}else{

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

		}

		if( DATABASE::$databases[$this->__use_db]['type'] == DATABASE::ORACLE && !empty( $replacetxt ) ){

			$__sql = "DECLARE\n".
			implode( " CLOB;\n", $declaretxt )." CLOB;\n".
			"BEGIN\n";

			foreach( $declaretxt as $txt ){
				$__sql .=  $txt." := '".$DBOVAR::valueescape( $valuestxt[ ":".$txt ] )."';\n";
			}

			if( !empty( $subvaluestxt ) ){
				foreach( $subvaluestxt as $bindvar=>$bindval ){
					$__sql .=  $bindvar." := ".$bindval.";\n";
				}
			}

			foreach( $replacetxt as $bindname=>$bindbasename ){
				unset( $values[$bindname] );
				$str_sql = str_replace( $bindname, $bindbasename, $str_sql );
			}

			foreach( $values as $bindname=>$bindbasename ){
				unset( $values[$bindname] );
				$str_sql = str_replace( $bindname, "'".$DBOVAR::valueescape( $bindbasename )."'", $str_sql );
			}

			$values = NULL;

			$__sql .=  $str_sql."\n";
			$__sql .= ";\nEND;";

			$str_sql = $__sql;

		}

		$this->__statement__ = $str_sql;
		$this->__values__ = $values;

	}

	/**
	 * returns the sql statement with bindings
	 *
	 * @param void
	 *
	 * @return string
	 */

	public function __prepared(){
		return $this->__statement__;
	}

	/**
	 * returns the sql bindings
	 *
	 * @param void
	 *
	 * @return associative array
	 */

	public function __values(){
		return $this->__values__;
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
		return $this->__statement__;
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
		return $this->__statement__;
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

		$DBOVAR = $this->__use_db;
		if (is_bool($countWhat)) {
			$whereAddOnly = $countWhat;
		}

		$table = $this->__table__;

		// support distinct on default keys.
		$countWhat = (strtoupper($countWhat) == 'DISTINCT') ? "DISTINCT ".$DBOVAR::TF( $table.".".$this->__getfirstkey() ) : $countWhat;
		$countWhat = is_string($countWhat) ? $countWhat : $DBOVAR::TF( $table.".".$this->__getfirstkey() );
		$as = "rowcount";
		$this->__count__ = true;
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
		$DBOVAR = $this->__use_db;
		if( DATABASE::$databases[$this->__use_db]['type'] == DATABASE::ORACLE ){

			if( !isset(DATABASE::$database_data[$this->__use_db]) ){

				$db_data = $DBOVAR::query( "select * from all_tab_columns where lower(owner)='".strtolower(DATABASE::$databases[$this->__use_db]['user'])."'", DATABASE::VOID );
				DATABASE::$database_data[$this->__use_db] = array();

				while( $db_data->fetch() ){

					$entry = array();
					foreach( $db_data as $column=>$value ){
						$entry[strtolower($column)] = $value ;
					}

					if( !isset( DATABASE::$database_data[$this->__use_db][strtolower($entry["table_name"])] ) ){
						DATABASE::$database_data[$this->__use_db][strtolower($entry["table_name"])] = array();
					}

					DATABASE::$database_data[$this->__use_db][strtolower($entry["table_name"])][] = $entry;

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

		if( DATABASE::$databases[$this->__use_db]['type'] == DATABASE::ORACLE ){

			if( preg_match( '/\./', $column ) ){
				$column = explode( ".", $column );
				$column = array_reverse($column);
				$column = $column[0];
			}

			foreach( DATABASE::$database_data[$this->__use_db][strtolower( $this->__prependTableName( $this->__table__ ) )] as $row ){

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

		$DBOVAR = $this->__use_db;

		if( !empty(DATABASE::$databases[$DBOVAR]['tablecolumns']) && isset( DATABASE::$databases[$DBOVAR]['tablecolumns'][$this->__tablename()] ) ){
			$this->__available_columns__ = DATABASE::$databases[$DBOVAR]['tablecolumns'][$this->__tablename()] ;
		}

		if( $this->__tablename() != "" && empty($this->__available_columns__) ){
			$this->__available_columns__ = $DBOVAR::getColumns( $this->__tablename() ) ;
		}

		return $this->__available_columns__;

	}

	/**
	 * returns first key
	 *
	 * @param boolean $primary optional. sets whether to return
	 * the primary key or one specified by the user through $__keys__
	 *
	 * @return string
	 */

	public function __getfirstkey( $primary=false ){

		if( count( $this->__keys__ ) > 0 ){
			return $this->__keys__[0];
		}

		$DBOVAR = $this->__use_db;
		$primaryKey = $DBOVAR::getPrimaryKey( $this->__tablename() );

		if( $primaryKey != "" || $primary ){
			return $primaryKey;
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

		if( strpos( $this->__prepend_tablename, $name ) === 0 ){
			$name = ltrim( $name, $this->__prepend_tablename );
		}

		$column = "";

		if( preg_match( '/\./', $name ) ){
			$name = explode(".", $name);
			$column = ".".$name[1];
			$name = $name[0];
		}

		if( strpos( $this->__prepend_tablename, $tempname ) === 0 ){
			$name = $this->__prepend_tablename.$name;
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

		if( $this->__prepend_tablename != "" ){
			if( strpos( $this->__prepend_tablename, $name ) !== 0 ){
				$name = $this->__prepend_tablename.$name;
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

		$DBOVAR = $this->__use_db;
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

		$this->__orderdirection__ = $direction;
		$this->__orderby__ = $orderby;

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

		$this->__limit__["from"] = $from;
		$this->__limit__["count"] = $count;

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
			if( $this->__count__ ){
				$this->__selectadd__["count"] = $add;
			}else{
				$this->__selectadd__[] = $add;
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

		$DBOVAR = $this->__use_db;
		$group__BY = ($selectAdd)? "__selectadd_groupby__" : "__groupby__" ;


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

		if( count( $this->__selectadd_groupby__ ) > 0 ){
			$group = $this->__selectadd_groupby__;
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

		$DBOVAR = $this->__use_db;
		if( $table == NULL ){

			foreach( $this->__available_columns__ as $column ){

				$table_column_pair = $DBOVAR::TF( $this->__table__ ).".".$DBOVAR::NF( $column );
				$column_name = ( strtolower( $this->__find_column_datatype_ora( $column ) ) == "clob" )? "to_char(".$table_column_pair.")" : $table_column_pair;
				$this->__selectas__[$column] = $table_column_pair." AS ".$DBOVAR::NF( sprintf( $format , $column ) );

				if( DATABASE::$databases[$this->__use_db]['type'] == DATABASE::ORACLE ){
					$this->__selectas_groupby__[$column] = $column_name." AS ".$DBOVAR::NF( sprintf( $format , $column ) );
					$this->__add_selectAs_groupBy( $column_name );
				}

			}

		}else if( $table instanceof SQL  ){

			$format = ( $format == "%s" )? $table->__table__."_%s" : $format;

			$this->__otherselectas__[$table->__table__] = array();
			$this->__otherselectas_groupby__[$table->__table__] = array();

			foreach( $table->__available_columns__ as $column ){

				$table_column_pair = $DBOVAR::TF( $table->__table__ ).".".$DBOVAR::NF( $column );
				$column_name = ( strtolower( $this->__find_column_datatype_ora( $column ) ) == "clob" )? "to_char(".$table_column_pair.")" : $table_column_pair;

				$this->__otherselectas__[$table->__table__][] = $table_column_pair." AS ".$DBOVAR::NF( sprintf( $format , $column ) );
				if( DATABASE::$databases[$this->__use_db]['type'] == DATABASE::ORACLE ){
					$this->__otherselectas_groupby__[$table->__table__][] = $column_name." AS ".$DBOVAR::NF( sprintf( $format , $column ) );
					$this->__add_selectAs_groupBy( $column_name );
				}

			}

		}else{

			$this->__tablealias__ = $table;

			foreach( $this->__available_columns__ as $column ){
				$table_column_pair = $DBOVAR::NF( $table ).".".$DBOVAR::NF( $column );
				$column_name = ( strtolower( $this->__find_column_datatype_ora( $column ) ) == "clob" )? "to_char(".$table_column_pair.")" : $table_column_pair;
				$this->__selectas__[$column] = $table_column_pair." AS ".$DBOVAR::NF( sprintf( $format , $column ) );
				if( DATABASE::$databases[$this->__use_db]['type'] == DATABASE::ORACLE ){
					$this->__selectas_groupby__[$column] = $column_name." AS ".$DBOVAR::NF( sprintf( $format , $column ) );
					$this->__add_selectAs_groupBy( $DBOVAR::NF( $table ).".".$DBOVAR::NF( $column ) );
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
		$DBOVAR = $this->__use_db;
		$this->__joinonadd__[$tablename] = $joinType." JOIN ".$DBOVAR::NF($tablename)." ON ".$join;
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
			$this->__links__[$linkto] = $table->__table__.":".$column;
		}elseif( is_array( $table ) ){
			$this->__links__ = $table;
		}

		return $this;

	}

	/**
	 * checks whether a column exists in a table
	 *
	 * __available_columns__ is set by the user. This a list of all
	 * columns in the table.
	 *
	 * @param string $field
	 *
	 * @return boolean
	 */

	public function __hasColumn( $field ) {

		return in_array( trim( $field ), $this->__available_columns__ );

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
		$DBOVAR = $this->__use_db;

		if( $table instanceof SQL  ){

			$link_found = false;
			$table->__selectAs( $tableAs );
			$this->__joinselectadd__[$table->__table__] = $table->__selectas__;
			if( count( $table->__joinselectadd__ ) > 0 ){
				foreach( $table->__joinselectadd__ as $jsat=>$joinselectadd ){
					$this->__joinselectadd__[$jsat] = $joinselectadd;
				}
			}

			if( count( $table->__groupby__ ) > 0 ){
				foreach( $table->__groupby__ as $groupBy ){
					$this->__add_selectAs_groupBy( $groupBy );
				}
			}

			$__use_table = ( $table->__tablealias__ != "" )? $table->__tablealias__ : $table->__table__;
			$__use_table_formatted = ( $__use_table == $table->__table__ )? $DBOVAR::TF( $__use_table ) : $DBOVAR::NF( $__use_table );

			if( isset( $this->__joinonadd__[$table->__table__] ) ){

				$link_found = true;

				$__table = $table->__table__;
				$__column = "joinonadd";
				$__link = $this->__joinon__;
				$this->__joinon__++;


				$this->__join__[$__table.".".$__column.".".$__link] = $this->__joinonadd__[$table->__table__];

				foreach( $table->__columns__ as $column=>$value ){
					$column = strtolower($column);
					$this->__joinwhere__[ $__use_table_formatted ][$column] = $value;
				}

				if( count( $table->__join__ ) > 0 ){
					foreach( $table->__join__ as $joinname=>$join ){
						$this->__join__[$joinname] = $join;
					}
				}

				if( count( $table->__joinwhere__ ) > 0 ){
					foreach( $table->__joinwhere__ as $joinwherename=>$joinwhere ){
						$this->__joinwhere__[$joinwherename] = $joinwhere;
					}
				}

			}

			if( !$link_found && $tableAs != "" && $columnAs != "" ){

				$__this__column = $columnAs;
				$__other__column = $columnAs;

				if( preg_match( '/:/', $columnAs ) ){
					$links = explode( ":", $columnAs );
					$__this__column = $links[1];
					$__other__column = $links[0];
				}

				if( $this->__hasColumn( $__this__column ) && $table->__hasColumn( $__other__column ) ){

					$link_found = true;

					$__table = $table->__table__;
					$__column = $__other__column;
					$__link = $__this__column;

					$this->__join__[$__table.".".$__column.".".$__link] = $joinType." JOIN ".$DBOVAR::TF( $__table )." ".( ( $tableAs != $__table )? $DBOVAR::NF( $tableAs ) : "" )." ON ".$DBOVAR::TF( $this->__table__ ).".".$DBOVAR::NF( $__link )." = ". $__use_table_formatted .".".$DBOVAR::NF( $__column )."";

					foreach( $table->__columns__ as $column=>$value ){
						$column = strtolower($column);
						$this->__joinwhere__[$__use_table_formatted][$column] = $value;
					}

					if( count( $table->__join__ ) > 0 ){
						foreach( $table->__join__ as $joinname=>$join ){
							$this->__join__[$joinname] = $join;
						}
					}

					if( count( $table->__joinwhere__ ) > 0 ){
						foreach( $table->__joinwhere__ as $joinwherename=>$joinwhere ){
							$this->__joinwhere__[$joinwherename] = $joinwhere;
						}
					}

				}


			}


			if( !$link_found ){

				foreach( $table->__links__ as $columnname=>$link  ){

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

						if( $table__ == $this->__table__ ){

							$__table = $table->__table__;
							$__column = $columnname;
							$__link = $table_link;


							$this->__join__[$__table.".".$__column.".".$__link] = $joinType." JOIN ".$DBOVAR::TF( $__table )." ".( ( $tableAs != "" && $tableAs != $__table )? $DBOVAR::NF( $tableAs ) : "" )." ON ".$DBOVAR::TF( $this->__table__ ).".".$DBOVAR::NF( $__link )." = ". $__use_table_formatted .".".$DBOVAR::NF( $__column )."";

							foreach( $table->__columns__ as $column=>$value ){
								$column = strtolower($column);
								$this->__joinwhere__[ $__use_table_formatted ][$column] = $value;
							}

							if( count( $table->__join__ ) > 0 ){
								foreach( $table->__join__ as $joinname=>$join ){
									$this->__join__[$joinname] = $join;
								}
							}

							if( count( $table->__joinwhere__ ) > 0 ){
								foreach( $table->__joinwhere__ as $joinwherename=>$joinwhere ){
									$this->__joinwhere__[$joinwherename] = $joinwhere;
								}
							}

							$link_found = true;

							break;

						}

					}

				}

			}

			if( !$link_found ){

				foreach( $this->__links__ as $columnname=>$link  ){

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

						if( $table__ == $table->__table__ ){

							$__table = $table->__table__;
							$__column = $table_link;
							$__link = $columnname;


							$this->__join__[$__table.".".$__column.".".$__link] = $joinType." JOIN ".$DBOVAR::TF( $__table )." ".( ( $tableAs != "" && $tableAs != $__table )? $DBOVAR::NF( $tableAs ) : "" )." ON ".$DBOVAR::TF( $this->__table__ ).".".$DBOVAR::NF( $__link )." = ". $__use_table_formatted.".".$DBOVAR::NF( $__column )."";

							foreach( $table->__columns__ as $column=>$value ){
								$column = strtolower($column);
								$this->__joinwhere__[$__use_table_formatted][$column] = $value;
							}

							if( count( $table->__join__ ) > 0 ){
								foreach( $table->__join__ as $joinname=>$join ){
									$this->__join__[$joinname] = $join;
								}
							}

							if( count( $table->__joinwhere__ ) > 0 ){
								foreach( $table->__joinwhere__ as $joinwherename=>$joinwhere ){
									$this->__joinwhere__[$joinwherename] = $joinwhere;
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
		return $this->__table__;
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
		foreach( DATABASE::$functions[DATABASE::$databases[$this->__use_db]['type']] as $functions ){
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


		$this->__statement__ = $sql;
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

		$str = $this->__statement__;
		preg_match_all( '/WHERE(\s+)1(\s+)AND/i', $str, $matches );

		if( count($matches) > 0 ){
			if( DATABASE::$databases[$this->__use_db]['type'] == DATABASE::ORACLE ){
				foreach ( $matches[0] as $i=>$match ) {
					$str = str_replace( $matches[0][$i], "WHERE", $str );
				}
			}
		}
		$this->__statement__ = $str;

	}

}
