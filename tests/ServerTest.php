<?php
/**
 * @author Mikhail Dolgov <dolgov@bk.ru>
 */
require_once 'vendor/autoload.php';
require_once "piraty/server/class_server.php";
class ServerTest  extends PHPUnit_Framework_TestCase{
    function testInput(){
        $server = new server();
        try{
            $server->input("");
        }catch (Exception $e){
            $this->assertTrue($e->getMessage() == "failed");
        }

    }
}
