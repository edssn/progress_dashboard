<?php
require_once(dirname(__FILE__).'/../../config.php');
global $COURSE, $USER;

$courseid = required_param('courseid', PARAM_INT);
$course = $DB->get_record('course', array('id' => $courseid), '*', MUST_EXIST);

$context = context_course::instance($course->id);

$url = new moodle_url('/local/progress_dashboard/graph1.php?courseid='.$COURSE->id);
$PAGE->set_url($url);
require_login($course, false);
$PAGE->set_title('Título');
$PAGE->set_pagelayout('standard');
$PAGE->set_heading($course->fullname);

$content = array(
    "data" => [],
    "options" => [],
);

$PAGE->requires->js_call_amd('local_progress_dashboard/graph1','init', ['content' => $content]);
echo $OUTPUT->header();

echo('hola');
echo $OUTPUT->render_from_template('local_progress_dashboard/graph1', ['content' => $content]);

echo $OUTPUT->footer();
