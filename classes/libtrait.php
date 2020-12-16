<?php
  require_once(dirname(__FILE__) . '/../../../config.php');

  trait lib_trait{

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

  }