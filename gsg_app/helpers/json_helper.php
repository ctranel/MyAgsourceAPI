<?php
function json_func_expr($json)
{
    return preg_replace_callback(
        '/(?<=:)"function((?:(?!)").)*}"/',
        'json_strip_escape',
        $json
    );
}
 
function json_strip_escape($string)
{
    return str_replace(array('\"','/','"','n','t'),array('"','/','"','',''),substr($string[0],1,-1));
}

function json_encode_jsfunc($input=array(), $funcs=array(), $level=0) 
 { 
  foreach($input as $key=>$value) 
         { 
          if (is_array($value)) 
             { 
              $ret = json_encode_jsfunc($value, $funcs, 1); 
              $input[$key]=$ret[0]; 
              $funcs=$ret[1]; 
             } 
          else 
             { 
              if (substr($value,0,10)=='function()') 
                 { 
                  $func_key="#".uniqid()."#"; 
                  $funcs[$func_key]=$value; 
                  $input[$key]=$func_key; 
                 } 
             } 
         } 
  if ($level==1) 
     { 
      return array($input, $funcs); 
     } 
  else 
     { 
      $input_json = json_encode($input); 
      foreach($funcs as $key=>$value) 
             { 
              $input_json = str_replace('"'.$key.'"', $value, $input_json); 
             } 
      return $input_json; 
     } 
 } 



/* temp function until PHP is upgraded to 5.2 */

if (!function_exists('json_encode')){
	function json_encode( $data ) {            
//var_dump($data);
		if( is_array($data) || is_object($data) ) { 
	        $islist = is_array($data) && ( empty($data) || array_keys($data) === range(0,count($data)-1) );
	         
	        if( $islist ) { 
	            $json = '[' . implode(',', array_map('json_encode', $data) ) . ']';
	         } else { 
	            $items = Array(); 
	            foreach( $data as $key => $value ) { 
	                $items[] = json_encode("$key") . ':' . json_encode($value); 
	            } 
	            $json = '{' . implode(',', $items) . '}'; 
	        } 
	    } elseif( is_string($data) ) { 
	        # Escape non-printable or Non-ASCII characters. 
	        # I also put the \\ character first, as suggested in comments on the 'addclashes' page.
	         $string = '"' . addcslashes($data, "\\\"\n\r\t/" . chr(8) . chr(12)) . '"';
	         $json    = ''; 
	        $len    = strlen($string); 
	        # Convert UTF-8 to Hexadecimal Codepoints. 
	        for( $i = 0; $i < $len; $i++ ) { 
	            
	            $char = $string[$i]; 
	            $c1 = ord($char); 
	            
	            # Single byte; 
	            if( $c1 <128 ) { 
	                $json .= ($c1 > 31) ? $char : sprintf("\\u%04x", $c1); 
	                continue; 
	            } 
	            
	            # Double byte 
	            $c2 = ord($string[++$i]); 
	            if ( ($c1 & 32) === 0 ) { 
	                $json .= sprintf("\\u%04x", ($c1 - 192) * 64 + $c2 - 128); 
	                continue; 
	            } 
	            
	            # Triple 
	            $c3 = ord($string[++$i]); 
	            if( ($c1 & 16) === 0 ) { 
	                $json .= sprintf("\\u%04x", (($c1 - 224) <<12) + (($c2 - 128) << 6) + ($c3 - 128));
	                 continue; 
	            } 
	                
	            # Quadruple 
	            $c4 = ord($string[++$i]); 
	            if( ($c1 & 8 ) === 0 ) { 
	                $u = (($c1 & 15) << 2) + (($c2>>4) & 3) - 1; 
	            
	                $w1 = (54<<10) + ($u<<6) + (($c2 & 15) << 2) + (($c3>>4) & 3); 
	                $w2 = (55<<10) + (($c3 & 15)<<6) + ($c4-128); 
	                $json .= sprintf("\\u%04x\\u%04x", $w1, $w2); 
	            } 
	        } 
	    } else { 
	        # int, floats, bools, null 
	        $json = strtolower(var_export( $data, true )); 
	    } 
	    return $json; 
	} 
}

