<?php

require_once(dirname(__FILE__) . '/../../config.php');
global $COURSE, $USER;

$courseid = required_param('courseid', PARAM_INT);
$course = $DB->get_record('course', array('id' => $courseid), '*', MUST_EXIST);

$context = context_course::instance($course->id);

$url = new moodle_url('/local/progress_dashboard/graph2.php?courseid=' . $COURSE->id);
$PAGE->set_url($url);
require_login($course, false);
$PAGE->set_title('Gráfico 2');
$PAGE->set_pagelayout('standard');
$PAGE->set_heading($course->fullname);


$chart = array(
    "chart" => array(
        "polar" => true,
        "type" => "line",
    ),
    "title" => array(
        "text" => "Figura 1"
    ),
    "pane" => array(
        "size" => "100%"
    ),
    "xAxis" => array(
        "categories" => array(
            "Totalmente en Desacuerdo",
            "En Desacuerdo",
            "Ni de Acuerdo ni en Desacuerdo",
            "De Acuerdo",
            "Totalmente de Acuerdo"
        ),
        "tickmarkPlacement" => "on",
        "lineWidth" => 0
    ),
    "yAxis" => array(
        "gridLineInterpolation" => "polygon",
        "lineWidth" => 0,
        "min" => 0
    ),
    "tooltip" => array(
        "shared" => true,
        "pointFormat" => '<span style="color:{series.color}">{series.name}: <b>${point.y:,.0f}</b><br/>'
    ),
    "legend" => array(
        "align" => "right",
        "verticalAlign" => "middle",
        "layout" => "vertical",
    ),
    "series" => array(
        array(
            "name" => "Pregunta 1",
            "data" => array(0, 2, 1, 0, 0),
            "pointPlacement" => "on",
        ),
        array(
            "name" => "Pregunta 2",
            "data" => array(1, 2, 0, 0, 0),
            "pointPlacement" => "on",
        ),
        array(
            "name" => "Pregunta 3",
            "data" => array(0, 3, 0, 0, 0),
            "pointPlacement" => "on",
        ),
        array(
            "name" => "Pregunta 4",
            "data" => array(0, 3, 0, 0, 0),
            "pointPlacement" => "on",
        ),
        array(
            "name" => "Pregunta 5",
            "data" => array(0, 3, 0, 0, 0),
            "pointPlacement" => "on",
        ),
        array(
            "name" => "Pregunta 6",
            "data" => array(0, 3, 0, 0, 0),
            "pointPlacement" => "on",
        ),
        array(
            "name" => "Pregunta 7",
            "data" => array(0, 1, 1, 1, 0),
            "pointPlacement" => "on",
        ),
    ),
    "responsive" => array(
        "rules" => array(
            array(
                "condition" => array(
                    "maxWidth" => 500
                ),
                "chartOptions" => array(
                    "legend" => array(
                        "align" => "center",
                        "verticalAlign" => "bottom",
                        "layout" => "horizontal"
                    )
                ),
            ),
        ),
    ),
    "credits" => array(
        "enabled" => false
    ),
);

$content = array(
    "chart" => $chart,
    "options" => [],
);

$PAGE->requires->js_call_amd('local_progress_dashboard/graph1', 'init', ['content' => $content]);

echo $OUTPUT->header();
echo $OUTPUT->render_from_template('local_progress_dashboard/graph1', ['content' => $content]);
echo $OUTPUT->footer();
