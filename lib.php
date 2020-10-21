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
    $array = array(
        '*'                    => get_string('page-x', 'pagetype'),
        'report-*'             => get_string('page-report-x', 'pagetype'),
        'report-outline-*'     => get_string('page-report-outline-x',  'report_outline'),
        'report-outline-index' => get_string('page-report-outline-index',  'report_outline'),
    );
    return $array;
}

function report_up1synopsis_extend_navigation($reportnav, $course, $context) {
    $url = new moodle_url('/report/up1synopsis/index.php', array('id' => $course->id));
    $reportnav->add(get_string('Synopsis', 'report_up1synopsis'), $url);
}
