 <?php

	require_once("Rest.inc.php");// for rest 
    require_once("mysql.php");
	class API extends REST {
    	
		public $data = "";
		
        private $config =  array('server' => 'localhost', 'database' => 'comunityportal',
                                 'username' => 'root', 'password' => '', 'status' => 1 );
		public $db_pg = NULL;
		private $mysqli = NULL;
		public function __construct(){
			parent::__construct();				// Init parent contructor
            $this->db_pg = new mysql();
            $this->mysqli = $this->db_pg->connect($this->config);
            //$this->mysqli = $this->db_pg->connect($this->config);
			//$this->db = $this->connect($this->config);					// Initiate Database connection
            session_start(); // intiate sessoin for application
		}
		/**
         * User Profile image path
         */
        const IMAGE_USER = 'img/users/';
		/**
		 *  Connect to Database
		*/
		private function dbConnect(){
		    
			$this->mysqli = new mysqli(self::DB_SERVER, self::DB_USER, self::DB_PASSWORD, self::DB);
		}
		
		/**
		 * Dynmically call the method based on the query string
		 */
		public function processApi(){
			$func = strtolower(trim(str_replace("/","",$_REQUEST['x'])));
			if((int)method_exists($this,$func) > 0)
				$this->$func();
			else
				$this->response('',404); // If the method not exist with in this class "Page not found".
		}
				
		/**
         * funciton for session
         * @return array either sucess or failure
         */
         public function session()
         {
            if($this->get_request_method() != "POST"){
				$this->response('',406);
			}
            if(isset($_SESSION['user_id']) && !empty($_SESSION['user_id']))
            {
                $data= array( 'user_id' => $_SESSION['user_id'] , 'user_image' => $_SESSION['user_image'], 'username' => $_SESSION['username'],
                             'type' => 'success', 'status' => 'Logged-in');
            }
            else
            {
                $data = array( 'user_id' => 'Not_Logged-in' , 'type' => 'error', 'status' => 'Not Logged In');
            }
            
            $this->response($this->json($data),200);
         } 

        /**
         * prepare data insert data for search
         * @return nothing     
         */
         public function prepareData()
         {
            if($this->get_request_method() != "POST"){
				$this->response('',406);
			}
            $sql = "select  distinct c.id,c.first_name,c.last_name,c.contact_1,c.children,c.contact_2,
            c.contact_3,c.street_address,c.city,c.state,c.zip,c.username from group_profile c order by 1";
    
            $result = $this->mysqli->query($sql) or die($this->mysqli->error.__LINE__);
            $data = array();
            if($result->numRows > 0)
            {
                foreach($result as $key=>$value)
                {
                    $data[] = $value[0];
                    $data[] = $value[1];
                    $data[] = $value[2];
                    $data[] = $value[3];
                    $data[] = $value[4];
                    $data[] = $value[5];
                    $data[] = $value[6];
                    $data[] = $value[7];
                    $data[] = $value[8];
                    $data[] = $value[9];
                    $data[] = $value[10];
                    $data[] = $value[11];
                }
            }
            $inset_sql = 'INSERT INTO data_completer values ("'.implode(',',$data).'")';
            $result = $this->mysqli->query($sql) or die($this->mysqli->error.__LINE__);
         }    
        /**
         * Get All saved search of particular user
         * @return search data array 
         */ 	
         public function getUserSearch()
         {
            if($this->get_request_method() != "POST"){
				$this->response('',406);
			}
            $post_value = $this->get_post_array();
            $user_id = (int)$post_value ['user_id'];
			$query="SELECT us.search_name,gi.icon_url, us.icon_id, us.search_query FROM user_search us
                    INNER JOIN  group_icons gi ON us.icon_id = gi.id
                    where us.user_id = ".$user_id;
			$r = $this->db_pg->executeQuery($query);
			if($this->db_pg->numRows($r) > 0) 
            {
                $data = array();
                while($row = mysqli_fetch_object($r))
                {
                    $data[] = array('text' => $row->search_name, 'icon_url' => $row->icon_url,
                                    'icon_id' => $row->icon_id, 'search_query' => $row->search_query);
                }
				$this->response($this->json($data), 200); // Send Respose back to controller
			}
			$this->response('',204);
         }

        /**
         * Get All saved search of particular user
         * @return search data array 
         */ 	
         public function getUserGroup()
         {
            if($this->get_request_method() != "POST"){
				$this->response('',406);
			}
            $post_value = $this->get_post_array();
            $user_id = $post_value ['user_id'];
			$query="SELECT grp.id, grp.`name` FROM groups grp
                        INNER JOIN group_members gm ON gm.`group_id` = grp.`id`
                        WHERE gm.`user_id`= ".$user_id;
			$r = $this->db_pg->executeQuery($query);
			if($this->db_pg->numRows($r) > 0) 
            {
                $data = array();
                while($row = $r->fetch_object())
                {
                    $data[] = array('group_id' => $row->id, 'group_name' => $row->name);
                }
				$this->response($this->json($data), 200); // Send Respose back to controller
			}
			$this->response('',204);
         }

        /**
         * Get All Groups-Icons info
         * @return info data array 
         */ 	
         public function getAllGroupIcons()
         {
            if($this->get_request_method() != "POST")
            {
				$this->response('',406);
			}
			$q_str = "SELECT * FROM group_icons";
            $r = $this->db_pg->executeQuery($q_str);
            //$this->response($this->json(array('test' => $r)), 200);
			if($this->db_pg->numRows($r) > 0) 
            {
                
                $data = array();
                while($row = mysqli_fetch_object($r))
                { 
                    $data[] = array('id' => $row->id,'icons' => $row->icon_url);
                    
                }
                $this->response($this->json($data), 200);
            }
            $this->response($this->db_pg->numRows($r), 200);
         } 
   

         
        /**
         * Get All info
         * @return info data array 
         */ 	
         public function getAllInfo()
         {
            if($this->get_request_method() != "POST"){
				$this->response('',406);
			}
			$query="SELECT * FROM group_info";
			$r = $this->mysqli->query($query) or die($this->mysqli->error.__LINE__);
			if($r->num_rows > 0) 
            {
                $data = array();
                while($row = $r->fetch_Object())
                {
                    $data[] = array('id' =>$row->id, 'text' => $row->group_name, 'icon_url' => $row->icon_url);
                    
                }
				$this->response($this->json($data), 200); // Send Respose back to controller
			}
			$this->response('',204);
         } 
   
        /**
         * Get All info
         * @return info data array 
         */ 	
         public function getAutoComplete()
         {
            if($this->get_request_method() != "POST"){
				$this->response('',406);
			}
            $post_value = $this->get_post_array();
            $query = $post_value['query'];
            //$this->response($this->json(var_dump($_REQUEST)), 200);
            //$query = $this->_request['query'];
            

            $q_str = "SELECT ud.user_id, ud.first_name, ud.last_name, ud.address, ud.city, ud.state, ud.zip, ud.contact_1, 
                       ud.contact_2, ud.contact_3 
                       FROM user_details as ud
                       inner join user_master um ON um.id = ud.id
                       inner join group_field gf 
                       WHERE um.status = 1  OR ud.first_name = '".$query."' OR ud.last_name = '".$query."' 
                        	OR ud.address = '".$query."' OR ud.city = '".$query."' OR ud.state = '".$query."'  
                       	OR ud.image_url = '".$query."' OR ud.contact_1 = '".$query."' OR ud.contact_2 = '".$query."' OR ud.contact_3 = '".$query."' LIMIT 10 ";   
      
            //and ud.user_id = 1 OR ud.zip = 97305 
            $r = $this->db_pg->executeQuery($q_str);
			if($this->db_pg->numRows($r) > 0) 
            {
                $data = array();
                while($row = mysqli_fetch_object($r))
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
				$this->response($this->json($data), 200); // Send Respose back to controller
			}
			$this->response(array('test'=>'test'),204);
         } 
        /**
         * Get Members list
         * @return array of all users
         */
        public function  getMemberlist()
        {
            if($this->get_request_method() != "GET"){
				$this->response('',406);
			}
            
			$r = $this->db_pg->executeQuery("SELECT ud.user_id, ud.first_name, ud.last_name, ud.address, ud.city, ud.state, ud.zip, 
                                                ud.image_url, ud.contact_1, ud.contact_2, ud.contact_3 
                                            FROM user_details ud
                                            INNER JOIN user_master um ON um.id = ud.user_id
                                            WHERE um.status = 1 ");
			if($this->db_pg->numRows($r) > 0) 
            {
                $data = array();
                while($row = mysqli_fetch_object($r))
                {
                    $image = null;
                    if(file_exists("../".$row->image_url))
                    {
                        $image = $row->image_url;
                    }
                    else
                    {
                        $image = 'img/users/preview.jpg';
                    }
                    $data[] = array
                              ( //'age'            => $row->age,
                                'id'             => $row->user_id,
                                'imageUrl'       => $image,
                                'name'           => $row->first_name." ".$row->last_name,
                                'contact_1'      => $row->contact_1,
                                'contact_2'      => $row->contact_2,
                                'contact_3'      => $row->contact_3,
                                'street_address' => $row->address,
                                'city'           => $row->city,
                                'state'          => $row->state,
                                'zip'            => $row->zip
                              );
                }
				$this->response($this->json($data), 200); // Send Respose back to controller
			}
			$this->response('',204);
            
        }
        
        /**
         * Get user data on signin
         * @return array either sucess or failure
         */
         public function authentication()
         {
            if($this->get_request_method() != "POST"){
				$this->response('',406);
			}
            
            //$post_value = json_decode(file_get_contents("php://input"),true);
            $post_value = $this->get_post_array();
            
            $user_id   = strtolower($this->db_pg->escape($post_value['username']));
            $user_pass = $this->db_pg->escape($post_value['password']);
            // validation start
            if(empty($user_id) || $user_id == "" )
            {
                $data = array( 'type' => 'error', 'status' => 'Please Enter Email Id');
                $this->response($this->json($data),200);
            }
            else if(empty($user_pass) || $user_pass == "" )
            {
                $data = array( 'type' => 'error', 'status' => 'Please Enter Password');
                $this->response($this->json($data),200);
            }
            // validation ends
            
            $r = $this->db_pg->getRow('user_master',"username = '".$user_id."' AND password = '".$user_pass."'");
            
            if($r == false)
            {
                $this->response($this->json(array('type'=>'error', 'status' => $this->db_pg->error())),200);
            }
            if($this->db_pg->numRows($r) > 0)
            {
                while($row = $r->fetch_object())
                {
                    $status = (int)$row->status;
                    if($status == 0)
                    {
                        $data = array(  'type' => 'warning', 'status' => 'Email Not Vefified yet! Please Check your email inbox');
                    }
                    else
                    {
                        $user_id = $row->id;
                        $r1 = $this->db_pg->getSelectedRC('image_url, first_name, last_name','user_details','user_id = '.$user_id);
                        while($row1 = $r1->fetch_object())
                        {
                             $image_url = $row1->image_url;
                             $fname = $row1->first_name;
                             $lname = $row1->last_name;
                        }
                        $_SESSION['user_image'] = $image_url;
                        $_SESSION['username'] = $fname." ".$lname;
                        $_SESSION['user_id'] = $user_id;
                        $data = array(  'type' => 'success', 'status' => 'Logged in Successfully',
                                        'user_image' => $image_url, 'username' => $fname." ".$lname , 'user_id' =>$user_id);
                        
                    }
                }
            }
            else
            {
                $data = array( 'user_id' => 'Not_logged_in' , 'type' => 'error', 'status' => 'Login failed. Incorrect credentials');
            }
            
            $this->response($this->json($data),200);
         }
         /**
         * funciton for checking user id 
         * @return array either sucess or failure
         */
         public function checkUserId()
         {
            if($this->get_request_method() != "POST"){
				$this->response('',406);
			}
            
            $post_value = json_decode(file_get_contents("php://input"),true);
            $user_id = $post_value['user_id'];
            $query = "SELECT sno,username,userpass FROM group_profile WHERE username = '".$user_id."'";
            $r = $this->mysqli->query($query) or die($this->mysqli->error.__LINE__);
            if($r->num_rows > 0)
            {
                while($row = $r->fetch_object())
                {
                    $_SESSION['uid'] = $row->username; 
                    $data = array( 'uid' => $row->username , 'type' => 'success', 'status' => 'Logged-in');
                }
            }
            else
            {
                $data = array( 'uid' => 'Not_logged_in' , 'type' => 'error', 'status' => 'Login failed. Incorrect credentials');
            }
            
            $this->response($this->json($data),200);
         }
         /**
         * funciton for authentication
         * @return array either sucess or failure
         */
         public function checkUserEmail()
         {
            if($this->get_request_method() != "POST"){
				$this->response('',406);
			}
            
            $post_value = json_decode(file_get_contents("php://input"),true);
            $user_id = $post_value['user_id'];
            $query = "SELECT sno,username,userpass FROM group_profile WHERE username = '".$user_id."'";
            $r = $this->mysqli->query($query) or die($this->mysqli->error.__LINE__);
            if($r->num_rows > 0)
            {
                while($row = $r->fetch_object())
                {
                    $data = array( 'email' => $row->username);
                }
            }
            else
            {
                $data = array( 'email' => '' );
            }
            
            $this->response($this->json($data),200);
         }
         /**
         * funciton for logout
         * @return array either sucess or failure
         */
         public function logout()
         {
             unset($_SESSION['user_image']);
             unset($_SESSION['username']);
             unset($_SESSION['user_id']);
         }
         /**
         * Get Member Details 
         * @return array either sucess or failure
         */
         public function memberDetails()
         {
            if($this->get_request_method() != "POST"){
				$this->response('',406);
			}
            $post_value = $this->get_post_array();
            $user_id = $post_value['user_id'];
            $action = $post_value['action'];
            if($action == 'public' ||$action == 'private' )//added private for code testing need to be removed later one ** Mufaddal **
            {
            $data = array();
            $r = $this->db_pg->getSelectedRC('first_name, image_url, last_name','user_details','user_id = '.$user_id.' LIMIT 1');
            if($r == false)
            {
                $this->response($this->json(array('type'=>'error', 'status' => $this->db_pg->error())),200);
            }
			if($this->db_pg->numRows($r) > 0) 
            {
               
                while($row = $r->fetch_object())
                { 
                    $data['memberDetails'][] = array('id'=>$user_id,
                    'firstname' =>$row->first_name,
                    'imageUrl' =>$row->image_url,
                    'lastname' =>$row->last_name);
                    
                }
            }
            $r = $this->db_pg->getSelectedRC('field_name, field_value, field_type','user_profile_field','user_id = '.$user_id.' 
                                                AND access_by = "public"');
            if($r == false)
            {
                $this->response($this->json(array('type'=>'error', 'status' => $this->db_pg->error())),200);
            }
			if($this->db_pg->numRows($r) > 0) 
            {
                while($row = $r->fetch_object())
                {
                    $data['member_profile_fields'][] = array('field_name'=>$row->field_name,
                    'field_value' =>$row->field_value,
                    'field_type' =>$row->field_type);
                }
            }
            
            $this->response($this->json($data), 200);
            }//public section ends
            else if($action == 'private')
            {
                $q_str = "SELECT * FROM user_details WHERE user_id = ".$user_id." LIMIT 1";
                $data = array();
                $r = $this->db_pg->executeQuery($q_str);
    			if($this->db_pg->numRows($r) > 0) 
                {
                   
                     while($row = mysqli_fetch_object($r))
                    { 
                        $data['memberDetails'] = array('id'=>$row->user_id,
                        'firstname' =>$row->first_name,
                        'imageUrl' =>$row->image_url,
                        'lastname' =>$row->last_name);
                      
    
                        
                    }
                }
            }//private if ends
            $this->response($this->json($data), 200);

         }


        /**
         * funciton for authentication
         * @return array either sucess or failure
         */
         public function addItem()
         {
            if($this->get_request_method() != "POST"){
				$this->response('',406);
			}
            
            $post_value = $this->get_post_array();
            $user_id = $post_value['user_id'];
            $query = "SELECT sno,username,userpass FROM group_profile WHERE username = '".$user_id."'";
            $r = $this->mysqli->query($query) or die($this->mysqli->error.__LINE__);
            if($r->num_rows > 0)
            {
                while($row = $r->fetch_object())
                {
                    $data = array( 'email' => $row->username);
                }
            }
            else
            {
                $data = array( 'email' => '' );
            }
            
            $this->response($this->json($data),200);
         }
         /**
         * Add Items in User profile
         * @return Success or Error
         */
        public function  addItemById()
        {
            if($this->get_request_method() != "POST"){
        		$this->response('',406);
        	}
            $post_value = $this->get_post_array();
            
//added_by_user_id: $scope.username,
//added_to_user_id: $routeParams.churchId,
//type: addItem.type,
//label: addItem.labelname,
//fvalue: addItem.fvalue,
//access_key: addItem.passkey,
//access_by: addItem.accessby,
//ck_data: addItem.ck_data,
//access_to: addItem.access_private,
//date_time: addItem.date_time,
//increment_by: addItem.increment_by,
//increment_when: addItem.increment_when,
//grouplabel: addItem.grouplabel,
//member: addItem.member,
//membername: addItem.membername,
//email: addItem.email

            $type               = $post_value['type'];
//            $ck_editor_data = $post_value['ck_data'];
//            $access_private = $post_value['access_private'];
//            $date_time      = $post_value['date_time'];
//            $group_name     = $post_value['grouplabel'];
//            $increment_by   = $post_value['increment_by'];
//            $increment_when = $post_value['increment_when'];
//            $member         = $post_value['member'];
//            $membername     = $post_value['membername'];
//            $email          = $post_value['email'];     
                    
//            $this->response($this->json($post_value), 200);
            if($type == 'number')
            {
                $access_by          = $post_value['access_by'];
                if($access_by == 'public')
                {
                    $label              = $post_value['label'];
                    $fvalue             = $post_value['fvalue'];
                    $added_by_user_id   = $post_value['added_by_user_id'];
                    $added_to_user_id   = $post_value['added_to_user_id'];
                    
                    $data = array(  'user_id' => $added_to_user_id, 
                                    'field_name' => $label,
                                    'field_type' => $type,
                                    'field_value' => $fvalue,
                                    'access_by' => "public");
                    $r = $this->db_pg->insertRowSetArray('user_profile_field',$data);   
             
                    if($this->db_pg->numAffected() > 0)
                    {
                        $data = array('type' => 'success', 'status' => 'Item Added Successfully');
                    }
                    else
                    {
                        $data = array('type' => 'error', 'status' => 'Post Falied. Item already exist');
                    }
                		$this->response($this->json($data), 200); // Send Respose back to controller
                }
                else if ($access = 'private')
                {
                    
                }
            }
            else if($type == 'date')
            {
                $access_by          = $post_value['access_by'];
                if($access_by == 'public')
                {
                   $label = $post_value['label'];
                   $fdatetime = $post_value['date_time'];
                   $added_by_user_id   = $post_value['added_by_user_id'];
                   $added_to_user_id   = $post_value['added_to_user_id'];
                   
                   $data = array(  'user_id' => $added_to_user_id, 
                                    'field_name' => $label,
                                    'field_type' => $type,
                                    'field_value' => $fdatetime,
                                    'access_by' => "public");
                    $r = $this->db_pg->insertRowSetArray('user_profile_field',$data);   
             
                    if($this->db_pg->numAffected() > 0)
                    {
                        $data = array('type' => 'success', 'status' => 'Date Added Successfully');
                    }
                    else
                    {
                        $data = array('type' => 'error', 'status' => 'Post Falied');
                    }
                		$this->response($this->json($data), 200); // Send Respose back to controller
                    
                }
                else if ($access = 'private')
                {
                    
                }
                
            }
            else if($type == 'shorttext')
            {
                $access_by          = $post_value['access_by'];
                if($access_by == 'public')
                {
                    $label              = $post_value['label'];
                    $fvalue             = $post_value['fvalue'];
                    $added_by_user_id   = $post_value['added_by_user_id'];
                    $added_to_user_id   = $post_value['added_to_user_id'];
                   
                    $data = array(  'user_id' => $added_to_user_id, 
                                    'field_name' => $label,
                                    'field_type' => $type,
                                    'field_value' => $fvalue,
                                    'access_by' => "public");
                    $r = $this->db_pg->insertRowSetArray('user_profile_field',$data);   
             
                    if($this->db_pg->numAffected() > 0)
                    {
                        $data = array('type' => 'success', 'status' => 'Short Text Added Successfully');
                    }
                    else
                    {
                        $data = array('type' => 'error', 'status' => 'Post Falied.');
                    }
                		$this->response($this->json($data), 200); // Send Respose back to controller
                }
                else if ($access = 'private')
                {
                    
                }
            }
            else if($type == 'blob')
            {
                $access_by          = $post_value['access_by'];
                if($access_by == 'public')
                {
                    $label = $post_value['label'];
                    $fvalue = $post_value['ck_data'];
                    $added_by_user_id   = $post_value['added_by_user_id'];
                    $added_to_user_id   = $post_value['added_to_user_id'];
                    
                    $data = array(  'user_id' => $added_to_user_id, 
                                    'field_name' => $label,
                                    'field_type' => $type,
                                    'field_value' => $fvalue,
                                    'access_by' => "public");
                    $r = $this->db_pg->insertRowSetArray('user_profile_field',$data);   
             
                    if($this->db_pg->numAffected() > 0)
                    {
                        $data = array('type' => 'success', 'status' => 'Long Text/Blob Added Successfully');
                    }
                    else
                    {
                        $data = array('type' => 'error', 'status' => 'Post Falied.');
                    }
                		$this->response($this->json($data), 200); // Send Respose back to controller
                }
                else if ($access = 'private')
                {
                    
                }
            }
            else if($type == 'image')
            {
                if($access_by == 'public')
                {
                    
                }
                else if ($access = 'private')
                {
                    
                }
            }
            else if($type == 'group')
            {
                $added_by_user_id = $post_value['added_by_user_id'];
                $group_name       = $post_value['grouplabel'];
                $member_list      = $post_value['member'];
                //validation starts
                if(empty($group_name))
                {
                    $this->response($this->json(array('type' => 'error', 'status' =>'Please Enter Group Name')), 200);
                }
                if(!empty($member_list))
                {
                    if(count($member_list) == 0)
                    {
                        $this->response($this->json(array('type' => 'error', 'status' =>'Please Add Atlest One Member in Group')), 200);
                    }
                }
                else
                {
                    $this->response($this->json(array('type' => 'error', 'status' =>'Please Add Atlest One Member in Group')), 200);
                }
                //validation ends
                
                if(isset($post_value['is_private']))
                {
                    $access      = ($post_value['is_private'] == true ? 'private': 'public');
                }
                else
                {
                    $access      = 'public';
                }
                
                $data = array(  
                                'added_by' => $added_by_user_id,
                                'name'     => $group_name,
                                'access'   => $access
                );
                $r = $this->db_pg->insertRowSetArray('groups',$data);// create group
                if($r == false)
                {
                    $this->response($this->json(array('type'=>'error', 'status' => $this->db_pg->error())),200);
                }
                if($this->db_pg->numAffected() > 0)
                {
                    $group_id = $this->db_pg->lastInsertId();
                    $data = null;
                    foreach($member_list as $row)
                    {
                        $data[] = array('group_id' => $group_id, 'user_id' => $row);
                        
                    }
                    $r = $this->db_pg->insertBatch('group_members',$data);//insert group members

                    if($r == false)
                    {
                        $this->response($this->json(array('type'=>'error', 'status' => $this->db_pg->error())),200);
                    }
                    else
                    {   
                        $data = array('type' => 'success', 'status' => 'Group Create Successfully');
                    }
                }
                else
                {
                    $data = array('type' => 'error', 'status' => 'Group Creation Falied outer');
                }
                $this->response($this->json($data), 200);
                
            }
            else if($type == 'individual')
            {
                
            }
            
            $query = "INSERT INTO add_item SET emailid = '".$email."', key_label = '".$label."', value_label = '".$fvalue."', 
                        access_by = '".$access."', pass_key = '".$passkey."', _type = '".$type."'";
                        
            $r = $this->db_pg->insertRowSet('user_profile_field','');                        
            if($this->db_pg->numAffected() > 0)
            {
                $data = array('type' => 'success', 'status' => 'Item Added Successfully');
            }
            else
            {
                $data = array('type' => 'error', 'status' => 'Post Falied. Item already exist');
            }
        		$this->response($this->json($data), 200); // Send Respose back to controller
        }
        /**
         * Set user data!   
         * @return Success or Error
         */
        public function  signup()
        {
            if($this->get_request_method() != "POST")
            {
        		$this->response('',406);
        	}
            $post_value = $this->get_post_array();
            $post_value = json_decode($post_value['data'],true);
            
            $username    = isset($post_value['emailid']) === FALSE ? null : $post_value['emailid'];
            $password    = isset($post_value['password']) === FALSE ? null : $post_value['password'];
            $fname       = isset($post_value['fname']) === FALSE ? null : $post_value['fname'];
            $lname       = isset($post_value['lname']) === FALSE ? null : $post_value['lname'];
            $cn_password = isset($post_value['cnpassword']) === FALSE ? null : $post_value['cnpassword'];
            
            //validation starts
            if(empty($username))
            {
                $data = array('type' => 'error', 'status' => 'Please Enter Email Id!');
                $this->response($this->json($data), 200); // Send Respose back to controller
            }            
            if(empty($password))
            {
                $data = array('type' => 'error', 'status' => 'Please Enter Password!');
                $this->response($this->json($data), 200); // Send Respose back to controller
            }
            if(empty($password))
            {
                $data = array('type' => 'error', 'status' => 'Please Enter Confirm Password!');
                $this->response($this->json($data), 200); // Send Respose back to controller
            }
            if(empty($fname))
            {
                $data = array('type' => 'error', 'status' => 'Please Enter First Name!');
                $this->response($this->json($data), 200); // Send Respose back to controller
            }
            if(empty($lname))
            {
                $data = array('type' => 'error', 'status' => 'Please Enter Last Name!');
                $this->response($this->json($data), 200); // Send Respose back to controller
            }
            if(strcmp($password,$cn_password) != 0)
            {
                $data = array('type' => 'error', 'status' => 'Password and Confirm Password does not match!');
                $this->response($this->json($data), 200); // Send Respose back to controller
            }
            // validation ends
            $user_image = $_FILES['file'];
            $r = $this->db_pg->getRow("user_master","username = '".$this->db_pg->escape($username)."'");
            if($this->db_pg->numRows($r) > 0)
            {
                $data = array('type' => 'error', 'status' => 'Email Id already exist!');
                $this->response($this->json($data), 200); // Send Respose back to controller
            }
            
             
            $activation_key = md5(uniqid(rand(), true));
            $current_time = date('Y-m-d H:i:s',time());
            $file_prefix = time();
            //file upload code do not delete or uncomment
            
            if($user_image['name'] != null)
            {
                // upload checks and size shrink
                $file_name = $file_prefix.$user_image['name'];
                $user_image['name'] = $file_name;
                if(!move_uploaded_file($user_image["tmp_name"] , "../".self::IMAGE_USER.$file_name ))
                {
                    $data = array('type' => 'error', 'status' => 'File Upload Failed please try again');
                    $this->response($this->json($data), 200); 
                }
            }
            else
            {
                $data = array('type' => 'error', 'status' => 'Profile Image is Mandatory!');
                $this->response($this->json($data), 200); 
            }

            $r = $this->db_pg->insertRow('user_master','username, password, activation_code, status, date_created',
                                                       "'".strtolower($username)."', '".$password."', 
                                                       '".$activation_key."', 0 , '".$current_time."'");
                                                       
            $this->response($this->json(array('type' => 'error', 'status' => $this->db_pg->error())), 200);
            
            if($this->db_pg->numAffected() > 0)
            {   
                if($r > 0)
                {
                    $user_id = $this->db_pg->lastInsertId();
                    $r = $this->db_pg->insertRow('user_details','user_id, first_name, last_name, image_url',
                                                           "".$user_id.", '".$fname."', '".$lname."', '".self::IMAGE_USER.$file_name."'");
                    if($r > 0)
                    {
                        //email code
                        
                        $data = array('type' => 'success', 'status' => 'User Successfully Created! Please check your email');
                    }
                    else
                    {
                        $r = $this->db_pg->deleteOneRow('user_master','id = '.$user_id);
                        $data = array('type' => 'error', 'status' => 'User Creation Failed!');
                    }
                }
                else
                {
                    
                    $data = array('type' => 'error', 'status' => 'User Creation Failed!');
                }
            }
            else
            {
                $data = array('type' => 'error', 'status' => 'User Creation Failed!');
            }
            
            $this->response($this->json($data), 200); // Send Respose back to controller
        }
        /**
         * Add Group
         * @return Success or Error
         */
        public function  addGroup()
        {
            if($this->get_request_method() != "POST"){
        		$this->response('',406);
        	}
            $post_value = json_decode(file_get_contents("php://input"),true);
            $group = $post_value['group'];
            $search_query = $post_value['query'];
            $id = $post_value['id'];
            $icon = $post_value['icon'];
            
            $query = "INSERT INTO group_info SET emailid = '".$id."', 
                                                 group_name = '".$group."', 
                                                 icon_url = '".$icon."', 
                                                 group_query = '".$search_query."'";
        	$r = $this->mysqli->query($query) or die($this->mysqli->error.__LINE__);
            if($this->mysqli->affected_rows > 0)
            {
                $this->prepareData();
                $data = array('type' => 'success', 'status' => 'Group Successfully Created.');
            }
            else
            {
                $data = array('type' => 'error', 'status' => 'Group Creation Failed.');
            }
            
        	$this->response($this->json(array('values' => $data)), 200); // Send Respose back to controller
        }
        /**
         * Add User Profile
         * @return Success or Error
         */
        public function  addUserProfile()
        {
            if($this->get_request_method() != "POST"){
        		$this->response('',406);
        	}
            $post_value = json_decode(file_get_contents("php://input"),true);
            // manu fields yet not added
            $group_profile = $post_value['group_profile'];
            $group_img = $post_value['group_img'];
            $fname = $post_value['fname'];
            $lname = $post_value['lname'];
            $display_name = $post_value['displayngroup'];
            $children = $post_value['children'];
            $group_membership = $post_value['group_membership'];
            $mobile_text = $post_value['mobile_text'];
            $long_form_text = $post_value['long_form_text'];
            $voice = $post_value['voice'];
            $videochat = $post_value['videochat'];
            $image_mail = $post_value['image_mail'];
            $id = $fname."-".$lname;
            $query = "INSERT INTO group_profile SET id = '".$id."', 
                                                 imageurl = 'img/users/preview.jpg', 
                                                 first_name = '".$fname."', 
                                                 last_name = '".$lname."',
                                                 children = '".$children."',
                                                 contact = '".$last_name."',
                                                 
                                                 ";
        	$r = $this->mysqli->query($query) or die($this->mysqli->error.__LINE__);
            if($this->mysqli->affected_rows > 0)
            {
                $this->prepareData();
                $data = array('type' => 'success', 'status' => 'Group Profile Successfully Created.');
            }
            else
            {
                $data = array('type' => 'error', 'status' => 'Group Profile Creation Failed.'.$post_value);
            }
        		$this->response($this->json(array('values' => $data)), 200); // Send Respose back to controller
        }
        /**
         * Get All Group name for given email id 
         * @return group data array 
         */ 	
         public function getGroupName()
         {
            if($this->get_request_method() != "POST"){
				$this->response('',406);
			}
            $post_value = json_decode(file_get_contents("php://input"),true);
            $emailid = $post_value['emailid'];
			$query="SELECT group_name FROM group_info where emailid = '".$emailid."'";
			$r = $this->mysqli->query($query) or die($this->mysqli->error.__LINE__);
			if($r->num_rows > 0) 
            {
                $data = array();
                while($row = $r->fetch_Object())
                {
                    $data[] = array('groupname' =>$row->content);
                    
                }
				$this->response($this->json($data), 200); // Send Respose back to controller
			}
			$this->response('',204);
         }
         /**
          * Save Search
          * @return error or success message
          */
          public function saveUserSearch()
          {
            if($this->get_request_method() != "POST")
            {
				$this->response('',406);
			}
            $post_val = $this->get_post_array();
            $search_name = $this->db_pg->escape($post_val['group']);
            $s_query = $this->db_pg->escape($post_val['query']);
            $user_id = $this->db_pg->escape($post_val['id']);
            $icon_id = $this->db_pg->escape($post_val['icon']);
            
            if(empty($search_name))
            {
                $this->response($this->json(array('status' => 'Search name is mandatory!', 'type' =>'error')), 200);
            }
            
            $r = $this->db_pg->insertRowSet('user_search',"user_id ='".$user_id."' , icon_id = '".$icon_id."', 
                                                    search_name = '".$search_name."', search_query = '".$s_query."'");
            if($r)
            {
                $this->response($this->json(array('status' => 'Search saved succesfully!', 'type' =>'success')), 200); 
            }
            else
            {
                $this->response($this->json(array('status' => 'Error while saving search!', 'type' =>'error')), 200); 
            }
            
            $this->response($this->json(''), 200); 
          }
         /**
         * Get Profile of Member
         * @return array of all users
         */
        public function  getUserDetails()
        {
            if($this->get_request_method() != "POST"){
				$this->response('',406);
			}
            $post_value = $this->get_post_array();
            $user_id = $this->db_pg->escape($post_value['user_id']);
			$r = $this->db_pg->executeQuery("SELECT um.username, ud.user_id, ud.first_name, ud.last_name, ud.address, ud.city, ud.state, ud.zip, 
                                                ud.image_url, ud.contact_1, ud.contact_2, ud.contact_3, um.last_login, 
                                                um.date_created, ud.access_key
                                            FROM user_details ud
                                            INNER JOIN user_master um ON um.id = ud.user_id
                                            WHERE um.status = 1 AND um.id = ".$user_id);
            
            if($r == false)
            {
                $this->response($this->json(array('type' => 'error','status' => $this->db_pg->error())), 200);
            }
			if($this->db_pg->numRows($r) > 0) 
            {
                $data = array();
                while($row = mysqli_fetch_object($r))
                {
                    $image = null;
                    if(file_exists("../".$row->image_url))
                    {
                        $image = $row->image_url;
                    }
                    else
                    {
                        $image = 'img/users/preview.jpg';
                    }
                    $data[] = array
                              ( //'age'            => $row->age,
                                'id'             => $row->user_id,
                                'email_id'       => $row->username,
                                'imageUrl'       => $image,
                                'first_name'     => $row->first_name,
                                'last_name'      => $row->last_name,
                                'contact_1'      => $row->contact_1,
                                'contact_2'      => $row->contact_2,
                                'contact_3'      => $row->contact_3,
                                'address'        => $row->address,
                                'city'           => $row->city,
                                'state'          => $row->state,
                                'zip'            => $row->zip,
                                'last_login'     => $row->last_login,
                                'date_created'   => $row->date_created,
                                'access_key'     => $row->access_key
                              );
                }
				$this->response($this->json($data), 200); // Send Respose back to controller
			}
			$this->response('',204);
        } 
        /**
         * Set/update user password
         * @return array either sucess or failure
         */
         public function setNewPassword()
         {
            if($this->get_request_method() != "POST"){
				$this->response('',406);
			}
            
            //$post_value = json_decode(file_get_contents("php://input"),true);
            $post_value = $this->get_post_array();
            
            $user_id        = $this->db_pg->escape($post_value['user_id']);
            $old_password   = $this->db_pg->escape($post_value['old_password']);
            $new_password   = $this->db_pg->escape($post_value['new_password']);
            $con_password   = $this->db_pg->escape($post_value['con_password']);
            
            // validation start
            if(empty($user_id))
            {
                $data = array( 'type' => 'error', 'status' => 'User id not found');
                $this->response($this->json($data),200);
            }
            else if(empty($old_password))
            {
                $data = array( 'type' => 'error', 'status' => 'Please Enter Old Password');
                $this->response($this->json($data),200);
            }
            else if(empty($new_password))
            {
                $data = array( 'type' => 'error', 'status' => 'Please Enter New Password');
                $this->response($this->json($data),200);
            }
            else if(empty($con_password))
            {
                $data = array( 'type' => 'error', 'status' => 'Please Enter Confirm Password');
                $this->response($this->json($data),200);
            }
            else if(strcmp($new_password,$con_password) != 0)
            {
                $data = array( 'type' => 'error', 'status' => 'New password and Confirm password Does not match');
                $this->response($this->json($data),200);
            }
            
            // validation ends
            
            $r = $this->db_pg->getRow('user_master',"id = '".$user_id."' AND password = '".$old_password."'");
            $has_rows = (int)$this->db_pg->numRows($r); 
            if($has_rows > 0)
            {
                $r = $this->db_pg->updateRow('user_master','password = "'.$new_password.'"',' id = '.$user_id );
                if($r == false)
                {
                    $this->response($this->json(array('type' => 'error','status' => $this->db_pg->error())), 200);
                }
                if($this->db_pg->numAffected() > 0)
                {
                    $data = array( 'type' => 'success', 'status' => 'Password Updated');
                    $this->response($this->json($data),200);
                } 
            }
            else
            {
                $data = array( 'type' => 'error', 'status' => 'Old password Does not match');
                $this->response($this->json($data),200);
            }
        }
        /**
         * update user details
         * @return array either sucess or failure
         */
         public function updateUserDetails()
         {
            if($this->get_request_method() != "POST"){
				$this->response('',406);
			}
            $post_value = $this->get_post_array();
            $user_id    = $this->db_pg->escape($post_value['user_id']);
            $first_name = $this->db_pg->escape($post_value['first_name']);
            $last_name  = $this->db_pg->escape($post_value['last_name']);
            $email_id   = $this->db_pg->escape($post_value['email_id']);
            $contact_1  = $this->db_pg->escape($post_value['contact_1']);
            $contact_2  = $this->db_pg->escape($post_value['contact_2']);
            $contact_3  = $this->db_pg->escape($post_value['contact_3']);
            $address    = $this->db_pg->escape($post_value['address']);
            $city       = $this->db_pg->escape($post_value['city']);
            $zip        = $this->db_pg->escape($post_value['zip']);
            $state      = $this->db_pg->escape($post_value['state']);
            $access_key = $this->db_pg->escape($post_value['access_key']);
            
            // validation start
            if(empty($user_id))
            {
                $data = array( 'type' => 'error', 'status' => 'User id not found');
                $this->response($this->json($data),200);
            }
            else if(empty($first_name))
            {
                $data = array( 'type' => 'error', 'status' => 'Please Enter First Name');
                $this->response($this->json($data),200);
            }
            else if(empty($last_name))
            {
                $data = array( 'type' => 'error', 'status' => 'Please Enter Last Name');
                $this->response($this->json($data),200);
            }
            else if(empty($email_id))
            {
                $data = array( 'type' => 'error', 'status' => 'Please Enter Email id');
                $this->response($this->json($data),200);
            }
            else if(empty($access_key))
            {
                $data = array( 'type' => 'error', 'status' => 'Please Enter Access Key');
                $this->response($this->json($data),200);
            }
            
            
            // validation ends
            
            $r = $this->db_pg->updateRow('user_details','first_name = "'.$first_name.'",
                                                         last_name  = "'.$last_name.'",
                                                         address = "'.$address.'",
                                                         city = "'.$city.'",
                                                         state = "'.$state.'",
                                                         zip ="'.$zip.'",
                                                         contact_1 = "'.$contact_1.'",
                                                         contact_2 = "'.$contact_2.'",
                                                         contact_3 = "'.$contact_3.'",
                                                         access_key = "'.$access_key.'"',
                                        ' user_id = '.$user_id );
            if($r == false)
            {
                $this->response($this->json(array('type' => 'error','status' => $this->db_pg->error())), 200);
            }
            if($this->db_pg->numAffected() > 0)
            {
                $data = array( 'type' => 'success', 'status' => 'Profile Updated Sucessfully');
                $this->response($this->json($data),200);
            }
            else
            {
                $data = array( 'type' => 'warning', 'status' => 'Profile Not Updated');
                $this->response($this->json($data),200);
            } 
            
        }
        /**
         * Get detail of group
         * @return array of group data
         */
        public function getGroupDetails()
        {
            if($this->get_request_method() != "POST"){
				$this->response('',406);
			}
            $post_value = $this->get_post_array();
            
            $group_id = isset($post_value['group_id']) === FALSE ? null : $this->db_pg->escape($post_value['group_id']);
            $user_id  = isset($_SESSION['user_id']) === FALSE ? null : $_SESSION['user_id'];
                                         
            $r = $this->db_pg->getSelectedRC('added_by, image, name, access, date_created, group_title, 
                                                pass_key, access','groups','id = '.$group_id);
            if($r == false)
            {
                $this->response($this->json(array('type' => 'error','status' => $this->db_pg->error())), 200);
            }
            $access = "";
			if($this->db_pg->numRows($r) > 0) 
            {
                $data = array();
                while($row = $r->fetch_object())
                {
                    $image = null;
                    if(file_exists("../".$row->image))
                    {
                        $image = $row->image;
                    }
                    else
                    {
                        $image = 'img/users/preview.jpg';
                    }
                    $access = $row->access;
                    $data['group_details'][] = array
                              ( 
                                'group_id'       => $group_id,
                                'group_name'     => $row->name,
                                'imageUrl'       => $image,
                                'access'         => $row->access,
                                'date_created'   => $row->date_created,
                                'group_title'    => $row->group_title,
                                'pass_key'       => $row->pass_key,
                                'access'         => $row->access
                              );
                }
                // if private group then cannot be access directly from url 
                if($access == 'private' )
                {
                    if(!empty($user_id))
                    {
                    	$query="SELECT grp.id FROM groups grp 
                                    INNER JOIN group_members gm ON gm.group_id = grp.id 
                                    WHERE gm.user_id= ".$user_id." AND grp.id = ".$group_id;
            			$r = $this->db_pg->executeQuery($query);
            			if($this->db_pg->numRows($r) == 0) 
                        {
                            $data = array('type' => 'passkeyrequired','status' => "Not a member of this group Cannot view it!");
        				    $this->response($this->json($data), 200); // Send Respose back to controller
            			}
                    }
                    else
                    {
                        $this->response($this->json(array('type' => 'error','status' => "Please login to access this group!")), 200);
                    }
                }
                $query = "SELECT ud.`user_id`, ud.`first_name`,ud.`last_name` FROM groups grp
                            INNER JOIN group_members gm ON gm.`group_id` = grp.`id`
                            INNER JOIN user_details ud ON ud.`user_id` = gm.`user_id`
                            WHERE grp.id = ".$group_id;
                $r = $this->db_pg->executeQuery($query);
                if($r == false)
                {
                    $this->response($this->json(array('type' => 'error','status' => $this->db_pg->error())), 200);
                } 
                if($this->db_pg->numRows($r) > 0) 
                {
                    while($row =$r->fetch_object())
                    {
                        $data['group_members'][] = array
                                  ( 
                                    'user_id'        => $row->user_id,
                                    'first_name'     => $row->first_name,
                                    'last_name'      => $row->last_name,
                                  );
                    }
    			}    
				$this->response($this->json(array('type' => 'success', 'data' => $data)), 200); // Send Respose back to controller
			}
            $this->response($this->json(array('type' => 'error','status' => 'Group not found! Maybe its removed')), 200);
        } 
        /**
         * Add Item to Group Profile
         * @param group id and fileds required
         * @return Error or Sucess message
         */
         public function addGroupItem()
         {
            if($this->get_request_method() != "POST"){
        		$this->response('',406);
        	}
            $post_value = $this->get_post_array();
//added_by_user_id: $scope.username,
//added_to_user_id: $routeParams.churchId,
//type: addItem.type,
//label: addItem.labelname,
//fvalue: addItem.fvalue,
//access_key: addItem.passkey,
//access_by: addItem.accessby,
//ck_data: addItem.ck_data,
//access_to: addItem.access_private,
//date_time: addItem.date_time,
//increment_by: addItem.increment_by,
//increment_when: addItem.increment_when,
//grouplabel: addItem.grouplabel,
//member: addItem.member,
//membername: addItem.membername,
//email: addItem.email

            $type               = $post_value['type'];
//            $ck_editor_data = $post_value['ck_data'];
//            $access_private = $post_value['access_private'];
//            $date_time      = $post_value['date_time'];
//            $group_name     = $post_value['grouplabel'];
//            $increment_by   = $post_value['increment_by'];
//            $increment_when = $post_value['increment_when'];
//            $member         = $post_value['member'];
//            $membername     = $post_value['membername'];
//            $email          = $post_value['email'];     
                    
//            $this->response($this->json($post_value), 200);
            if($type == 'number')
            {
                $access_by = isset($post_value['access_by']) === FALSE ? null : $post_value['access_by'];
                if(empty($access_by))
                {
                    $data = array('type' => 'error', 'status' => 'Please Select Access By');
                	$this->response($this->json($data), 200); 
                }
                if($access_by == 'public')// for public access
                {
                    $label              = isset($post_value['label']) === FALSE ? null : $post_value['label'];
                    $fvalue             = isset($post_value['fvalue']) === FALSE ? null : $post_value['fvalue']; 
                    $added_by_user_id   = $post_value['added_by_user_id'];
                    $added_to_group_id  = $post_value['added_to_group_id'];

                    if(empty($label))
                    {
                        $data = array('type' => 'error', 'status' => 'Please Enter Field Name');
                		$this->response($this->json($data), 200); 
                    }
                    else if(empty($fvalue))
                    {
                        $data = array('type' => 'error', 'status' => 'Please Enter Field Value');
                		$this->response($this->json($data), 200); 
                    } 
                    
                    $data = array(  'group_id' => $added_to_group_id, 
                                    'field_name' => $label,
                                    'field_type' => $type,
                                    'field_value' => $fvalue,
                                    'access_by' => "public");
                    $r = $this->db_pg->insertRowSetArray('group_field',$data); 
                      
                    if($r == false)
                    {
                        $this->response($this->json(array('type'=>'error', 'status' => $this->db_pg->error())),200);
                    }
                    
                    if($this->db_pg->numAffected() > 0)
                    {
                        $data = array('type' => 'success', 'status' => 'Item Added Successfully');
                    }
                    else
                    {
                        $data = array('type' => 'error', 'status' => 'Post Falied. Item already exist');
                    }
                	$this->response($this->json($data), 200); // Send Respose back to controller
                }//public access ends
                else if ($access = 'private')// if private selected starts here
                {
                    //$this->response($this->json(array($post_value)), 200); // Send Respose back to controller
                    
                    $added_by_user_id   = $post_value['added_by_user_id'];
                    $added_to_group_id  = $post_value['added_to_group_id'];
                    
                    $label              = isset($post_value['label']) === FALSE ? null : $post_value['label'];
                    $fvalue             = isset($post_value['fvalue']) === FALSE ? null : $post_value['fvalue']; 
                    $view_access        = isset($post_value['view_access']) === FALSE ? null : $post_value['view_access'];
                    $view_passkey       = isset($post_value['view_passkey']) === FALSE ? null : $post_value['view_passkey'];
                    $view_group         = isset($post_value['view_group']) === FALSE ? null : $post_value['view_group'];
                    $edit_access        = isset($post_value['edit_access']) === FALSE ? null : $post_value['edit_access'];
                    $edit_passkey       = isset($post_value['edit_passkey']) === FALSE ? null : $post_value['edit_passkey'];
                    $edit_group         = isset($post_value['edit_group']) === FALSE ? null : $post_value['edit_group'];
                    // validation starts    
                    
                    if(empty($label))
                    {
                        $data = array('type' => 'error', 'status' => 'Please Enter Field Name');
                		$this->response($this->json($data), 200); 
                    }
                    else if(empty($fvalue))
                    {
                        $data = array('type' => 'error', 'status' => 'Please Enter Field Value');
                		$this->response($this->json($data), 200); 
                    } 
                    if($view_access == 2 ) // 1. only me 2. any one with pass key 3. anyone in group
                    {
                        if(empty($view_passkey))
                        {
                            $data = array('type' => 'error', 'status' => 'Please Enter View Password');
                		    $this->response($this->json($data), 200);
                        }
                    }
                    else if ($view_access == 3) // only to selected group can view this content
                    {
                        if(empty($view_group))
                        {
                            $data = array('type' => 'error', 'status' => 'Please Select Group who can view');
                		    $this->response($this->json($data), 200);
                        }
                    }
                    if($edit_access == 2 ) // 1. only me 2. any one with pass key 3. anyone in group
                    {
                        if(empty($edit_passkey))
                        {
                            $data = array('type' => 'error', 'status' => 'Please Enter Edit Password');
                		    $this->response($this->json($data), 200);
                        }
                    }
                    else if ($edit_access == 3) // only to selected group can edit this content
                    {
                        if(empty($edit_group))
                        {
                            $data = array('type' => 'error', 'status' => 'Please Select Group who can edit');
                		    $this->response($this->json($data), 200);
                        }
                    }
                    // validation ends
                    
                    $data = array(  'group_id'      => $added_to_group_id, 
                                    'field_name'    => $label,
                                    'field_type'    => $type,
                                    'field_value'   => $fvalue,
                                    'access_by'     => "private",
                                    'user_id'       => $added_by_user_id,
                                    'view_access'   => $view_access,
                                    'view_passkey'  => $view_passkey,
                                    'edit_access'   => $edit_access,
                                    'edit_passkey'  => $edit_passkey, 
                                    'view_group_id' => $view_group,
                                    'edit_group_id' => $edit_group                                   
                                    );
                    $r = $this->db_pg->insertRowSetArray('group_field',$data); 
                      
                    if($r == false)
                    {
                        $this->response($this->json(array('type'=>'error', 'status' => $this->db_pg->error())),200);
                    }
                    
                    if($this->db_pg->numAffected() > 0)
                    {
                        $data = array('type' => 'success', 'status' => 'Item Added Successfully');
                    }
                    else
                    {
                        $data = array('type' => 'error', 'status' => 'Post Falied. Item already exist');
                    }
                	$this->response($this->json($data), 200); // Send Respose back to controller
                }// priavte access ends
            }
            else if($type == 'date')
            {
                $access_by = isset($post_value['access_by']) === FALSE ? null : $post_value['access_by'];
                if(empty($access_by))
                {
                    $data = array('type' => 'error', 'status' => 'Please Select Access By');
                	$this->response($this->json($data), 200); 
                }
                if($access_by == 'public')
                {
                   $label = isset($post_value['label']) === FALSE ? null : $post_value['label'];
                   $fdatetime = isset($post_value['date_time']) === FALSE ? null : $post_value['date_time'];
                   $added_by_user_id   = $post_value['added_by_user_id'];
                   $added_to_group_id   = $post_value['added_to_group_id'];
                   if(empty($label))
                   {
                       $data = array('type' => 'error', 'status' => 'Please Enter Field Name');
                	   $this->response($this->json($data), 200); 
                   }
                   else if(empty($fdatetime))
                   {
                       $data = array('type' => 'error', 'status' => 'Please Enter Date');
                       $this->response($this->json($data), 200); 
                   } 
                   
                   $data = array(   'group_id' => $added_to_group_id, 
                                    'field_name' => $label,
                                    'field_type' => $type,
                                    'field_value' => $fdatetime,
                                    'access_by' => "public");
                    $r = $this->db_pg->insertRowSetArray('group_field',$data);  
                     
                    if($r == false)
                    {
                        $this->response($this->json(array('type'=>'error', 'status' => $this->db_pg->error())),200);
                    }
                    
                    if($this->db_pg->numAffected() > 0)
                    {
                        $data = array('type' => 'success', 'status' => 'Date Added Successfully');
                    }
                    else
                    {
                        $data = array('type' => 'error', 'status' => 'Post Falied');
                    }
                		$this->response($this->json($data), 200); // Send Respose back to controller
                    
                }//public access ends
                else if ($access = 'private') // private access start
                {
                    $added_by_user_id   = $post_value['added_by_user_id'];
                    $added_to_group_id  = $post_value['added_to_group_id'];
                    
                    $label              = isset($post_value['label']) === FALSE ? null : $post_value['label'];
                    $fdatetime          = isset($post_value['date_time']) === FALSE ? null : $post_value['date_time']; 
                    
                    $view_access        = isset($post_value['view_access']) === FALSE ? null : $post_value['view_access'];
                    $view_passkey       = isset($post_value['view_passkey']) === FALSE ? null : $post_value['view_passkey'];
                    $edit_access        = isset($post_value['edit_access']) === FALSE ? null : $post_value['edit_access'];
                    $edit_passkey       = isset($post_value['edit_passkey']) === FALSE ? null : $post_value['edit_passkey'];
                    
                    // validation starts    
                    
                    if(empty($label))
                    {
                        $data = array('type' => 'error', 'status' => 'Please Enter Field Name');
                		$this->response($this->json($data), 200); 
                    }
                    else if(empty($fdatetime))
                    {
                        $data = array('type' => 'error', 'status' => 'Please Enter Date');
                		$this->response($this->json($data), 200); 
                    } 
                    if($view_access == 2 ) // 1. only me 2. any one with pass key 3. anyone in group
                    {
                        if(empty($view_passkey))
                        {
                            $data = array('type' => 'error', 'status' => 'Please Enter View Password');
                		    $this->response($this->json($data), 200);
                        }
                    }
                    if($edit_access == 2 ) // 1. only me 2. any one with pass key 3. anyone in group
                    {
                        if(empty($edit_passkey))
                        {
                            $data = array('type' => 'error', 'status' => 'Please Enter Edit Password');
                		    $this->response($this->json($data), 200);
                        }
                    }
                    // validation ends
                    
                    $data = array(  'group_id'      => $added_to_group_id, 
                                    'field_name'    => $label,
                                    'field_type'    => $type,
                                    'field_value'   => $fdatetime,
                                    'access_by'     => "private",
                                    'user_id'       => $added_by_user_id,
                                    'view_access'   => $view_access,
                                    'view_passkey'  => $view_passkey,
                                    'edit_access'   => $edit_access,
                                    'edit_passkey'  => $edit_passkey,                                    
                                    );
                    $r = $this->db_pg->insertRowSetArray('group_field',$data);  
                     
                    if($r == false)
                    {
                        $this->response($this->json(array('type'=>'error', 'status' => $this->db_pg->error())),200);
                    }
                    
                    if($this->db_pg->numAffected() > 0)
                    {
                        $data = array('type' => 'success', 'status' => 'Date Added Successfully');
                    }
                    else
                    {
                        $data = array('type' => 'error', 'status' => 'Post Falied');
                    }
                		$this->response($this->json($data), 200); // Send Respose back to controller
                }// private access ends
                
            }
            else if($type == 'shorttext')
            {
                $access_by = isset($post_value['access_by']) === FALSE ? null : $post_value['access_by'];
                if(empty($access_by))
                {
                    $data = array('type' => 'error', 'status' => 'Please Select Access By');
                	$this->response($this->json($data), 200); 
                }
                if($access_by == 'public')
                {
                    $label              = isset($post_value['label']) === FALSE ? null : $post_value['label'];
                    $fvalue             = isset($post_value['fvalue']) === FALSE ? null : $post_value['fvalue']; 
                    $added_by_user_id   = $post_value['added_by_user_id'];
                    $added_to_group_id  = $post_value['added_to_group_id'];

                    if(empty($label))
                    {
                        $data = array('type' => 'error', 'status' => 'Please Enter Field Name');
                		$this->response($this->json($data), 200); 
                    }
                    else if(empty($fvalue))
                    {
                        $data = array('type' => 'error', 'status' => 'Please Enter Field Value');
                		$this->response($this->json($data), 200); 
                    } 
                   
                    $data = array(  'group_id' => $added_to_group_id, 
                                    'field_name' => $label,
                                    'field_type' => $type,
                                    'field_value' => $fvalue,
                                    'access_by' => "public");
                    $r = $this->db_pg->insertRowSetArray('group_field',$data);   
                    if($r == false)
                    {
                        $this->response($this->json(array('type'=>'error', 'status' => $this->db_pg->error())),200);
                    }
                    if($this->db_pg->numAffected() > 0)
                    {
                        $data = array('type' => 'success', 'status' => 'Short Text Added Successfully');
                    }
                    else
                    {
                        $data = array('type' => 'error', 'status' => 'Post Falied.');
                    }
                		$this->response($this->json($data), 200); // Send Respose back to controller
                }//public ends
                else if ($access = 'private') // private access start
                {
                    $label              = isset($post_value['label']) === FALSE ? null : $post_value['label'];
                    $fvalue             = isset($post_value['fvalue']) === FALSE ? null : $post_value['fvalue']; 
                    
                    $added_by_user_id   = $post_value['added_by_user_id'];
                    $added_to_group_id  = $post_value['added_to_group_id'];
                    
                    $view_access        = isset($post_value['view_access']) === FALSE ? null : $post_value['view_access'];
                    $view_passkey       = isset($post_value['view_passkey']) === FALSE ? null : $post_value['view_passkey'];
                    $edit_access        = isset($post_value['edit_access']) === FALSE ? null : $post_value['edit_access'];
                    $edit_passkey       = isset($post_value['edit_passkey']) === FALSE ? null : $post_value['edit_passkey'];
                    
                    // validation starts    
                    
                    if(empty($label))
                    {
                        $data = array('type' => 'error', 'status' => 'Please Enter Field Name');
                		$this->response($this->json($data), 200); 
                    }
                    else if(empty($fvalue))
                    {
                        $data = array('type' => 'error', 'status' => 'Please Enter Field Value');
                		$this->response($this->json($data), 200); 
                    } 
                    if($view_access == 2 ) // 1. only me 2. any one with pass key 3. anyone in group
                    {
                        if(empty($view_passkey))
                        {
                            $data = array('type' => 'error', 'status' => 'Please Enter View Password');
                		    $this->response($this->json($data), 200);
                        }
                    }
                    if($edit_access == 2 ) // 1. only me 2. any one with pass key 3. anyone in group
                    {
                        if(empty($edit_passkey))
                        {
                            $data = array('type' => 'error', 'status' => 'Please Enter Edit Password');
                		    $this->response($this->json($data), 200);
                        }
                    }
                    // validation ends
                    
                    $data = array(  'group_id'      => $added_to_group_id, 
                                    'field_name'    => $label,
                                    'field_type'    => $type,
                                    'field_value'   => $fvalue,
                                    'access_by'     => "private",
                                    'user_id'       => $added_by_user_id,
                                    'view_access'   => $view_access,
                                    'view_passkey'  => $view_passkey,
                                    'edit_access'   => $edit_access,
                                    'edit_passkey'  => $edit_passkey,                                    
                                    );
                    
                    $r = $this->db_pg->insertRowSetArray('group_field',$data);   
                    if($r == false)
                    {
                        $this->response($this->json(array('type'=>'error', 'status' => $this->db_pg->error())),200);
                    }
                    if($this->db_pg->numAffected() > 0)
                    {
                        $data = array('type' => 'success', 'status' => 'Short Text Added Successfully');
                    }
                    else
                    {
                        $data = array('type' => 'error', 'status' => 'Post Falied.');
                    }
                		$this->response($this->json($data), 200); // Send Respose back to controller
                }// private access ends
            }
            else if($type == 'blob')
            {
                $access_by = isset($post_value['access_by']) === FALSE ? null : $post_value['access_by'];
                if(empty($access_by))
                {
                    $data = array('type' => 'error', 'status' => 'Please Select Access By');
                	$this->response($this->json($data), 200); 
                }
                if($access_by == 'public')
                {
                    $label              = isset($post_value['label']) === FALSE ? null : $post_value['label'];
                    $fvalue             = isset($post_value['ck_data']) === FALSE ? null : $post_value['ck_data']; 
                    $added_by_user_id   = $post_value['added_by_user_id'];
                    $added_to_group_id  = $post_value['added_to_group_id'];

                    if(empty($label))
                    {
                        $data = array('type' => 'error', 'status' => 'Please Enter Field Name');
                		$this->response($this->json($data), 200); 
                    }
                    else if(empty($fvalue))
                    {
                        $data = array('type' => 'error', 'status' => 'Please Enter Field Value');
                		$this->response($this->json($data), 200); 
                    } 
                   
                    $data = array(  'group_id' => $added_to_group_id, 
                                    'field_name' => $label,
                                    'field_type' => $type,
                                    'field_value' => $fvalue,
                                    'access_by' => "public");
                    $r = $this->db_pg->insertRowSetArray('group_field',$data);   
                    if($r == false)
                    {
                        $this->response($this->json(array('type'=>'error', 'status' => $this->db_pg->error())),200);
                    }
                    if($this->db_pg->numAffected() > 0)
                    {
                        $data = array('type' => 'success', 'status' => 'Long Text Added Successfully');
                    }
                    else
                    {
                        $data = array('type' => 'error', 'status' => 'Post Falied.');
                    }
                		$this->response($this->json($data), 200); // Send Respose back to controller
                }//public ends
                else if ($access = 'private') // private access start
                {
                    $label              = isset($post_value['label']) === FALSE ? null : $post_value['label'];
                    $fvalue             = isset($post_value['ck_data']) === FALSE ? null : $post_value['ck_data'];
                    
                    $added_by_user_id   = $post_value['added_by_user_id'];
                    $added_to_group_id  = $post_value['added_to_group_id'];
                    
                    $view_access        = isset($post_value['view_access']) === FALSE ? null : $post_value['view_access'];
                    $view_passkey       = isset($post_value['view_passkey']) === FALSE ? null : $post_value['view_passkey'];
                    $edit_access        = isset($post_value['edit_access']) === FALSE ? null : $post_value['edit_access'];
                    $edit_passkey       = isset($post_value['edit_passkey']) === FALSE ? null : $post_value['edit_passkey'];
                    
                    // validation starts    
                    
                    if(empty($label))
                    {
                        $data = array('type' => 'error', 'status' => 'Please Enter Field Name');
                		$this->response($this->json($data), 200); 
                    }
                    else if(empty($fvalue))
                    {
                        $data = array('type' => 'error', 'status' => 'Please Enter Field Value');
                		$this->response($this->json($data), 200); 
                    } 
                    if($view_access == 2 ) // 1. only me 2. any one with pass key 3. anyone in group
                    {
                        if(empty($view_passkey))
                        {
                            $data = array('type' => 'error', 'status' => 'Please Enter View Password');
                		    $this->response($this->json($data), 200);
                        }
                    }
                    if($edit_access == 2 ) // 1. only me 2. any one with pass key 3. anyone in group
                    {
                        if(empty($edit_passkey))
                        {
                            $data = array('type' => 'error', 'status' => 'Please Enter Edit Password');
                		    $this->response($this->json($data), 200);
                        }
                    }
                    // validation ends
                    
                    $data = array(  'group_id'      => $added_to_group_id, 
                                    'field_name'    => $label,
                                    'field_type'    => $type,
                                    'field_value'   => $fvalue,
                                    'access_by'     => "private",
                                    'user_id'       => $added_by_user_id,
                                    'view_access'   => $view_access,
                                    'view_passkey'  => $view_passkey,
                                    'edit_access'   => $edit_access,
                                    'edit_passkey'  => $edit_passkey,                                    
                                    );
                    
                    $r = $this->db_pg->insertRowSetArray('group_field',$data);   
                    if($r == false)
                    {
                        $this->response($this->json(array('type'=>'error', 'status' => $this->db_pg->error())),200);
                    }
                    if($this->db_pg->numAffected() > 0)
                    {
                        $data = array('type' => 'success', 'status' => 'Long Text Added Successfully');
                    }
                    else
                    {
                        $data = array('type' => 'error', 'status' => 'Post Falied.');
                    }
                		$this->response($this->json($data), 200); // Send Respose back to controller
                }// private access ends
                
                
                
            }
            else if($type == 'image')
            {
                $access_by = isset($post_value['access_by']) === FALSE ? null : $post_value['access_by'];
                if(empty($access_by))
                {
                    $data = array('type' => 'error', 'status' => 'Please Select Access By');
                	$this->response($this->json($data), 200); 
                }
                if($access_by == 'public')
                {
                    $label              = isset($post_value['label']) === FALSE ? null : $post_value['label'];
                    $file_name          = isset($post_value['file_name']) === FALSE ? null : $post_value['file_name']; 
                    $added_by_user_id   = $post_value['added_by_user_id'];
                    $added_to_group_id  = $post_value['added_to_group_id'];

                    if(empty($label))
                    {
                        $data = array('type' => 'error', 'status' => 'Please Enter Field Name');
                		$this->response($this->json($data), 200); 
                    }
                    else if(empty($file_name))
                    {
                        $data = array('type' => 'error', 'status' => 'Please Select File to upload');
                		$this->response($this->json($data), 200); 
                    } 
                   
                    $data = array(  'group_id' => $added_to_group_id, 
                                    'field_name' => $label,
                                    'field_type' => $type,
                                    'field_value' => $file_name,
                                    'access_by' => "public");
                    $r = $this->db_pg->insertRowSetArray('group_field',$data);   
                    if($r == false)
                    {
                        $this->response($this->json(array('type'=>'error', 'status' => $this->db_pg->error())),200);
                    }
                    if($this->db_pg->numAffected() > 0)
                    {
                        $data = array('type' => 'success', 'status' => 'Image Added Successfully');
                    }
                    else
                    {
                        $data = array('type' => 'error', 'status' => 'Post Falied.');
                    }
                		$this->response($this->json($data), 200); // Send Respose back to controller
                }//public ends
                else if ($access = 'private') // private access start
                {
                    $label              = isset($post_value['label']) === FALSE ? null : $post_value['label'];
                    $file_name          = isset($post_value['file_name']) === FALSE ? null : $post_value['file_name'];
                    
                    $added_by_user_id   = $post_value['added_by_user_id'];
                    $added_to_group_id  = $post_value['added_to_group_id'];
                    
                    $view_access        = isset($post_value['view_access']) === FALSE ? null : $post_value['view_access'];
                    $view_passkey       = isset($post_value['view_passkey']) === FALSE ? null : $post_value['view_passkey'];
                    $edit_access        = isset($post_value['edit_access']) === FALSE ? null : $post_value['edit_access'];
                    $edit_passkey       = isset($post_value['edit_passkey']) === FALSE ? null : $post_value['edit_passkey'];
                    
                    // validation starts    
                    
                    if(empty($label))
                    {
                        $data = array('type' => 'error', 'status' => 'Please Enter Field Name');
                		$this->response($this->json($data), 200); 
                    }
                    else if(empty($file_name))
                    {
                        $data = array('type' => 'error', 'status' => 'Please Select Image to Upload');
                		$this->response($this->json($data), 200); 
                    } 
                    if($view_access == 2 ) // 1. only me 2. any one with pass key 3. anyone in group
                    {
                        if(empty($view_passkey))
                        {
                            $data = array('type' => 'error', 'status' => 'Please Enter View Password');
                		    $this->response($this->json($data), 200);
                        }
                    }
                    if($edit_access == 2 ) // 1. only me 2. any one with pass key 3. anyone in group
                    {
                        if(empty($edit_passkey))
                        {
                            $data = array('type' => 'error', 'status' => 'Please Enter Edit Password');
                		    $this->response($this->json($data), 200);
                        }
                    }
                    // validation ends
                    
                    $data = array(  'group_id'      => $added_to_group_id, 
                                    'field_name'    => $label,
                                    'field_type'    => $type,
                                    'field_value'   => $file_name,
                                    'access_by'     => "private",
                                    'user_id'       => $added_by_user_id,
                                    'view_access'   => $view_access,
                                    'view_passkey'  => $view_passkey,
                                    'edit_access'   => $edit_access,
                                    'edit_passkey'  => $edit_passkey,                                    
                                    );
                    
                    $r = $this->db_pg->insertRowSetArray('group_field',$data);   
                    if($r == false)
                    {
                        $this->response($this->json(array('type'=>'error', 'status' => $this->db_pg->error())),200);
                    }
                    if($this->db_pg->numAffected() > 0)
                    {
                        $data = array('type' => 'success', 'status' => 'File Added Successfully');
                    }
                    else
                    {
                        $data = array('type' => 'error', 'status' => 'Post Falied.');
                    }
                		$this->response($this->json($data), 200); // Send Respose back to controller
                }// private access ends
            }
            else if($type == 'group')
            {
                $added_by_user_id = isset($post_value['added_by_user_id']) === FALSE ? null : $post_value['added_by_user_id'];
                $group_name       = isset($post_value['grouplabel']) === FALSE ? null : $post_value['grouplabel'];
                $member_list      = isset($post_value['member']) === FALSE ? null : $post_value['member'];
                
                // its basically id of group in which this new group would be added as a child group.
                $parent_group_id  = isset($post_value['added_to_group_id']) === FALSE ? -1 : $post_value['added_to_group_id'];
                
                /* Validation starts */
                if(empty($added_by_user_id))
                {
                    $this->response($this->json(array('type' => 'error', 'status' =>'You Must Login In order to create group!')), 200);
                }
                else if(empty($group_name))
                {
                    $this->response($this->json(array('type' => 'error', 'status' =>'Please Enter Group Name')), 200);
                }
                if(!empty($member_list))
                {
                    if(count($member_list) == 0)
                    {
                        $this->response($this->json(array('type' => 'error', 'status' =>'Please Add Atlest One Member in Group')), 200);
                    }
                }
                else
                {
                    $this->response($this->json(array('type' => 'error', 'status' =>'Please Add Atlest One Member in Group')), 200);
                }
                //validation ends
                
                if(isset($post_value['is_private']))
                {
                    $access      = ($post_value['is_private'] == true ? 'private': 'public');
                }
                else
                {
                    $access      = 'public';
                }
                
                $data = array(  
                                'added_by'          => $added_by_user_id,
                                'name'              => $group_name,
                                'access'            => $access,
                                'parent_group_id'   => $parent_group_id
                );
                $r = $this->db_pg->insertRowSetArray('groups',$data);// create group
                if($r == false)
                {
                    $this->response($this->json(array('type'=>'error', 'status' => $this->db_pg->error())),200);
                }
                if($this->db_pg->numAffected() > 0)
                {
                    $group_id = $this->db_pg->lastInsertId();
                    $data = null;
                    foreach($member_list as $row)
                    {
                        $data[] = array('group_id' => $group_id, 'user_id' => $row);
                        
                    }
                    $r = $this->db_pg->insertBatch('group_members',$data);//insert group members

                    if($r == false)
                    {
                        $this->response($this->json(array('type'=>'error', 'status' => $this->db_pg->error())),200);
                    }
                    else
                    {   
                        $data = array('type' => 'success', 'status' => 'Group Create Successfully');
                    }
                }
                else
                {
                    $data = array('type' => 'error', 'status' => 'Group Creation Falied!');
                }
                $this->response($this->json($data), 200);
                
            }
            else if($type == 'individual')
            {
                
            }
            
        	$this->response($this->json($data), 200); // Send Respose back to controller
        }
        /**
         * Add Item to Group Profile
         * @param group id and fileds required
         * @return Error or Sucess message
         */
         public function updateGroupItem()
         {
            if($this->get_request_method() != "POST"){
        		$this->response('',406);
        	}
            $post_value = $this->get_post_array();
//added_by_user_id: $scope.username, for refrence DO NOT DELETE 
//added_to_user_id: $routeParams.churchId,
//type: addItem.type,
//label: addItem.labelname,
//fvalue: addItem.fvalue,
//access_key: addItem.passkey,
//access_by: addItem.accessby,
//ck_data: addItem.ck_data,
//access_to: addItem.access_private,
//date_time: addItem.date_time,
//increment_by: addItem.increment_by,
//increment_when: addItem.increment_when,
//grouplabel: addItem.grouplabel,
//member: addItem.member,
//membername: addItem.membername,
//email: addItem.email

            $type               = $post_value['type'];
            if($type == 'number')
            {
                $access_by = isset($post_value['access_by']) === FALSE ? null : $post_value['access_by'];
                if(empty($access_by))
                {
                    $data = array('type' => 'error', 'status' => 'Please Select Access By');
                	$this->response($this->json($data), 200); 
                }
                if($access_by == 'public')// for public access
                {
                    $label              = isset($post_value['label']) === FALSE ? null : $post_value['label'];
                    $fvalue             = isset($post_value['fvalue']) === FALSE ? null : $post_value['fvalue']; 
                    $added_by_user_id   = $post_value['added_by_user_id'];
                    $added_to_group_id  = $post_value['added_to_group_id'];

                    if(empty($label))
                    {
                        $data = array('type' => 'error', 'status' => 'Please Enter Field Name');
                		$this->response($this->json($data), 200); 
                    }
                    else if(empty($fvalue))
                    {
                        $data = array('type' => 'error', 'status' => 'Please Enter Field Value');
                		$this->response($this->json($data), 200); 
                    } 
                    
                    $data = array(  'group_id' => $added_to_group_id, 
                                    'field_name' => $label,
                                    'field_type' => $type,
                                    'field_value' => $fvalue,
                                    'access_by' => "public");
                    $r = $this->db_pg->updateRowArray('group_field',$data); 
                      
                    if($r == false)
                    {
                        $this->response($this->json(array('type'=>'error', 'status' => $this->db_pg->error())),200);
                    }
                    
                    if($this->db_pg->numAffected() > 0)
                    {
                        $data = array('type' => 'success', 'status' => 'Item Added Successfully');
                    }
                    else
                    {
                        $data = array('type' => 'error', 'status' => 'Post Falied. Item already exist');
                    }
                	$this->response($this->json($data), 200); // Send Respose back to controller
                }//public access ends
                else if ($access = 'private')// if private selected starts here
                {
                    //$this->response($this->json(array($post_value)), 200); // Send Respose back to controller
                    
                    $added_by_user_id   = $post_value['added_by_user_id'];
                    $added_to_group_id  = $post_value['added_to_group_id'];
                    
                    $label              = isset($post_value['label']) === FALSE ? null : $post_value['label'];
                    $fvalue             = isset($post_value['fvalue']) === FALSE ? null : $post_value['fvalue']; 
                    $view_access        = isset($post_value['view_access']) === FALSE ? null : $post_value['view_access'];
                    $view_passkey       = isset($post_value['view_passkey']) === FALSE ? null : $post_value['view_passkey'];
                    $edit_access        = isset($post_value['edit_access']) === FALSE ? null : $post_value['edit_access'];
                    $edit_passkey       = isset($post_value['edit_passkey']) === FALSE ? null : $post_value['edit_passkey'];
                    
                    // validation starts    
                    
                    if(empty($label))
                    {
                        $data = array('type' => 'error', 'status' => 'Please Enter Field Name');
                		$this->response($this->json($data), 200); 
                    }
                    else if(empty($fvalue))
                    {
                        $data = array('type' => 'error', 'status' => 'Please Enter Field Value');
                		$this->response($this->json($data), 200); 
                    } 
                    if($view_access == 2 ) // 1. only me 2. any one with pass key 3. anyone in group
                    {
                        if(empty($view_passkey))
                        {
                            $data = array('type' => 'error', 'status' => 'Please Enter View Password');
                		    $this->response($this->json($data), 200);
                        }
                    }
                    if($edit_access == 2 ) // 1. only me 2. any one with pass key 3. anyone in group
                    {
                        if(empty($edit_passkey))
                        {
                            $data = array('type' => 'error', 'status' => 'Please Enter Edit Password');
                		    $this->response($this->json($data), 200);
                        }
                    }
                    // validation ends
                    
                    $data = array(  'group_id'      => $added_to_group_id, 
                                    'field_name'    => $label,
                                    'field_type'    => $type,
                                    'field_value'   => $fvalue,
                                    'access_by'     => "private",
                                    'user_id'       => $added_by_user_id,
                                    'view_access'   => $view_access,
                                    'view_passkey'  => $view_passkey,
                                    'edit_access'   => $edit_access,
                                    'edit_passkey'  => $edit_passkey,                                    
                                    );
                    $r = $this->db_pg->insertRowSetArray('group_field',$data); 
                      
                    if($r == false)
                    {
                        $this->response($this->json(array('type'=>'error', 'status' => $this->db_pg->error())),200);
                    }
                    
                    if($this->db_pg->numAffected() > 0)
                    {
                        $data = array('type' => 'success', 'status' => 'Item Added Successfully');
                    }
                    else
                    {
                        $data = array('type' => 'error', 'status' => 'Post Falied. Item already exist');
                    }
                	$this->response($this->json($data), 200); // Send Respose back to controller
                }// priavte access ends
            }
            else if($type == 'date')
            {
                $access_by = isset($post_value['access_by']) === FALSE ? null : $post_value['access_by'];
                if(empty($access_by))
                {
                    $data = array('type' => 'error', 'status' => 'Please Select Access By');
                	$this->response($this->json($data), 200); 
                }
                if($access_by == 'public')
                {
                   $label = isset($post_value['label']) === FALSE ? null : $post_value['label'];
                   $fdatetime = isset($post_value['date_time']) === FALSE ? null : $post_value['date_time'];
                   $added_by_user_id   = $post_value['added_by_user_id'];
                   $added_to_group_id   = $post_value['added_to_group_id'];
                   if(empty($label))
                   {
                       $data = array('type' => 'error', 'status' => 'Please Enter Field Name');
                	   $this->response($this->json($data), 200); 
                   }
                   else if(empty($fdatetime))
                   {
                       $data = array('type' => 'error', 'status' => 'Please Enter Date');
                       $this->response($this->json($data), 200); 
                   } 
                   
                   $data = array(   'group_id' => $added_to_group_id, 
                                    'field_name' => $label,
                                    'field_type' => $type,
                                    'field_value' => $fdatetime,
                                    'access_by' => "public");
                    $r = $this->db_pg->insertRowSetArray('group_field',$data);  
                     
                    if($r == false)
                    {
                        $this->response($this->json(array('type'=>'error', 'status' => $this->db_pg->error())),200);
                    }
                    
                    if($this->db_pg->numAffected() > 0)
                    {
                        $data = array('type' => 'success', 'status' => 'Date Added Successfully');
                    }
                    else
                    {
                        $data = array('type' => 'error', 'status' => 'Post Falied');
                    }
                		$this->response($this->json($data), 200); // Send Respose back to controller
                    
                }//public access ends
                else if ($access = 'private') // private access start
                {
                    $added_by_user_id   = $post_value['added_by_user_id'];
                    $added_to_group_id  = $post_value['added_to_group_id'];
                    
                    $label              = isset($post_value['label']) === FALSE ? null : $post_value['label'];
                    $fdatetime          = isset($post_value['date_time']) === FALSE ? null : $post_value['date_time']; 
                    
                    $view_access        = isset($post_value['view_access']) === FALSE ? null : $post_value['view_access'];
                    $view_passkey       = isset($post_value['view_passkey']) === FALSE ? null : $post_value['view_passkey'];
                    $edit_access        = isset($post_value['edit_access']) === FALSE ? null : $post_value['edit_access'];
                    $edit_passkey       = isset($post_value['edit_passkey']) === FALSE ? null : $post_value['edit_passkey'];
                    
                    // validation starts    
                    
                    if(empty($label))
                    {
                        $data = array('type' => 'error', 'status' => 'Please Enter Field Name');
                		$this->response($this->json($data), 200); 
                    }
                    else if(empty($fdatetime))
                    {
                        $data = array('type' => 'error', 'status' => 'Please Enter Date');
                		$this->response($this->json($data), 200); 
                    } 
                    if($view_access == 2 ) // 1. only me 2. any one with pass key 3. anyone in group
                    {
                        if(empty($view_passkey))
                        {
                            $data = array('type' => 'error', 'status' => 'Please Enter View Password');
                		    $this->response($this->json($data), 200);
                        }
                    }
                    if($edit_access == 2 ) // 1. only me 2. any one with pass key 3. anyone in group
                    {
                        if(empty($edit_passkey))
                        {
                            $data = array('type' => 'error', 'status' => 'Please Enter Edit Password');
                		    $this->response($this->json($data), 200);
                        }
                    }
                    // validation ends
                    
                    $data = array(  'group_id'      => $added_to_group_id, 
                                    'field_name'    => $label,
                                    'field_type'    => $type,
                                    'field_value'   => $fdatetime,
                                    'access_by'     => "private",
                                    'user_id'       => $added_by_user_id,
                                    'view_access'   => $view_access,
                                    'view_passkey'  => $view_passkey,
                                    'edit_access'   => $edit_access,
                                    'edit_passkey'  => $edit_passkey,                                    
                                    );
                    $r = $this->db_pg->insertRowSetArray('group_field',$data);  
                     
                    if($r == false)
                    {
                        $this->response($this->json(array('type'=>'error', 'status' => $this->db_pg->error())),200);
                    }
                    
                    if($this->db_pg->numAffected() > 0)
                    {
                        $data = array('type' => 'success', 'status' => 'Date Added Successfully');
                    }
                    else
                    {
                        $data = array('type' => 'error', 'status' => 'Post Falied');
                    }
                		$this->response($this->json($data), 200); // Send Respose back to controller
                }// private access ends
                
            }
            else if($type == 'shorttext')
            {
                 $access_by = isset($post_value['access_by']) === FALSE ? null : $post_value['access_by'];
                if(empty($access_by))
                {
                    $data = array('type' => 'error', 'status' => 'Please Select Access By');
                	$this->response($this->json($data), 200); 
                }
                if($access_by == 'public')
                {
                    $label              = isset($post_value['label']) === FALSE ? null : $post_value['label'];
                    $fvalue             = isset($post_value['fvalue']) === FALSE ? null : $post_value['fvalue']; 
                    $added_by_user_id   = $post_value['added_by_user_id'];
                    $added_to_group_id  = $post_value['added_to_group_id'];

                    if(empty($label))
                    {
                        $data = array('type' => 'error', 'status' => 'Please Enter Field Name');
                		$this->response($this->json($data), 200); 
                    }
                    else if(empty($fvalue))
                    {
                        $data = array('type' => 'error', 'status' => 'Please Enter Field Value');
                		$this->response($this->json($data), 200); 
                    } 
                   
                    $data = array(  'group_id' => $added_to_group_id, 
                                    'field_name' => $label,
                                    'field_type' => $type,
                                    'field_value' => $fvalue,
                                    'access_by' => "public");
                    $r = $this->db_pg->insertRowSetArray('group_field',$data);   
                    if($r == false)
                    {
                        $this->response($this->json(array('type'=>'error', 'status' => $this->db_pg->error())),200);
                    }
                    if($this->db_pg->numAffected() > 0)
                    {
                        $data = array('type' => 'success', 'status' => 'Short Text Added Successfully');
                    }
                    else
                    {
                        $data = array('type' => 'error', 'status' => 'Post Falied.');
                    }
                		$this->response($this->json($data), 200); // Send Respose back to controller
                }//public ends
                else if ($access = 'private') // private access start
                {
                    $label              = isset($post_value['label']) === FALSE ? null : $post_value['label'];
                    $fvalue             = isset($post_value['fvalue']) === FALSE ? null : $post_value['fvalue']; 
                    
                    $added_by_user_id   = $post_value['added_by_user_id'];
                    $added_to_group_id  = $post_value['added_to_group_id'];
                    
                    $view_access        = isset($post_value['view_access']) === FALSE ? null : $post_value['view_access'];
                    $view_passkey       = isset($post_value['view_passkey']) === FALSE ? null : $post_value['view_passkey'];
                    $edit_access        = isset($post_value['edit_access']) === FALSE ? null : $post_value['edit_access'];
                    $edit_passkey       = isset($post_value['edit_passkey']) === FALSE ? null : $post_value['edit_passkey'];
                    
                    // validation starts    
                    
                    if(empty($label))
                    {
                        $data = array('type' => 'error', 'status' => 'Please Enter Field Name');
                		$this->response($this->json($data), 200); 
                    }
                    else if(empty($fvalue))
                    {
                        $data = array('type' => 'error', 'status' => 'Please Enter Field Value');
                		$this->response($this->json($data), 200); 
                    } 
                    if($view_access == 2 ) // 1. only me 2. any one with pass key 3. anyone in group
                    {
                        if(empty($view_passkey))
                        {
                            $data = array('type' => 'error', 'status' => 'Please Enter View Password');
                		    $this->response($this->json($data), 200);
                        }
                    }
                    if($edit_access == 2 ) // 1. only me 2. any one with pass key 3. anyone in group
                    {
                        if(empty($edit_passkey))
                        {
                            $data = array('type' => 'error', 'status' => 'Please Enter Edit Password');
                		    $this->response($this->json($data), 200);
                        }
                    }
                    // validation ends
                    
                    $data = array(  'group_id'      => $added_to_group_id, 
                                    'field_name'    => $label,
                                    'field_type'    => $type,
                                    'field_value'   => $fvalue,
                                    'access_by'     => "private",
                                    'user_id'       => $added_by_user_id,
                                    'view_access'   => $view_access,
                                    'view_passkey'  => $view_passkey,
                                    'edit_access'   => $edit_access,
                                    'edit_passkey'  => $edit_passkey,                                    
                                    );
                    
                    $r = $this->db_pg->insertRowSetArray('group_field',$data);   
                    if($r == false)
                    {
                        $this->response($this->json(array('type'=>'error', 'status' => $this->db_pg->error())),200);
                    }
                    if($this->db_pg->numAffected() > 0)
                    {
                        $data = array('type' => 'success', 'status' => 'Short Text Added Successfully');
                    }
                    else
                    {
                        $data = array('type' => 'error', 'status' => 'Post Falied.');
                    }
                		$this->response($this->json($data), 200); // Send Respose back to controller
                }// private access ends
            }
            else if($type == 'blob')
            {
                $access_by = isset($post_value['access_by']) === FALSE ? null : $post_value['access_by'];
                
                if($access_by == 'public')
                {
                    $label  = isset($post_value['label']) === FALSE ? null : $post_value['label'];
                    $fvalue = isset($post_value['ck_data']) === FALSE ? null : $post_value['ck_data'];
                    $added_by_user_id   = $post_value['added_by_user_id'];
                    $added_to_group_id  = $post_value['added_to_group_id'];
                   
                    if(empty($label))
                    {
                        $data = array('type' => 'error', 'status' => 'Please Enter Field Name');
                		$this->response($this->json($data), 200); 
                    }
                    else if(empty($fvalue))
                    {
                        $data = array('type' => 'error', 'status' => 'Please Enter Field Value');
                		$this->response($this->json($data), 200); 
                    } 
                    
                    $data = array(  'user_id' => $added_to_group_id, 
                                    'field_name' => $label,
                                    'field_type' => $type,
                                    'field_value' => $fvalue,
                                    'access_by' => "public");
                    $r = $this->db_pg->insertRowSetArray('group_field',$data);   
                    
                    if($r == false)
                    {
                        $this->response($this->json(array('type'=>'error', 'status' => $this->db_pg->error())),200);
                    }
                    
                    if($this->db_pg->numAffected() > 0)
                    {
                        $data = array('type' => 'success', 'status' => 'Long Text/Blob Added Successfully');
                    }
                    else
                    {
                        $data = array('type' => 'error', 'status' => 'Post Falied.');
                    }
                		$this->response($this->json($data), 200); // Send Respose back to controller
                }
                else if ($access = 'private')
                {
                    
                }
            }
            else if($type == 'image')
            {
                if($access_by == 'public')
                {
                    
                }
                else if ($access = 'private')
                {
                    
                }
            }
            else if($type == 'group')
            {
                $added_by_user_id = isset($post_value['added_by_user_id']) === FALSE ? null : $post_value['added_by_user_id'];
                $group_name       = isset($post_value['grouplabel']) === FALSE ? null : $post_value['grouplabel'];
                $member_list      = isset($post_value['member']) === FALSE ? null : $post_value['member'];
                
                // its basically id of group in which this new group would be added as a child group.
                $parent_group_id  = isset($post_value['added_to_group_id']) === FALSE ? -1 : $post_value['added_to_group_id'];
                
                /* Validation starts */
                if(empty($added_by_user_id))
                {
                    $this->response($this->json(array('type' => 'error', 'status' =>'You Must Login In order to create group!')), 200);
                }
                else if(empty($group_name))
                {
                    $this->response($this->json(array('type' => 'error', 'status' =>'Please Enter Group Name')), 200);
                }
                if(!empty($member_list))
                {
                    if(count($member_list) == 0)
                    {
                        $this->response($this->json(array('type' => 'error', 'status' =>'Please Add Atlest One Member in Group')), 200);
                    }
                }
                else
                {
                    $this->response($this->json(array('type' => 'error', 'status' =>'Please Add Atlest One Member in Group')), 200);
                }
                //validation ends
                
                if(isset($post_value['is_private']))
                {
                    $access      = ($post_value['is_private'] == true ? 'private': 'public');
                }
                else
                {
                    $access      = 'public';
                }
                
                $data = array(  
                                'added_by'          => $added_by_user_id,
                                'name'              => $group_name,
                                'access'            => $access,
                                'parent_group_id'   => $parent_group_id
                );
                $r = $this->db_pg->insertRowSetArray('groups',$data);// create group
                if($r == false)
                {
                    $this->response($this->json(array('type'=>'error', 'status' => $this->db_pg->error())),200);
                }
                if($this->db_pg->numAffected() > 0)
                {
                    $group_id = $this->db_pg->lastInsertId();
                    $data = null;
                    foreach($member_list as $row)
                    {
                        $data[] = array('group_id' => $group_id, 'user_id' => $row);
                        
                    }
                    $r = $this->db_pg->insertBatch('group_members',$data);//insert group members

                    if($r == false)
                    {
                        $this->response($this->json(array('type'=>'error', 'status' => $this->db_pg->error())),200);
                    }
                    else
                    {   
                        $data = array('type' => 'success', 'status' => 'Group Create Successfully');
                    }
                }
                else
                {
                    $data = array('type' => 'error', 'status' => 'Group Creation Falied!');
                }
                $this->response($this->json($data), 200);
                
            }
            else if($type == 'individual')
            {
                
            }
            
        	$this->response($this->json($data), 200); // Send Respose back to controller
        }
        /**
         * Get Group Fields private or public accourding to user id 
         * @return array either sucess or failure
         */
         public function getGroupFields()
         {
            if($this->get_request_method() != "POST"){
				$this->response('',406);
			}
            $post_value = $this->get_post_array();
            $user_id = isset($post_value['user_id']) === FALSE ? null : $post_value['user_id'];
            $group_id = isset($post_value['group_id']) === FALSE ? null : $post_value['group_id'];
            $data = array();
            if(empty($group_id))
            {
                $this->response($this->json(array('type'=>'error', 'status' => 'Opps.No Group found')),200);
            }
            
            if(empty($user_id))
            {
            $r = $this->db_pg->getSelectedRC('id as field_id, field_name, field_value, field_type,view_access,edit_access,access_by'
                                                ,'group_field'
                                                ,'group_id = '.$group_id.' AND access_by = "public"');
            }
            else
            {
                
                $r = $this->db_pg->getSelectedRC('id as field_id, field_name, (CASE WHEN view_access = 2
                                                                                THEN "NA"
                                                                                ELSE field_value END) AS field_value, 
                                                        field_type, view_access, edit_access,access_by'
                                                        ,'group_field'
                                                        ,'group_id = '.$group_id.' AND access_by = "public"  
                                                        OR (access_by = "private" AND (view_access = 1 OR view_access = 2 )
                                                        AND user_id = '.$user_id.' AND group_id = '.$group_id.')');
                        
            }
            if($r == false)
            {
                $this->response($this->json(array('type'=>'error', 'status' => $this->db_pg->error())),200);
            } 
			if($this->db_pg->numRows($r) > 0) 
            {
                while($row = $r->fetch_object())
                {
                    $data['group_fields'][] = array('field_name'  => $row->field_name,
                                                    'field_value' => $row->field_value,
                                                    'field_type'  => $row->field_type,
                                                    'field_id'    => $row->field_id,
                                                    'view_access' => $row->view_access,
                                                    'edit_access' => $row->edit_access,
                                                    'access_by'   => $row->access_by
                                                    );
                }
                
            }
            // for access type 3 only user on entered group could view/edit selected field
            $r = $this->db_pg->executeQuery('SELECT gf.id AS field_id, gf.field_name, gf.field_value, 
                                            gf.field_type, gf.view_access, gf.edit_access,gf.access_by
                                            FROM group_field gf
                                            INNER JOIN group_members gm ON gm.group_id = gf.view_group_id
                                            WHERE gf.group_id = "'.$group_id.'" AND gm.user_id = "'.$user_id.'"' );
                        
            //$this->response($this->json(array('type'=>'error', 'status' => "inner".$r)),200);
            if($r == false)
            {
                $this->response($this->json(array('type'=>'error', 'status' => "inner".$this->db_pg->error())),200);
            } 
			if($this->db_pg->numRows($r) > 0) 
            {
                while($row = $r->fetch_object())
                {
                    $data['group_fields'][] = array('field_name'  => $row->field_name,
                                                    'field_value' => $row->field_value,
                                                    'field_type'  => $row->field_type,
                                                    'field_id'    => $row->field_id,
                                                    'view_access' => $row->view_access,
                                                    'edit_access' => $row->edit_access,
                                                    'access_by'   => $row->access_by
                                                    );
                }
                
                
            }
            
            $this->response($this->json(array('type'=>'success', 'data' => $data)),200);
            
         }
         /**
         * Get Members list for group dialog
         * @return array list of all users. Id and Name
         */
        public function  getMemberlistForGroup()
        {
            if($this->get_request_method() != "GET"){
				$this->response('',406);
			}
            
			$r = $this->db_pg->executeQuery("SELECT ud.user_id, ud.first_name, ud.last_name 
                                                FROM user_details ud
                                                INNER JOIN user_master um ON um.id = ud.user_id
                                                WHERE um.status = 1");
			if($this->db_pg->numRows($r) > 0) 
            {
                $data = array();
                while($row = $r->fetch_object())
                {
                    $data[] = array
                              ( 
                                'id'             => $row->user_id,
                                'name'           => $row->first_name." ".$row->last_name,
                              );
                }
				$this->response($this->json($data), 200); // Send Respose back to controller
			}
			$this->response('',204);
            
        }
         /**
         * check passkey for particular field if match show field otheriwse return error.
         * @return array value of field.
         */
        public function checkpassword()
        {
            if($this->get_request_method() != "POST"){
				$this->response('',406);
			}
            
            $post_value = $this->get_post_array();
            $field_id   = isset($post_value['field_id']) === FALSE ? null : $post_value['field_id'];;
            $passkey    = isset($post_value['passkey']) === FALSE ? null : $post_value['passkey'];;
            $type       = $post_value['type'];
            // validation
            if(empty($passkey))
            {
                $this->response($this->json(array('type'=>'error', 'status' => "Please enter passkey!")),200);
            }
            if(empty($field_id))
            {
                $this->response($this->json(array('type'=>'error', 'status' => "Please refresh the page and try again!")),200);
            }
            // validation ends
            if($type == 'view')
            {
                $r = $this->db_pg->getSelectedRC('field_value,field_type,field_name,id','group_field','
                                                                        id = '.$field_id.' AND view_passkey = "'.$passkey.'"');
            }
            else if($type == 'edit')
            {
                $r = $this->db_pg->getSelectedRC('field_value,field_type,field_name,id','group_field',
                                                                        'id = '.$field_id.' AND edit_passkey = "'.$passkey.'"');
            }
            
            if($r == false)
            {
                $this->response($this->json(array('type'=>'error', 'status' => $this->db_pg->error())),200);
            } 
			if($this->db_pg->numRows($r) > 0) 
            {
                $data = array();
                while($row = $r->fetch_object())
                {
                   $data['group_fields'][] = array( 'field_name'  => $row->field_name,
                                                    'field_value' => $row->field_value,
                                                    'field_type'  => $row->field_type,
                                                    'field_id'    => $row->id,
                                                    );
                }
				$this->response($this->json(array('type'=>'success','data' => $data)), 200); // Send Respose back to controller
			}
            else
            {
                $this->response($this->json(array('type'=>'error', 'status' => "Passkey Did not match!")),200);
            }
			$this->response('',204);
            
        }
        /**
         * Get List of group that can share the info privacy Only View/Edit by selected Group
         * @param group id in which data needs to be added
         * @return list of allowed groups 
         */
        public function getAllowedGroup()
        {
            if($this->get_request_method() != "POST")
            {
				$this->response('',406);
			}
            $post_value = $this->get_post_array();
            $group_id = $post_value['group_id'];
            $r = $this->db_pg->getSelectedRC('access', 'groups', 'id = '.$group_id);
            
            if($r == false)
            {
                $this->response($this->json(array('type'=>'error', 'status' => $this->db_pg->error())),200);
            }
            
            if($this->db_pg->numRows($r) > 0) 
            {
                $data = array();
                while($row = $r->fetch_object())
                {
                   $access = $row->access;
                }
                if($access == 'public')
                {
                    $r = $this->db_pg->getSelectedRC('id, name', 'groups', 'access = "public" AND id != '.$group_id,'ORDER BY name DESC');
                    if($r == false)
                    {
                        $this->response($this->json(array('type'=>'error', 'status' => $this->db_pg->error())),200);
                    }
                    if($this->db_pg->numRows($r) > 0) 
                    {
                        $data = array();
                        while($row = $r->fetch_object())
                        {
                           $data['allowed_group'][] = array('group_id'   => $row->id, 
                                                            'group_name' => $row->name
                                                            );
                        }
                        $this->response($this->json(array('type'=>'success','data' => $data)),200);
        			}
                }
                else if($access == 'private')
                {
                    $r = $this->db_pg->executeQuery('SELECT id, name, @pv:=parent_group_id AS "parentid" FROM 
                                                        (SELECT * FROM groups ORDER BY id DESC) a
                                                        JOIN
                                                        (SELECT @pv:='.$group_id.')tmp
                                                        WHERE id=@pv ');
                    if($r == false)
                    {
                        $this->response($this->json(array('type'=>'error', 'status' => $this->db_pg->error())),200);
                    }
                    if($this->db_pg->numRows($r) > 0) 
                    {
                        $data = array();
                        while($row = $r->fetch_object())
                        {
                           if($row->id != $group_id)//not to include called group
                           {
                               $data['allowed_group'][] = array('group_id'   => $row->id, 
                                                                'group_name' => $row->name
                                                                );
                           }
                        }
                        $this->response($this->json(array('type'=>'success','data' =>  $data)),200);
        			}
                }
			}
            else
            {
                $this->response($this->json(array('type'=>'error', 'status' => 'No Group Available')),200);
            }
            
        }
        /**
         * @param length of Random String Default 10
         * @param Name of table in which need to check for unique passkey Default null
         * @return Random string from Alpha Numeric Character Set
         */
         private function randomPassKey($length = 10, $table = null)
         {
            if($this->get_request_method() != "POST")
            {
				$this->response('',406);
			}
            $post_value = $this->get_post_array();
            $table = isset($post_value['table']) == FALSE ? null : $post_value['table'] ;
             
            $letters = 'abcefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890';
            $passkey = substr(str_shuffle($letters), 0, $length);
            if($table == null)
            {
                 $this->response($this->json(array('type'=>'success','data' => $passkey)),200);
            }
            else
            {
                switch($table)
                {
                    case 'group' :
                    break;
                    
                }
                
            }
         }
         /**
          * Update Group Profile data
          * @param group id
          * @param group name
          * @param group title
          * @param access
          * @param pass key
          * @param image link
          * @return Success or Error message
          */
         public function updateGroupProfile()
         {            
            if($this->get_request_method() != "POST")
            {
				$this->response('',406);
			}
            $post_value     = $this->get_post_array();
            $group_id       = isset($post_value['group_id']) === FALSE ? null : $post_value['group_id'] ;
            $name           = isset($post_value['group_name']) === FALSE ? null : $post_value['group_name'] ;
            $group_title    = isset($post_value['group_title']) === FALSE ? null : $post_value['group_title'] ;
            $pass_key       = isset($post_value['pass_key']) === FALSE ? null : $post_value['pass_key'] ;
            $image_url      = isset($post_value['image_url']) === FALSE ? null : $post_value['image_url'] ;
            $access         = isset($post_value['access']) === FALSE ? null : $post_value['access'] ;
            // validation 
            if(empty($group_id))
            {
                $this->response($this->json(array('type'=>'error', 'status' => 'Can not update group please try again!')),200);
            }
            if(empty($name))
            {
                $this->response($this->json(array('type'=>'error', 'status' => 'Please enter group name!')),200);
            }
            if(empty($group_title))
            {
                $this->response($this->json(array('type'=>'error', 'status' => 'Please enter group Title!')),200);
            }
            if(empty($pass_key))
            {
                $this->response($this->json(array('type'=>'error', 'status' => 'Please enter Group Default Pass Key!')),200);
            }
            //validation ends
            if(empty($image_url))
            {
                $data = array(
                              'name'        => $name,
                              'group_title' => $group_title,
                              'pass_key'    => $pass_key,
                              'access'      => $access
                                );
            }
            else
            {
                $data = array(
                              'name'        => $name,
                              'group_title' => $group_title,
                              'pass_key'    => $pass_key,
                              'access'      => $access,
                              'image'       => $image
                                );
            }
            
            $r = $this->db_pg->updateRowArray('groups', $data, 'id = '.$group_id);
            
            if($r == false)
            {
                $this->response($this->json(array('type'=>'error', 'status' => $this->db_pg->error())),200);
            }
            
            if($this->db_pg->numAffected() > 0) 
            {
                $this->response($this->json(array('type'=>'success', 'status' => 'Group Profile Updated!')),200);
			}
            else
            {
                $this->response($this->json(array('type'=>'error', 'status' => 'No Change! Nothing to update!')),200);
            }
            
        }
        /**
         * To upload Image Via add item. 
         * @param file object
         * @param location to upload on
         * @return array location on file, path, error or success message
         */
         public function uploadImage()
         {
            if($this->get_request_method() != "POST")
            {
				$this->response('',406);
			}
            
            $post_array = $this->get_post_array();
            $post_array = json_decode($post_array['data'],true);
           
            $location = isset($post_array['location']) === FALSE ? null : $post_array['location'];
            $user_image = isset($_FILES['file']) === FALSE ? null : $_FILES['file'];
            
            //validation
            if(empty($user_image))
            {
                $data = array('type' => 'error', 'status' => 'Please Select Image!');
                $this->response($this->json($data), 200);
            }
            if(empty($location))
            {
                $data = array('type' => 'error', 'status' => 'Image Location Missing!');
                $this->response($this->json($data), 200);
            }
            // validation ends
            
            $file_prefix = time();
            //file upload code do not delete or uncomment
            
            if($user_image['name'] != null)
            {
                // upload checks and size shrink
                $file_name = $file_prefix.$user_image['name'];
                $user_image['name'] = $file_name;
                if(!move_uploaded_file($user_image["tmp_name"] , $location.$file_name )) 
                {
                    $data = array('type' => 'error', 'status' => 'File Upload Failed please try again');
                    $this->response($this->json($data), 200); 
                }
                else
                {
                    $data = array('type' => 'success', 'file_name' => $file_name);
                    $this->response($this->json($data), 200);
                }
            }
            else
            {
                $data = array('type' => 'error', 'status' => 'Please Select Image!');
                $this->response($this->json($data), 200); 
            }
            
         }
		/**
		 *	Encode array into JSON DONOT CHANGE
		*/
		private function json($data){
			if(is_array($data)){
				return json_encode($data);
			}
		}
        /**
         * Get php input file data for post DONOT CHANGE
         */
         private function get_post_array()
         {
            $val = json_decode(file_get_contents("php://input"),true); 
            if($val != null)
            {
                return $val;
            }
            else if(!empty($this->_request))
            {
                return $this->_request;
            }
            else
            {
                return array('status' => 'No Data Found!', 'type' =>'error');
            }
            
         }
	}
	
	// Initiiate Library
	
	$api = new API;
	$api->processApi();
?>