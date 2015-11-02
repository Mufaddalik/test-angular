<?php

/**
 * @author lolkittens
 * @copyright 2015
 */

//return json_encode(array('test' =>'test'));
var_dump($_REQUEST);
die();
            //$query = $this->_request['query'];
            
            
            $q_str = "SELECT ud.user_id, ud.first_name, ud.last_name, ud.address, ud.city, ud.state, ud.zip, ud.contact_1, 
                        ud.contact_2, ud.contact_3 
                        FROM user_details as ud
                        inner join user_master um ON um.id = ud.id
                        WHERE um.status = 1 and ud.user_id = '".$query."' OR ud.first_name = '".$query."' OR ud.last_name = '".$query."' 
                        	OR ud.address = '".$query."' OR ud.city = '".$query."' OR ud.state = '".$query."' OR ud.zip = '".$query."' 
                        	OR ud.image_url = '".$query."' OR ud.contact_1 = '".$query."' OR ud.contact_2 = '".$query."' OR ud.contact_3 LIMIT 10 ";
                            
            
            
            $r = $this->db_pg->executeQuery($q_str);
			if($this->db_pg->numRows($r) > 0) 
            {
                $data = array();
                while($row = pg_fetch_object($r))
                {
                    $data[] = array('content' =>$row->user_id);
                    $data[] = array('content' =>$row->first_name);
                    $data[] = array('content' =>$row->last_name);
                    $data[] = array('content' =>$row->address);
                    $data[] = array('content' =>$row->city);
                    $data[] = array('content' =>$row->state);
                    $data[] = array('content' =>$row->zip);
                    $data[] = array('content' =>$row->contact_1);
                    $data[] = array('content' =>$row->contact_2);
                    $data[] = array('content' =>$row->contact_3);
                }
				$this->response($this->json($data), 200); // send user details
			}

?>