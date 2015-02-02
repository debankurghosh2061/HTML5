<?php

// The communication script between the server side php and the client side javascript. 

$videodir = "videos/"; //video directory
$numberofvideos = 5; // the number of videos that are encoded

header('Content-Type: text/xml');
echo '<?xml version="1.0" encoding="UTF-8" standalone="yes" ?>';

echo '<response>'; // echo xml response tags
	
	//initialise variables
	$video = "";
	$getVar = "";
	$runserver = "";
	
	// check which variable javascript has requested
	if (isset($_GET['getVars'])){
		$getVar = $_GET['getVars'];
	}elseif (isset($_GET['nextvideoup'])){
		$video = $_GET['nextvideoup'];
	}else if (isset($_GET['nextvideodown'])){
		$video = $_GET['nextvideodown'];
	}else if (isset($_GET['runcheck'])){
		$video = $_GET['runcheck'];
	}
	
	// if the client requested the configuration details
	if (isset($_GET['getVars'])){
		if ($getVar == "true"){//if the variable is true
			$handleconfig = fopen("ServerConfig.txt", "r");//open up the serverconfig.txt file to read each line
			if ($handleconfig) {
				$configstr = "";
				//while there is still a nextline add the configuration to the config string
				while (($line = fgets($handleconfig)) !== false) {
					if ($line != ""){
						$configstr = $configstr . $line . ";";
					}
				}
				//output this string in the xml response
				echo $configstr;
				
			} else {// if the text file cant be opened then return nofile
				echo "errornofile";
			}
		}
		
	//if the client requested either the next video up or the next video down from the current video
	}else if (isset($_GET['nextvideoup']) || isset($_GET['nextvideodown'])) {
		//if the video directory includes the subfolder where the other bitrate videos are contained extract the name of the video
		if (strpos($video, $videodir) !== false) {
			$videoset = substr($video, strpos($video, '/') + 1, strpos($video, '/', strpos($video, '/') + 1) - strpos($video, '/') - 1);
		}else{//else the name is extracted in a different way
			$videoset = substr($video, 0, strpos($video, '.', strlen($video)- 5));
		}
		
		//opens the connection to the video subfolder in the video directory
		if ($handle = opendir($videodir . $videoset)) {
		
			//initialise the variables
			$folders = "";
			$mkv = "";
			$mkvfilesize;
			$mpg = "";
			$directorystring = "";
			$mkvfilesize = "";
			
			//for each file in the subfolder
			while (false !== ($entry = readdir($handle))) {
				// if the file is a directory store it in the folder string
				if (filetype($videodir . $videoset . "/" . $entry) == 'dir'){
					$folders = $folders . $entry . ";";
				} else {// else store the video and the file size in the the mkv variable string
					if (substr($entry, strpos($entry, '.', strlen($entry)- 5) + 1) == "mkv"){
						$mkv = $mkv . $entry . ";";
						$mkvfilesize = $mkvfilesize . filesize($videodir . $videoset . "/" . $entry) . ";";
					//another example of implementing for another video filetype
					} elseif (substr($entry, strpos($entry, '.', strlen($entry)- 5) + 1) == "mpg" || substr($entry, strpos($entry, '.', strlen($entry)- 5) + 1) == "mpeg"){
						$mpg = $mpg . $entry . ";";
					}else{ // all other filetypes are stored in this string
						$directorystring = $directorystring . $entry . ";";
					}
				}
			}
			// initialise the variables into arrays
			$nextvid = "";
			$mkv = explode(";", $mkv);
			$mkvfilesize = explode(";", $mkvfilesize);
			
			// for each value in the array find the current playing video file name by comparing the size of each file in the folder
			for ($i = 0; $i < count($mkv); $i++){
				if (filesize($videodir . $videoset . "/" . $mkv[$i]) == filesize($video)){
					$video = $mkv[$i];
					break;
				}
			}
			
			//if the mkv file size and mkv file arrays are the same size
			if (count($mkv) == count($mkvfilesize)){
				//for each file
				for ($i = 0; $i < count($mkv); $i++){
					//if you are searching for the next video up
					if (isset($_GET['nextvideoup'])){
						//if the current array value is not the same name as currently playing video
						if ($mkv[$i] != $video){
							// if the next video is not set yet then check if the file size is greater than the current video file size
							if ($nextvid == ""){
								if ($mkvfilesize[$i] > filesize($videodir . $videoset . "/" . $video)){
									$nextvid = $mkv[$i];
								}
							}else{//else check the file size of the next video is greater than the current video but less than the nextvid set
								if ($mkvfilesize[$i] > filesize($videodir . $videoset . "/" . $video) && $mkvfilesize[$i] < filesize($videodir . $videoset . "/" . $nextvid)){
									$nextvid = $mkv[$i];
								}
							}
						}
					// else if you want the next video down
					}else if (isset($_GET['nextvideodown'])){
						//if the current array value is not the same name as currently playing video
						if ($mkv[$i] != $video){
							// if the next video is not set yet then check if the file size is less than the current video file size
							if ($nextvid == ""){
								if ($mkvfilesize[$i] < filesize($videodir . $videoset . "/" . $video)){
									$nextvid = $mkv[$i];
								}
							}else{//else check the file size of the next video is less than the current video but greater than the nextvid set
								if ($mkvfilesize[$i] < filesize($videodir . $videoset . "/" . $video) && $mkvfilesize[$i] > filesize($videodir . $videoset . "/" . $nextvid)){
									$nextvid = $mkv[$i];
								}
							}
						}
					//else set the next video to nothing
					}else{
						$nextvid = "";
					}
				}
				// if next video is nothing and you want the next video down then return "novideodown"
				if ($nextvid == "" && isset($_GET['nextvideodown'])){
					echo "novideodown";
				// if next video is nothing and you want the next video up then return "novideoup"
				}else if ($nextvid == "" && isset($_GET['nextvideoup'])){
					echo "novideoup";
				//else return the next video name
				}else{
					echo $videodir . $videoset . "/" . $nextvid;
				}
			// if the folder cannot be opened then it does not exist and the server needs to be updated.
			}else{
				echo "error please update server";
			}
		}
	}else if (isset($_GET['runcheck'])) { // If the client calls run check then check whether the files exist else execute the php update server.
		if (strpos($video, $videodir) !== false) {// format the video name
			$videoset = substr($video, strpos($video, '/') + 1, strpos($video, '/', strpos($video, '/') + 1) - strpos($video, '/') - 1);
		}else{//else the name is extracted in a different way
			$videoset = substr($video, 0, strpos($video, '.', strlen($video)- 5));
		}
		
		//set the video count to zero before the check
		$videocount = 0;
		
		// if the video directory exists then open it
		if ($handlesub = opendir(substr($videodir, 0,strlen($videodir) - 1))) {
			// if the subfolder exists then open it
			if ($handleset = opendir($videodir . $videoset)) {
				while (false !== ($entry = readdir($handleset))) {
					//read each file in subfolder and count for each file that contains the video name
					if (filetype($videodir . $videoset . "/" . $entry) != 'dir'){
						if (strpos($entry, $videoset) !== false){
							$videocount++;
						}
					}
				}
				// if all the videos are there then do not run the server else run the server
				if ($videocount > $numberofvideos - 1){
					$runserver = false;
				}else{
					$runserver = true;
				}
			// if the subfolder is not there then run the server
			}else{
				$runserver = true;
			}
		// if the video folder is not there then run it
		}else{
			$runserver = true;
		}
		// run the server if runserver is set to true else return complete
		if ($runserver == true){
			pclose(popen("php Server_fnc.php", "r"));  // updates the server
			echo "updating";
		}else{
			echo "complete";
		}
	}
	
echo '</response>';

?>