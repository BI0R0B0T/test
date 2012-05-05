var gameId;
var interval = 10000;
var timer;
var intervalMapList = null;
var diametr = "20px";

function message(comandCode,comand){
	this.comandCode = comandCode;
	this.comand = comand;
}
function toPost(message){
	var sid = getCookie("PHPSESSID");
	if(sid){ message.sid = sid; }
	return JSON.stringify(message);
}
function StartGame(){
	if(gameId != null){ StopGame(); }
	var msg = new message(0,"start");
	var jsonData = toPost(msg);
	var req = getXmlHttpRequest();
	req.onreadystatechange = function(){
		if (req.readyState != 4) return;
		map = document.getElementById("map");
		while(map.hasChildNodes()) map.removeChild(map.lastChild);
		var game = JSON.parse(req.responseText);
		gameId = game["gameId"];
//		var text = document.createTextNode(game["gameId"]);
//		map.appendChild(text);
//		setCookie("gameId",gameId);
//		setCookie("play",1);
		setCookie("PHPSESSID",game["SID"]);
		game_update();
		gameUpdate.start();
		//Подчищаем память
		req = null;
		msg = null;
		jsonData = null;
		game = null;
	}
	req.open("POST", "../server/game_server.php", true);
	req.setRequestHeader("Content-Type", "text/plain");
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
        if(!map){ return;}
		else{ var msg = new message(1,"");}
    } else{
 //       alert(map_id);
        var msg = new message(6,map_id);
    }
	gameUpdate.stop();

//	setCookie("gameId",null);
//	setCookie("play",null);
	var jsonData = toPost(msg);
	var req = getXmlHttpRequest();
	req.onreadystatechange = function(){
		if (req.readyState != 4) return;
		map = document.getElementById("map");
		while(map.hasChildNodes()) map.removeChild(map.lastChild);
		req = null;
		msg = null;
		jsonData = null;
		map = null;
		map_id = null;	
		gameId = null;
		mapList.updateStop();
		mapList.get();
		mapList.updateStart();
	}
	req.open("POST", "../server/game_server.php", true);
	req.setRequestHeader("Content-Type", "text/plain");
	req.setRequestHeader("Content-Type", "text/plain");
	req.send(jsonData);				
	
}
function gameUpdate(){}
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
	req.setRequestHeader("Content-Type", "text/plain");
	req.setRequestHeader("Content-Length", jsonData.length);			
	req.send(jsonData);				
	req.onreadystatechange = function(){
		if (req.readyState != 4) return;
		var cell = JSON.parse(req.responseText);
		if(cell["status"] == "OK") { changeCell(cell["cell"]); }
		gameUpdate.start();
		id = null;
		msg = null;
		req = null;
		jsonData = null;
		cell = null;
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
		if(game["status"] != "FAIL"){gameUpdate.start();}
		msg = null;
		jsonData = null;
		req = null;
		game = null;		
	}
	req.open("POST", "../server/game_server.php", true);
	req.setRequestHeader("Content-Type", "text/plain");
	req.setRequestHeader("Content-Length", jsonData.length);			
	req.send(jsonData);				
}
function game_open(Id){
    gameId = Id;
    if(!gameId){return;}
    var str = ""+gameId;
    var msg = new message(8,str);
    var jsonData = toPost(msg);
    var req = getXmlHttpRequest();
    req.onreadystatechange = function(){
        if (req.readyState != 4) return;
        var game = JSON.parse(req.responseText);
        drawMap(game);
        if(game["status"] != "FAIL")gameUpdate.start();
  		req = null;
		msg = null;
		jsonData = null;
		game = null;
		Id = null;
		str = null;
		game_update();
		gameUpdate.start();
   }
    req.open("POST", "../server/game_server.php", true);
    req.setRequestHeader("Content-Type", "text/plain");
    req.setRequestHeader("Content-Length", jsonData.length);
    req.send(jsonData);
    gameUpdate.stop();
}
function drawMap(newMap){
	map = document.getElementById("map");
	while(map.hasChildNodes()){ map.removeChild(map.lastChild);}
	for(var i in newMap["map"]){
		var cell = newMap["map"][i];
		var div = document.createElement("DIV");
		var id = cell["cell_id"];
		div.id = id;
		var className = setClass4cell(cell);
		div.className = className
		div.marck = new Array(9999).join('leak');
		if(className == "closed"){
			div.style.cursor = "pointer";
			div.setAttribute("onclick","cellUpdate("+id+")");
		}else{
			var possib = cell["possible_next_cells"];
			div.possible_next_cells = possib;
			possib = null;
		}
		map.appendChild(div);
		cell = null;
//		div.possible_next_cells = null;
		div = null;
		id = null;
	}
	for(var i in newMap["units"]){
		var unit = newMap["units"][i];
		var unitDiv = document.createElement("DIV");
		unitDiv.id = "unit_"+unit.id;
		unitDiv.className = "unit";
		unitDiv.die = unit.die;
		unitDiv.have_coins = unit.have_coins;
		unitDiv.cell_part = unit.cell_part;
		unitDiv.can_move = unit.can_move;
		unitDiv.possible_move = unit.possible_move;
        unitDiv.style.width = diametr;
        unitDiv.style.height = diametr;
        unitDiv.setAttribute("draggable",true);
        //Событие вызываемое при переносе юнита
        addEvent(unitDiv, 'dragstart', function (e) {
            e.dataTransfer.effectAllowed = 'copy'; // only dropEffect='copy' will be dropable
            e.dataTransfer.setData('Text', this.id); // required otherwise doesn't work
 //           e.dataTransfer.setData('Text', this.position); // required otherwise doesn't work
        });
        var parentDiv = document.getElementById(unit.position);
		parentDiv.appendChild(unitDiv);
        for(var cellId in parentDiv["possible_next_cells"]){
            var div = document.getElementById(parentDiv["possible_next_cells"][cellId]);
            // если юнит над клеткой
            addEvent(div, 'dragover', function (e) {
                if (e.preventDefault) e.preventDefault(); // allows us to drop
                this.style.border = "1px solid red";
                this.style.width = "48px";
                this.style.height = "48px";
                e.dataTransfer.dropEffect = 'copy';
                return false;
            });
            // если юнит ушел с клетки
            addEvent(div,'dragleave', function (){
                this.style.border = "";
                this.style.width = "50px";
                this.style.height = "50px";

            });
            // если юнита перенесли сюда
            addEvent(div, 'drop', function (e) {
                if (e.stopPropagation) e.stopPropagation(); // stops the browser from redirecting...why???

 //               var_dump(e.dataTransfer) ;
 //               var el = document.getElementById(e.dataTransfer.getData('Text'));
 //               var text = document.createTextNode("Тыц");
 //               el.appendChild(text);
                return false;
            });
        }
 //       var text = document.createTextNode(unit.position);
 //       unitDiv.appendChild(text);
		unit = null;
		unitDiv = null;
		parentDiv = null;
	}
	i = null;
 	newMap = null;
	map = null;
}
function var_dump(getObject){
    var str = "";
    for(var i in getObject){
       str += i+" = "+getObject[i]+"\n";
    }
    alert(str);
    return;
}
function decoratePossibleMove(id){
	var cell = document.getElementById(id);
	for(var cellId in cell["possible_next_cells"]){
		var div = document.getElementById(cell["possible_next_cells"][cellId]);
		div.style.border = "1px solid red";
		div.style.width = "48px";
		div.style.height = "48px";
	}
	cellId = null;
}
function undecorate(id){
	var cell = document.getElementById(id);
	for(var cellId in cell["possible_next_cells"]){
		var div = document.getElementById(cell["possible_next_cells"][cellId]);
		div.style.border = "";
		div.style.width = "50px";
		div.style.height = "50px";
	}
	cellId = null;
}
function changeCell(cell){
    var prevCell = document.getElementById(cell["cell_id"]);
	prevCell.className = setClass4cell(cell);
	prevCell.possible_next_cells = cell["possible_next_cells"];
	prevCell.setAttribute("onmouseover","decoratePossibleMove("+cell["cell_id"]+")");
	prevCell.setAttribute("onmouseout","undecorate("+cell["cell_id"]+")");
	prevCell = null;
	cell = null;
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
	req.setRequestHeader("Content-Type", "text/plain");
	req.setRequestHeader("Content-Length", jsonData.length);			
	req.send(jsonData);	
	jsonData = null;
	req = null;			
}

function mapList(){
//    var mapList;
//    mapList.get();
}
mapList.get = function(){
    var jsonData = toPost(new message(5,""));
    var reqML = getXmlHttpRequest();
    reqML.open("POST", "../server/game_server.php", true);
    reqML.setRequestHeader("Content-Type", "text/plain");
    reqML.setRequestHeader("Content-Length", jsonData.length);
    reqML.send(jsonData);
    reqML.onreadystatechange = function(){
        if (reqML.readyState != 4) return;
        var gameList = JSON.parse(reqML.responseText);
        mapList.draw(gameList["gamelist"]);
		mapList.updateStart();
        reqML = null;
        gameList = null;
        jsonData = null;
    }
	mapList.updateStop();
}
mapList.draw = function(games){
    var div = document.getElementById("map_list") ;
    while(div.hasChildNodes()){ div.removeChild(div.lastChild);}
    var ul = document.createElement("UL");
    div.appendChild(ul);
    for(var gameName in games){
        var li = document.createElement("LI");
        li.innerHTML  = "<a href=\"javascript:game_open("+gameName+")\">"+gameName+"</a> "+
			games[gameName]["game_status"]+" "+games[gameName]["player_number"] +
            "<a href='javascript:StopGame(\""+gameName+"\")'> x </a>";
        li.players = games[gameName]["players"];
        ul.appendChild(li);
		li = null;
    }
    gameName = null;
    ul = null;
    div = null;
	games = null;
}
mapList.updateStart = function(){
    if(!intervalMapList){ intervalMapList = setInterval("mapList.get()",10000)}
}
mapList.updateStop = function(){
    clearInterval(intervalMapList);
    intervalMapList = null;
}

window.onload = function(){
    mapList.get();
    mapList.updateStart();
}
/*
** Функция возвращат объект XMLHttpRequest
*/
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
function addEvent(elem, evType, fn) {
    if (elem.addEventListener) {
        elem.addEventListener(evType, fn, false);
    }
    else if (elem.attachEvent) {
        elem.attachEvent('on' + evType, fn)
    }
    else {
        elem['on' + evType] = fn
    }
}
/*
addEvent(bin, 'drop', function (e) {
    if (e.stopPropagation) e.stopPropagation(); // stops the browser from redirecting...why???

    var el = document.getElementById(e.dataTransfer.getData('Text'));

    el.parentNode.removeChild(el);

    // stupid nom text + fade effect
    bin.className = '';
    yum.innerHTML = eat[parseInt(Math.random() * eat.length)];

    var y = yum.cloneNode(true);
    bin.appendChild(y);

    setTimeout(function () {
        var t = setInterval(function () {
            if (y.style.opacity <= 0) {
                if (msie) { // don't bother with the animation
                    y.style.display = 'none';
                }
                clearInterval(t);
            } else {
                y.style.opacity -= 0.1;
            }
        }, 50);
    }, 250);

    return false;
}); */