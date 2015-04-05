<?php


/**
 * Description of main
 *
 * @author Roman
 */



namespace RUsers;

class User {
    
    public $info;

    public function __construct($info) {
        
        if(is_array($info)){
            $this->info = array_change_key_case($info);
        } else {
            throw new \RemusException('Wrong user info for creating User class');
        }
    }

    public function getName(){
        return $this->info['name'];
    }
    public function getID(){
        return $this->info['id'];
    }
    public function getLogin(){
        return $this->info['login'];
    }
    public function getHash(){
        return Main::$security->CodeToHash($this->info['pass']);
    }
    public function getCode(){
        return $this->info['pass'];
    }
    
    public function __call($name,$arg = NULL) {
        
        if(substr(strtolower($name), 0,3) === 'get'){
            return $this->_get_(substr($name, 3));
        }
        
        return NULL;
    }
    
    public function changePass($pass) {
        $code = Main::$security->PassToCode($pass);
        
        $QB = new \QueryBuilder(Main::$PDO);
        $QB->update(array('pass'=>$code))
           ->from(Main::$settings['settings']['table'])
           ->where(' `id` = '.$this->getID());
        
        return $QB->result();
    }
    
    private function _get_($name) {
        
        $name = strtolower($name);
        
        if(isset($this->info[$name])){
            return $this->info[$name];
        }

        throw new \RemusException('Wrong user info');
    }
}
