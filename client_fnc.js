// You can change the following variables using the ServerConfig.txt file.

var numBufferrates = 20;// Buffer rate window size, used for switching
var timeout = 1000; // Sampling rate in milliseconds for buffer duration in seconds
var dangerbufferzone = 1; //in seconds, Minimum buffer duration below which switching to lower bitrate is enforced.
var maxbufferlength = 120; // in seconds, Maximum buffer duration above which switching to higher bitrate is enforced

//Do not edit these variables
var xmlHttp = createXmlHttpRequestObject(); // The XML object used to communicate with the server
var currentnumBufferrates = 0;// initialise the current number of buffer rates in the buffer rate array
var bufferindex = 0; // the current buffer index of the buffer rate array
var bufferrateArray = new Array(); // the buffer rate array
var lengthnow = 0; // the difference between the buffer length and the play length

// Initialises the the configuration variables and starts the smooth criteria method
function init(){
	
	var myVideo = "";
	//gets all videos from the current page and sets the events for each player
	myVideoArr = document.getElementsByTagName('video');
	for (var i = 0;i < myVideoArr.length;i++){
		myVideo = document.getElementsByTagName('video')[i];
		myVideo.addEventListener('play',function(){playVideo();}, false);// listens for the video paused and 
		checkFile(myVideo); //checks to see if the video is ready
	}
	getConfig(); // call the get configuration function
	
}

// Gets the Configuration values from the serverConfig.txt file on the server side
function getConfig(){

	// if the xmlHttp transport is not busy then get the configuration
	if(xmlHttp.readyState == 4 || xmlHttp.readyState == 0){
		document.getElementById("underInput").innerHTML = "sent";
		xmlHttp.open("GET", "AJAXcomm.php?getVars=" + "true", true);
		xmlHttp.onreadystatechange = handleConfigResponse;
		xmlHttp.send(null);
		
	}else{ // if the xmlhttp connection is busy then try again 
		setTimeout('getConfig()',100);
	}
}

// the event handler for when the player is played
function playVideo(){
	
	//runs the smooth criteria
	smoothcriteria(event.srcElement);;
	
}

// checks the files on the server side are present, if they are not then it creates them
function checkFile(myVideo){
	
	 // communicate with the server to get the configuration variables
	if(xmlHttp.readyState == 4 || xmlHttp.readyState == 0){
		
		xmlHttp.open("GET", "AJAXcomm.php?runcheck=" + myVideo.getAttribute("src"), true);
		xmlHttp.onreadystatechange = handleCheckResponse;
		xmlHttp.send(null);
		
	}else{ // if the xmlhttp connection is busy then try again 
		setTimeout(function() {checkFile(myVideo);},100);
	}
	
}

// The smooth criteria method loops round and detects the buffer rate 
// and length from the buffer length in order to switch up or down the video appropriately
function smoothcriteria(_video) {

	//inititalise the video and video vairables and store them
	var buffer = _video.buffered;
	var duration = _video.duration;
	var current = _video.currentTime;
	var lengthprev = lengthnow;
	
	// calculate the length of the buffer and figure out the buffer rate
	lengthnow = buffer.end(0) - buffer.start(0);
	bufferrate = (lengthnow - lengthprev)/(timeout/1000);
	
	// if the buffer rate index is less than the number of buffer rates then just store the buffer rate and increase the buffer rate index
	if (numBufferrates - 1 > bufferindex){
		bufferrateArray[bufferindex] = bufferrate;
		bufferindex++;
	}else{// else reset the buffer index to the start and continue storing and increase the buffer index
		bufferindex = 0;
		bufferrateArray[bufferindex] = bufferrate;
		bufferindex++;
	}
	
	// if the current number of buffer rates is less than the number of buffer rates the array can store then increase the current number of buffer rates
	if (currentnumBufferrates < numBufferrates){
		currentnumBufferrates++;
	}
	
	// calculate the average buffer rate from the current buffer rates in the array
	var total = 0;
	for (var i=0;i<numBufferrates;i++){
		if(typeof(bufferrateArray[i])!='undefined') {
			total = total + bufferrateArray[i];
			averageval = total/currentnumBufferrates;
		}
	}
	
	// calculate the time difference between the play time and the buffer length
	var timediff = buffer.end(0) - current;
	
	// if the time difference is less than the danger buffer zone and
	if (timediff < dangerbufferzone){
		// the average buffer rate is less than one
		if (averageval < 1){
			if (_video.paused === false){
				switchVidDown(_video); // switch the video down
			}
		}
	}
	
	// if the time difference is greater than the maximum buffer length and
	if (timediff > maxbufferlength){
		// the average buffer rate is greater than one
		if (averageval > 1){
			if (_video.paused === false){
				switchVidUp(_video);// switch video up
			}
		}
	}
	
	// if the video is paused stop the criteria
	if (_video.paused === false){
		// repeat the smoothcriteria function at a sampling interval equal to timeout
		setTimeout(function() {smoothcriteria(_video);}, timeout);
	}
	
}

// The XML request object is initialised depending upon the browser
function createXmlHttpRequestObject(){
	var xmlHttp;
	// if the browser is internet explorer
	if(window.ActiveXObject){
		// try and set the variable to an activeX Object
		try{
			xmlHttp = new ActiveXObject("Microsoft.XMLHTTP");
		}catch(e){
			xmlHttp = false;
		}
	}else{ // for all other browsers set the object to a new XMLHttpRequest
		try{
			xmlHttp = new XMLHttpRequest();
		}catch(e){
			xmlHttp = false;
		}
	}
	// if the object is false
	if(!xmlHttp){
		alert("can't create that object!"); // alert that it cannot be made
	}else{ // else return the variable
		return xmlHttp;
	}
}

// switch the video down to the next video
function switchVidDown(Video) {
	
	// if the xmlHttp object is not busy
	if(xmlHttp.readyState == 4 || xmlHttp.readyState == 0){
		xmlHttp.open("GET", "AJAXcomm.php?nextvideodown=" + Video.getAttribute("src"), true);
		xmlHttp.onreadystatechange = function(){handleServerResponse(Video);} // sets the method to handle the response
		xmlHttp.send(null);	
	}else{ // if the object is busy then repeat the method in 100ms
		setTimeout(function() {switchVidDown(Video);},100);
	}
	
}

// switch the video up to the next video
function switchVidUp(Video) {

	// if the xmlHttp object is not busy
	if(xmlHttp.readyState == 4 || xmlHttp.readyState == 0){
		// get the current video and request the next video up
		xmlHttp.open("GET", "AJAXcomm.php?nextvideoup=" + Video.getAttribute("src"), true);
		xmlHttp.onreadystatechange = function(){handleServerResponse(Video);};// sets the method to handle the response
		xmlHttp.send(null);
	}else{// if the object is busy then set then repeat the method in 100ms
		setTimeout(function() {switchVidUp(Video);},100);
	}
}

// general video switch method called when the server responds with the next video. It switches to the response video
function videoSwitch(_video, videoname) {
	
	// get the video object and the source 	
	var timeval = _video.currentTime;
	var sourceVid = document.getElementById("sourceVid");
	
	//set the source of the video and poster of the video
	_video.setAttribute("poster", "poster.jpg"); // this is what will be displayed during the switching
	_video.setAttribute("src", videoname);
	// load the video and call the set time method
	_video.load();
	setTime(timeval, _video);
	
}

// sets the time of the next video to the time of the current video
function setTime(time, _video) {
	
	// if the video is ready
	if (_video.readyState> 0){
		// try to set the time and then play the video
		try{
			_video.currentTime = time;
			_video.play();
		}catch(err){ // if there is an error try again
			setTimeout(function() {setTime(time, _video);}, 10);
		}
	}else{ // if it is not ready try again
		setTimeout(function() {setTime(time, _video);}, 10);
	}
}

// handle the response when the config is requested
function handleConfigResponse(){
	// if the xml object is ready
	if (xmlHttp.readyState == 4){
		// if the object does not return an error
		if (xmlHttp.status == 200){
			// create a new array to store the response
			var configArr = new Array();
			//get response
			xmlResponse = xmlHttp.responseXML;
			xmlDoc = xmlResponse.documentElement;
			message = xmlDoc.firstChild.data;
			// if the response of the system is errornofile
			if (message.trim() == "errornofile"){
				alert("No Server Configuration file found! Default values will be used"); // alert the user that there is no server configuration
			}else if (message != ""){ // else if there is not any empty message
				configArr = message.split(";"); // split the message and store in the array
				if (configArr.length > 0){ // if the length is greater than 0
					for (i = 0; i < configArr.length; i++){ // for each value
						var messageArr = configArr[i].split("="); // split the value into two other variables
						if (messageArr[0].trim() == "number_of_buffer_rates"){ // if the variable is number of buffer rates 
							numBufferrates = parseInt(messageArr[1], 10); // set the number of buffer rates
						}else if (messageArr[0].trim() == "time_between_buffer_rates"){// if the variable is time between buffer rates
							timeout = parseInt(messageArr[1], 10) * 1000;// set the time between buffer rates
						}else if (messageArr[0].trim() == "switch_up_time"){// if the variable is switch up time
							maxbufferlength = parseInt(messageArr[1], 10);// set the switch up time
						}else if (messageArr[0].trim() == "switch_down_time"){//if the variable is switch down time
							dangerbufferzone = parseInt(messageArr[1], 10);// set the switch down time
						}
					}
				}
			}
		}else{ // alert that there was an error
			alert('Something went wrong!');
		}
	}
}

// handle the server response
function handleCheckResponse(){
	// if the xml object is ready
	if (xmlHttp.readyState == 4){
		// if it does not return an error
		if (xmlHttp.status == 200){
			// get the response of the request
			message = xmlHttp.responseXML;
			// if the message is an error
			if (message.trim() == "updating"){
				alert("The Server is updating this video please try again later"); // alert the user that the server is still encoding these videos
			}
		}else{ // if the object returns an error
			alert('Something went wrong!'); // alert user
		}
	}
}

// handle the server response
function handleServerResponse(_video){
	// if the xml object is ready
	if (xmlHttp.readyState == 4){
		// if it does not return an error
		if (xmlHttp.status == 200){
			// get the response of the request
			xmlResponse = xmlHttp.responseXML;
			xmlDoc = xmlResponse.documentElement;
			message = xmlDoc.firstChild.data;
			// if the message is an error
			if (message == "error"){
				alert("Error"); // alert the user that theres an error
			}else if(message == "novideoup") {
				// insert method to deal with no video up
			}else if(message == "novideodown") {
				//insert method to deal with no video down
			}else{// else switch the video with next video name
				videoSwitch(_video, message); 
			}
		}else{ // if the object returns an error
			alert('Something went wrong!'); // alert user
		}
	}
}