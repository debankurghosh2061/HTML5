<?php 

//Set the time so the script does not time out. Large videos may take a while.
ini_set('max_execution_time', 10000); //300 seconds = 5 minutes

$videodir = "videos/"; //video folder directory including the "/" i.e. "videos/"

if ($handle = opendir('.')) { // open the current directory

	//initialise variables
	$folders = ""; 
	$mkv = "";
	$directorystring = "";
	
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