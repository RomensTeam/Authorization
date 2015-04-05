<?php

namespace RUsers;


trait Settings {


  public static $settings;


  private function readSettings($settings) {
    $settings = $this->getSettings($settings);

    $settings = json_decode($settings, TRUE);

    if(json_last_error() or !isset($settings['users'])){
        throw new \RemusException(__CLASS__.': Wrong settings data. Please check JSON-data.');
    }

    self::$settings = $settings['users'];
  }

  private function getSettings($settings) {
      $settings_source = file_get_contents($settings);

      if(!$settings_source){
          throw new \RemusException(__CLASS__.': Wrong settings. Please check settings and make his readeble.');
      }
      return $settings_source;
  }

}

?>
