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
class RESULTSET extends ArrayObject {
	
	public $___result = null;
	public $___n = 0;
	public $___status = false;
	public $___limit = -1;
	public $___is_oci_ = false;
	
	public function __construct( $result, $n, $type, $status, $is_oci_=false ) {
		
		$this->___n = $n;
		$this->___status = $status;
		$this->___is_oci_ = $is_oci_;
		
		if( $type===DATABASE::FET && $n > 0 ){
			
			foreach( $result as $name=>$value ){
				$name = strtolower($name);
				$this[$name] = $this->getfieldvalue( $value );
			}
			
		}elseif( $type===DATABASE::ALL && $n > 0 ){
			$this->___result = $result;
			$this->___n = $n;
		}
		
    }
    
    public function count(){
		return $this->___n;
	}
	
	private function getfieldvalue( $value ){						
		$value = is_resource( $value ) ? stream_get_contents( $value ) : ( is_object($value) && get_class($value) == 'OCI-Lob'  ? $value->read($value->size()) : $value ) ;
		return $value;
	}
    
    public function fetch( $limit=-1 ){
		if( $this->___n > 0 ){
				
			$this->___limit++;
			if( $this->___limit == $limit ){
				$this->___limit = -1;
				return false;
			}
				
			if( !$this->___is_oci_ ){
				
				foreach( $this->___result as $result ){
					
					foreach( $result as $name=>$value ){
						if( !is_int( $name ) ){	
							$name = strtolower($name);
							$this[$name] = $this->getfieldvalue( $value );
						}
					}
					return true;
				}
				return false;	
			}else{
				while( $result = oci_fetch_array( $this->___result ) ){
					
					foreach( $result as $name=>$value ){
						if( !is_int( $name ) ){	
							$name = strtolower($name);
							$this[$name] = $this->getfieldvalue( $value );
						}
					}
					return true;
				}
				oci_free_statement($this->___result);
				return false;
			}
		}
	}
    
    function __set( $name, $value ){
		$name = strtolower($name);
		$array = array();
		foreach( $this as $thisname=>$thisvalue ){
			$array[$thisname] = $thisvalue;
		}
		$array[$name] = $value;
		$this->exchangeArray($array);
	}
	
	function __get( $name ){
		return $this->offsetGet($name);
	}
    
    function offsetSet($name, $value) {		
        if (!is_null($name)) {
			$name = strtolower($name);
            parent::offsetSet($name, $value);
        }
    }
    
    function offsetUnset($name) {		
        $name = strtolower($name);
        parent::offsetUnset($name);
    }
    
    function offsetExists($name) {		
        $name = strtolower($name);
        return parent::offsetExists($name);
    }

    function offsetGet($name) {
		$name = strtolower($name);
		if( parent::offsetExists($name) ){
			return parent::offsetGet($name);
		}
        return NULL;
    }
	
}
