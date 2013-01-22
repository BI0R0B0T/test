window.onload = function(){
	VK_auth();	
}

function VK_auth(){
	var url = document.location.href;
	var write = 0;
	var data = 0;
	var parsed = ["","",""];
	var iterator = 0;
	var str = "";
	for(var i in url){
		if(write == 1){
			str += url[i];
			if("=" == url[i]){
				data = 1;
				continue;
			}
			if("&" == url[i]){
				data = 0;
				iterator++;
				continue;
			}
			if(1 == data){
				parsed[iterator]+=url[i];
			}
		}
		if("#" == url[i]) { write = 1;}
	}
	if(write == 1){
		location.href = "http://kodomo.fbb.msu.ru/~dolgov/piraty/vk_auth.php?"+str;
		var reqML = getXmlHttpRequest();
		var access_token = parsed[0];
		var expires_in = parsed[1];
		var user_id = parsed[2];
		var url = "https://api.vk.com/method/users.get?uid="+user_id+"&fields=first_name,last_name,photo&access_token="+access_token;
//		alert(url);
	    reqML.open("GET", url, true);
//	    reqML.setRequestHeader("Connection", "keep-alive");
//	    reqML.setRequestHeader("Host", "api.vk.com");
	    reqML.setRequestHeader("Type", "text/plain");
//	    reqML.setRequestHeader("Content-Length", str.length);
		if (window.XMLHttpRequest){
			reqML.send(null);
		}else{
			reqML.send();
		}
	    
	    reqML.onreadystatechange = function(){
	        if (reqML.readyState != 4) return;
			alert(reqML.responseText);
//	        var user_info = JSON.parse(reqML.responseText);
//			alert(user_info);
	    }		
	}	
}

function getXmlHttpRequest() {
	if (window.XMLHttpRequest) {
		try {
			return new XMLHttpRequest();
		} catch (e){}
	} 
	else if (window.ActiveXObject) {
		try {
			return new ActiveXObject('Msxml2.XMLHTTP');
		} catch (e){}
		try{
			return new ActiveXObject('Microsoft.XMLHTTP');
		} catch (e){}
	}
	return null;
}