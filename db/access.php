<?php
defined('MOODLE_INTERNAL') || die();

$capabilities = array(

    'report/rezultati:view' => array(
        'riskbitmask' => RISK_PERSONAL,
        'captype' => 'read',
        //'contextlevel' => CONTEXT_SYSTEM,
		'contextlevel' => CONTEXT_COURSE,
        'archetypes' => array(
            'teacher' => CAP_ALLOW,
			//'student' => CAP_ALLOW,
            //'editingteacher' => CAP_ALLOW,
            'manager' => CAP_ALLOW
        ),

        //'clonepermissionsfrom' => 'moodle/site:viewreports',
    )
);
