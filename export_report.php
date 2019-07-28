 
<?php


class export_report {
  
    var $hostName = "localhost";
    var $userName = "javacope_demo";
    var $password = "javacope_demo";
    var $dbName = "javacope_task_manager";
                
   
    var $conn;
    var $progress_percent = 0;
    var $report_type = 0; //0 = progress report //1 = task Details //2 = attendance
    var $emp_id = "";
    var $emp_name = "";
    var $emp_designation = "";
    var $emp_contact_number = "";
    var $emp_email_id = "";
    var $total_task = "";
    var $completed_task = "";
    var $total_percentage = "";
    var $total_earned_points = "";
    var $start_date = "";
    var $end_date = "";
    
    function connectDB(){
        
        date_default_timezone_set("Asia/Calcutta");

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
   
   
            
 function prepareReportData(){
     
    $file_name = str_replace(" ","_", "Progress_Report_".$this->emp_name."_". time().".pdf");
             
    
   
    $css = '<style> table, td, th {
                border: 3px solid #FFFFFF;
            }
                .task{width:96%; height:auto;display:inline-block; font-weight:bold; font-size:110%; float:left; margin-top:20px; margin-bottom:20px;}
                .desc{width:96%; height:auto;  display:inline-block; font-size:110%; float:left; margin-top:20px; margin-bottom: 20px;}
                .points{width:96%; height:auto;  display:inline-block;  font-size:150%; float:left; margin-top:20px;}
                .priority{width:96%; height:auto;  display:block; font-size:150%; float:left; margin-top:10px; margin-bottom:10px;}
                .remarks{width:96%; height:auto;  display:inline-block; font-size:100%; float:left; margin-top:10px; margin-bottom:10px;}
                
                .pending-task{ color: #d40009;}
                .completed{ color: #019400;}
            </style>';
          
    $logoFile = "img/company_logo.jpg";
           
     // logo display
    $logo = '<div style="width:100%; height:250px; display:inline-block;  margin-top:20px;" >'
            . '<div style="width:100%; height:180px; display:inline-block;  text-align:center;" ><center><img src="'. $logoFile.'" height="170px"/></center></div>'
            . '<div style="width:100%; height:40px; padding:10px; display:inline-block;font-size:17pt; text-align:center;" > <u>Work Progress Report</u></div>'
            .'</div>'; 
    
    $userInfo = '<center><table width="100%" border="0" style="font-size:14pt; border-top: 0.1mm solid #000000; ">
                <tr>
                  <td width="300" height="29">Name : <b>'. $this->emp_name.'</b></td>
                  <td width="100"></td>
                  <td width="300"> Designation: <b>'. $this->emp_designation.'</b> </td>
                </tr>
                <tr>
                  <td height="29">Contact Number :<b>'. $this->emp_contact_number.'</b> </td>
                  <td></td>
                  <td>Email ID:  <b>'. $this->emp_email_id.'</b></td>
                </tr>
                <tr>
                  <td height="29"> Completed Task Out Of : <b>'. $this->completed_task.'</b></td>
                  <td></td>
                  <td>Date: <b>'.$this->start_date.' - '. $this->end_date .' </b></td>
                </tr>
                  <tr>
                  <td>Total Points Earned :  <b>'. $this->total_earned_points.'</b></td>
                  <td></td>
                  <td>Work Progress Ration : <b>'. $this->total_percentage.' % </b></td>
                </tr>
              </table></center>'; 
    
    
    // table display with stats
    $tableHead = '<br><br><table width="100%" style="background:#BFCEE9;" cellpadding="5" cellspacing="0">
    <tr valign="middle" style="background:#F7BBA1;">
      <td width="282" height="25">Task Name</td>
      <td width="70">Priority</td>
      <td width="70">Deadline</td>
      <td width="64">Task Point</td>
      <td width="64">Status</td>
    </tr>';
    
    $tableContent = null;

   
   
    $marksObtainString = '<div  style="font-size:14pt; display:inline-block; margin-top:20px; margin-bottom:20px;">  Task Descriptions and Remarks <br/> </div>';
     

    $htmlStr = null;
    
    
    $this->connectDB();
            
   

        $currentQuestion = null;
            
        $result = mysqli_query($this->conn,  "SELECT * FROM task where task_assign_to='$this->emp_id' ORDER BY task_deadline "); // by date
        
        if(mysqli_num_rows($result) > 0){
                $i = 0;
               while($row = mysqli_fetch_assoc($result)){
                    $i++;
                                 
                    $task_status = strtoupper($row['task_status']);
                    
                    $task_status_tag = "";
                                        
                    $priority_tag = "";
                    
                    if(intval($row['task_priority']) > 1){
                        $priority_tag = "Critical";
                    }else if(intval($row['task_status']) > 0){
                        $priority_tag = "High";
                    }else {
                        $priority_tag = "Normal";
                    }
                    
                    if($task_status == "COMPLETED"){
                        $task_status_tag = '<td width="64" style="background:#88E38D;">'.$task_status.'</td>';
                    }else{
                        $task_status_tag = '<td width="64" style="background:#F27272;">'.$task_status.'</td>';
                    }
                    
                    $tableContent .= '<tr valign="middle">
                        <td width="282" height="25">'.$row['task_name'].'</td>
                        <td width="70">'. $priority_tag .'</td>
                        <td width="70">'. $row['task_deadline'] .'</td>
                        <td width="64">'. $row['bonus_point'] .'</td>'.$task_status_tag.'
                        
                      </tr>';
                                        
                    $task_name ='<div class="task">'.$i.'.'. $row['task_name'] .'</div>';

                    $task_point = '<div class="points"> Task Points :'. $row['bonus_point'] .'</div>';
                    
                    $priority =  '<div class="priority">Priority :'. $priority_tag .'</div>';

                    $description = '<div class="desc"> Description :' . $row['task_desc'] .'</div>';
                    $remarks = '<div class="remarks">Remarks :'. $row['remarks'] . '</div>';
                   
                    $currentQuestion .= $task_name.''
                                        .$description .''
                                        .$task_point
                                        .$priority.''
                                        .$remarks;
                   
                }
         }
         
        $tableFooter = '</table>';
        $constructTable = $tableHead.''.$tableContent.''.$tableFooter;
     
         // Generate HTML
        $htmlStr .=     $css.''
                        .$logo .''
                        .$userInfo.''
                        .$constructTable.''
                        .$marksObtainString .''
                        .$currentQuestion;
       
    include("./mpdf.php");    
   
    $mpdf= new mPDF(); 

    $mpdf->autoScriptToLang = true;
    $mpdf->baseScript = 1;
    $mpdf->autoVietnamese = true;
    $mpdf->autoArabic = true;

    $mpdf->autoLangToFont = true;

//    $mpdf->showImageErrors = true;
    

    $mpdf->mirrorMargins = 1;	// Use different Odd/Even headers and footers and mirror margins

    $header = '
    <table width="100%" style="vertical-align: bottom; font-family: serif; font-size: 9pt; color: #000088;"><tr>
    <td width="33%">Page <span style="font-size:9pt;">{PAGENO}</span></td>
    <td width="33%" align="center"></td>
    
    </tr></table>
    ';

    $footer = '<div aligh="center"></div><div align="center"><img src="img/logo_report.jpg" width="126px" /></div>';
    
    $mpdf->SetHTMLHeader($header);
    $mpdf->SetHTMLFooter($footer);
    
    $mpdf->WriteHTML($htmlStr);
    
    $mpdf->Output($file_name,'D');
    
 }
 
function initAllEmpReport(){
     
     
    $file_name = str_replace(" ","_", "Progress_Report_ALL_EMP_". time().".pdf");
     
         
    $css = '<style> table, td, th {
                border: 3px solid #FFFFFF;
            }
                .task{width:96%; height:auto;display:inline-block; font-weight:bold; font-size:110%; float:left; margin-top:20px; margin-bottom:20px;}
                .desc{width:96%; height:auto;  display:inline-block; font-size:110%; float:left; margin-top:20px; margin-bottom: 20px;}
                .point{width:96%; height:auto;  display:inline-block;  font-size:150%; float:left; margin-top:20px;}
                .priority{width:96%; height:auto;  display:block; font-size:150%; float:left; margin-top:10px; margin-bottom:10px;}
                .remarks{width:96%; height:auto;  display:inline-block; font-size:100%; float:left; margin-top:10px; margin-bottom:10px;}
                
                .pending-task{ color: #d40009;}
                .completed{ color: #019400;}
            </style>';
          
    $logoFile = "img/company_logo.jpg";
           
     // logo display
    $logo = '<div style="width:100%; height:250px; display:inline-block;  margin-top:20px;" >'
            . '<div style="width:100%; height:180px; display:inline-block;  text-align:center;" ><center><img src="'. $logoFile.'" height="170px"/></center></div>'
            . '<div style="width:100%; height:40px; padding:10px; display:inline-block;font-size:17pt; text-align:center;" > <u>Team Work Progress Report</u></div>'
            .'</div>'; 
    
        
    $userInfo = '<center><table width="100%" border="0" style="font-size:14pt; border-top: 0.1mm solid #000000; ">
                
                <tr>
                  <td>Duration : <b>'.$this->start_date.' to '. $this->end_date .' </b></td>
               
              </table></center>';
    
    // table display with stats
    $tableHead = '<br><br><table width="100%" style="background:#BFCEE9;" cellpadding="5" cellspacing="0">
    <tr valign="middle" style="background:#F7BBA1;">
      <td width="200" height="25">Task Name</td>
      <td width="70">Priority</td>
      <td width="70">Deadline</td>
      <td width="100">Assign To</td>
      <td width="64">Status</td>
    </tr>';
    
    $tableContent = null;

   
   
    $marksObtainString = '<div  style="font-size:14pt; display:inline-block; margin-top:20px; margin-bottom:20px;">  Task Descriptions and Remarks <br/> </div>';
     

    $htmlStr = null;
    
    
    $this->connectDB();
            
   

        $currentQuestion = null;
            
        $result = mysqli_query($this->conn,  "SELECT t.task_name, t.task_priority,t.task_deadline,t.task_status,t.task_desc,t.remarks, e.first_name,e.last_name from task t INNER JOIN employee e ON t.task_assign_to = e.emp_id ORDER BY e.first_name "); // by date
        
        if(mysqli_num_rows($result) > 0){
                $i = 0;
               while($row = mysqli_fetch_assoc($result)){
                    $i++;
                                 
                    $task_status = strtoupper($row['task_status']);
                    
                    $task_status_tag = "";
                    
                    $priority_tag = "";
                    
                    if(intval($row['task_priority']) > 1){
                        $priority_tag = "Critical";
                    }else if(intval($row['task_status']) > 0){
                        $priority_tag = "High";
                    }else {
                        $priority_tag = "Normal";
                    }
                   
                    
                    if($task_status == "COMPLETED"){
                        $task_status_tag = '<td width="64" style="background:#88E38D;">'.$task_status.'</td>';
                    }else{
                        $task_status_tag = '<td width="64" style="background:#F27272;">'.$task_status.'</td>';
                    }
                    
                    $tableContent .= '<tr valign="middle">
                        <td width="200" height="25">'.$row['task_name'].'</td>
                        <td width="70">'. $priority_tag .'</td>
                        <td width="70">'. $row['task_deadline'] .'</td>
                        <td width="100">'. $row['first_name'] .' '. $row['last_name'].'</td>'.
                        $task_status_tag
                      .'</tr>';
                    
                    
                    $task_name ='<div class="task">'.$i.'.'. $row['task_name'] .'</div>';

                   
                    $task_point = '<div class="point"> Task Points :'. $row['bonus_point'] .'</div>';
                    
                    
                    $priority =  '<div class="priority">Priority :'. $priority_tag .'</div>';

                    $description = '<div class="desc"> Description :' . $row['task_desc'] .'</div>';
                    $remarks = '<div class="remarks">Remarks :'. $row['remarks'] . '</div>';
                   
                    $currentQuestion .= $task_name.''
                                        .$description .''
                                        .$task_point
                                        .$priority.''
                                        .$remarks;
                   
                }
         }
         
          $tableFooter = '</table>';
         $constructTable = $tableHead.''.$tableContent.''.$tableFooter;
    
 
         // Generate HTML
         
        $htmlStr .=     $css.''
                        .$logo .''
                        .$userInfo.''
                        .$constructTable.''
                        .$marksObtainString .''
                        .$currentQuestion;
        
    
    include("./mpdf.php");    
   
    $mpdf= new mPDF(); 

    $mpdf->autoScriptToLang = true;
    $mpdf->baseScript = 1;
    $mpdf->autoVietnamese = true;
    $mpdf->autoArabic = true;

    $mpdf->autoLangToFont = true;
    

    $mpdf->mirrorMargins = 1;	// Use different Odd/Even headers and footers and mirror margins

    $header = '
    <table width="100%" style="vertical-align: bottom; font-family: serif; font-size: 9pt; color: #000088;"><tr>
    <td width="33%">Page <span style="font-size:9pt;">{PAGENO}</span></td>
    <td width="33%" align="center"></td>
    
    </tr></table>
    ';

    $footer = '<div aligh="center"></div><div align="center"><img src="img/logo_report.jpg" width="126px" /></div>';
    
    $mpdf->SetHTMLHeader($header);
    $mpdf->SetHTMLFooter($footer);
    
    $mpdf->WriteHTML($htmlStr);

    $mpdf->Output($file_name,'D');
 }
 
 
 function reportStates(){
     
       $this->connectDB();
            
        $report_statistics = mysqli_query($this->conn,  "SELECT * FROM reports where emp_id='$this->emp_id' Limit 1 "); // by date
        
        if(mysqli_num_rows($report_statistics) > 0){
               while($row = mysqli_fetch_assoc($report_statistics)){
                   
                   $this->total_earned_points = $row['obtain_task_score'];
                   $this->completed_task = $row['completed_task_count']."/".$row['total_task_count'];
                   $this->total_percentage = $row['progress_percent'];
                   
               }
           $this->prepareReportData();

        }else{
            $this->reportNotFound();
        }
        
  }
  
  
 //PROGRESS REPORT
 function progressReport(){
     
     if($this->emp_id == "ALL"){
          $this->initAllEmpReport();
      }else{
            
        $this->connectDB();
        $report_details = mysqli_query($this->conn, "SELECT * from employee WHERE emp_id ='$this->emp_id'");

         if(mysqli_num_rows($report_details) > 0){
            while($row = mysqli_fetch_assoc($report_details)){

                $this->emp_name = $row['first_name']." ".$row['last_name'];
                $this->emp_email_id = $row['email_id'];
                $this->emp_contact_number = $row['phone_number'];
                $this->emp_designation = $row['designation'];
             }
             $this->reportStates();
         }
      }
 }
 
 //TASK Details
function taskDetailsReport(){
    
    if($this->emp_id == "ALL"){
         $this->exportAllEmpTaskList();
     }else{
        
         $this->connectDB();
        $report_details = mysqli_query($this->conn, "SELECT * from employee WHERE emp_id ='$this->emp_id'");

         if(mysqli_num_rows($report_details) > 0){
            while($row = mysqli_fetch_assoc($report_details)){

                $this->emp_name = $row['first_name']." ".$row['last_name'];
                $this->emp_email_id = $row['email_id'];
                $this->emp_contact_number = $row['phone_number'];
                $this->emp_designation = $row['designation'];
             }
             $this->exportEmpTaskList();
         }
    }
}

function exportAllEmpTaskList(){
    
    $file_name = str_replace(" ","_", "Task_List_ALL_EMP_". time().".pdf");
     
         
    $css = '<style> table, td, th {
                border: 3px solid #FFFFFF;
            }
                .task{width:96%; height:auto;display:inline-block; font-weight:bold; font-size:110%; float:left; margin-top:20px; margin-bottom:20px;}
                .desc{width:96%; height:auto;  display:inline-block; font-size:110%; float:left; margin-top:20px; margin-bottom: 20px;}
                .point{width:96%; height:auto;  display:inline-block;  font-size:150%; float:left; margin-top:20px;}
                .priority{width:96%; height:auto;  display:block; font-size:150%; float:left; margin-top:10px; margin-bottom:10px;}
                .remarks{width:96%; height:auto;  display:inline-block; font-size:100%; float:left; margin-top:10px; margin-bottom:10px;}
                
                .pending-task{ color: #d40009;}
                .completed{ color: #019400;}
            </style>';
          
    $logoFile = "img/company_logo.jpg";
           
     // logo display
    $logo = '<div style="width:100%; height:250px; display:inline-block;  margin-top:20px;" >'
            . '<div style="width:100%; height:180px; display:inline-block;  text-align:center;" ><center><img src="'. $logoFile.'" height="170px"/></center></div>'
            . '<div style="width:100%; height:40px; padding:10px; display:inline-block;font-size:17pt; text-align:center;" > <u>Assigned Task List</u></div>'
            .'</div>'; 
    
        
    $userInfo = '<center><table width="100%" border="0" style="font-size:14pt; border-top: 0.1mm solid #000000; ">
                
                <tr>
                  <td>Duration : <b>'.$this->start_date.' to '. $this->end_date .' </b></td>
               
              </table></center>';
    
    // table display with stats
    $tableHead = '<br><br><table width="100%" style="background:#BFCEE9;" cellpadding="5" cellspacing="0">
    <tr valign="middle" style="background:#F7BBA1;">
      <td width="200" height="25">Task Name</td>
      <td width="70">Priority</td>
      <td width="70">Deadline</td>
      <td width="100">Assign To</td>
      <td width="64">Status</td>
    </tr>';
    
    $tableContent = null;

   
   
    $marksObtainString = '<div  style="font-size:14pt; display:inline-block; margin-top:20px; margin-bottom:20px;">  Task Descriptions and Remarks <br/> </div>';
     

    $htmlStr = null;
    
    
    $this->connectDB();
            
   

        $currentQuestion = null;
            
        $result = mysqli_query($this->conn,  "SELECT t.task_name, t.task_priority,t.task_deadline,t.task_status,t.task_desc,t.remarks, e.first_name,e.last_name from task t INNER JOIN employee e ON t.task_assign_to = e.emp_id ORDER BY e.first_name "); // by date
        
        if(mysqli_num_rows($result) > 0){
                $i = 0;
               while($row = mysqli_fetch_assoc($result)){
                    $i++;
                                 
                    $task_status = strtoupper($row['task_status']);
                    
                    $task_status_tag = "";
                    
                    $priority_tag = "";
                    
                    if(intval($row['task_priority']) > 1){
                        $priority_tag = "Critical";
                    }else if(intval($row['task_status']) > 0){
                        $priority_tag = "High";
                    }else {
                        $priority_tag = "Normal";
                    }
                   
                    
                    if($task_status == "COMPLETED"){
                        $task_status_tag = '<td width="64" style="background:#88E38D;">'.$task_status.'</td>';
                    }else{
                        $task_status_tag = '<td width="64" style="background:#F27272;">'.$task_status.'</td>';
                    }
                    
                    $tableContent .= '<tr valign="middle">
                        <td width="200" height="25">'.$row['task_name'].'</td>
                        <td width="70">'. $priority_tag .'</td>
                        <td width="70">'. $row['task_deadline'] .'</td>
                        <td width="100">'. $row['first_name'] .' '. $row['last_name'].'</td>'.
                        $task_status_tag
                      .'</tr>';
                    
                    
                    $task_name ='<div class="task">'.$i.'.'. $row['task_name'] .'</div>';

                   
                    $task_point = '<div class="point"> Task Points :'. $row['bonus_point'] .'</div>';
                    
                    
                    $priority =  '<div class="priority">Priority :'. $priority_tag .'</div>';

                    $description = '<div class="desc"> Description :' . $row['task_desc'] .'</div>';
                    $remarks = '<div class="remarks">Remarks :'. $row['remarks'] . '</div>';
                   
                    $currentQuestion .= $task_name.''
                                        .$description .''
                                        .$task_point
                                        .$priority.''
                                        .$remarks;
                   
                }
         }
         
          $tableFooter = '</table>';
         $constructTable = $tableHead.''.$tableContent.''.$tableFooter;
    
 
         // Generate HTML
         
        $htmlStr .=     $css.''
                        .$logo .''
                        .$userInfo.''
                        .$constructTable.''
                        .$marksObtainString .''
                        .$currentQuestion;
 
    
    include("./mpdf.php");    
   
    $mpdf= new mPDF(); 

    $mpdf->autoScriptToLang = true;
    $mpdf->baseScript = 1;
    $mpdf->autoVietnamese = true;
    $mpdf->autoArabic = true;

    $mpdf->autoLangToFont = true;
    

    $mpdf->mirrorMargins = 1;	// Use different Odd/Even headers and footers and mirror margins

    $header = '
    <table width="100%" style="vertical-align: bottom; font-family: serif; font-size: 9pt; color: #000088;"><tr>
    <td width="33%">Page <span style="font-size:9pt;">{PAGENO}</span></td>
    <td width="33%" align="center"></td>
    
    </tr></table>
    ';

    $footer = '<div aligh="center"></div><div align="center"><img src="img/logo_report.jpg" width="126px" /></div>';
    
    $mpdf->SetHTMLHeader($header);
    $mpdf->SetHTMLFooter($footer);
    
    $mpdf->WriteHTML($htmlStr);

    $mpdf->Output($file_name,'D');
}

function exportEmpTaskList(){
    
    $file_name = str_replace(" ","_", "Assigned_Task_List_".$this->emp_name."_". time().".pdf");
     
         
    $css = '<style> table, td, th {
                border: 3px solid #FFFFFF;
            }
                .task{width:96%; height:auto;display:inline-block; font-weight:bold; font-size:110%; float:left; margin-top:20px; margin-bottom:20px;}
                .desc{width:96%; height:auto;  display:inline-block; font-size:110%; float:left; margin-top:20px; margin-bottom: 20px;}
                .points{width:96%; height:auto;  display:inline-block;  font-size:150%; float:left; margin-top:20px;}
                .priority{width:96%; height:auto;  display:block; font-size:150%; float:left; margin-top:10px; margin-bottom:10px;}
                .remarks{width:96%; height:auto;  display:inline-block; font-size:100%; float:left; margin-top:10px; margin-bottom:10px;}
                
                .pending-task{ color: #d40009;}
                .completed{ color: #019400;}
            </style>';
          
    $logoFile = "img/company_logo.jpg";
           
     // logo display
    $logo = '<div style="width:100%; height:250px; display:inline-block;  margin-top:20px;" >'
            . '<div style="width:100%; height:180px; display:inline-block;  text-align:center;" ><center><img src="'. $logoFile.'" height="170px"/></center></div>'
            . '<div style="width:100%; height:40px; padding:10px; display:inline-block;font-size:17pt; text-align:center;" > <u>Work Progress Report</u></div>'
            .'</div>'; 
    
    $userInfo = '<center><table width="100%" border="0" style="font-size:14pt; border-top: 0.1mm solid #000000; ">
                <tr>
                  <td width="300" height="29">Name : <b>'. $this->emp_name.'</b></td>
                  <td width="100"></td>
                  <td width="300"> Designation: <b>'. $this->emp_designation.'</b> </td>
                </tr>
                <tr>
                  <td height="29">Contact Number :<b>'. $this->emp_contact_number.'</b> </td>
                  <td></td>
                  <td>Email ID:  <b>'. $this->emp_email_id.'</b></td>
                </tr>
                <tr>
                  <td height="29"> Completed Task Out Of : <b>'. $this->completed_task.'</b></td>
                  <td></td>
                  <td>Date: <b>'.$this->start_date.' - '. $this->end_date .' </b></td>
                </tr>
                  <tr>
                  <td>Total Points Earned :  <b>'. $this->total_earned_points.'</b></td>
                  <td></td>
                  <td>Work Progress Ration : <b>'. $this->total_percentage.' % </b></td>
                </tr>
              </table></center>'; 
    
    
    // table display with stats
    $tableHead = '<br><br><table width="100%" style="background:#BFCEE9;" cellpadding="5" cellspacing="0">
    <tr valign="middle" style="background:#F7BBA1;">
      <td width="282" height="25">Task Name</td>
      <td width="70">Priority</td>
      <td width="70">Deadline</td>
      <td width="64">Task Point</td>
      <td width="64">Status</td>
    </tr>';
    
    $tableContent = null;

   
   
    $marksObtainString = '<div  style="font-size:14pt; display:inline-block; margin-top:20px; margin-bottom:20px;">  Task Descriptions and Remarks <br/> </div>';
     

    $htmlStr = null;
    
    
    $this->connectDB();
            
   

        $currentQuestion = null;
            
        $result = mysqli_query($this->conn,  "SELECT * FROM task where task_assign_to='$this->emp_id' ORDER BY task_deadline "); // by date
        
        if(mysqli_num_rows($result) > 0){
                $i = 0;
               while($row = mysqli_fetch_assoc($result)){
                    $i++;
                                 
                    $task_status = strtoupper($row['task_status']);
                    
                    $task_status_tag = "";
                                        
                    $priority_tag = "";
                    
                    if(intval($row['task_priority']) > 1){
                        $priority_tag = "Critical";
                    }else if(intval($row['task_status']) > 0){
                        $priority_tag = "High";
                    }else {
                        $priority_tag = "Normal";
                    }
                    
                    if($task_status == "COMPLETED"){
                        $task_status_tag = '<td width="64" style="background:#88E38D;">'.$task_status.'</td>';
                    }else{
                        $task_status_tag = '<td width="64" style="background:#F27272;">'.$task_status.'</td>';
                    }
                    
                    $tableContent .= '<tr valign="middle">
                        <td width="282" height="25">'.$row['task_name'].'</td>
                        <td width="70">'. $priority_tag .'</td>
                        <td width="70">'. $row['task_deadline'] .'</td>
                        <td width="64">'. $row['bonus_point'] .'</td>'.$task_status_tag.'
                        
                      </tr>';
                                        
                    $task_name ='<div class="task">'.$i.'.'. $row['task_name'] .'</div>';

                    $task_point = '<div class="points"> Task Points :'. $row['bonus_point'] .'</div>';
                    
                    $priority =  '<div class="priority">Priority :'. $priority_tag .'</div>';

                    $description = '<div class="desc"> Description :' . $row['task_desc'] .'</div>';
                    $remarks = '<div class="remarks">Remarks :'. $row['remarks'] . '</div>';
                   
                    $currentQuestion .= $task_name.''
                                        .$description .''
                                        .$task_point
                                        .$priority.''
                                        .$remarks;
                   
                }
         }
         
        $tableFooter = '</table>';
        $constructTable = $tableHead.''.$tableContent.''.$tableFooter;
     
         // Generate HTML
        $htmlStr .=     $css.''
                        .$logo .''
                        .$userInfo.''
                        .$constructTable.''
                        .$marksObtainString .''
                        .$currentQuestion;
                        
    
    include("./mpdf.php");    
   
    $mpdf= new mPDF(); 

    $mpdf->autoScriptToLang = true;
    $mpdf->baseScript = 1;
    $mpdf->autoVietnamese = true;
    $mpdf->autoArabic = true;

    $mpdf->autoLangToFont = true;

//    $mpdf->showImageErrors = true;
    

    $mpdf->mirrorMargins = 1;	// Use different Odd/Even headers and footers and mirror margins

    $header = '
    <table width="100%" style="vertical-align: bottom; font-family: serif; font-size: 9pt; color: #000088;"><tr>
    <td width="33%">Page <span style="font-size:9pt;">{PAGENO}</span></td>
    <td width="33%" align="center"></td>
    
    </tr></table>
    ';

    $footer = '<div aligh="center"></div><div align="center"><img src="img/logo_report.jpg" width="126px" /></div>';
    
    $mpdf->SetHTMLHeader($header);
    $mpdf->SetHTMLFooter($footer);
    
    $mpdf->WriteHTML($htmlStr);

    $mpdf->Output($file_name,'D');
    
}

function attendanceReport(){
    
      $file_name = str_replace(" ","_", "Attendance Report ". time().".pdf");
     
         
    $css = '<style> table, td, th {
                border: 3px solid #FFFFFF;
            }
                .task{width:96%; height:auto;display:inline-block; font-weight:bold; font-size:110%; float:left; margin-top:20px; margin-bottom:20px;}
                .desc{width:96%; height:auto;  display:inline-block; font-size:110%; float:left; margin-top:20px; margin-bottom: 20px;}
                .points{width:96%; height:auto;  display:inline-block;  font-size:110%; float:left; margin-top:20px;}
                .priority{width:96%; height:auto;  display:block; font-size:110%; float:left; margin-top:10px; margin-bottom:10px;}
                .remarks{width:96%; height:auto;  display:inline-block; font-size:100%; float:left; margin-top:10px; margin-bottom:10px;}
                
                .pending-task{ color: #d40009;}
                .completed{ color: #019400;}
            </style>';
          
    $logoFile = "img/company_logo.jpg";
           
     // logo display
    $logo = '<div style="width:100%; height:250px; display:inline-block;  margin-top:20px;" >'
            . '<div style="width:100%; height:180px; display:inline-block;  text-align:center;" ><center><img src="'. $logoFile.'" height="170px"/></center></div>'
            . '<div style="width:100%; height:40px; padding:10px; display:inline-block;font-size:17pt; text-align:center;" > <u>Attendance Report</u></div>'
            .'</div>'; 
    
    $userInfo = '<center><table width="100%" border="0" style="font-size:14pt; border-top: 0.1mm solid #000000; ">
                
                <tr>
                  <td>Attendance Duration : <b>'.$this->start_date.' - '. $this->end_date .' </b></td>
               
              </table></center>'; 
    
    
    // table display with stats
    $tableHead = '<br><br><table width="100%" style="background:#BFCEE9;" cellpadding="5" cellspacing="0">
    <tr valign="middle" style="background:#F7BBA1;">
      <td width="80" height="25">Date</td>
      <td width="100">Employee Name</td>
      <td width="110">Check In</td>
      <td width="110">Check Out</td>
      <td width="64">Status</td>
    </tr>';
    
    $tableContent = null;

   
   
    $additionalheadline = '<div  style="font-size:14pt; display:inline-block; margin-top:20px; margin-bottom:20px;"> Leaves Details <br/> </div>';
     

    $htmlStr = null;
    
    
    $this->connectDB();
            
   

            
        $result = mysqli_query($this->conn,  "SELECT a.attendance_date,a.in_time,a.out_time,a.status,e.first_name,e.last_name from attendance a INNER JOIN employee e ON a.emp_id = e.emp_id ORDER BY a.attendance_date"); // by date
        
        if(mysqli_num_rows($result) > 0){
                $i = 0;
               while($row = mysqli_fetch_assoc($result)){
                    $i++;
                                                     
                                                            
                    $tableContent .= '<tr valign="middle">
                        <td width="70" height="25">'.$row['attendance_date'].'</td>
                        <td width="100">'. $row['first_name'] .' '.$row['last_name'].'</td>
                        <td width="110">'. $row['in_time'] .'</td>
                        <td width="110">'. $row['out_time'] .'</td>'
                        .'<td width="64">'. $row['status'] .'</td>'
                      .'</tr>';
                  
                }
         }
         
        $tableFooter = '</table>';
        $constructTable = $tableHead.''.$tableContent.''.$tableFooter;
     
        $attendance_content = "";
        
        $leavesQuery = mysqli_query($this->conn,  "SELECT l.apply_date, l.leave_title,l.leave_description,l.leave_from,l.leave_to,l.leave_status, e.first_name,e.last_name FROM leaves l INNER JOIN employee e ON l.emp_id = e.emp_id ORDER BY l.apply_date"); // by date
        
        if(mysqli_num_rows($leavesQuery) > 0){
                $i = 0;
               while($row = mysqli_fetch_assoc($leavesQuery)){
                    $i++;
                                                     
                    $leave_title ='<div class="task">'.$i.'.'. $row['leave_title'] .'</div>';
                    $description = '<div class="desc"> Description : ' . $row['leave_description'] .'</div>';

                    $leave_duration = '<div class="points"> Leave Duration : '. $row['leave_from'] .' to '. $row['leave_to'].'</div>';
                    
                    $leave_applyed_by =  '<div class="priority">Applied By : '. $row['first_name'] .' '. $row['last_name'].'</div>';

                    $leave_status = '<div class="remarks">Status : '. $row['leave_status'] . '</div>';
                   
                    $attendance_content .= $leave_title.''
                                        .$description .''
                                        .$leave_duration
                                        .$leave_applyed_by.''
                                        .$leave_status;
                   
                }
         }
        
        
         // Generate HTML
        $htmlStr .=     $css.''
                        .$logo .''
                        .$userInfo.''
                        .$constructTable.''
                        .$additionalheadline .''
                        .$attendance_content;
                        
    
    include("./mpdf.php");    
   
    $mpdf= new mPDF(); 

    $mpdf->autoScriptToLang = true;
    $mpdf->baseScript = 1;
    $mpdf->autoVietnamese = true;
    $mpdf->autoArabic = true;

    $mpdf->autoLangToFont = true;

//    $mpdf->showImageErrors = true;
    

    $mpdf->mirrorMargins = 1;	// Use different Odd/Even headers and footers and mirror margins

    $header = '
    <table width="100%" style="vertical-align: bottom; font-family: serif; font-size: 9pt; color: #000088;"><tr>
    <td width="33%">Page <span style="font-size:9pt;">{PAGENO}</span></td>
    <td width="33%" align="center"></td>
    
    </tr></table>
    ';

    $footer = '<div aligh="center"></div><div align="center"><img src="img/logo_report.jpg" width="126px" /></div>';
    
    $mpdf->SetHTMLHeader($header);
    $mpdf->SetHTMLFooter($footer);
    
    $mpdf->WriteHTML($htmlStr);

    $mpdf->Output($file_name,'D');
    
}

function reportNotFound(){
    
    
         // Generate HTML
         
    $htmlStr = "<div> report Not found!</div>";
                        


    
    include("./mpdf.php");    
   
    $mpdf= new mPDF(); 

    $mpdf->autoScriptToLang = true;
    $mpdf->baseScript = 1;
    $mpdf->autoVietnamese = true;
    $mpdf->autoArabic = true;

    $mpdf->autoLangToFont = true;

//    $mpdf->showImageErrors = true;
    

    $mpdf->mirrorMargins = 1;	// Use different Odd/Even headers and footers and mirror margins

    $header = '
    <table width="100%" style="vertical-align: bottom; font-family: serif; font-size: 9pt; color: #000088;"><tr>
    <td width="33%">Page <span style="font-size:9pt;">{PAGENO}</span></td>
    <td width="33%" align="center"></td>
    
    </tr></table>
    ';

    $footer = '<div aligh="center"></div><div align="center"><img src="img/logo_report.jpg" width="126px" /></div>';
    
    $mpdf->SetHTMLHeader($header);
    $mpdf->SetHTMLFooter($footer);
    
    $mpdf->WriteHTML($htmlStr);

    $mpdf->Output('report_not_found.pdf','D');
}
  
function initReport(){
    

    $this->report_type = $_GET['mode'];
    $this->emp_id = $_GET['emp_id'];
    $this->start_date = $_GET['s_date'];
    $this->end_date = $_GET['e_date'];
        
    switch ($this->report_type) {
        case 0: // Progress Report
            $this->progressReport();
            break;
        case 1: // Task List
            $this->taskDetailsReport();
            break;
        case 2: // attendance
            $this->attendanceReport();
            break;
        default:
            $this->reportNotFound();
            break;
    }
      
  }
  
    
}

$self = new export_report();
$self->initReport();

 


