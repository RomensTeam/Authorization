<?php

namespace RUsers;

  /**
   * Подключаем трейты
   */
   include __DIR__._DS.'traits'._DS.'Settings.php';

   class Main {

     # Traits
     use Settings;

     # Variable
     public static $PDO;
     
     /**
      * 
      * @var Security
      */
     public static $security = NULL;

     /**
      * 
      */
     public static $User = FALSE;
     public $UserID;
     public $UserProfile;


     # Methods
     /**
      *
      * @param string $settings Принимает путь к настройкам модуля
      */
      public function __construct($PDO = NULL, $settings = null) {
          
        @session_start();
          
        if(is_null($settings)){
            $settings = DIR_SETTINGS.'users.json';
        }
        
        if(!empty($PDO)){
            self::$PDO = $PDO;
        } else {
            throw new \RemusException('PDO is empty');
        }

        $this->readSettings($settings);
        
        $this->start_security();
      }

      /**
       * Авторизация
       * Это метод равторизует пользователя при наличии необходимых данных
       * 
       * @return User Возвращает объект пользователя
       */
        public function autorize($id = null){
          if(!is_null($id)){ return $this->autorizeByID( (int) $id);}
          
          $pre = self::$settings['settings']['security']['prefix'];
          
          if(isset($_SESSION[$pre.'_id'],$_SESSION[$pre.'_hash']) and is_numeric($_SESSION[$pre.'_id'])){
              $user = $this->getProfile( (int) $_SESSION[$pre.'_id']);
              
              $result = Security::hasher($user->getHash(), $_SESSION[$pre.'_hash']);
              
              if($result){
                  return $user;
              }
          }
          return FALSE;
      }
      
      /**
       * Устанавливает профиль как авторизованный
       * 
       * @param User $user Профиль
       * @return User Профиль
       */
      public function SetProfile($user) {
          $this->UserProfile = $user;
          self::$User = true;
          $this->UserID = $user->getID();
          $this->create_session($user);
          
          return $user;
      }
      
      /**
       * Создаёт сессиию
       */
      public function create_session($user) {
          @session_start();
          
          $pre = self::$settings['settings']['security']['prefix'];
          
          $_SESSION[$pre.'_id'] = $user->getID();
          $_SESSION[$pre.'_hash'] = $user->getHash();
      }
      
      /**
       * Уничтожает сессию
       */
      public function destroy_session() {
          $this->UserProfile = null;
          self::$User = false;
          $this->UserID = null;
          session_destroy();
      }
      
      
      /**
       * 
       * Авторизация пользователя с заданым ID
       * 
       * @param integer $id ProfileID
       * @return User|boolean
       */
      public function autorizeByID($id){
          
          $user = $this->getProfile($id);
          
          if($user !== FALSE){
              return $this->SetProfile($user);
          }
      }
      
      public function AutorizeByForm($array){
          
        $QB = new \QueryBuilder(self::$PDO);
        $QB->select()
           ->from(self::$settings['settings']['table']);
        
        foreach ($array as $key => $value) {
            $keyt = strtolower($key);
            if($keyt != 'pass'){
                $QB->where(' `'.$key.'` = :'.$keyt);
                $QB->bind(':'.$keyt, $value);
            }
        }
        
        $QB->limit(1);
        $result = $QB->result();
        $result = (array) array_shift($result);
        if(!empty($result)){
            $diff = (self::$security->PassToCode($array['pass']) == $result['Pass']);
            if($diff){
                return $this->autorizeByID($result['id']);
            } else {return FALSE;}
        }
      }
      
      /**
       * Устанавливает соль для шифрования
       */
      public function setSalt($salt) {
          return self::$security->setSalt( (string) $salt);
      }
      
      /**
       * Запускает службу безопасности
       * 
       */
      private function start_security() {
          if(empty(self::$security)){
              self::$security = new Security;
          }
      }
      
      /**
       * 
       * @param integer $id ID
       * @return User|boolean
       */
      private function getProfile($id) {
          
          $user = $this->get_user_info($id);
          
          if(!empty($user)){
              return new User($user);
          }
          return FALSE;
      }

      
      
      /**
       * Достаёт необходимую информацию по ID
       * 
       * @param integer $id ID
       * @return array
       */
      private function get_user_info($id) {
        $QB = new \QueryBuilder(self::$PDO);
        $QB->select()
           ->from(self::$settings['settings']['table'])
           ->where(' `id` = ?')
           ->bind($id)
           ->limit(1);
        $result = $QB->result();
        
        return (array) array_shift($result);
      }
  }
