<?php
  require_once(dirname(__FILE__) . '/../../../config.php');
  // Las clases que usen este trait requieren de una propiedad $course con el objeto respectivo 
  // Las clases que usen este trait requieren de una propiedad $user con el objeto respectivo 
  trait lib_trait{
    public function get_student_ids(){
      $roles = array(5);
      global $DB;
      $students = array();
      $users = array();
      $context = context_course::instance($this->course->id);
      foreach($roles as $role){
        $users = array_merge($users, get_role_users($role, $context));
      }
      foreach($users as $user){
        if(!in_array($user->id, $students)){
          $students[] = $user->id;
        }
      }
      $students = self::filter_users_by_selected_group($students);
      return $students;
    }

    protected function filter_users_by_selected_group ($users) {
      global $COURSE, $USER;
      $group_manager = new local_student_reports_group_manager($COURSE, $USER);
      $participants = new local_student_reports_course_participant($USER->id, $COURSE->id);
      $groups = $participants->all_groups_with_members($COURSE->groupmode);
      $selectedgroup = $group_manager->selected_group();
      if(!isset($selectedgroup->groupid) || $selectedgroup->groupid == 0 ){
        return $users;
      }
      foreach ($groups as $group) {
        if($selectedgroup->groupid == $group->id){
          $users = self::extract_users_in_group($users, $group->members);
        }
      }
      return $users;
    }

    private function extract_users_in_group($allusers, $ingroup){
      $extracted = array();
      foreach($allusers as $userid){
        if(isset($ingroup[$userid]) && !in_array($userid, $extracted)){
            array_push($extracted, $userid);
        }
      }
      return $extracted;
    }

    protected function get_full_users(){
      global $DB;
      $users = [];
      list($in, $invalues) = $DB->get_in_or_equal($this->users);
      $fields = self::USER_FIELDS;
      $sql = "select $fields from {user} where id $in order by lastname asc";
      $rows = $DB->get_recordset_sql($sql, $invalues);
      foreach($rows as $key => $row){
        array_push($users, $row);
      }
      $rows->close();
      return $users;
    }

    protected function get_course_modules($sections){
      $course_modules = array();
      foreach($sections as $key => $section){
        if($section->visible != 1){
          continue;
        }
        $modules = self::get_sequence_section($section->sectionid);
        $modules = array_filter($modules, function($module){ return $module->visible == 1;});
        $course_modules = array_merge($course_modules, $modules);
      }
      return $course_modules;
    }

    public function get_sequence_section($sectionid) {
      global $DB;
      $sql =  "select sequence from {course_sections} where id = ?";
      $sequence = $DB->get_record_sql($sql, array($sectionid));
      $course_modules = self::get_course_module_section($sequence->sequence);
      return $course_modules;
    }
  
    public function get_course_module_section($sequence) {
      $sequence = explode(',', $sequence);
      $course_modules = array();
      foreach ($sequence as $key => $course_module_id) {
        $module = get_coursemodule_from_id( '', $course_module_id, $this->course->id, MUST_EXIST);
        array_push($course_modules, $module);
      }
      return $course_modules;
    }

    public function parse_average_to($timeformat, $average){
      if($timeformat == "hours"){
        $average->average = self::minutes_to_hours($average->average);
      }
      return $average;
    }

    public function minutes_to_hours($minutes, $decimals = 2){
      $hours = 0;
      if($minutes <= 0){
        return $hours;
      }else{
        if($decimals > 0){
          $hours = number_format($minutes / 60, 2);
        }else{
          $hours = $minutes / 60;
        }
      }
      return $hours;
    }

    public function stringify_time($hours, $minuts){
      $txt_hour = get_string("txt_hour", "local_student_reports");
      $txt_hours = get_string("txt_hours", "local_student_reports");
      $txt_minut = get_string("txt_minut", "local_student_reports");
      $txt_minuts = get_string("txt_minuts", "local_student_reports");
      $txt_hours = $hours == 1 ? $txt_hour : $txt_hours;
      $txt_minut = $minuts == 1 ? $txt_minut : $txt_minuts;
      $response = "$hours $txt_hours $minuts $txt_minut"; 
      return $response;
    }

    public function now_timestamp(){
      $tz = self::get_timezone();      
      date_default_timezone_set($tz);
      $now = new DateTime();
      $now = $now->format('U');
      return $now;
    }

    public function to_timestamp($date){
      $tz = self::get_timezone();      
      date_default_timezone_set($tz);
      $fecha = new DateTime($date);
      $date = $fecha->format('U');
      return $date;
    }

    public function to_format($format, $timestamp){
      $tz = self::get_timezone();      
      date_default_timezone_set($tz);
      if(gettype($timestamp) == "string"){
        $timestamp = (int) $timestamp;
      }
      $date = date($format, $timestamp);
      return $date;
    }
    
    public function get_course($course){
      if(gettype($course) == "string"){
        $course = (int) $course;
      }
      if(gettype($course) == "integer"){
        $course = self::get_course_from_id($course);
      }
      return $course;
    }

    public static function get_course_from_id($courseid){
      global $DB;
      $course = $DB->get_record('course', array('id' => $courseid), '*', MUST_EXIST);
      return $course;
    }

    public function get_user($user){
      if(gettype($user) == "string"){
        $user = (int) $user;
      }
      if(gettype($user) == "integer"){
        $user = self::get_user_from_id($user);
      }
      return $user;
    }

    public static function get_user_from_id($userid){
      global $DB;
      $user = $DB->get_record('user', array('id' => $userid), '*', MUST_EXIST);
      return $user;
    }

    public function get_course_sections(){
      $modinfo  = get_fast_modinfo($this->course->id);
      $sections = $modinfo->get_section_info_all();
      $sections = self::format_sections($sections);
      return $sections;
    }

    private function format_sections($sections){
      $full_sections = array();
      foreach ($sections as $index => $section){
        $full_section = [
          'sectionid' => $section->id,
          'section' => $section->section,
          'name' => self::get_section_name($section, $index),
          'visible' => $section->visible,
          'availability' =>  $section->availability,
        ];
        $full_sections[] = $full_section;
      }
      return $full_sections;
    }

    private function get_section_name($section, $current_index){
      if(isset($section->name) ){
        return $section->name;
      }
      $build_name = get_string("course_format_{$this->course->format}", 'local_student_reports');
      $name = "$build_name $current_index";
      return $name;
    }

    public function extract_ids ($elements, $stringify = false){
      $ids = array();
      if(gettype($elements) == 'array'){
        foreach($elements as $key => $element){
          if(gettype($element) == "array"){
            if(isset($element['id'])){
              $ids[] = $element['id'];
            }
          }elseif(gettype($element) == "object"){
            if(isset($element->id)){
              $ids[] = $element->id;
            }
          }
        }
      }
      $sql = "";
      if($stringify){
        foreach($ids as $key => $id){
          if($key == 0){
            $sql = $sql . $id;
          }else{
            $sql = $sql . "," . $id;
          }
        }
        $ids = $sql;
      }
      return $ids;
    }

    public function to_key($fieldname, $values){
      $converted = array();
      foreach($values as $key => $value){
        if(gettype($value) == "object" && isset($value->$fieldname)){
          $converted[$value->$fieldname] = $value;
        }else if(gettype($value) == "array" && isset($value[$fieldname])){
          $converted[$value[$fieldname]] = $value;
        }else{
          $converted[] = $value;
        }
      }
      return $converted;
    }

    public function get_timezone(){
      $timezone = usertimezone($this->user->timezone);
      $timezone = self::accent_remover($timezone);
      if(!self::is_valid_timezone($timezone)){
        $timezone = self::get_server_timezone();
      }
      return $timezone;
    }

    public function get_average($total, $count){
      if($count == 0){
        $average = 0;  
      }else{
        $average = $total / $count; 
      }
      return $average;
    }

    public function accent_remover($cadena){
      $cadena = str_replace(
          array('á', 'à', 'ä', 'â', 'ª', 'Á', 'À', 'Â', 'Ä'),
          array('a', 'a', 'a', 'a', 'a', 'A', 'A', 'A', 'A'),
          $cadena
      );
      $cadena = str_replace(
          array('é', 'è', 'ë', 'ê', 'É', 'È', 'Ê', 'Ë'),
          array('e', 'e', 'e', 'e', 'E', 'E', 'E', 'E'),
          $cadena );
      $cadena = str_replace(
          array('í', 'ì', 'ï', 'î', 'Í', 'Ì', 'Ï', 'Î'),
          array('i', 'i', 'i', 'i', 'I', 'I', 'I', 'I'),
          $cadena );
      $cadena = str_replace(
          array('ó', 'ò', 'ö', 'ô', 'Ó', 'Ò', 'Ö', 'Ô'),
          array('o', 'o', 'o', 'o', 'O', 'O', 'O', 'O'),
          $cadena );
      $cadena = str_replace(
          array('ú', 'ù', 'ü', 'û', 'Ú', 'Ù', 'Û', 'Ü'),
          array('u', 'u', 'u', 'u', 'U', 'U', 'U', 'U'),
          $cadena );
      $cadena = str_replace(
          array('ñ', 'Ñ', 'ç', 'Ç'),
          array('n', 'N', 'c', 'C'),
          $cadena
      );
      return $cadena;
    }
    public function is_valid_timezone($timezone) {
      return in_array($timezone, timezone_identifiers_list());
    }

    public function get_server_timezone(){
      $date = new DateTime();
      $timeZone = $date->getTimezone();
      return $timeZone->getName();
    }

    public function convert_time($measure, $time){
      $response = false;
      $valid_params = true;
      if ($measure == 'minutes') {
        $time = $time * 60 / 1;
      } elseif ($measure == 'hours') {
          $time = $time * 3600 / 1;
      } else {
        $valid_params = false;
      }
      if($valid_params){
        $horas = floor($time / 3600);
        $minutos = floor(($time % 3600) / 60);
        $segundos = $time % 60;
        $response = self::convert_time_as_string($horas, $minutos, $segundos);
      }
      return $response;
    }

    protected function convert_time_as_string($hours, $minutes, $seconds = null){
      $text = [
        'minute' => get_string("sr_minute", "local_student_reports"),
        'minutes' => get_string("sr_minutes", "local_student_reports"),
        'hour' => get_string("sr_hour", "local_student_reports"),
        'hours' => get_string("sr_hours", "local_student_reports"),
        'second' => get_string("sr_second", "local_student_reports"),
        'seconds' => get_string("sr_seconds", "local_student_reports")];

      $hour = new stdClass();
      $hour->text = $hours == 1 ? $text['hour'] : $text['hours'];
      $hour->stringify_value = $hours <= 9 ? "0$hours" : $hours ;
      $hour->output = $hours == 0 ? "" : "$hour->stringify_value $hour->text"; 
      
      $minute = new stdClass();
      $minute->text = $minutes == 1 ? $text['minute'] : $text['minutes'];
      $minute->stringify_value = $minutes <= 9 ? "0$minutes" : $minutes;
      $minute->output = $minutes == 0 ? "" : "$minute->stringify_value $minute->text";
      $response = "$hour->output $minute->output";
      
      $hidde_seconds = ($minutes > 0 && $seconds == 0) || ($hours > 0);

      $second = new stdClass();
      $second->text = $seconds == 1 ? $text['second'] : $text['seconds'];
      $second->stringify_value = $seconds <= 9 ? "0$seconds" : $seconds;
      $second->output = $hidde_seconds ? "" : "$second->stringify_value $second->text";
      
      $response = "$hour->output $minute->output $second->output";
      return $response;
    }

    public function get_resource_type($cm){
      global $DB;
      $query = "SELECT f.mimetype FROM {files} f JOIN {context} c  ON c.id = f.contextid
                WHERE c.instanceid = ? AND contextlevel = ? AND f.mimetype <> 'NULL' AND f.filesize > 0";
      $file = $DB->get_record_sql( $query, array($cm, CONTEXT_MODULE));
      if(!isset($file->mimetype)){
        $type = local_student_reports_resourcetype::get_default_type();
      }else{
        $type = local_student_reports_resourcetype::get_type($file->mimetype);
      }
  
      return $type;
    }

    public function update_array_key_by($field, $values){
      $transformed = array();
      foreach($values as $value){
        if(gettype($value) == 'array' && isset($value[$field])){
          $transformed[$value[$field]] = $value;
        }
        if(gettype($value) == 'object' && isset($value->$field)){
          $transformed[$value->$field] = $value;
        }
      }
      return $transformed;
    }

    public function day_to_english($day){
      $day = strtolower($day);
      $days_EN = ["lun" => "mon","mar" => "tue","mier"=>"wed","jue"=>"thu","vie"=>"fri","sab"=>"sat","dom"=>"sun"];
      return isset($days_EN[$day]) ? $days_EN[$day] : null;
    }
  }