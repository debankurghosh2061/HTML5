<!--!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Frameset//EN" "http://www.w3.org/TR/html4/frameset.dtd">-->
<html>

<head>
	<title>Test Video</title>
	<meta http-equiv="cache-control" content="max-age=0" />
	<meta http-equiv="cache-control" content="no-cache" />
	<meta http-equiv="expires" content="0" />
	<meta http-equiv="expires" content="Tue, 01 Jan 1980 1:00:00 GMT" />
	<meta http-equiv="pragma" content="no-cache" />
	<script type="text/javascript" src="client_fnc.js"></script>
	<script src="//code.jquery.com/jquery-1.11.2.min.js"></script>
</head> 	 	

<body onload="init()">
 <div id="test">
<video id="MyEdit" video width="500" height="500" controls="controls" src=""></video>
</div>

<!--<a id="Display" onclick ="myFunction()" ></a>-->
<!--<p id="demo" onclick="myFunction()">Click me to change my HTML content (innerHTML).</p>-->
<!--<video id="MyEdit" video width="500" height="500" controls="controls" src=""></video>-->
 
 <?php 
 include "Server_fnc.php";
 $dance= get_server_load();
 echo $dance;

 ?>

  <!--<script>
   $("#test").hide();
   function myFunction (){
   $("#test").show();
   }
	function reqListener () {
      console.log(this.responseText);
    }

    var oReq = new XMLHttpRequest(); //New request object
    oReq.onload = function() {
        //This is where you handle what to do with the response.
        //The actual data is found on this.responseText
      // alert(this.responseText); //Will alert: 42
		console.log(this.responseText);
		//console.log($sload[0]);
		document.getElementById("MyEdit").src = this.responseText;
		document.getElementById("Display").innerHTML = this.responseText;
    };
    oReq.open("get", "Server_fnc.php", true);
    //                               ^ Don't block the rest of the execution.
    //                                 Don't wait until the request finishes to 
    //                                 continue.
    oReq.send();

    </script>-->
	
<p>
<!-- <video width="500" height="500" controls="controls" src="mixtape.mkv"></video> -->
</body>
</html>
