<?php

require_once('../../config.php');
require_once("$CFG->libdir/formslib.php");

class rezultati_form extends moodleform {

    public function definition() {
        global $DB;
        global $CFG;
        $mform = & $this->_form;
        $options = array();

		// Prvi element forme - odabir kolegija
		$options[0] = 'Odaberi kolegij:';
        $options += $this->_customdata['courses'];
		$mform->addElement('select', 'course', "Kolegij", $options, 'align="center"');
        $mform->setType('course', PARAM_ALPHANUMEXT);	
		
		// Drugi element forme - odabir testa
        $options2 = array();
        $options2[0] = 'Odaberi test:';
        $options2 += $this->_customdata['tests'];
        $mform->addElement('select', 'test', "Test", $options2, 'align="center"');
        $mform->setType('test', PARAM_ALPHANUMEXT);
        
		// Treći element forme - odabir lekcije
        $options3 = array();
        $options3[0] = 'Odaberi lekciju:';
        $options3 += $this->_customdata['lessons'];
        $mform->addElement('select', 'lesson', "Lekcija", $options3, 'align="center"');
        $mform->setType('lesson', PARAM_ALPHANUMEXT);
		
		// Četvrti element forme - botun prikaži
        $mform->addElement('submit', 'save', "Prikaži", get_string('report_rezultati'), 'align="right"');
    }
}
?>


        
		
