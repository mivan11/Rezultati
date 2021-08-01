<?php

defined('MOODLE_INTERNAL') || die;

$ADMIN->add('reports', new admin_externalpage('reportrezultati', get_string('pluginname', 
        'report_rezultati'), "$CFG->wwwroot/report/rezultati/index.php",'report/rezultati:view'));

// no report settings
$settings = null;


