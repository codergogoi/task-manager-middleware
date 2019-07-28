<?php

/*
    -- TaskManagerBase.php
 *  -- Index.php
 *  -- upload.php
 *  */


/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of TaskManagerAPI
 *
 * @author mac01
 */

class TaskManagerBase {
    
   
    var $hostName = "localhost";
    var $userName = "javacope_demo";
    var $password = "javacope_demo";
    var $dbName = "javacope_task_manager";
                
    
    var $conn = NULL;
    
    var $successCode = 200;
    var $failureCode = 404;
    var $existCode = 202;

    var $title;
    var $description;
           
    
    function connectDB(){
        
        
        date_default_timezone_set("Asia/Kolkata");
        

        $this->conn = mysqli_connect($this->hostName, $this->userName, $this->password, $this->dbName);
        
          if(mysqli_connect_errno()){
            // echo mysqli_connect_error();
             echo 'Fail to connect with database.'.mysqli_connect_error() ;
         } 
         
        if(!$this->conn){
            die("cannot connect to the database");
        } 
        
        mysqli_set_charset($this->conn, 'utf8');
         
    }
        
    function getConn(){
        
        return mysqli_connect($this->hostName, $this->userName, $this->password, $this->dbName);

    }
    
    // ============================= TASK  ==========================
    function addNewTask($data){
                
        $this->connectDB();
         
        $task_name = mysqli_real_escape_string($this->getConn(),$data['title']);
        $task_desc = mysqli_real_escape_string($this->getConn(),$data['description']);
        $task_created = date("Y-m-d");
        $task_deadline  =  mysqli_real_escape_string($this->getConn(),$data['deadline']);
        $task_priority = mysqli_real_escape_string($this->getConn(),$data['priority']);
        $task_point = mysqli_real_escape_string($this->getConn(),$data['points']);
        $task_type = mysqli_real_escape_string($this->getConn(),$data['type']);
        $remarks = mysqli_real_escape_string($this->getConn(),$data['remarks']);
        $task_owner = mysqli_real_escape_string($this->getConn(),$data['owner']);
        
                
        $sql = "INSERT INTO task(task_name,task_desc,task_created,task_deadline,task_priority,task_owner,bonus_point,task_type,remarks)VALUES('$task_name','$task_desc','$task_created','$task_deadline','$task_priority','$task_owner','$task_point','$task_type','$remarks')";


       if(!mysqli_query($this->conn, $sql)) {

          die('error :'. mysqli_error($this->conn));

      }else{
          $this->response($this->successCode, "Sucessfully Created", []);
          return;
      } 
        
    }
    
    function fetchAvailableTask(){ //for admin

            $this->connectDB();
            //            $query = "SELECT * from task WHERE task_assign_to = 0 ORDER BY task_created DESC"; 

            $query = "SELECT * from task WHERE task_assign_to = 0 ORDER BY task_created DESC"; 
                        
            $result = mysqli_query($this->conn,$query);

            $taskArray = array();
                        
            if (mysqli_num_rows($result) > 0){

                while ($row = mysqli_fetch_assoc($result)) {
                    
                        $task_id = $row['task_id'];
                        $task_name = $row['task_name'];
                        $task_desc = $row['task_desc'];
                        $task_created = $row['task_created'];
                        $task_deadline = $row['task_deadline'];
                        $task_assign_to = $row['task_assign_to']; 
                        $task_priority = $row['task_priority'];
                        $task_status = $row['task_status'];
                        $task_point = $row['bonus_point'];
                        $task_type = $row['task_type'];
                        $remarks = $row['remarks'];
                                
                        $taskArray[] = array(
                           "id"=> $task_id,
                           "title"=> $task_name,
                           "details"=> $task_desc,
                           "date"=> $task_created,
                           "deadline"=> $task_deadline,
                           "assigned"=> "Not Assigned",
                           "priority"=>$task_priority,
                           "status"=>$task_status,
                           "points"=> $task_point,
                           "type" => $task_type,
                           "remarks" => $remarks);

                }
                                
            }
            

         $this->response($this->successCode, "Available Task List", $taskArray);

    }
    
    function fetchTaskWithType($taskType, $data){ //for admin

            $this->connectDB();
            
            
            $user_id = mysqli_real_escape_string($this->getConn(),$data['user_id']);
            
            $query = $this->queryForTask($taskType, $user_id);

            $result = mysqli_query($this->conn,$query);

            $taskArray = array();
                        
            if (mysqli_num_rows($result) > 0){

                while ($row = mysqli_fetch_assoc($result)) {
                    
                        $task_id = $row['task_id'];
                        $task_name = $row['task_name'];
                        $task_desc = $row['task_desc'];
                        $task_created = $row['task_created'];
                        $task_deadline = $row['task_deadline'];
                        $task_assign_to = $row['task_assign_to']; 
                        $task_priority = $row['task_priority'];
                        $task_status = $row['task_status'];
                        $task_point = $row['bonus_point'];
                        $task_type = $row['task_type'];
                        $remarks = $row['remarks'];
                        $first_name = $row['first_name'];
                        $last_name = $row['last_name'];
                       
                        $assign_to = $first_name ." ".$last_name; 
                                
                        $taskArray[] = array(
                           "id"=> $task_id,
                           "title"=> $task_name,
                           "details"=> $task_desc,
                           "date"=> $task_created,
                           "deadline"=> $task_deadline,
                           "assigned"=> $assign_to,
                           "priority"=>$task_priority,
                           "status"=>$task_status,
                           "points"=> $task_point,
                           "type" => $task_type,
                           "remarks" => $remarks);

                }
                                
            }
            

         $this->response($this->successCode, "Task List", $taskArray);

    }
    
    
    //Accessibility    
    private function queryForTask($taskType, $userId){
        
        $query = "";
        
        if(strlen($userId) < 1){
             switch ($taskType){
                case 'assigned':
                    $query = "SELECT t.task_id,t.task_name,t.remarks,t.bonus_point, t.task_desc,t.task_created,t.task_type,t.task_deadline,t.task_status,t.task_priority, emp.first_name, emp.last_name, emp.emp_id from task t 
                            INNER JOIN employee emp 
                            ON t.task_assign_to = emp.emp_id 
                            WHERE t.task_assign_to > 0 ORDER BY t.task_created DESC"; 
                    break;
                case 'completed':
                    $query = "SELECT t.task_id,t.task_name,t.remarks,t.bonus_point, t.task_desc,t.task_created,t.task_deadline,t.task_status,t.task_priority, emp.first_name, emp.last_name, emp.emp_id from task t 
                            INNER JOIN employee emp 
                            ON t.task_assign_to = emp.emp_id 
                            WHERE t.task_assign_to > 0  AND t.task_status='completed' ORDER BY t.task_created DESC"; 
                    break;
                case 'pending':
                    $query = "SELECT t.task_id,t.task_name,t.remarks,t.bonus_point, t.task_desc,t.task_created,t.task_deadline,t.task_type,t.task_assign_to,t.task_status,t.task_priority, emp.first_name, emp.last_name, emp.emp_id from task t 
                            INNER JOIN employee emp 
                            ON t.task_assign_to = emp.emp_id 
                            WHERE t.task_assign_to > 0  AND t.task_status='pending' ORDER BY t.task_created DESC"; 
                    break;
            } 
        } else {
            
            switch ($taskType){
                case 'available':
                    $query = "SELECT * from task WHERE task_assign_to = 0 AND task_tpe='wishlist' ORDER BY task_created DESC"; 
                    break;
                case 'assigned':
                    $query = "SELECT * from task WHERE task_assign_to = '$userId' ORDER BY task_created DESC"; 
                    break;
                case 'completed':
                    $query = "SELECT * from task WHERE task_status = 'completed' AND task_assign_to='$userId' ORDER BY task_created DESC"; 
                    break;
                case 'pending':
                    $query = "SELECT * from task WHERE task_status = 'pending' AND task_assign_to='$userId' ORDER BY task_created DESC"; 
                    break;
            } 
            
        }
        
        return $query;
        
    }
    
    function assignTaskTo($data){
        
        $task_id = mysqli_real_escape_string($this->getConn(),$data['task_id']);
        $user_id = mysqli_real_escape_string($this->getConn(),$data['user_id']);
         
        $this->connectDB();

        $sql = "UPDATE task SET task_assign_to='$user_id' WHERE task_id='$task_id'";

        if(!mysqli_query($this->conn, $sql)) {

             die('error :'. mysqli_error($this->conn));

         }else{
             
            $query = "SELECT e.email_id, e.first_name, t.task_name from employee e INNER JOIN task t ON e.emp_id = t.task_assign_to WHERE t.task_id='$task_id'"; 
        
            $result = mysqli_query($this->conn,$query);

            if (mysqli_num_rows($result) > 0){
                
                while ($row = mysqli_fetch_assoc($result)) {
                        $email_id = $row['email_id'];
                        $first_name = $row['first_name'];
                        $subject = $row['task_name'];
                        $this->response($this->successCode, "Task Assigned Successfully", []);
                        $this->sendTaskAssignEmail($email_id, $subject,$first_name);
                        return;
                }
            }else{
                
               $this->response($this->failureCode, "Fail to assign task", []);

            }
                          
         } 
         
         //fetch User Info and Task Details
         //sendTaskAssignEmail
        
    }
    
    function updateTaskStatus($data){
        
        $this->connectDB();
        $task_id = mysqli_real_escape_string($this->getConn(), $data['task_id']);
        $status = mysqli_real_escape_string($this->getConn(),$data['status']);
        
        $sql = "UPDATE task SET task_status='$status' WHERE task_id='$task_id'";

       if(!mysqli_query($this->conn, $sql)) {

          die('error :'. mysqli_error($this->conn));

      }else{
          $this->response($this->successCode, "Sucessfully Updated", null);
          return;
      } 
      
    }
    
    function updateRemarks($data){
        
        $this->connectDB();
        $task_id = mysqli_real_escape_string($this->getConn(), $data['task_id']);
        $remarks = mysqli_real_escape_string($this->getConn(),$data['remarks']);
        
        $sql = "UPDATE task SET remarks='$remarks' WHERE task_id='$task_id'";

       if(!mysqli_query($this->conn, $sql)) {

          die('error :'. mysqli_error($this->conn));

      }else{
          $this->response($this->successCode, "Sucessfully Updated remarks", null);
          return;
      } 
      
    }
    
    function editTask($data){
        
        $this->connectDB();
        
        $task_id = mysqli_real_escape_string($this->getConn(), $data['task_id']);
        $task_name = mysqli_real_escape_string($this->getConn(),$data['title']);
        $task_desc = mysqli_real_escape_string($this->getConn(),$data['description']);
        $task_deadline  =  mysqli_real_escape_string($this->getConn(),$data['deadline']);
        $task_priority = mysqli_real_escape_string($this->getConn(),$data['priority']);
        $task_point = mysqli_real_escape_string($this->getConn(),$data['points']);
        $task_type = mysqli_real_escape_string($this->getConn(),$data['type']);
        $remarks = mysqli_real_escape_string($this->getConn(),$data['remarks']);
        
        $sql = "UPDATE task SET task_name='$task_name',task_desc='$task_desc',task_deadline='$task_deadline',task_priority='$task_priority',bonus_point='$task_point',task_type='$task_type',remarks='$remarks' WHERE task_id='$task_id'";

       if(!mysqli_query($this->conn, $sql)) {

          die('error :'. mysqli_error($this->conn));

      }else{
          $this->response($this->successCode, "Sucessfully Created", null);
          return;
      } 
      
    }
    
    protected function removeTask($data){
        
        $this->connectDB();
       
        $task_id = mysqli_real_escape_string($this->getConn(),$data['task_id']);
       
        $sql = "DELETE FROM task WHERE task_id='$task_id'";
        
        if(!mysqli_query($this->conn, $sql)) {
            $this->response($this->failureCode, "Fail to Remove Task", NULL);
        }else{
            $this->response($this->successCode, "Successfully Removed Task". $sql, NULL);
        }
        
    }
    
    
    
    //Report Functionality
    
    function insertReport($emp_id,$total_task_count,$completed_task_count,$total_score,$obtain_score) {
        
        $this->connectDB();
       
        $report_date = date("Y-m-d");
        $progress_percent = number_format(floatval(floatval($obtain_score)/( floatval($total_score)/100)),2);
        
        $query = "SELECT * from reports WHERE emp_id ='$emp_id' AND report_date='$report_date'"; 
                
        $result = mysqli_query($this->conn,$query);
                        
        if (mysqli_num_rows($result) > 0){
            

            $sql = "UPDATE reports SET total_task_count='$total_task_count',completed_task_count='$completed_task_count',total_task_score='$total_score',obtain_task_score='$obtain_score',progress_percent='$progress_percent' WHERE emp_id='$emp_id' AND report_date='$report_date'";

           if(!mysqli_query($this->conn, $sql)) {
//               die('error :'. mysqli_error($this->conn));
               return FALSE;

           }else{
               return TRUE;
           }
            
        }else{


            $sql = "INSERT INTO reports(emp_id,total_task_count,completed_task_count,total_task_score,obtain_task_score,progress_percent,report_date)VALUES("
                    . "'$emp_id','$total_task_count','$completed_task_count','$total_score','$obtain_score','$progress_percent','$report_date')";

           if(!mysqli_query($this->conn, $sql)) {
//               die('error :'. mysqli_error($this->conn));
               return FALSE;

           }else{
               return TRUE;
           } 
        }
        
    }
    
    function generateReport($emp_id, $isAdmin){
        
        $this->connectDB();
       
        $query = "SELECT * from task WHERE task_assign_to ='$emp_id' ORDER BY task_deadline DESC"; 
        
        $result = mysqli_query($this->conn,$query);
                        
        if (mysqli_num_rows($result) > 0){
            
            $total_task_count = 0;
            $completed_task_count = 0;
            $total_score = 0;
            $obtain_score = 0;
            
            while ($row = mysqli_fetch_assoc($result)) {

                    $current_score = intval($row['bonus_point']);
                    $total_score = ($total_score + $current_score);
                    $total_task_count += 1;
                    $status = strtoupper($row['task_status']);
                    
                    if($status == "COMPLETED"){
                        $obtain_score = ($obtain_score + $current_score);
                        $completed_task_count += 1;
                    }
            }
            
           if($this->insertReport($emp_id, $total_task_count, $completed_task_count, $total_score, $obtain_score)){
               if(!$isAdmin){
                    $this->viewCurrentReport($emp_id);
               }
           }else{
               $this->response($this->failureCode, "No report Data available!", []);
           }
        }
        
    }
    
    function viewCurrentReport($emp_id){ //for admin
            
            $this->connectDB();

            $query = "";
            
            if(strlen($emp_id) > 0){
                $query = "SELECT * from reports WHERE emp_id ='$emp_id' ORDER BY report_date DESC"; 
            }else{
                $query = "SELECT * from reports ORDER BY report_date DESC"; 
            } 
                                   
            $result = mysqli_query($this->conn,$query);

                        
            if (mysqli_num_rows($result) > 0){
                
                $reportArray = array();

                while ($row = mysqli_fetch_assoc($result)) {
                                            
                        $user_id = $row['emp_id'];
                        $total_task_count = $row['total_task_count'];
                        $completed_task_count = $row['completed_task_count'];
                        $total_task_score = $row['total_task_score'];
                        $obtain_score = $row['obtain_task_score']; 
                        $progress_percent = $row['progress_percent'];
                        $report_date = $row['report_date'];
                        
                        $reportArray[] = array(
                           "emp_id"=> $user_id,
                           "task_count"=> $total_task_count,
                           "completed"=> $completed_task_count,
                           "score"=> $obtain_score.'/'.$total_task_score,
                           "percent" => $progress_percent,
                           "date"=> $report_date,
                          );

                }
                
                $this->response($this->successCode, "Available data", $reportArray);
               
            }else{
                
                $this->response($this->successCode, "No report Data available!", []);

            }
    }
    
    function initCurrentReport($data){
        
        $emp_id = mysqli_real_escape_string($this->getConn(),$data['user_id']);

        if($emp_id == ""){
        
            $this->connectDB();

             $query = "SELECT * from employee WHERE designation!='Admin' ORDER BY emp_id"; 

             $result = mysqli_query($this->conn,$query);

            if (mysqli_num_rows($result) > 0){

                while ($row = mysqli_fetch_assoc($result)) {
                        $emp_id = $row['emp_id'];
                        $this->generateReport($emp_id, true);
                }
                $this->viewCurrentReport('');
            }
        }else{
            $this->generateReport($emp_id, false);
        }
        
        
    }
  
    
    // Bonus
    function bonusReport($data){ //for admin
        
            $this->connectDB();
            
            $user_id = mysqli_real_escape_string($this->getConn(),$data['user_id']);

            $query = "";
            
            if(strlen($user_id) > 0){
                $query = "SELECT t.task_id,t.task_name, t.task_priority,t.task_deadline,t.bonus_point, e.first_name,e.last_name from task t INNER JOIN employee e ON t.task_assign_to = e.emp_id  WHERE t.task_status= 'completed' AND emp_id='$user_id' ORDER BY e.first_name"; 
            }else{
                $query = "SELECT t.task_id,t.task_name, t.task_priority,t.task_deadline,t.bonus_point, e.first_name,e.last_name from task t INNER JOIN employee e ON t.task_assign_to = e.emp_id  WHERE t.task_status= 'completed' ORDER BY e.first_name"; 
            } 
                        
            $result = mysqli_query($this->conn,$query);
                
            if (mysqli_num_rows($result) > 0){
                
                $reportArray = array();

                while ($row = mysqli_fetch_assoc($result)) {
                    
                        $name = $row['first_name'] ." ".$row['last_name'];
                        $bonus_point = $row['bonus_point'];
                        $deadline = $row['task_deadline'];
                        $priority = $row['priority'];
                        $id = $row['task_id'];
                        
                        $reportArray[] = array(
                            "id"=> $id,
                           "name"=> $name,
                           "points"=> $bonus_point,
                           "deadline"=> $deadline,
                           "priority"=> $priority,
                          );

                }
                
                $this->response($this->successCode, "Available data", $reportArray);
               
            }else{
                
                $this->response($this->successCode, "No Data available!", []);

            }


    }
    
    // ========================= Control Panel ============================= //
    
     function addNewBonus($data){
                
        $this->connectDB();
         
        $bonus_name = mysqli_real_escape_string($this->getConn(),$data['bonus_title']);
        $bonus_conditions = mysqli_real_escape_string($this->getConn(),$data['bonus_conditions']);
        $bonus_created = date("Y-m-d");
        $bonus_deadline  =  $data['bonus_deadline'];
        $bonus_point = mysqli_real_escape_string($this->getConn(),$data['bonus_qualify_points']);
        $user_id = mysqli_real_escape_string($this->getConn(),$data['emp_id']);
        
        $notify_content = "Bonus Title:". $bonus_name ." | Qualify Conditions: ". $bonus_conditions ." | Minimum Points Required: ". $bonus_point ." | Deadline: ". $bonus_deadline; 
        
        $notify_data["user_id"] =  $user_id;
        $notify_data["content"] = $notify_content;
       
        
        $sql = "INSERT INTO bonus(bonus_title,bonus_conditions,bonus_deadline,bonus_qualify_points,date)VALUES('$bonus_name','$bonus_conditions','$bonus_deadline','$bonus_point','$bonus_created')";


       if(!mysqli_query($this->conn, $sql)) {

          die('error :'. mysqli_error($this->conn));

      }else{
          
             $this->updateStatus($notify_data);
          
          return;
      } 
        
    }
    
    //Delete Bonus
    function removeBonus($data){
        
        $this->connectDB();
         
        $bonus_id = mysqli_real_escape_string($this->getConn(),$data['id']);
        
        $sql = "DELETE FROM bonus WHERE bonus_id='$bonus_id'";

       if(!mysqli_query($this->conn, $sql)) {

          die('error :'. mysqli_error($this->conn));

        }else{
            $this->response($this->successCode, "Deleted Bonus !", []);
        } 
      
    }
    
    
    function bonusCategory(){ //for admin
        
            $this->connectDB();
            
            $query = "SELECT * from bonus ORDER BY date DESC"; 
                        
            $result = mysqli_query($this->conn,$query);
                        
            if (mysqli_num_rows($result) > 0){
                
                $bonus = array();

                while ($row = mysqli_fetch_assoc($result)) {
                    
                        $id = $row['bonus_id'];
                        $title = $row['bonus_title'];
                        $conditions = $row['bonus_conditions'];
                        $deadline = $row['bonus_deadline'];
                        $date = $row['date'];
                        $qualify_points = $row['bonus_qualify_points']; 
                                
                        $bonus[] = array(
                           "id"=> $id,
                           "title"=> $title,
                           "conditions"=> $conditions,
                           "deadline"=> $deadline,
                           "qualify_points"=> $qualify_points,
                           "date"=> $date,
                          );

                }
                
                $this->response($this->successCode, "Available Bonus", $bonus);
               
            }else{
                
                $this->response($this->successCode, "No Bonus Data available!", []);

            }


    }
    
    function addAnnouncement($data){
        
        $this->connectDB();

        $sender = mysqli_real_escape_string($this->getConn(),$data['sender']);
        $recipient = $data['recipients'];
        $subject = mysqli_real_escape_string($this->getConn(),$data['subject']);
        $content = mysqli_real_escape_string($this->getConn(),$data['content']);
        $date = date("Y-m-d"); 
        
        $activity_content = $subject ." ". $content;

        $msg_id = $this->randID();

        $sql = "INSERT INTO message(msg_title,msg_content,msg_id,msg_date,msg_from,msg_type)VALUES('$subject','$content','$msg_id','$date','$sender','1')";

        if(!mysqli_query($this->conn, $sql)) {

            die('error :'. mysqli_error($this->conn));

        }else{
            
            $this->announcementActivity($sender, $activity_content, $msg_id,$recipient, $subject, $content);
        }
        
    }
    
     //Delete Announcement
    function removeAnnouncement($data){
        
        $this->connectDB();
         
        $message_id = mysqli_real_escape_string($this->getConn(),$data['id']);
        
        $sql = "DELETE FROM message WHERE msg_id='$message_id'";

       if(!mysqli_query($this->conn, $sql)) {

          die('error :'. mysqli_error($this->conn));

        }else{
            $this->response($this->successCode, "Deleted Announcement !", []);
        } 
      
    }
    
    
    function announcementActivity($sender, $msg_id,$recipient, $subject, $content){
        
        $this->connectDB();

        $date = date("Y-m-d H:i:s");

        $sql = "INSERT INTO activities(activity_by,activity_content,activity_log_at)VALUES('$sender','$content','$date')";

        if(!mysqli_query($this->conn, $sql)) {

             die('error :'. mysqli_error($this->conn));

         }else{

            $this->insertIntoInbox($sender, $recipient, $msg_id,TRUE ,$subject,$content);
        }         

    }
    
    
    
    function announcement(){
        
        $this->connectDB();
        
        $query = "SELECT * from message WHERE msg_type > 0 ORDER BY msg_date DESC"; 
            
        $result = mysqli_query($this->conn,$query);

        if (mysqli_num_rows($result) > 0){
            
                $messages = array();


                while ($row = mysqli_fetch_assoc($result)) {
                    
                        $msg_id = $row['msg_id'];
                        $date = $row['msg_date'];
                        $msg_title = $row['msg_title'];
                        $content = $row['msg_content'];
                        
                        $messages[] = array(
                           "id"=> $msg_id,
                           "date"=> $date,
                           "subject"=> $msg_title,
                           "content"=> $content,
                           "recipient"=> "Some recipient",
                           );

                }
             
                $this->response($this->successCode, "Annoucement List", $messages);
                
        }else{
            $this->response($this->failureCode, "Annoucement Data Not found!", []);

        }
            

         
    }
    
    function settings(){
        
        $this->connectDB();
        
        $query = "SELECT * from app_settings Limit 1"; 
            
        $result = mysqli_query($this->conn,$query);

        if (mysqli_num_rows($result) > 0){
            
                $messages = array();


                while ($row = mysqli_fetch_assoc($result)) {
                    
                        $send_message = $row['message_send'];
                        $activity_capture = $row['activity_capture'];
                        $report_download = $row['report_download'];
                        $email_notification = $row['email_notification'];
                        $apply_leaves = $row['apply_leaves'];
                        
                        $messages[] = array(
                           "send_message"=> $send_message,
                           "activity_capture"=> $activity_capture,
                           "download_report"=> $report_download,
                           "email_notification"=> $email_notification,
                           "apply_leaves"=> $apply_leaves,
                           );

                }
             
                $this->response($this->successCode, "Messages List", $messages);
                
        }else{
            $this->response($this->failureCode, "Messages Data Not found", []);

        }
         
    }
    
     protected function saveSettings($data){

                
     $this->connectDB();
     $send_message = intval(mysqli_real_escape_string($this->getConn(),$data['send_message']));
     $download_report = intval(mysqli_real_escape_string($this->getConn(),$data['download_report']));
     $email_notification = intval(mysqli_real_escape_string($this->getConn(),$data['email_notification']));
     $apply_leaves = intval(mysqli_real_escape_string($this->getConn(),$data['apply_leaves']));
     $activity_capture = intval(mysqli_real_escape_string($this->getConn(),$data['activity_capture']));

     $sql = "UPDATE app_settings SET message_send='$send_message',activity_capture='$activity_capture',report_download='$download_report',email_notification='$email_notification', apply_leaves='$apply_leaves' WHERE sl='1'";
          
      if(!mysqli_query($this->conn, $sql)) {
          $this->response($this->failureCode, "Unable to Save Settings!", []);
      }else{
          $this->response($this->successCode, "Settings Saved Successfully", []);
       } 
        
    }
    
    
    // ============================= User Operations ==========================
    
    #USER Authentication
    function authenticateUser($data, $token){
        
        $user_id =  mysqli_real_escape_string($this->getConn(),$data['email']);
        $user_pwd = mysqli_real_escape_string($this->getConn(),$data['password']);
        
        $query =   "SELECT * from employee WHERE email_id='$user_id' AND password='$user_pwd'  limit 1";
        
        $count = 0;
        $this->connectDB();

        $result = mysqli_query($this->conn,$query);
        
        $userData = array();

         if (mysqli_num_rows($result) > 0){

               while ($row = mysqli_fetch_assoc($result)) {
                        $count++;
                        $first_name =  $row['first_name'];
                        $last_name = $row['last_name'] ;
                        $email_id = $row['email_id'];
                        $designation = $row['designation'];
                        $usertype = "User";
                        if($designation == "admin" || $designation == "manager"){
                            $usertype = "Admin";
                        }
                        $address = $row['address'];
                        $mobile = $row['phone_number'];
                        $date_of_joining = $row['date_of_joining'];
                        $emp_id = $row['emp_id'];
                        $profile_pic = $row['profile_pic'];
                        
                        $userData = array("user_name"=> $first_name." ". $last_name,
                           "first_name"=> $first_name,
                           "last_name"=> $last_name,
                           "email_id"=> $email_id,
                           "designation"=> $designation,
                           "mobile"=> $mobile,
                           "address"=> $address,
                           "date_of_joining"=> $date_of_joining,
                           "emp_id"=> $emp_id,
                           "token"=> $token,
                           "user_type" => $usertype,
                            "profile_pic"=> $profile_pic
                          );
                }

                return $this->response($this->successCode, "User Authenticated Successfully", $userData);

         }else{
             
                return $this->response($this->failureCode, "requested User Not Found!", $data);
         }
        
    }
    
    #forgot Password
    function getPassword($data){
        
        $email_id = $data['email_id'];
         
        $this->connectDB();

        $query = "SELECT * FROM employee WHERE email_id='$email_id'";
                 
        $result = mysqli_query($this->conn,$query);
    
        $responseData = array();
        
        $newPwd = $this->randID();

        if (mysqli_num_rows($result) > 0){

             $sql = "UPDATE employee "
                        . " SET password='$newPwd' WHERE email_id='$email_id'";
             
               if(!mysqli_query($this->conn, $sql)) {

                    die('error :'. mysqli_error($this->conn));

                }else{
                    
                    $this->sendEmail($email_id, $newPwd);
                    $this->response($this->successCode, "Found Users", $responseData);
                    return;
                } 

        }else{

             $this->response($this->failureCode, "User not found", $responseData);

        }
                 
    }
    
    #Register New User
    protected function registerNewEmployee($data){
        
        //check for existing
        
         $this->connectDB();
                
         $first_name = mysqli_real_escape_string($this->getConn(),$data['first_name']);
         $last_name = mysqli_real_escape_string($this->getConn(),$data['last_name']);
         $designation = mysqli_real_escape_string($this->getConn(),$data['designation']);
         $email_id = mysqli_real_escape_string($this->getConn(),$data['email_id']);
         $phone_number = mysqli_real_escape_string($this->getConn(),$data['phone_no']);
         $address = mysqli_real_escape_string($this->getConn(),$data['address']);
         $date_of_joining = date("Y-m-d"); //date_create_from_format("Y-m-d", mysqli_real_escape_string($this->getConn(),$data['date_of_joining']));
         
         $newPwd = $this->randID();
 
         $sql = "INSERT INTO employee(first_name,last_name,email_id,phone_number,address,designation,password,date_of_joining)VALUES('$first_name','$last_name','$email_id','$phone_number','$address','$designation','$newPwd','$date_of_joining')";


        if(!mysqli_query($this->conn, $sql)) {

          die('error :'. mysqli_error($this->conn));

      }else{

          $this->sendEmail($email_id, $newPwd);
          $this->response($this->successCode, "Sucessfully registred Users", $sql);
          return;
      } 
            
        
    }
    
    //View Users
    protected function viewUsers($data){
        
        
        $this->connectDB();
        
        $user_id = mysqli_real_escape_string($this->getConn(),$data['user_id']);
        $query = "";
        
        if(strlen($user_id) > 0){
            $query = "SELECT * from employee WHERE emp_id='$user_id'"; 
        } else {
            
           $query = "SELECT * from employee ORDER BY emp_id"; 
        }
            
        $result = mysqli_query($this->conn,$query);
        

        if (mysqli_num_rows($result) > 0){
            
                $users = array();


                while ($row = mysqli_fetch_assoc($result)) {
                    
                        $emp_id = $row['emp_id'];
                        $designation = $row['designation'];
                        $first_name = $row['first_name'];
                        $last_name = $row['last_name'];
                        $address = $row['address'];
                        $email_id = $row['email_id'];
                        $phone_number = $row['phone_number'];
                        $date_of_joining =$row['date_of_joining'];
                        $profile_pic = $row['profile_pic'];
                        
                        $users[] = array(
                           "id"=> $emp_id,
                           "first_name"=> $first_name,
                           "last_name"=> $last_name,
                           "designation"=> $designation,
                           "address"=> $address,
                           "phone"=> $phone_number,
                           "email"=>$email_id,
                           "date_of_joining"=>$date_of_joining,
                           "profile_pic"=> "img/".$profile_pic,
                           );

                }
             
                $this->response($this->successCode, "Users List ", $users);
                
        }else{
            $this->response($this->failureCode, "User Not found", []);

        }
            

         
        
    }
    
    protected function removeUser($data){
        
        $this->connectDB();
       
        $emp_id = mysqli_real_escape_string($this->getConn(),$data['emp_id']);
       
        $sql = "DELETE FROM employee WHERE emp_id='$emp_id'";
        
        if(!mysqli_query($this->conn, $sql)) {
            $this->response($this->failureCode, "Fail to Update User Data", NULL);
        }else{
            $this->response($this->successCode, "Successfully Updated". $sql, NULL);
        }
        
    }
   
    
    protected function updateUser($data){
        
        $this->connectDB();
        
        $password = mysqli_real_escape_string($this->getConn(),$data['password']);
        $old_password = mysqli_real_escape_string($this->getConn(),$data['old_password']);
        $emp_id = mysqli_real_escape_string($this->getConn(),$data['user_id']);
        $email_id = mysqli_real_escape_string($this->getConn(),$data['email_id']);
        $first_name = mysqli_real_escape_string($this->getConn(),$data['first_name']);
        $last_name = mysqli_real_escape_string($this->getConn(),$data['last_name']);
        $designation = mysqli_real_escape_string($this->getConn(),$data['designation']);
        $phone_number = mysqli_real_escape_string($this->getConn(),$data['phone_no']);
        $address = mysqli_real_escape_string($this->getConn(),$data['address']);
        $admin_token = mysqli_real_escape_string($this->getConn(),$data['token']);
                
        $sql = "";
        
        if(strlen($password)> 3){
            //check for existing user id and password
            $sql = "UPDATE employee SET password='$password' WHERE emp_id='$emp_id'";
        }else{
            
           if(strlen($admin_token) > 0){
               $sql = "UPDATE employee SET first_name='$first_name', last_name='$last_name',designation='$designation',phone_number='$phone_number', address='$address', email_id='$email_id' WHERE emp_id='$emp_id'";
           }else{
                $sql = "UPDATE employee SET first_name='$first_name', last_name='$last_name',phone_number='$phone_number', address='$address' WHERE emp_id='$emp_id'";
           }
        }
               
        if(!mysqli_query($this->conn, $sql)) {
            $this->response($this->failureCode, "Fail to Update User Data", NULL);
        }else{
            $this->response($this->successCode, "Successfully Updated". $sql, NULL);
        }
        
    }
    
    function updateProfilePicture($file_name,$emp_id){
        
         $this->connectDB();
        
         $sql = "UPDATE employee SET profile_pic='$file_name' WHERE emp_id='$emp_id'";
            
        if(!mysqli_query($this->conn, $sql)) {
            $this->response($this->failureCode, "Fail to Update User Data", NULL);
        }else{
            $this->response($this->successCode, "Successfully Updated". $sql, NULL);
        }
        
        
    }
    
    
   
     //Attendance
     protected function captureAttendance($data){
                
         
        $this->connectDB();

                 
        $user_id =  mysqli_real_escape_string($this->getConn(),$data['user_id']);
        
        $date = date("Y-m-d");
        
        $query =   "SELECT * from attendance WHERE emp_id='$user_id' AND attendance_date='$date' limit 1";
        
        $result = mysqli_query($this->conn,$query);
        
        $attendTime = date("Y-m-d H:m:s");

         if (mysqli_num_rows($result) > 0){

             $sql = "UPDATE attendance SET out_time='$attendTime' WHERE emp_id='$user_id' AND attendance_date='$date'";

             if(!mysqli_query($this->conn, $sql)) {
                return $this->response($this->failureCode, "Attendence Captured Error", []);
             }else{
                return $this->response($this->existCode, "Attendence Captured for Check Out", []);
             }
             
         }else{
                
             $sql = "INSERT INTO attendance(emp_id,attendance_date,in_time,status)VALUES('$user_id','$date','$attendTime','present')";
             if(!mysqli_query($this->conn, $sql)) {
//                     die('error :'. mysqli_error($this->conn));
                  return $this->response($this->failureCode, "Attendence Captured Error", []);
               }else{
                return $this->response($this->successCode, "Attendence Captured for Check In", []);
               }
         }

         
    }
    
    
    protected function attendance($data){
        
        $this->connectDB();
        
        $user_id = mysqli_real_escape_string($this->getConn(),$data['user_id']);
        $query = "";
        
        if(strlen($user_id) > 0){ // todo group by
            $query = "SELECT a.attendance_date,a.in_time,a.out_time,e.first_name, e.last_name from attendance a INNER JOIN  employee e ON a.emp_id = e.emp_id AND a.emp_id='$user_id' ORDER BY a.attendance_date DESC"; 
        } else {
            
            $query = "SELECT a.attendance_date,a.in_time,a.out_time,e.first_name, e.last_name from attendance a INNER JOIN  employee e ON a.emp_id = e.emp_id ORDER BY a.attendance_date DESC"; 
        }
            
        $result = mysqli_query($this->conn,$query);
        

        if (mysqli_num_rows($result) > 0){
            
                $attandanceArray = array();


                while ($row = mysqli_fetch_assoc($result)) {
                    
                        $emp_id = $row['emp_id'];
                        $date = $row['attendance_date'];
                        $in_time = $row['in_time'];
                        $out_time = $row['out_time'];
                        $name = $row['first_name']." ".$row['last_name'];;
                        
                        $attandanceArray[] = array(
                           "id"=> $emp_id,
                           "date"=> $date,
                           "name" => $name,
                           "check_in"=> $in_time,
                           "check_out"=> $out_time,
                           );

                }
             
                $this->response($this->successCode, "Attandance List", $attandanceArray);
                
        }else{
            $this->response($this->failureCode, "Attendance Data Not found", []);

        }
            

         
        
    }
    
    protected function applyLeaves($data){
                
         $this->connectDB();
                
         $leave_title = mysqli_real_escape_string($this->getConn(),$data['title']);
         $description = mysqli_real_escape_string($this->getConn(),$data['description']);
         $date_from = mysqli_real_escape_string($this->getConn(),$data['from_date']);
         $date_to = mysqli_real_escape_string($this->getConn(),$data['to_date']);
         $emp_id = mysqli_real_escape_string($this->getConn(),$data['user_id']);
         $date = date("Y-m-d");
          
         $sql = "INSERT INTO leaves(leave_title,apply_date,leave_description,leave_from,leave_to,emp_id)VALUES('$leave_title','$date','$description','$date_from','$date_to','$emp_id')";

        if(!mysqli_query($this->conn, $sql)) {
//          die('error :'. mysqli_error($this->conn));
           $this->response($this->failureCode, "Failed to Applyed for Leaves", []);

      }else{

          $this->response($this->successCode, "Sucessfully Applyed for Leaves", []);
          return;
      } 
        
    }
    
     protected function approveLeaves($data){
                
         $this->connectDB();
                
         $leave_id = mysqli_real_escape_string($this->getConn(),$data['leave_id']);
         $status = mysqli_real_escape_string($this->getConn(),$data['status']);
         
         $sql = "UPDATE leaves SET leave_status='$status' WHERE sl='$leave_id'";
             
        if(!mysqli_query($this->conn, $sql)) {
           $this->response($this->failureCode, "Failed to Aprove Leaves", $sql);

      }else{
          $this->response($this->successCode, "Sucessfully Approved Leaves", []);
          return;
      } 
        
    }
    
    
    // Leaves Data retrieve
    protected function leaves($data){
        
        $this->connectDB();
        
        $user_id = mysqli_real_escape_string($this->getConn(),$data['user_id']);
        $query = "";
        
        if(strlen($user_id) > 0){ // todo group by
            $query = "SELECT * from leaves WHERE emp_id='$user_id' ORDER BY apply_date DESC"; 
        } else {
            
            $query = "SELECT * from leaves ORDER BY apply_date DESC"; 
        }
            
        $result = mysqli_query($this->conn,$query);
        

        if (mysqli_num_rows($result) > 0){
            
                $leavesArray = array();


                while ($row = mysqli_fetch_assoc($result)) {
                    
                        $id = $row['sl'];
                        $emp_id = $row['emp_id'];
                        $date = $row['apply_date'];
                        $leave_from = $row['leave_from'];
                        $leave_to = $row['leave_to'];
                        $leave_title = $row['leave_title'];
                        $status = $row['leave_status'];
                        $desc = $row['leave_description'];
                        
                        $leavesArray[] = array(
                           "id" => $id,
                           "user_id"=> $emp_id,
                           "date"=> $date,
                           "name"=> "Jayanta Gogoi",
                           "leave_from"=> $leave_from,
                           "leave_to"=> $leave_to,
                           "title" => $leave_title,
                           "details" => $desc,
                           "status"=> $status,
                           );

                }
             
                $this->response($this->successCode, "Leave List", $leavesArray);
                
        }else{
            $this->response($this->failureCode, "Leave Data Not found", []);

        }
        
    }
    
    // ================== MESSAGES =============== //
     
    // Send Message Insert Function
    protected function sendMessage($data){

     $this->connectDB();

     $sender = mysqli_real_escape_string($this->getConn(),$data['sender']);
     $recipient = $data['recipients'];
     $subject = mysqli_real_escape_string($this->getConn(),$data['subject']);
     $content = mysqli_real_escape_string($this->getConn(),$data['content']);
     $send_email = mysqli_real_escape_string($this->getConn(),$data['email']);
     $date = date("Y-m-d"); 
     
     $canSendEmail = FALSE;
     
     if(intval($send_email) > 0){
         $canSendEmail = TRUE;
     }
     
     

     $msg_id = $this->randID();

     $sql = "INSERT INTO message(msg_title,msg_content,msg_id,msg_date,msg_from)VALUES('$subject','$content','$msg_id','$date','$sender')";

       if(!mysqli_query($this->conn, $sql)) {

          die('error :'. mysqli_error($this->conn));

      }else{
          
          $this->insertIntoInbox($sender, $recipient, $msg_id, $canSendEmail, $subject,$content);
      } 
        
    }
    
    // Bifurcate Recipient Messages to Inbox
    
    function insertIntoInbox($sender,$recipient,$msg_id, $sendEmail,$subject,$content){
        
        $this->connectDB();
        
        $response = "";
        $email_ids = "";
        
        foreach ($recipient as $key => $value) {
            
             $sql = "INSERT INTO inbox(sender_id,receiver_id,msg_id)VALUES('$sender','$value','$msg_id')";

             $response .= $sql;
             
                if(!mysqli_query($this->conn, $sql)) {

                  die('error :'. mysqli_error($this->conn));

               }
            
                $query = "SELECT * from employee WHERE emp_id='$value'"; 
                $result = mysqli_query($this->conn,$query);

                if (mysqli_num_rows($result) > 0){

                    while ($row = mysqli_fetch_assoc($result)) {
                        $email_id = $row['email_id'];
                        $email_ids .= $email_id .",";
                    }
                }

        }
        
        if($sendEmail){
            $this->sendEmailWithCustomContent($email_ids, $subject, $content);
        }
        
        $this->response($this->successCode, "Sent Message", $response);
      
    }

    // Insert replay Message
    protected function replayMessage($data){

     $this->connectDB();

     $message_id = mysqli_real_escape_string($this->getConn(),$data['id']);
     $user_id = mysqli_real_escape_string($this->getConn(),$data['user_id']);
     $content = mysqli_real_escape_string($this->getConn(),$data['content']);
     $subject = mysqli_real_escape_string($this->getConn(),$data['subject']);
     $recipient = mysqli_real_escape_string($this->getConn(),$data['recipient']);
     
     $date = date("Y-m-d"); 
     

     $sql = "UPDATE inbox SET replay='$content', replay_date='$date' WHERE msg_id='$message_id' AND receiver_id='$user_id'";

       if(!mysqli_query($this->conn, $sql)) {
            $this->response($this->failureCode, "Failed to send message", []);
      }else{
          
          $msgData['sender'] = $user_id;
          $msgData['content'] = $content;
          $msgData['subject'] = $subject;
          $msgData['recipients'] = array($recipient);
                  
          $this->sendMessage($msgData);
      } 
        
    }
    
    //User Inbox Messages retrieve
    protected function userInbox($data){
        
        $this->connectDB();
        
        $user_id = mysqli_real_escape_string($this->getConn(),$data['user_id']);
        
        $query = "SELECT i.sl,i.msg_id,i.sender_id,i.replay,i.replay_date ,m.msg_title,m.msg_content,m.msg_date,emp.email_id from inbox i INNER JOIN message m ON i.msg_id=m.msg_id INNER JOIN employee emp ON i.sender_id = emp.emp_id WHERE i.receiver_id='$user_id' ORDER BY m.msg_date, i.sl DESC"; 
            
        $result = mysqli_query($this->conn,$query);
        

        if (mysqli_num_rows($result) > 0){
            
                $messages = array();

                while ($row = mysqli_fetch_assoc($result)) {
                    
                        $msg_id = $row['msg_id'];
                        $date = $row['msg_date'];
                        $msg_title = $row['msg_title'];
                        $content = $row['msg_content'];
                        $sender = $row['email_id'];
                        $replay = $row['replay'];
                        $replay_date = $row['replay_date'];
                        $recipient = $row['sender_id'];
                        
                        $messages[] = array(
                           "id"=> $msg_id,
                           "date"=> $date,
                           "subject"=> $msg_title,
                           "content"=> $content,
                           "sender"=> $sender,
                           "recipient"=> $recipient,
                           "replay" => strlen($replay) > 0 ? $replay : "",
                           "replay_date" => $replay_date 
                           );

                }
             
                $this->response($this->successCode, "Messages List", $messages);
                
        }else{
            $this->response($this->failureCode, "Messages Data Not found", []);

        }
        
    }
    

    //Activity Timeline message reterieve
    
    protected function timelineMessages(){
        
        $this->connectDB();
                
        $query = "SELECT a.id,a.activity_content,a.activity_by,a.activity_log_at,emp.email_id, emp.first_name,emp.last_name from activities a INNER JOIN employee emp ON a.activity_by = emp.emp_id ORDER BY a.activity_log_at DESC"; 
        
        $result = mysqli_query($this->conn,$query);
        
        if (mysqli_num_rows($result) > 0){
            
                $timeline = array();

                while ($row = mysqli_fetch_assoc($result)) {
                    
                        $id = $row['id'];
                        $content = $row['activity_content'];
                        $user_name = $row['first_name']." ". $row['last_name'];
                        $avatarLetter = substr($row['first_name'], 0, 1) ."".substr($row['last_name'], 0, 1);
                        $email = $row['email_id'];
                        $date = $row['activity_log_at'];
                        
                        $timeline[] = array(
                           "id"=> $id,
                           "avatar" => $avatarLetter,
                           "date"=> $date,
                           "user"=> $user_name,
                           "email"=> $email,
                           "content"=> $content,
            
                           );
                }
             
                $this->response($this->successCode, "Timeline response", $timeline);
                
        }else{
            $this->response($this->failureCode, "Timeline Data Not found", []);

        } 
    }
    
    // Activity status Update
     protected function updateStatus($data){

     $this->connectDB();
     $sender = mysqli_real_escape_string($this->getConn(),$data['user_id']);
     $content = mysqli_real_escape_string($this->getConn(),$data['content']);
     
     $date = date("Y-m-d H:i:s");

     $sql = "INSERT INTO activities(activity_by,activity_content,activity_log_at)VALUES('$sender','$content','$date')";

       if(!mysqli_query($this->conn, $sql)) {

          die('error :'. mysqli_error($this->conn));

      }else{
          
          $this->response($this->successCode, "Updated Successfully", []);
       } 
        
    }
    
          
    
    //Format response to JSON structured Data
    function response($status, $status_message,$data){

        //header("HTTP/1.1 $status $status_message");
        $response['status']= $status;
        $response['status_message'] = $status_message;
        $response['data'] = $data;

        $json_response = json_encode($response);
        echo $json_response;
                        
    }  
    
    
    //Create Random ID wherever required to use
    function randID() {

        $alphabet = "abcdefghjkmnopqrstuwxyzABCDEFGHJKMNOPQRSTUWXYZ0123456789";
        $pass = array();  
        $alphaLength = strlen($alphabet) - 1;  
        for ($i = 0; $i < 10; $i++) {
            $n = rand(0, $alphaLength);
            $pass[] = $alphabet[$n];
        }
        return implode($pass); 
    }
    
    
    //=================== EMAIL ===================== //
    
    
     //Send General  Email using Hosting SMTP
    function sendEmailWithCustomContent($to, $subject, $content){
        
        $from = 'info@jayantagogoi.com';

        // To send HTML mail, the Content-type header must be set
        $headers  = 'MIME-Version: 1.0' . "\r\n";
        $headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";

        // Create email headers
        $headers .= 'From: '.$from."\r\n".
            'Reply-To: '.$from."\r\n" .
            'X-Mailer: PHP/' . phpversion();

        // Compose a simple HTML email message
        $message = '<html><body>';
         $message .= '<p><strong>Hi,</strong></p>
                        <br>
                        <p>'. $content.'</p>
                         
                      
                        <br><p>Thank You,</p>

                        <p>Task Manager App Support team</p>';
        $message .= '</body></html>';

        // Sending email
        if(mail($to, $subject, $message, $headers)){
           // echo 'Your mail has been sent successfully.';
        } else{
           // echo 'Unable to send email. Please try again.';
        }

    }
    
    
    //Send Task Specific Email using Hosting SMTP
    function sendTaskAssignEmail($to, $subject,$first_name){
        
        $from = 'info@jayantagogoi.com';

        // To send HTML mail, the Content-type header must be set
        $headers  = 'MIME-Version: 1.0' . "\r\n";
        $headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";

        // Create email headers
        $headers .= 'From: '.$from."\r\n".
            'Reply-To: '.$from."\r\n" .
            'X-Mailer: PHP/' . phpversion();

        // Compose a simple HTML email message
        $message = '<html><body>';
         $message .= '<p><strong>Hi '.$first_name.',</strong></p>
                        <br>
                        <p>There is a new task assigned by your manager.Please check your task list to accomplish on time.</p>
                         
                      
                        <br><p>Thank You,</p>

                        <p>Task Manager App Support team</p>';
        $message .= '</body></html>';

        // Sending email
        if(mail($to, $subject, $message, $headers)){
           // echo 'Your mail has been sent successfully.';
        } else{
           // echo 'Unable to send email. Please try again.';
        }

    }
    
    //Send New Account and Password reset email
    function sendEmail($to,$pwd){
        
        $subject = "Regarding your Accessibility!";
        $from = 'info@jayantagogoi.com';

        // To send HTML mail, the Content-type header must be set
        $headers  = 'MIME-Version: 1.0' . "\r\n";
        $headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";

        // Create email headers
        $headers .= 'From: '.$from."\r\n".
            'Reply-To: '.$from."\r\n" .
            'X-Mailer: PHP/' . phpversion();

        // Compose a simple HTML email message
        $message = '<html><body>';
         $message .= '<p><strong>Hi,</strong></p>
                        <br>
                        <p>Here is your new password .'.$pwd .'.
                        <br>You can change your password in Profile section.</p>
                         
                      
                        <br><p>Thank You,</p>

                        <p>Task Manager App Support team</p>';
        $message .= '</body></html>';

        // Sending email
        if(mail($to, $subject, $message, $headers)){
           // echo 'Your mail has been sent successfully.';
        } else{
           // echo 'Unable to send email. Please try again.';
        }

    }
    
    
    
          
    
    
}
