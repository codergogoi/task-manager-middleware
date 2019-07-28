<?php


  header("Access-Control-Allow-Origin: *");
  header("Access-Control-Allow-Headers: X-Accept-Charset,X-Accept,Content-Type,Authorization,Accept,Origin,Access-Control-Request-Method,Access-Control-Request-Headers");
    
    
include './TaskManagerBase.php';

class Upload_api extends TaskManagerBase {
    
    function init(){
        
        if(isset($_FILES['file'])){
            
            $id = $_REQUEST['emp_id'];
            $rand_id = $this->randID();
            $errors= array();
            $file_name =  $rand_id."_".$_FILES['file']['name'];
            $file_size =$_FILES['file']['size'];
            $file_tmp =$_FILES['file']['tmp_name'];
            $file_type=$_FILES['file']['type'];
            $file_ext=strtolower(end(explode('.',$_FILES['file']['name'])));

            $expensions= array("jpeg","jpg","png");

            if(in_array($file_ext,$expensions)=== false){
               $errors[]="extension not allowed, please choose a JPEG or PNG file.";
            }

            if($file_size > 2097152){
               $errors[]='File size must be excately 2 MB';
            }

            if(empty($errors)==true){
               move_uploaded_file($file_tmp,"img/".$file_name);
               $this->updateProfilePicture($file_name, $id);
            }
         }
   
    } 
    
}


$self  = new Upload_api();
$self->init();

  