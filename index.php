<?php
/**
 * Affiche la page de synopsis UP1 du cours
 *
 * @package    report_up1synopsis
 * @copyright  2012-2020 Silecs {@link http://www.silecs.info}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use report_up1synopsis\reporting;

require('../../config.php');
//require_once(__DIR__ . '/locallib.php');

global $DB, $PAGE, $OUTPUT;
 /* @var $PAGE moodle_page */

$id = required_param('id', PARAM_INT);       // course id
$layout = optional_param('layout', 'report', PARAM_ALPHA); // default layout=report
if ($layout != 'popup') {
    $layout = 'report';
}

$course = $DB->get_record('course', array('id' => $id), '*', MUST_EXIST);
$report = new reporting($id);
$PAGE->set_course($course);

$PAGE->set_url('/report/up1synopsis/index.php', array('id'=>$id));
$PAGE->set_pagelayout($layout);
$PAGE->requires->css(new moodle_url('/report/up1synopsis/styles.css'));

$site = get_site();
$strreport = get_string('pluginname', 'report_up1synopsis');
$pagename = up1_meta_get_text($course->id, 'up1nomnorme', false);
if ( ! $pagename ) {
    $pagename = $course->fullname;
}

$PAGE->set_title($pagename); // $course->shortname .': '. $strreport); // tab title
$PAGE->set_heading($site->fullname);
echo $OUTPUT->header();

echo "<h2>" . $pagename . "</h2>\n";

echo '<div id="synopsis-bigbutton">' . "\n";
echo $report->get_button_join();
if ( has_capability('local/crswizard:supervalidator', context_system::instance()) )
{
    $urlboard = new moodle_url('/local/courseboard/view.php', ['id' => $course->id]);
    $icon = $OUTPUT->action_icon($urlboard, new pix_icon('i/settings', 'Afficher le tableau de bord'));
    echo $icon;
}
echo '</div>' . "\n";

// Description
echo '<div id="synopsis-summary">'
    . format_text($course->summary, $course->summaryformat)
    . '</div>' . "\n\n";

echo '<div id="synopsis-informations">' . "\n";
echo "<h3>Informations sur l'espace de cours</h3>\n";
echo $report->get_table_informations($course);
echo '</div>' . "\n";

echo '<div id="synopsis-rattachements">' . "\n";
echo "<h3>Rattachements à l'offre de formation</h3>\n";
echo $report->get_table_rattachements($course);
echo '</div>' . "\n";

echo $OUTPUT->footer();
