var map;
var gameId;
var interval = 5000;
var timer;
function message(comandCode,comand){
	this.comandCode = comandCode;
	this.comand = comand;
}
function toPost(message){
	var sid = getCookie("PHPSESSID");
/*	if(sid){
		var str = "&SID="+sid;
	}else{
		var str = "";
	}
	return "code="+message.comandCode+"&cmd="+message.comand+str;
	*/
	if(sid){
		message.sid = sid;
	}
	return JSON.stringify(message);
}
function StartGame(){
	if(map != null){
		StopGame();
	}
	var msg = new message(0,"start");
	var jsonData = toPost(msg);
	var req = getXmlHttpRequest();
	req.onreadystatechange = function(){
		if (req.readyState != 4) return;
		// Завершение передачи... Сброс таймера и показ сообщения
		map = document.getElementById("map");
		while(map.hasChildNodes()) map.removeChild(map.lastChild);
		var game = JSON.parse(req.responseText);
		gameId = game["gameId"];
		var text = document.createTextNode(game["gameId"]);
		setCookie("gameId",gameId);
		setCookie("play",1);
		setCookie("PHPSESSID",game["SID"]);
		map.appendChild(text);
		game_update();
		gameUpdate.start();
		req = null;
	}
	req.open("POST", "../server/game_server.php", true);
	req.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
	req.setRequestHeader("Content-Length", jsonData.length);			
	req.send(jsonData);				
}
function setCookie (name, value, expires, path, domain, secure) {
      document.cookie = name + "=" + escape(value) +
        ((expires) ? "; expires=" + expires : "") +
        ((path) ? "; path=" + path : "") +
        ((domain) ? "; domain=" + domain : "") +
        ((secure) ? "; secure" : "");
}
function getCookie(name) {
	var cookie = " " + document.cookie;
	var search = " " + name + "=";
	var setStr = null;
	var offset = 0;
	var end = 0;
	if (cookie.length > 0) {
		offset = cookie.indexOf(search);
		if (offset != -1) {
			offset += search.length;
			end = cookie.indexOf(";", offset)
			if (end == -1) {
				end = cookie.length;
			}
			setStr = unescape(cookie.substring(offset, end));
		}
	}
	return(setStr);
}
function StopGame(map_id){
    if(!map_id) {
        if(!map){
            return;
        }else{
            var msg = new message(1,"");
        }
    } else{
        alert(map_id);
        var msg = new message(6,map_id);
    }
	gameUpdate.stop();

	gameId = null;
	gameUpdate.stop();
	setCookie("gameId",null);
	setCookie("play",null);
	var jsonData = toPost(msg);
	var req = getXmlHttpRequest();
	req.onreadystatechange = function(){
		if (req.readyState != 4) return;
		map = document.getElementById("map");
		while(map.hasChildNodes()) map.removeChild(map.lastChild);
		req = null;
	}
	req.open("POST", "../server/game_server.php", true);
	req.setRequestHeader("Content-Type", "text/plain");
	req.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
	req.send(jsonData);				
	
}
function gameUpdate(){
}
gameUpdate.start = function(){
	 if(!timer) timer = setInterval('game_update()',interval);
//	game_update();
}
gameUpdate.stop = function(){
	clearInterval(timer);
	timer = null;
}
function cellUpdate(id){
	gameUpdate.stop();
//    alert(id);
	var msg = new message(4,id);
	var req = getXmlHttpRequest();
	var jsonData = toPost(msg);
	req.open("POST", "../server/game_server.php", true);
	req.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
	req.setRequestHeader("Content-Length", jsonData.length);			
	req.send(jsonData);				
	req.onreadystatechange = function(){
		if (req.readyState != 4) return;
		var cell = JSON.parse(req.responseText);
		if(cell["status"] == "OK") {
			changeCell(cell["cell"]);
			req = null;
		}
		gameUpdate.start();
	}
}

function game_update(){
	if(!gameId){return;}
	var msg = new message(2,getCookie("PHPSESSID"));
	var jsonData = toPost(msg);
	var req = getXmlHttpRequest();
	req.onreadystatechange = function(){
		if (req.readyState != 4) return;
		var game = JSON.parse(req.responseText);
		drawMap(game);
		if(game["status"] != "FAIL")gameUpdate.start();
		req = null;
	}
	req.open("POST", "../server/game_server.php", true);
	req.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
	req.setRequestHeader("Content-Length", jsonData.length);			
	req.send(jsonData);				
}
function drawMap(newMap){
	map = document.getElementById("map");
	while(map.hasChildNodes()){ map.removeChild(map.lastChild);}
	for(var i in newMap["map"]){
		var cell = newMap["map"][i];
		var div = document.createElement("DIV");
		div.id = cell["cell_id"];
		div.className = setClass4cell(cell);
		div.style.cursor = "pointer";
		div.setAttribute("onclick","cellUpdate("+cell["cell_id"]+")");
		map.appendChild(div);
	}	
}

function changeCell(cell){
    var prevCell = document.getElementById(cell["cell_id"]);
	prevCell.className = setClass4cell(cell);
}
function setClass4cell(cell){
	var classList = new Array("empty_cell", "move_up", "strelka_dv_po_diag", "strelka_po_diag", "strelka_vo_vse_po_diag", "strelka_up_d_l_r", "strelka_ne_w_s", "strelka_l_r", "horses", "whirligig_2", "whirligig_3", "whirligig_4", "whirligig_5", "ice", "catcher", "gun", "fort", "aborigenka", "rom", "crocodille", "cannibal", "aerostat", "airplane", "storage_1", "storage_2", "storage_3", "storage_4", "storage_5", "sea", "ship", "closed");
	var a = classList[cell["type"]];
	var b = classList[cell["type"]]+"_"+cell["rotate"];
	var res = new Array(a, b, b, b, a, a, b, b, a, a, a, a, a, a, a, b, a, a, a, a, a, a, a, a, a, a, a, a, a, b, a);
	return res[cell["type"]];
}
function exitFromGame(){
	var jsonData = toPost(new message(3,getCookie("PHPSESSID")));
	setCookie("PHPSESSID",null);
	var req = getXmlHttpRequest();
	req.open("POST", "../server/game_server.php", false);
	req.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
	req.setRequestHeader("Content-Length", jsonData.length);			
	req.send(jsonData);				
}

function mapList(){
    var mapList;
//    mapList.get();
}
mapList.get = function(){
    var jsonData = toPost(new message(5,""));
    var req = getXmlHttpRequest();
    req.open("POST", "../server/game_server.php", true);
    req.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
    req.setRequestHeader("Content-Length", jsonData.length);
    req.send(jsonData);
    req.onreadystatechange = function(){
        if (req.readyState != 4) return;
        var gameList = JSON.parse(req.responseText);
        mapList.draw(gameList["gamelist"]);
        req = null;
    }
}
mapList.draw = function(games){
    var div = document.getElementById("map_list") ;
    while(div.hasChildNodes()){ div.removeChild(div.lastChild);}
    var ul = document.createElement("UL");
    div.appendChild(ul);
    for(var gameName in games){
        var li = document.createElement("LI");
        li.innerHTML  = gameName+" "+games[gameName]["game_status"]+" "+games[gameName]["player_number"] +
            "<a href='javascript:StopGame(\""+gameName+"\")'> x </a>";
        li.players = games[gameName]["players"];
        ul.appendChild(li);
    }
}
window.onload = function(){
    mapList.get();
}
/*
** Функция возвращат объект XMLHttpRequest
*/
function getXmlHttpRequest()
{
	if (window.XMLHttpRequest) 
	{
		try 
		{
			return new XMLHttpRequest();
		} 
		catch (e){}
	} 
	else if (window.ActiveXObject) 
	{
		try 
		{
			return new ActiveXObject('Msxml2.XMLHTTP');
		} catch (e){}
		try 
		{
			return new ActiveXObject('Microsoft.XMLHTTP');
		} 
		catch (e){}
	}
	return null;
}