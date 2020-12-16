<?php
require_once("libtrait.php");
class local_progress_dashboard_configweeks {
    use lib_trait;

    public $course;
    public $user;
    public $weeks;
    public $group;
    public $current_sections;
    public $startin;

    function __construct($course, $userid){
        global $DB;
        $this->course = self::get_course($course);
        $this->user = self::get_user($userid);
        $this->group = self::last_group();
//        $this->weeks = self::get_weeks();
    //    $this->current_sections = self::get_course_sections();
    //    $this->startin = isset($this->weeks[0]) ? $this->weeks[0]->weekstart : 999999999999;
    //    self::get_weeks_with_sections();
    }

    public function last_group(){
        global $DB;
        $sql = "select * from {progress_dashboard_group} where courseid = ? order by id desc LIMIT 1";
        $group = $DB->get_record_sql($sql, array($this->course->id));
        if(!isset($group) || empty($group)){
            $group = self::create_group($this->course->id);
        }
        return $group;
    }

    public function create_group(){
        global $DB;
        $group = new stdClass();
        $group->courseid = $this->course->id;
        $group->year = date("Y");
        $id = $DB->insert_record("progress_dashboard_group", $group, true);
        $group->id = $id;
        $this->group = $group;
        return $group;
    }

}