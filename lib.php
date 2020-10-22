<?php
/**
 * This file contains public API of up1synopsis report
 *
 * @package    report_up1synopsis
 * @copyright  2012-2020 Silecs {@link http://www.silecs.info}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

/**
 * Is current user allowed to access this report
 *
 * @private defined in lib.php for performance reasons
 *
 * @param stdClass $course
 * @return bool
 */
function report_up1synopsis_can_access_synopsis($course) {
    global $USER;
    
    $coursecontext = context_course::instance($course->id);

    if (has_capability('report/outline:view', $coursecontext)) {
        return true;
    }
    return false;
}

/**
 * Return a list of page types
 * @param string $pagetype current page type
 * @param stdClass $parentcontext Block's parent context
 * @param stdClass $currentcontext Current context of block
 * @return array
 */
function report_up1synopsis_page_type_list($pagetype, $parentcontext, $currentcontext) {
    $array = [
        '*'                    => get_string('page-x', 'pagetype'),
        'report-*'             => get_string('page-report-x', 'pagetype'),
        'report-outline-*'     => get_string('page-report-outline-x',  'report_outline'),
        'report-outline-index' => get_string('page-report-outline-index',  'report_outline'),
    ];
    return $array;
}

/**
 * This function extends the navigation with the report items
 *
 * @global stdClass $CFG
 * @global core_renderer $OUTPUT
 * @param navigation_node $navigation The navigation node to extend
 * @param stdClass        $course     The course to object for the report
 * @param stdClass        $context    The context of the course
 */
function report_up1synopsis_extend_navigation_course($navigation, $course, $context) {
    $url = new moodle_url('/report/up1synopsis/index.php', ['id' => $course->id]);
    $navigation->add(
		get_string('pluginname', 'report_up1synopsis'),
		$url, navigation_node::TYPE_SETTING, null, null, new pix_icon('i/report', '')
    );
}
