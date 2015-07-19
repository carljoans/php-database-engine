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
class SQL{

	public $__TABLE__ = "";
	public $__IS_SELECT__ = false;
	public $__IS_UPDATE__ = false;
	public $__IS_INSERT__ = false;
	public $__IS_DELETE__ = false;
	public $__IS_VOID__ = false;
	public $__COLUMNS__ = array();
	public $__LINKS__ = array();
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
	public $__SKIP_COMPILE__ = false;

	public function __construct( $__TABLE__ = "" ) {
		$this->__TABLE__ = $__TABLE__;
	}
	
	public function __call($name, $arg){
        if( count($arg) == 1 ){
			$this->__COLUMNS__[$name] = $arg[0];
		}
		return $this;
    }
	
	public function setIsSelect(){
		$this->__IS_SELECT__ = true;
		$this->__IS_DELETE__ = false;
		$this->__IS_UPDATE__ = false;
		$this->__IS_INSERT__ = false;
		$this->__IS_VOID__ = false;
	}
	
	public function setIsDelete(){
		$this->__IS_SELECT__ = false;
		$this->__IS_DELETE__ = true;
		$this->__IS_UPDATE__ = false;
		$this->__IS_INSERT__ = false;
		$this->__IS_VOID__ = false;
	}
	
	public function setIsUpdate(){
		$this->__IS_SELECT__ = false;
		$this->__IS_DELETE__ = false;
		$this->__IS_UPDATE__ = true;
		$this->__IS_INSERT__ = false;
		$this->__IS_VOID__ = false;
	}
	
	public function setIsInsert(){
		$this->__IS_SELECT__ = false;
		$this->__IS_DELETE__ = false;
		$this->__IS_UPDATE__ = false;
		$this->__IS_INSERT__ = true;
		$this->__IS_VOID__ = false;
	}
	
	public function setIsVoid(){
		$this->__IS_SELECT__ = false;
		$this->__IS_DELETE__ = false;
		$this->__IS_UPDATE__ = false;
		$this->__IS_INSERT__ = false;
		$this->__IS_VOID__ = true;
	}
	
	public function isSelect(){
		return $this->__IS_SELECT__;
	}
	
	public function isDelete(){
		return $this->__IS_DELETE__;
	}
	
	public function isUpdate(){
		return $this->__IS_UPDATE__;
	}
	
	public function isInsert(){
		return $this->__IS_INSERT__;
	}
	
	public function isVoid(){
		return $this->__IS_VOID__;
	}
	
	public function selectFrom($__TABLE__){
		$this->setIsSelect();
		$this->__TABLE__ = $__TABLE__;
		return $this;
	}
	
	public function deleteFrom($__TABLE__){
		$this->setIsDelete();
		$this->__TABLE__ = $__TABLE__;
		return $this;
	}
	
	public function insertInto($__TABLE__){
		$this->setIsInsert();
		$this->__TABLE__ = $__TABLE__;
		return $this;
	}
	
	public function updateSet($__TABLE__){
		$this->setIsUpdate();
		$this->__TABLE__ = $__TABLE__;
		return $this;
	}
	
	public function where( $add = "", $logic = "AND" ){
		
		if( $add != "" ){
			$this->__WHERECOUNT__++;
			$this->__WHERE__[$this->__WHERECOUNT__] = $add;
			$this->__WHERELOGIC__[$this->__WHERECOUNT__] = strtoupper($logic);
		}
		
		return $this;
			
	}
	
	public function use_database($__USE_DB) {
		$this->__USE_DB = $__USE_DB;
	}
	
	public function output( $str=false ) {
		
		if( trim($this->__TABLE__) != "" ){
			if($this->isSelect()){
				return $this->select( $str );
			}
			if($this->isDelete()){
				return $this->delete( $str );
			}
			if($this->isUpdate()){
				return $this->update( $str );
			}
			if($this->isInsert()){
				return $this->insert( $str );
			}		
		}
		
		return ( $str )? $this->__STATEMENT__ : $this ;
				
	}
	
	public function select( $str=false ) {
	
		$DBOVAR = $this->__USE_DB;
	
		$columns = array();
		$columns_str = array();
		$columnvalues = array();
		
		$selectadd__ADD = ( count( $this->__SELECTADD__ ) > 0 );
		$group_BY = ( count( $this->__GROUPBY__ ) > 0 );
		$select_count_set = isset( $this->__SELECTADD__['count'] );
		$select_count = ($select_count_set)?$this->__SELECTADD__['count']:"";
	
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
		
		if( count( $this->__GROUPBY__ ) == 0 && count( $this->__SELECTADD_GROUPBY__ ) == 0 && !$selectadd__ADD ){
			
			if( count($this->__OTHERSELECTAS__) > 0 ){
				
				foreach( $this->__OTHERSELECTAS__ as $table=>$as ){
					$selectas[] = implode( ", ", $as );
				}
				$this->selectAs();
				
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
				$this->selectAs();
			
			}
			
			if( count($this->__SELECTAS_GROUPBY__) > 0 ){
				$selectas[] = implode( ", ", $this->__SELECTAS_GROUPBY__ );
				$select = implode( ", ", $selectas );
			}			
			
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
							$column = strtolower(str_replace( $this->__TABLE__.".", "", $column )); 
							foreach( $this->__COLUMNS__ as $col ){
								$col = strtolower($col);
								if( $col == $column || $this->startswith( $column, $col." " ) ){
									$colcount++;
								}
							}
						}
						
					}else{
						$column = trim( $value );
						$column = strtolower(str_replace( $this->__TABLE__.".", "", $column )); 
						foreach( $this->__COLUMNS__ as $col ){
							$col = strtolower($col);
							if( $col == $column || $this->startswith( $column, $col." " ) ){
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
		$this->fix_where();
		return $this;
	
	}
	
	public function insert( $str=false ){	
		
		$DBOVAR = $this->__USE_DB;
		
		$insert_columns = array();
		$insert_preps = array();
		$insert_preps_str = array();
		$insert_columnvalues = array();
	
		foreach( $this->__COLUMNS__ as $column=>$value ){
			
			$insert_columns[] = $DBOVAR::NF( $column );				
				
			if( $this->is_function( $value ) ){
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
			$primaryKey = $this->getfirstkey( true );
		}
		
		foreach( $insert_columns as $bindname=>$bindvalue ){
			if( strtolower($primaryKey) == strtolower(ltrim( $bindname, ":" )) ){
				$primaryKeySet = true;
			}
		}
		
		if( DATABASE::$databases[$this->__USE_DB]['type'] == DATABASE::ORACLE ){
			if( !$primaryKeySet ){
				
				$this->__INSERTID__ = $DBOVAR::SEQ_NEXT( $this->tablename() );	
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
		$this->fix_where();
		$this->check_string_literal();
		
		return $this;
			
	}
	
	public function delete($str=false){	
		
		$DBOVAR = $this->__USE_DB;
		$columns = array();
		$columnvalues = array();
		$columns_str = array();
		
		$primaryKey = $this->getfirstkey( true );
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
			$this->fix_where();
			
		}
		
		return $this;
			
	}
	
	public function update($str=false){	
		
		$DBOVAR = $this->__USE_DB;
		$where_columns = array();
		$where_columns_str = array();
		$where_columnvalues = array();
		
		$update_columns = array();
		$update_columns_str = array();
		$update_columnvalues = array();
		
		$primaryKey = $DBOVAR::getPrimaryKey( $this->tablename() );
	
		foreach( $this->__COLUMNS__ as $column=>$value ){
			if( strtolower($column) == strtolower($primaryKey) ){
				if( count( $this->__WHERE__ ) == 0 ){						
					$where_columns[] = $DBOVAR::NF( $column )." = :".$column;
					$where_columns_str[] = $DBOVAR::NF( $column )." = '".$value."'";
					$where_columnvalues[":".$column] = $value;
				}
			}else{
				
				if( $this->is_function( $value ) ){
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
			$this->fix_where();
			$this->check_string_literal();
		}
			
	}
	
	public function lastInsertId(){
		return $this->__INSERTID__;
	}
	
	public function check_string_literal(){
		
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
				$str_sql = str_replace( $bindname, $bindbasename, $str_sql );
			}
			
			$values = array_merge( $subvaluestxt, $valuestxt );
			
			$__sql .=  $str_sql."\n";
			$__sql .= ";\nEND;";
			
			$str_sql = $__sql;
			
		}
		
		$this->__STATEMENT__ = $str_sql;
		$this->__VALUES__ = $values;
		
	}
	
	public function prepared(){
		return $this->__STATEMENT__;
	}
	
	public function values(){
		return $this->__VALUES__;
	}
	
	public function output_str(){
		$this->output( true );
		return $this->__STATEMENT__;
	}
	
	public function count_str( $countWhat = false, $whereAddOnly = false ){
		$this->count( $countWhat, $whereAddOnly, true );
		return $this->__STATEMENT__;
	}
	
	public function count( $countWhat = false, $whereAddOnly = false, $str=false ){
		
		$DBOVAR = $this->__USE_DB;
		if (is_bool($countWhat)) {
            $whereAddOnly = $countWhat;
        }
        
        $table = $this->__TABLE__;
        $key = $this->getfirstkey();        
        
        // support distinct on default keys.
        $countWhat = (strtoupper($countWhat) == 'DISTINCT') ? "DISTINCT ".$DBOVAR::TF( $table.".".$key ) : $countWhat;        
        $countWhat = is_string($countWhat) ? $countWhat : $DBOVAR::TF( $table.".".$key );
        $as = "rowcount";
        $this->__COUNT__ = true;
        $this->selectAdd( "COUNT( ".$countWhat." ) AS ".$DBOVAR::NF( $as ) ); 
        
        return $this->output($str);
		
	}
	
	private function get_database_data(){
		$DBOVAR = $this->__USE_DB;
		if( DATABASE::$databases[$this->__USE_DB]['type'] == DATABASE::ORACLE ){
			
			if( !isset(DATABASE::$database_data[$this->__USE_DB]) ){
				
				$db_data = $DBOVAR::query( "select * from all_tab_columns where lower(owner)='".strtolower(DATABASE::$databases[$this->__USE_DB]['user'])."'", DATABASE::ALL );
				DATABASE::$database_data[$this->__USE_DB] = array();
				
				foreach( $db_data as $row ){
					
					$entry = array();
					foreach( $row as $column=>$value ){
						if( !is_int( $column ) ){
							$entry[strtolower($column)] = is_resource( $value ) ? stream_get_contents( $value ) : $value ;
						}
					}
					
					if( !isset( DATABASE::$database_data[$this->__USE_DB][strtolower($entry["table_name"])] ) ){
						DATABASE::$database_data[$this->__USE_DB][strtolower($entry["table_name"])] = array();
					}
					
					DATABASE::$database_data[$this->__USE_DB][strtolower($entry["table_name"])][] = $entry;
					
				}
				
			}
			
			
			
		}
	}
	
	private function find_column_datatype_ora( $column ){
		
		if( DATABASE::$databases[$this->__USE_DB]['type'] == DATABASE::ORACLE ){
			$format = array( DATABASE::QUOTES, strrev(DATABASE::QUOTES), DATABASE::PREPEND, strrev(DATABASE::PREPEND) );
			$column = str_replace( $format, "", $column );
			
			if( preg_match( '/\./', $column ) ){
				$column = explode( ".", $column );
				$column = array_reverse($column);
				$column = $column[0];
			}
			
			foreach( DATABASE::$database_data[$this->__USE_DB][strtolower( $this->prependTableName( $this->__TABLE__ ) )] as $row ){
				
				if( strtolower( $row['column_name'] ) == strtolower( $column ) ){
					return $row['data_type'];
				}
				
			}
		}
		return "";
		
	}
	
	public function getfirstkey( $primary=false ){
		
		$DBOVAR = $this->__USE_DB;
		$primaryKey = $DBOVAR::getPrimaryKey( $this->tablename() );
		
		if( $primaryKey != "" || $primary ){
			return $primaryKey;
		}
		
		if( count( $this->__KEYS__ ) > 0 ){
			return $this->__KEYS__[0];
		}
		
		return "";
		
	}
	
	public function replaceTableName( $name ){
		
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
		
		$name = TABLENAME::finalname($name);
		
		if( strpos( $this->__PREPEND_TABLENAME, $tempname ) === 0 ){
			$name = $this->__PREPEND_TABLENAME.$name;
		}
		
		return $name.$column;
		
	}
	
	public function prependTableName( $name ){
		
		$name = $this->replaceTableName( $name );
		
		if( $this->__PREPEND_TABLENAME != "" ){
			if( strpos( $this->__PREPEND_TABLENAME, $name ) !== 0 ){
				$name = $this->__PREPEND_TABLENAME.$name;
			}
		}
		
		return $name;
		
	}
		
	public function orderBy( $orderby="" ){
		
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
	
	public function limit( $from, $count=0 ){
		
		$this->__LIMIT__["from"] = $from; 
		$this->__LIMIT__["count"] = $count; 
		
		return $this;
				
	}
	
	/*
	 FOR Oracle:
	 If using a groupBy, the selectAdd must contain the same columns as the groupBy. The developer must see to this manually. 
	 If only using the selectAdd, go nuts.
	 If using only a groupBy and not using the selectAdd TABLE will automatically balance out the selected columns with the groupBy.
	 */
	
	public function selectAdd( $add = "" ){	
		
		if( $add != "" ){
			if( $this->__COUNT__ ){
				$this->__SELECTADD__["count"] = $add;
			}else{
				$this->__SELECTADD__[] = $add;
			}
		}
			
	}
	
	/*
	 FOR Oracle:
	 If using a selectAdd the groupBy must contain the same columns as the selectAdd. The developer must see to this manually. 
	 If only using a groupBy and not using the selectAdd TABLE will automatically balance out the selected columns with the groupBy.
	 */
	
	public function groupBy( $groupBy = "", $selectAdd=false ){	
		
		$DBOVAR = $this->__USE_DB;
		$group__BY = ($selectAdd)? "__SELECTADD_GROUPBY__" : "__GROUPBY__" ;
		
		
		if( $groupBy != "" ){
			if( preg_match( '/,/', $groupBy ) ){
				$groupBy = explode( ",", $groupBy );
				$this->$group__BY = array_merge ( $groupBy, $this->$group__BY );
			}else{
				if( !preg_match( '/\./', $groupBy ) ){
					$groupBy = $DBOVAR::TF($this->tablename()).".".$groupBy;
				}
				$array = $this->$group__BY;
				$array[] = $groupBy;
				$this->$group__BY = $array;
			}
			$this->$group__BY = array_unique($this->$group__BY);
		}
			
	}
	
	private function add_selectAs_groupBy( $groupBy ){
		
		$format = array( DATABASE::QUOTES, strrev(DATABASE::QUOTES), DATABASE::PREPEND, strrev(DATABASE::PREPEND) );
		$b = str_replace( $format, "", $groupBy );
		
		if( count( $this->__SELECTADD_GROUPBY__ ) > 0 ){
			$group = $this->__SELECTADD_GROUPBY__;
			foreach( $group as $element  ){
				$a = str_replace( $format, "", $element );
				if( $b == $a ){
					return;
				}
			}
		}
		
		$this->groupBy( $groupBy, true );
		
	}
	
	public function selectAs( $table=NULL, $format="%s" ){
		
		$DBOVAR = $this->__USE_DB;
		if( $table == NULL ){
			
			foreach( $this->__COLUMNS__ as $column ){
				
				$table_column_pair = $DBOVAR::TF( $this->__TABLE__ ).".".$DBOVAR::NF( $column );
				$column_name = ( strtolower( $this->find_column_datatype_ora( $column ) ) == "clob" )? "to_char(".$table_column_pair.")" : $table_column_pair;
				$this->__SELECTAS__[$column] = $table_column_pair." AS ".$DBOVAR::NF( sprintf( $format , $column ) );
				
				if( DATABASE::$databases[$this->__USE_DB]['type'] == DATABASE::ORACLE ){
					$this->__SELECTAS_GROUPBY__[$column] = $column_name." AS ".$DBOVAR::NF( sprintf( $format , $column ) );
					$this->add_selectAs_groupBy( $column_name );		
				}
				
			}
			
		}else if( $table instanceof TABLE  ){
			
			$format = ( $format == "%s" )? $table->__TABLE__."_%s" : $format;
			
			$this->__OTHERSELECTAS__[$table->__TABLE__] = array();
			$this->__OTHERSELECTAS_GROUPBY__[$table->__TABLE__] = array();
			
			foreach( $table->__COLUMNS__ as $column ){
				
				$table_column_pair = $DBOVAR::TF( $table->__TABLE__ ).".".$DBOVAR::NF( $column );
				$column_name = ( strtolower( $this->find_column_datatype_ora( $column ) ) == "clob" )? "to_char(".$table_column_pair.")" : $table_column_pair;
				
				$this->__OTHERSELECTAS__[$table->__TABLE__][] = $table_column_pair." AS ".$DBOVAR::NF( sprintf( $format , $column ) );	
				if( DATABASE::$databases[$this->__USE_DB]['type'] == DATABASE::ORACLE ){
					$this->__OTHERSELECTAS_GROUPBY__[$table->__TABLE__][] = $column_name." AS ".$DBOVAR::NF( sprintf( $format , $column ) );
					$this->add_selectAs_groupBy( $column_name );				 
				}	
				
			}
			
		}else{
			
			$this->__TABLEALIAS__ = $table;
			
			foreach( $this->__COLUMNS__ as $column ){
				$table_column_pair = $DBOVAR::TF( $table ).".".$DBOVAR::NF( $column );
				$column_name = ( strtolower( $this->find_column_datatype_ora( $column ) ) == "clob" )? "to_char(".$table_column_pair.")" : $table_column_pair;
				$this->__SELECTAS__[$column] = $table_column_pair." AS ".$DBOVAR::NF( sprintf( $format , $column ) );	
				if( DATABASE::$databases[$this->__USE_DB]['type'] == DATABASE::ORACLE ){
					$this->__SELECTAS_GROUPBY__[$column] = $column_name." AS ".$DBOVAR::NF( sprintf( $format , $column ) );
					$this->add_selectAs_groupBy( $DBOVAR::TF( $table ).".".$DBOVAR::NF( $column ) );			
				}	 			
			}
			
		}
		
	}
	
	public function joinOnAdd( $tablename, $join, $joinType='INNER' ){
		$DBOVAR = $this->__USE_DB;
		$this->__JOINONADD__[$tablename] = $joinType." JOIN ".$DBOVAR::NF($tablename)." ON ".$join;
	}
	
	public function createLink( $table, $column=null, $linkto=null ){
		
		if( $table instanceof SQL  ){
			$this->__LINKS__[$linkto] = $table->__TABLE__.":".$column;
		}elseif( is_array( $table ) ){
			$this->__LINKS__ = $table;
		}
		
	}
		
	public function joinAdd( $table, $joinType='INNER', $tableAs="", $columnAs="" ){	
		$DBOVAR = $this->__USE_DB;		
		
		if( $table instanceof SQL  ){	
			
			$link_found = false;
			$table->selectAs( $tableAs );
			$this->__JOINSELECTADD__[$table->__TABLE__] = $table->__SELECTAS__;
			if( count( $table->__JOINSELECTADD__ ) > 0 ){
				foreach( $table->__JOINSELECTADD__ as $jsat=>$joinselectadd ){
					$this->__JOINSELECTADD__[$jsat] = $joinselectadd;
				}
			}
			
			if( count( $table->__GROUPBY__ ) > 0 ){
				foreach( $table->__GROUPBY__ as $groupBy ){
					$this->add_selectAs_groupBy( $groupBy );
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
				
				foreach( $table->__COLUMNS__ as $column ){
					$column = strtolower($column);
					if( isset( $table->$column ) ){
						$this->__JOINWHERE__[ $__use_table_formatted ][$column] = $table->$column;
					}
					
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
				
				if( $this->hasColumn( $columnAs ) && $table->hasColumn( $columnAs ) ){
					
					$link_found = true;
					
					$__table = $table->__TABLE__;
					$__column = $columnAs;
					$__link = $columnAs;
					
					$this->__JOIN__[$__table.".".$__column.".".$__link] = $joinType." JOIN ".$DBOVAR::TF( $__table )." ".( ( $tableAs != $__table )? $DBOVAR::NF( $tableAs ) : "" )." ON ".$DBOVAR::TF( $this->__TABLE__ ).".".$DBOVAR::NF( $__link )." = ". $__use_table_formatted .".".$DBOVAR::NF( $__column )."";
					
					foreach( $table->__COLUMNS__ as $column ){
						$column = strtolower($column);
						if( isset( $table->$column ) ){
							$this->__JOINWHERE__[$__use_table_formatted][$column] = $table->$column;
						}
						
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
		
					$linked = $link;
		
					if( is_array( $linked ) ){
						
						continue;					
						
					}
		
					$linked = explode( ":", $linked );
					$table__ = trim( $linked[0] );
					$table_link = trim( $linked[1] );
					
					if( $table__ == $this->__TABLE__ ){
						
						$__table = $table->__TABLE__;
						$__column = $columnname;
						$__link = $table_link;
						
						
						$this->__JOIN__[$__table.".".$__column.".".$__link] = $joinType." JOIN ".$DBOVAR::TF( $__table )." ".( ( $tableAs != "" && $tableAs != $__table )? $DBOVAR::NF( $tableAs ) : "" )." ON ".$DBOVAR::TF( $this->__TABLE__ ).".".$DBOVAR::NF( $__link )." = ". $__use_table_formatted .".".$DBOVAR::NF( $__column )."";
						
						foreach( $table->__COLUMNS__ as $column ){
							$column = strtolower($column);
							if( isset( $table->$column ) ){
								$this->__JOINWHERE__[ $__use_table_formatted ][$column] = $table->$column;
							}
							
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
			
			if( !$link_found ){
				
				foreach( $this->__LINKS__ as $columnname=>$link  ){
		
					$linked = $link;
		
					if( is_array( $linked ) ){
						
						continue;					
						
					}
		
					$linked = explode( ":", $linked );
					$table__ = trim( $linked[0] );
					$table_link = trim( $linked[1] );
					
					if( $table__ == $table->__TABLE__ ){
						
						$__table = $table->__TABLE__;
						$__column = $table_link;
						$__link = $columnname;
						
						
						$this->__JOIN__[$__table.".".$__column.".".$__link] = $joinType." JOIN ".$DBOVAR::TF( $__table )." ".( ( $tableAs != "" && $tableAs != $__table )? $DBOVAR::NF( $tableAs ) : "" )." ON ".$DBOVAR::TF( $this->__TABLE__ ).".".$DBOVAR::NF( $__link )." = ". $__use_table_formatted.".".$DBOVAR::NF( $__column )."";
						
						foreach( $table->__COLUMNS__ as $column ){
							$column = strtolower($column);
							if( isset( $table->$column ) ){
								$this->__JOINWHERE__[$__use_table_formatted][$column] = $table->$column;
							}
							
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
	
	public function tablename(){
		##TODO USING TABLENAME
		return $this->__TABLE__;
	}
	
	public function is_function( $value ){
		
		$is_function = false;
		foreach( DATABASE::$functions[DATABASE::$databases[$this->__USE_DB]['type']] as $functions ){
			if( preg_match('/'.$functions.'\(/i', $value ) ){
				$is_function = true;
			}
		}
		return $is_function;
	}
	
	public function compiled_str( $sql, $void=false ) {
		
		$this->setIsVoid();
		
		if( is_bool($void) ){
			
			if( !$void ){
				
				if( DATABASE::startswith( $sql, "SELECT" ) ){
					$this->setIsSelect();
				}
				
				if( DATABASE::startswith( $sql, "UPDATE" ) ){
					$this->setIsUpdate();
				}
				
				if( DATABASE::startswith( $sql, "DELETE" ) ){
					$this->setIsDelete();
				}
				
				if( DATABASE::startswith( $sql, "INSERT" ) ){
					$this->setIsInsert();
				}
				
			}
			
		}elseif( is_int($void) ){
			
			if( $void === DATABASE::ALL ){
				$this->setIsSelect();
			}
			
			if( $void === DATABASE::UPD ){
				$this->setIsUpdate();
			}
			
			if( $void === DATABASE::DEL ){
				$this->setIsDelete();
			}
			
			if( $void === DATABASE::INS ){
				$this->setIsInsert();
			}
			
		}
		
		
		$this->__STATEMENT__ = $sql;
		$this->fix_where();
		return $this;
		
	}
	
	public function fix_where(){		
		
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
