<?php 

//Set the time so the script does not time out. Large videos may take a while.
ini_set('max_execution_time', 10000); //300 seconds = 5 minutes

$videodir = "videos/"; //video folder directory including the "/" i.e. "videos/"


if ($handle = opendir('.')) { // open the current directory

	//initialise variables
	$folders = ""; 
	$mkv = "";
	$directorystring = "";
	
	
	function get_server_load() {
    
        if (stristr(PHP_OS, 'win')) {
        
            $wmi = new COM("Winmgmts://");
            $server = $wmi->execquery("SELECT LoadPercentage FROM Win32_Processor");
            
            $cpu_num = 0;
            $load_total = 0;
            
            foreach($server as $cpu){
                $cpu_num++;
                $load_total += $cpu->loadpercentage;
            }
            
            $load = round($load_total/$cpu_num);
            
        } else {
        
            $sys_load = sys_getloadavg();
            $load = $sys_load[0];
        
        }
        
        return $load;
    
    }   
		$temp = get_server_load();
		
		//connection to mysql database and query the information. 
		
	$link = mysqli_connect('mydbinstance.crk8jaubwnsf.eu-west-1.rds.amazonaws.com', 'awsuser', 'mypassword', 'mydb', 3306);
		if (!$link) { 
			die('Could not connect to MySQL: ' . mysql_error()); 
				} 
					//echo 'Connection OK'; 
					//mysqli_close($link); 
					
					$sql = "CREATE TABLE MyGuests (
					id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY, 
					firstname VARCHAR(30) NOT NULL,
					lastname VARCHAR(30) NOT NULL,
					email VARCHAR(50),
					reg_date TIMESTAMP
					)";
					$table = 'MyGuests';
					$sql = "INSERT INTO MyGuests (firstname, lastname, email)
					VALUES ('John', 'Doe', 'john@example.com')";
					
					$sql = "INSERT INTO MyGuests (firstname, lastname, email)
					VALUES ('bib', 'dylan', 'john@example.com')";
					
					if ($link->query($sql) === TRUE) {
    echo "New record created successfully";
} else {
    echo "Error: " . $sql . "<br>" . $link->error;
}
				$dance = "SELECT id, firstname, lastname FROM MyGuests";
				$result = $link->query($dance);

			if ($result->num_rows > 0) {
    // output data of each row
    while($row = $result->fetch_assoc()) {
        echo "id: " . $row["id"]. " - Name: " . $row["firstname"]. " " . $row["lastname"]. "<br>";
    }
} else {
    echo "0 results";
}
$link->close();
					


/*if ($link->query($sql) === TRUE) {
    echo "Table MyGuests created successfully";
} else {
    echo "Error creating table: " . $link->error;
}

$link->close();*/
		
	/*function getServerLoad($windows = false){
    $os=strtolower(PHP_OS);
    if(strpos($os, 'win') === false){
        if(file_exists('/proc/loadavg')){
            $load = file_get_contents('/proc/loadavg');
            $load = explode(' ', $load, 1);
            $load = $load[0];
        }elseif(function_exists('shell_exec')){
            $load = explode(' ', `uptime`);
            $load = $load[count($load)-1];
        }else{
            return false;
        }

        if(function_exists('shell_exec'))
            $cpu_count = shell_exec('cat /proc/cpuinfo | grep processor | wc -l');        

        return array('load'=>$load, 'procs'=>$cpu_count);
    }elseif($windows){
        if(class_exists('COM')){
            $wmi=new COM('WinMgmts:\\\\.');
            $cpus=$wmi->InstancesOf('Win32_Processor');
            $load=0;
            $cpu_count=0;
            if(version_compare('4.50.0', PHP_VERSION) == 1){
                while($cpu = $cpus->Next()){
                    $load += $cpu->LoadPercentage;
                    $cpu_count++;
                }
            }else{
                foreach($cpus as $cpu){
                    $load += $cpu->LoadPercentage;
                    $cpu_count++;
                }
            }
            
			$sload = array('load'=>$load, 'procs'=>$cpu_count);
			echo $sload[0];
        }
        return false;
    }
    return false;
}*/
	//$cars = array("Volvo", "BMW", "Toyota");
	//echo $cars[0];
	
	//while the directory still has a file or folder sort them into folders mkv files and other.
    while (false !== ($entry = readdir($handle))) {
		if (filetype($entry) == 'dir'){ // if it is a dir then add it to folders
			$folders = $folders . $entry . ";";
		} else {// if it isn't a directory then it must be a file
			if (substr($entry, strpos($entry, '.', strlen($entry)- 5) + 1) == "mkv"){// if the extension is mkv then add the file to mkv list
				$mkv = $mkv . $entry . ";";
			} elseif (substr($entry, strpos($entry, '.', strlen($entry)- 5) + 1) == "mpg" || substr($entry, strpos($entry, '.', strlen($entry)- 5) + 1) == "mpeg"){ // example to show this can be extended for other video file types
				$mpg = $mpg . $entry . ";";
			}else{ // every other file name gets stored in this string
				$directorystring = $directorystring . $entry . ";";
			}
		}
    }
	$temp2=chop($mkv,";");
	
	$arr = array($temp, $temp2);
	echo json_encode($arr);
	
	//if the video folder is not in the directory then create it
	if (strpos($folders, substr($videodir, 0, strlen($videodir)-1)) === false) {
		mkdir($videodir, 0755);
	}
	
	// store each video name in an array
	$mkvvideoArray = explode(";", $mkv);
	
	// for each video name check if an equivalent folder is named in the video directory otherwise create it.
	foreach ($mkvvideoArray as $video){
		if ($video != ""){
			if (file_exists($videodir . substr($video, 0, strpos($video, '.', strlen($video)- 5))) == FALSE){
				mkdir($videodir . substr($video, 0, strpos($video, '.', strlen($video)- 5)), 0755);
			} else {// if the folder exists check if its a directory. If not delete it and create the folder.
				if (is_dir($videodir . substr($video, 0, strpos($video, '.', strlen($video)- 5))) == FALSE){
					if (unlink($videodir . substr($video, 0, strpos($video, '.', strlen($video)- 5))) == TRUE){
						mkdir($videodir . substr($video, 0, strpos($video, '.', strlen($video)- 5)), 0755);
					}
				}
			}
			
			// get duration and bitrate information about the video from ffmpeg
			ob_start();
			passthru("ffmpeg -i \"" . $video . "\" 2>&1"); // command to run
			$full = ob_get_contents(); // response
			ob_end_clean();
			
			// get the strings with bitrate and duration in
			preg_match('/bitrate: (........)/', $full, $bitratematches);
			preg_match('/Duration: (.*?),/', $full, $durationmatches);
			
			// format the exact bitrate and duration
			$duration = $durationmatches[1];
			$bitrate = $bitratematches[0];
			$bitrateArray = explode(' ', $bitrate);
			$bitrate = $bitrateArray[1];
			$duration_array = explode(':', $duration);
			$duration = $duration_array[0] * 3600 + $duration_array[1] * 60 + $duration_array[2];
			$firstbitrate = $bitrate;
			
			// create the different video qualities if they dont already exist
			//highest quality
			$videonumber = 1;
			$bitrate = $firstbitrate * 1024 * 2;
			if (!file_exists($videodir . substr($video, 0, strpos($video, '.', strlen($video)- 5)) . "/" . substr($video, 0, strpos($video, '.', strlen($video)- 5)) . $videonumber . substr($video, strpos($video, '.', strlen($video)- 5)))){
				pclose(popen("start /B ffmpeg -i \"" . $video . "\" -b " . $bitrate . " \"" . $videodir . substr($video, 0, strpos($video, '.', strlen($video)- 5)) . "/" . substr($video, 0, strpos($video, '.', strlen($video)- 5)) . $videonumber . substr($video, strpos($video, '.', strlen($video)- 5)) . "\"","r"));
			}
			
			//second highest quality
			$videonumber = 2;
			$bitrate = $firstbitrate * 1024 * 1.5;
			if (!file_exists($videodir . substr($video, 0, strpos($video, '.', strlen($video)- 5)) . "/" . substr($video, 0, strpos($video, '.', strlen($video)- 5)) . $videonumber . substr($video, strpos($video, '.', strlen($video)- 5)))){
				pclose(popen("start /B ffmpeg -i \"" . $video . "\" -b " . $bitrate . " \"" . $videodir . substr($video, 0, strpos($video, '.', strlen($video)- 5)) . "/" . substr($video, 0, strpos($video, '.', strlen($video)- 5)) . $videonumber . substr($video, strpos($video, '.', strlen($video)- 5)) . "\"","r"));
			}
			
			//A copy of the video placed in the video folder
			$videonumber = 3;
			if (!file_exists($videodir . substr($video, 0, strpos($video, '.', strlen($video)- 5)) . "/" . substr($video, 0, strpos($video, '.', strlen($video)- 5)) . $videonumber . substr($video, strpos($video, '.', strlen($video)- 5)))){
				pclose(popen("copy /y " . "\"" . $video . "\" " . "\"" . $videodir . substr($video, 0, strpos($video, '.', strlen($video)- 5)) . "/" . substr($video, 0, strpos($video, '.', strlen($video)- 5)) . $videonumber . substr($video, strpos($video, '.', strlen($video)- 5)) . "\"","r"));
			}
			
			//the second lowest quality
			$videonumber = 4;
			$bitrate = ($firstbitrate * 1024) / 2;
			if (!file_exists($videodir . substr($video, 0, strpos($video, '.', strlen($video)- 5)) . "/" . substr($video, 0, strpos($video, '.', strlen($video)- 5)) . $videonumber . substr($video, strpos($video, '.', strlen($video)- 5)))){
			pclose(popen("start /B ffmpeg -i \"" . $video . "\" -b " . $bitrate . " \"" . $videodir . substr($video, 0, strpos($video, '.', strlen($video)- 5)) . "/" . substr($video, 0, strpos($video, '.', strlen($video)- 5)) . $videonumber . substr($video, strpos($video, '.', strlen($video)- 5)) . "\"","r"));
			}
			
			//the lowest quality
			$videonumber = 5;
			$bitrate = ($firstbitrate * 1024) / 4;
			if (!file_exists($videodir . substr($video, 0, strpos($video, '.', strlen($video)- 5)) . "/" . substr($video, 0, strpos($video, '.', strlen($video)- 5)) . $videonumber . substr($video, strpos($video, '.', strlen($video)- 5)))){
			pclose(popen("start /B ffmpeg -i \"" . $video . "\" -b " . $bitrate . " \"" . $videodir . substr($video, 0, strpos($video, '.', strlen($video)- 5)) . "/" . substr($video, 0, strpos($video, '.', strlen($video)- 5)) . $videonumber . substr($video, strpos($video, '.', strlen($video)- 5)) . "\"","r"));
			}
			
		}
	}
	//close directory connection
    closedir($handle);
}

?>