var game;
//var sid;
var gameUpdate = new gameUpdater();
var globals = {
	gameStatus: 0,				// 0-не играем, 1-наш ход, 2-не наш ход, 3 - ожидаем подключения остальных
	type: 1,					// Для заглушки
	gameStatusInterval: 2000, 	// Время через которое обновляется статус игры
	intervalGameStatus: null,	// Хранится ссылка на интервал обновления статуса игры
	gameUpdateInterval: 10000,	// Время через которое обновляется игра
	lastMoveId: 		0, 		// id последней записи в ходе игры
	gameId: 			null,	// id текущей игры
	mapListUpdInterval: 30000,	// Время через которое обновляется список игр
	intervalMapList:	null,	// Хранится ссылка на интервал обновления списка игр
	diametr:			"20px"	// Диаметр юнита
}
/**
* функция связи с сервером
* @param string текст запроса
* @return string текст ответа сервера
*/
function serverConnect(message){
    this.jsonData = toPost(message);	//текст запроса
    this.req = null;					//указатель на объект XmlHttpRequest
	this.assinc = true;					//режим запроса (синхронный/ассинхронный)
    this.send = function(assinc){
        var req = getXmlHttpRequest();
        req.open("POST", "../server/game_server.php", this.assinc);
        req.setRequestHeader("Content-Type", "text/plain");
        req.setRequestHeader("Content-Length", this.jsonData.length);
        req.send(this.jsonData);
        if(this.assinc){
            req.onreadystatechange = function(){
                if( req.readyState != 4) return;
                return   JSON.parse(req.responseText);
            }
        }else{
            return   JSON.parse(req.responseText);
        }
    }
}

function games(){
    this.gameId = null;
    this.start = function (option){
        if(this.gameId != null){ this.stop(); }
        var msg = {
			type:option,
			desc:""
        };
        var conn = new serverConnect(new message(0,msg));
        var g = conn.send(true);
        this.gameId = g["gameId"];
//      sid = g["SID"];
//      this.checkStatus();
//		globals.gameStatus = 3;
        document.location.href = "game.php?g="+this.gameId;
//      this.update();
//      gameUpdate.start();
        g = null;
        conn = null;
    }
    this.stop = function(map_id){
        if(!map_id) {
            if(!this.gameId){ return;}
            else{ var msg = new message(1,"");}
        } else{
            var msg = new message(6,map_id);
        }
        gameUpdate.stop();
        var conn = new serverConnect(msg);
        conn.send();
        map = document.getElementById("map");
 //       while(map.hasChildNodes()) map.removeChild(map.lastChild);
        map = null;
        map_id = null;
        this.gameId = null;
//        mapList.updateStop();
//        mapList.get();
//        mapList.updateStart();
    }
    this.update = function(){
        if(!this.gameId){return;}
//		if(globals.gameStatus != 1){return;}
//		var msg = new message(2,getCookie("PHPSESSID"));
        var msg = new message(13,globals.lastMoveId);
        var conn = new serverConnect(msg);
        var g = conn.send();
        updateMap(g);
        if(g["status"] != "FAIL"){gameUpdate.start();}
        msg = null;
        g = null;
    }
    this.open = function(Id){
        this.gameId = Id;
        if(!this.gameId){return;}
        var str = ""+this.gameId;
        var msg = new message(8,str);
        var conn = new serverConnect(msg);
        var g = conn.send();
        if(g["status"] != "FAIL"){
			drawMap(g);
			gameUpdate.start();
			msg = null;
	        g = null;
	        str = null;

		}else{
			alert(g["reason"]);
			document.location.href = "game.php";
			return;
		}
    }
	this.newGame = function(){
		document.getElementById("create_game").style.display = "block";
		document.getElementById("select_option").style.display = "block";
		document.getElementById("player_info").style.display = "none";
		mapList.updateStop();
	}
	this.cancel = function(){
		document.getElementById("create_game").style.display = "none";
		document.getElementById("select_option").style.display = "none";
		document.getElementById("wait_connection").style.display = "none";
		document.getElementById("player_info").style.display = "block";
//		var elems = document.getElementsByClass("pop_up");
//		for (var i = 0; i < elems.length; i++) {
//			elems[i].style.display = "none";
//		}
		clearInterval(globals.intervalGameStatus);
    	globals.intervalGameStatus = null;
    	mapList.updateStart();
	}
	this.checkStatus = function(){
		document.getElementById("create_game").style.display = "block";
		document.getElementById("select_option").style.display = "none";
		document.getElementById("player_info").style.display = "none";
		document.getElementById("wait_connection").style.display = "block";
        var conn = new serverConnect(new message(11,this.gameId));
        var status = conn.send(true);
        var_dump(status);
        if(status["gameId"] != undefined){
			document.location.href = "game.php?g="+status["gameId"];
        }
        if(status["game_status"] == 2){
        	if(!globals.intervalGameStatus){ 
//        		window.setTimeout("game.checkStatus()",globals.gameStatusInterval);
        		globals.intervalGameStatus = setInterval("game.checkStatus()",globals.gameStatusInterval)
        	}
			globals.gameStatus = 3;
			mapList.updateStop();
        }else{
			clearInterval(globals.intervalGameStatus);
    		globals.intervalGameStatus = null;
    		document.location.href = "game.php?g="+this.gameId;
        }
	}
}  
function message(comandCode,comand){
	this.comandCode = comandCode;
	this.comand = comand;
}
function toPost(message){
//   if(!getCookie("SID")) {message.sid = sid;}
//	var sid = getCookie("PHPSESSID");
//	if(sid){ message.sid = sid; }
	return JSON.stringify(message);
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

function gameUpdater(){
    this.timer = null;
    this.start = function(){
        if(!this.timer) this.timer = setInterval('game.update()',globals.gameUpdateInterval);
    }
    this.stop = function(){
        clearInterval(this.timer);
        this.timer = null;
    }
}
/*
gameUpdate.start = function(){
	 if(!timer) timer = setInterval('game.update()',interval);
//	game.update();
}
gameUpdate.stop = function(){
	clearInterval(timer);
	timer = null;
}     */
function cells(cell){
    this.id = cell["cell_id"];
    this.possible_next_cells = cell["possible_next_cells"];
    this.type = cell["type"];
    this.rotate = cell["rotate"];
    this.update = function(id){
        gameUpdate.stop();
        var conn = new serverConnect(new message(4,id));
        var cl = conn.send();
        if(cl["status"] == "OK") { this.changeCell(cl["cell"]); }
        gameUpdate.start();
		cl = null;
    }
    this.changeCell = function(){
        var prevCell = document.getElementById(this.id);
        prevCell.className = this.setClass();
        prevCell.possible_next_cells = this.possible_next_cells;
        prevCell.setAttribute("onmouseover","decoratePossibleMove("+this.id+")");
        prevCell.setAttribute("onmouseout","undecorate("+this.id+")");
        prevCell = null;
    }
    this.setClass = function(){
        var classList = new Array("empty_cell", "move_up", "strelka_dv_po_diag", "strelka_po_diag", "strelka_vo_vse_po_diag", "strelka_up_d_l_r", "strelka_ne_w_s", "strelka_l_r", "horses", "whirligig_2", "whirligig_3", "whirligig_4", "whirligig_5", "ice", "catcher", "gun", "fort", "aborigenka", "rom", "crocodille", "cannibal", "aerostat", "airplane", "storage_1", "storage_2", "storage_3", "storage_4", "storage_5", "sea", "ship", "closed");
        var a = classList[this.type];
        var b = classList[this.type]+"_"+this.rotate;
        var res = new Array(a, b, b, b, a, a, b, b, a, a, a, a, a, a, a, b, a, a, a, a, a, a, a, a, a, a, a, a, a, b, a);
        return res[this.type];
    }

}


function drawMap(newMap){
	map = document.getElementById("map");
	while(map.hasChildNodes()){ map.removeChild(map.lastChild);}
	//Рисуем ячейки карты
	for(var i in newMap["map"]){
		var cell = new cells(newMap["map"][i]) ;
		var div = document.createElement("DIV");
		div.id = cell.id;
		div.id = cell.id;
        div.className = cell.setClass();
		div.marck = new Array(9999).join('leak');
		if(div.className == "closed"){
//			div.style.cursor = "pointer";
//			div.setAttribute("onclick","cells.update("+id+")");
		}else{
			div.possible_next_cells = cell.possible_next_cells;
		}
		map.appendChild(div);
		cell = null;
		div = null;
		id = null;
	}
	//рисуем юниты
	for(var i in newMap["units"]){
		drawUnit(newMap["units"][i]);
	}
	globals.lastMoveId = newMap["last_id"];
	i = null;
 	newMap = null;
	map = null;
}
/**
* Рисуем юнит на поле
*/
function drawUnit(unit){
		var unitDiv = document.createElement("DIV");
		unitDiv.id = "unit_"+unit.id;
		unitDiv.className = "unit";
		unitDiv.die = unit.die;
		unitDiv.have_coins = unit.have_coins;
		unitDiv.cell_part = unit.cell_part;
		unitDiv.can_move = unit.can_move;
		unitDiv.possible_move = unit.possible_move;
		unitDiv.style.width = globals.diametr;
		unitDiv.style.height = globals.diametr;
		unitDiv.style.background = "#"+unit.color.toString(16);
		unitDiv.setAttribute("draggable",true);
		//Событие вызываемое при переносе юнита
		addEvent(unitDiv, 'dragstart', function (e) {
			e.dataTransfer.effectAllowed = 'copy'; // only dropEffect='copy' will be dropable
			e.dataTransfer.setData('Text', this.id); // required otherwise doesn't work
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
				this.style.border = "";
				this.style.width = "50px";
				this.style.height = "50px";
				if (e.stopPropagation) e.stopPropagation();
				unit_move(e.dataTransfer.getData('Text'),this.id);
                return false;
            });
        }
		unit = null;
		unitDiv = null;
		parentDiv = null;
}
function updateMap(serverResponce){
	for(var i in serverResponce["map"]){
		var cell = new cells(serverResponce["map"][i]);
		cell.changeCell();
	}
	for(var i in serverResponce["units"]){
		while(exUnit = document.getElementById("unit_"+serverResponce["units"][i]["id"])){
			exUnit.parentNode.removeChild(exUnit);
		}
		drawUnit(serverResponce["units"][i]);
	}
	i = null;
}
function unit_move(unit_id, cell_id){
	//Проверяем произошел ли перенос юнита... а то он может остался на той-же клетке
	var unit = document.getElementById(unit_id);
	if(cell_id == unit.parentNode.id){ return;}
	var a = new Array(unit_id, cell_id);
    var conn = new serverConnect(new message(9,a));
    var cl = conn.send(true);
	if(cl["status"] == "FAIL"){ return; }
	updateMap(cl);
}
function displayInDebug(text){
	var div = document.getElementById("debug");
	while(div.hasChildNodes()){ div.removeChild(div.lastChild);}
	var text = document.createTextNode(div);
	div.appendChild(text)
	var str = "";
    for(var i in text){
       str += i+" = "+text[i]+"<br>\n";
    }

	var text = document.createTextNode(str);
	div.appendChild(text);
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
//выход из игры. Удаляются вся информация об играх и сбрасываются все временные переменные в null
function exitFromGame(){
    var conn = new serverConnect(new message(3,""));
    conn.send(false);
	window.location = "http://kodomo.fbb.msu.ru/~dolgov/piraty/index.php";
	conn = null;
}
//Заглушка чтобы работало
function mapList(){}
mapList.get = function(){
    var conn = new serverConnect(new message(5,""));
    var gameList = conn.send(true);
    if(null == gameList["gamelist"]){return ;}
    mapList.draw(gameList["gamelist"]);
	mapList.updateStart();
	gameList = null;
	conn = null;
}
mapList.draw = function(games){
    if(globals.type == 1){
        var mapListId = "map_list_big";
    }else{
//		alert(global.type);
//		var mapListId = "map_list";
        return ;
    }
    var div = document.getElementById(mapListId) ;
    while(div.hasChildNodes()){ div.removeChild(div.lastChild);}
    var ul = document.createElement("UL");
    div.appendChild(ul);
    for(var gameName in games){
        var li = document.createElement("LI");
		if(globals.type == 1){
	        li.innerHTML  = gameName+"<a href=game.php?g="+gameName+">play</a> <a href= #>view</a>"+
			"<a href='javascript:game.stop(\""+gameName+"\")'> x </a>";
	    }else{
	        li.innerHTML  = "<a href=\"javascript:game.open("+gameName+")\">"+gameName+"</a> "+
				games[gameName]["game_status"]+" "+games[gameName]["player_number"] +
          		"<a href='javascript:game.stop(\""+gameName+"\")'> x </a>";
	    }
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
    if(!globals.intervalMapList){ 
		globals.intervalMapList = setInterval("mapList.get()",globals.mapListUpdInterval)
	}
}
mapList.updateStop = function(){
    clearInterval(globals.intervalMapList);
    globals.intervalMapList = null;
}

function getPlayerInfo(id){
	var conn = new serverConnect(new message(10,id));
	return conn.send(true);
}

function drawPlayerInfo(playerInfo){
//	var_dump(playerInfo.player);
	playerInfo = playerInfo.player;
	var div = document.getElementById("player_info");
	while(div.hasChildNodes()){ div.removeChild(div.lastChild);}
	var textDiv = document.createElement("DIV");
	textDiv.id = "player_info_txt";
	div.appendChild(textDiv)
	textDiv.style.background ="url(\""+playerInfo["photo_rec"]+"\") no-repeat";
	var p = document.createElement("P"); 
	textDiv.appendChild(p);
	var text = document.createElement("A");
	text.href = "javascript:exitFromGame()";
	text.title = "exit";
	text.appendChild(document.createTextNode("exit")) ;
	textDiv.appendChild(text)
	textDiv = null;
	textDiv = p;
	var text = document.createTextNode(playerInfo["first_name"]+" ");
	textDiv.appendChild(text)
	var text = document.createTextNode(playerInfo["last_name"]);
	textDiv.appendChild(text)
	var text = document.createElement("DIV");
	text.id = "player_info_bg";
	div.appendChild(text)
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
