<?php


/**
 * Description of main
 *
 * @author Roman
 */



namespace RUsers;

class Security {
    
    private $salt = "UV3LQLJsTv";

    public function setSalt($salt) {
        $this->salt = $salt;
        $this->check_security();
    }
    
    public function PassToCode($pass) {
        return crypt($pass, md5($this->salt));
    }
    
    public function CodeToHash($code) {
        return md5(crypt($code, $this->salt));
    }
    
    public static function hasher($hash1,$hash2) {
        if($hash1 === $hash2){
            return true;
        }
        return false;
    }


    private function check_security() {
        if($this->salt === "UV3LQLJsTv"){
            throw new \RemusException('SECURITY ERROR: Salt is defined');
        }
    }
    
}
