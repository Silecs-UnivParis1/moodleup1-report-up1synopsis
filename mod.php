<?php
/**
 * This file contains public API of up1synopsis report
 *
 * @package    report_up1synopsis
 * @copyright  2012-2020 Silecs {@link http://www.silecs.info}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$url = new moodle_url('/report/up1synopsis/index.php', array('id'=>$id));
echo '<a href="'. $url .'">' . get_string('Synopsis', 'report_up1synopsis') . '</a>';

