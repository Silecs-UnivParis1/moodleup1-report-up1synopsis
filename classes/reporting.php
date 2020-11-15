<?php
/**
 * This file contains functions used by the outline reports
 *
 * @package    report_up1synopsis
 * @copyright  2012-2020 Silecs {@link http://www.silecs.info}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace report_up1synopsis;

defined('MOODLE_INTERNAL') || die;

require_once(__DIR__ . '/../lib.php');
require_once($CFG->dirroot . '/course/lib.php');
require_once($CFG->dirroot . '/local/course_validated/locallib.php');

class reporting
{
    private $course;

    /**
     * 
     * @param int $courseid
     * @global \moodle_database $DB
     */
    public function __construct($courseid)
    {
        global $DB;
        $this->course = $DB->get_record('course', ['id' => $courseid], '*', MUST_EXIST);
    }

    /**
     * Returns list of cohorts enrolled into course.
     * @todo this function should be moved to  enrol/cohort/locallib.php
     * OR lib/accesslib.php (next to get_enrolled_users)
     *
     * @param course id $courseid
     * @param array(role_id, ...) $roleids
     * @return array of cohort records
     */
    private function get_enrolled_cohorts($roleids=null) {
        global $DB;

            $sql = "SELECT c.id, c.name, c.idnumber, c.description
                  FROM {cohort} c
                  JOIN {enrol} e ON (e.enrol='cohort' AND e.customint1=c.id) ";
    //			  JOIN {role} r ON (r.id = e.roleid) //** @todo bugfix DML read exception; don't know why
            $sql .= " WHERE e.courseid = ? ";
        if ( isset($roleids) ) {
            $sql .= "AND roleid IN (". implode(',', $roleids) .")";
        }
        $sql .= " ORDER BY c.name ASC";

        return $DB->get_records_sql($sql, [$this->course->id]);
    }


    public function get_table_informations()
    {
        $res = "\n\n" . '<table class="generaltable">' . "\n";
        $res .= $this->get_rows_informations();
        $res .= $this->get_rows_teachers();
        $res .= $this->get_rows_cohorts();
        if (has_capability('local/crswizard:supervalidator', \context_system::instance())) {
            $res .= $this->get_rows_status();
        }
        $res .= "</table>\n";
        return $res;
    }


    private function get_rows_informations()
    {
        $res = '<tr> <td>Nom</td> <td>' . $this->course->fullname . '</td> </tr>' . "\n";
        $res .= '<tr> <td>Nom abrégé</td> <td>' . $this->course->shortname . '</td> </tr>' . "\n";
        return $res;
    }

    private function get_rows_teachers()
    {
        // output based on roles ; only editingteacher + teacher for now
        // for an output based on capabilities, use instead get_users_by_capability(): much heavier
        global $DB;
        $context = \context_course::instance($this->course->id);
        $troles = ['editingteacher' => 'Enseignants', 'teacher' => 'Autres intervenants' ];
        $res = '';
        foreach ($troles as $trole => $rowhead) {
            $role = $DB->get_record('role', ['shortname' => $trole]);
            $teachers = \get_role_users($role->id, $context);
            if ($teachers) {
                $res .= '<tr> <td>' . $rowhead . '</td>';
                $who = '';
                foreach ($teachers as $teacher) {
                    $who .= \fullname($teacher) . ', ';
                }
                $who = substr($who, 0, -2);
                $res .= '<td>' . $who . '</td> </tr>';
            }
        }
        return $res;
    }

    private function get_rows_cohorts()
    {
        global $DB;
        $res = '';
        $sroles = array(
            'student' => 'Consultation des ressources, participation aux activités :',
            'guest' => 'Consultation des ressources uniquement :'
            );
        $res .= '<tr> <td>Groupes utilisateurs inscrits</td> <td>';
        foreach ($sroles as $srole => $title) {
            $role = $DB->get_record('role', ['shortname' => $srole]);
            $cohorts = $this->get_enrolled_cohorts([$role->id]);
            if (empty($cohorts)) {
                $res .= "$title " . \get_string('Nocohort', 'report_up1synopsis') . "<br />\n";
            } else {
                $res .= "$title";
                $res .= "<ul>";
                    foreach ($cohorts as $cohort) {
                    $res .= "<li>" . $cohort->name . " (". $cohort->idnumber .") </li>";
                }
                $res .= "</ul>";
            }
        }
        $res .= '</td> </tr>';
        return $res;
    }

    private function get_rows_status() {
        $res = '<tr> <td>État</td> <td>';
        $demandeur = up1_meta_get_user($this->course->id, 'demandeurid', false);
        $adate = up1_meta_get_date($this->course->id, 'datedemande');
        if ($demandeur) {
            $res .= 'Créé par ' . $demandeur['name'] . ' le ' . $adate['datefr'] . "</br>\n";
        }
        $approbateureff = up1_meta_get_user($this->course->id, 'approbateureffid', false);
        $adate = up1_meta_get_date($this->course->id, 'datevalid');
        if ($adate['datefr']) {
            $res .= 'Approuvé par ' . $demandeur['name'] . ' le ' . $adate['datefr'] . "\n";
        } else {
            $res .= "En attente d'approbation.";
        }
        $res .= '</td></tr>';
        return $res;
    }


    public function get_table_rattachements() {

        $rofpathid = up1_meta_get_text($this->course->id, 'rofpathid');
        if ($rofpathid == '') {
            echo "<p>Aucun rattachement ROF pour cet espace de cours.</p>";
            return true;
        }
        $pathids = explode(';', $rofpathid);
        $res = '';

        $pathprefix = get_category_path(get_config('local_crswizard','cas2_default_etablissement')); //local_course_validated
        $res = "\n\n" . '<table class="generaltable">' . "\n";
        $parity = 1;
        foreach ($pathids as $pathid) {
            $parity = 1 - $parity;
            $patharray = array_filter(explode('/', $pathid));
            $rofid = $patharray[count($patharray)];
            $rofobject = rof_get_record($rofid);
            $roftitle = '<b>'.rof_get_code_or_rofid($rofid).'</b>' .' - '. $rofobject[0]->name;
            $res .= '<tr class="r'. $parity.'"> <td>Élément pédagogique</td> <td>';
            $res .= $roftitle . "</td></tr>\n";

            $combined = rof_get_combined_path($patharray);
            $res .= '<tr class="r'. $parity.'"> <td>Chemin complet</td> <td>';
            $res .= $pathprefix . rof_format_path($combined, 'name', false, ' > ') . "</td></tr>\n";
        }        
        $res .= "</table>\n";
        return $res;
    }

    public function get_button_join() {
        global $OUTPUT;
        $vistitle = ["Espace en préparation", "Rejoindre l'espace"];
        $visclass = ['prep', 'join'];

        return $OUTPUT->single_button(
            new \moodle_url('/course/view.php', ['id' => $this->course->id]),
            $vistitle[$this->course->visible],
            'get',
            ['class' => 'singlebutton '.$visclass[$this->course->visible]]
        );
    }
}