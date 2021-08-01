<?php
/* index.php */
require_once('../../config.php');
require($CFG->dirroot . '/report/rezultati/index_form.php');
// Get the system context.
$systemcontext = context_system::instance();
$url = new moodle_url('/report/rezultati/index.php');
// Check basic permission.
require_capability('report/rezultati:view', $systemcontext);
// Get the language strings from language file.
$strgrade      = get_string('grade', 'report_rezultati');
$strcourse     = get_string('course', 'report_rezultati');
$strrezultati = get_string('rezultati', 'report_rezultati');
$strname       = get_string('name', 'report_rezultati');
$strtitle      = get_string('title', 'report_rezultati');
// Set up page object.
$PAGE->set_url($url);
$PAGE->set_context($systemcontext);
$PAGE->set_title($strtitle);
$PAGE->set_pagelayout('report');
$PAGE->set_heading($strtitle);
//$br1 = 0;
$userid = $USER->id;
// upit za odabir kolegija u formi
$sql = "SELECT id, fullname
        FROM mdl_course
        WHERE visible = :visible AND id != :siteid
        ORDER BY fullname";
$courses  = $DB->get_records_sql_menu($sql, array(
    'visible' => 1,
    'siteid' => SITEID
));
$courseid = $_POST[course];
//upit za odabir testa u formi
$sql2     = "SELECT q.id, q.name 
         FROM mdl_quiz q";
$tests    = $DB->get_records_sql_menu($sql2);
$testid   = $_POST[test];
//upit za odabir lekcije u formi
$sql3     = "SELECT l.id, l.name 
         FROM mdl_lesson l";
$lessons    = $DB->get_records_sql_menu($sql3);
$lessonid   = $_POST[lesson];

// popuni formu sa kolegijom, testom i lekcijom 
$mform    = new rezultati_form('', array(
    'courses' => $courses,
    'tests' => $tests,
	'lessons'=>$lessons
));
echo $OUTPUT->header();
echo $OUTPUT->heading($strtitle);
$mform->display();

//Odabir kolegija - ako je kolegij odabran
if ($courseid != 0) {
	//Kad je test odabran
    if ($testid!=0) {
		$check = $DB->get_record_sql('SELECT course
		FROM mdl_quiz
		WHERE id=?', array(
		$testid
		));
		
		//rezultati studenata po testu
		//echo '<br><br><b>Rezultati studenata na odabranom testu:</b>';
		$niz_kvizova = "SELECT  u.email, qq.grade
			FROM mdl_quiz_grades qq
			JOIN mdl_quiz q ON qq.quiz = q.id
			JOIN mdl_course c ON q.course = c.id
			JOIN mdl_user u ON qq.userid = u.id
			where quiz = $testid AND course = $courseid";
		
		$kvizovi = $DB->get_records_sql_menu($niz_kvizova, array('visible' => 1, 'course' => courseid));
		if (count($kvizovi) == 0) {
			echo '<br><br>Ovaj kolegij nema odabrani test!';
		}
		else 
		{
			//ispis studenata i broja bodova
			//$normal = array();
			echo '<br><br><b>Rezultati studenata na odabranom testu:</b>';
			$brojac1=0;
			$max      = 0;
			foreach($kvizovi as $key => $value)
			{
				$brojac1++;
				echo "<br>Student sa e-mailom <b>".$key ."</b> je na odabranom testu dobio <b>" .number_format($value,2) ."</b> bodova";
			
			}
			echo "<br>";
			echo "<h4>Ukupno testova: " .$brojac1 ."</h4>";
			//echo "MAX JE******************************->: ".$max;
			//ocjene
			$niz_ocjena = "SELECT  u.email, qq.grade
				FROM mdl_quiz_grades qq
				JOIN mdl_quiz q ON qq.quiz = q.id
				JOIN mdl_course c ON q.course = c.id
				JOIN mdl_user u ON qq.userid = u.id
				where quiz = $testid AND course = $courseid";
				
			$ocjene = $DB->get_records_sql_menu($niz_ocjena, array('visible' => 1, 'course' => courseid));
			$proslo           = 0;
			$nijeproslo        = 0;
		
			$niz_max = "SELECT q.grade
				FROM mdl_quiz q";
			$najveci=0;					
			$maxtesta = $DB->get_records_sql_menu($niz_max, array('visible' => 1, 'course' => courseid));
			foreach ($maxtesta as $key => $value) {
				if($max<$key){
					$max=$key;
				}
			}
			//echo "Max je: ".$max;
			if (count($ocjene) == 0) { //kad je test odabran, ali odabrani kolegij ne sadrži odabrani test
				echo 'Za taj test nema niti jedna ocjena!';
			} else {
				//echo 'Analiza testa: <br>';
				foreach ($ocjene as $key => $value) {
					//echo "Kljucevi: ".$value ."<br>";
					$max=number_format($max,0);
					$value=number_format($value,0);
					if ($value / $max < 0.50) {
						$nijeproslo++;
						//echo "Nije proslo: ".$value/$max;
					} elseif ($value / $max >= 0.50 ) {
						$proslo++;
						//echo "Proslo je: ".$value/$max;
					}
				}
			}
			echo "<br>Testove je prošlo: <b>".$proslo ."</b> studenta.";
			echo "<br>Testove nije prošlo: <b>".$nijeproslo ."</b> studenta.";
			$pieData      = array(
				array(
					'Prolaznost',
					'Broj studenata'
				),
				array(
					'Prošlo',
					(double) $proslo
				),			
				array(
					'Nije prošlo',
					(double) $nijeproslo
				)
			);
			$jsonTable=json_encode($pieData);
			//echo '</br></br>Prolaznost studenata na testu:';
		}
	}	
	// kad test nije odabran
	if($testid==0)
	{
		echo "Niste odabrali test za ovaj kolegij!";
		//nemoj ništa uciniti
	}
	// kad je lekcija odabrana	
	if ($lessonid!=0) {
		$check = $DB->get_record_sql('SELECT course
			FROM mdl_lesson
			WHERE id=?', array(
			$lessonid
		));		
		$niz_lekcija ="SELECT  u.email, lg.grade
			FROM mdl_lesson_grades lg
			JOIN mdl_lesson l ON lg.lessonid = l.id
			JOIN mdl_course c ON l.course = c.id
			JOIN mdl_user u ON lg.userid = u.id
			where lessonid = $lessonid AND course = $courseid";		
		$lekcije = $DB->get_records_sql_menu($niz_lekcija, array('visible' => 1, 'course' => courseid));
		$brojac2=0;
		$ukupnoLekcije=0;				
			$pet           = 0;
			$cetiri        = 0;
			$tri           = 0;
			$dva           = 0;
			$jedan         = 0;
			
		if (count($lekcije) == 0) { //kad je lekcija odabrana, ali nema lekcija za taj kolegij
			echo '<br><br>Ovaj kolegij nema odabranu lekciju!';
		}
		else 
		{
			echo '<br><br><b>Lekcije na odabranom kolegiju:</b>';
			foreach($lekcije as $key => $value)
			{
				$brojac2++;
				echo "<br>Student sa e-mailom <b>".$key ."</b> je na odabranoj lekciji dobio <b>" .$value ."</b> bodova";
				/*echo "ocjene: ".$value;	
				if ($value < 50) {
					$jedan++;
				} elseif ($value >=50 and $value < 60) {
					$dva++;
				} elseif ($value >= 60 and $value < 75) {
					$tri++;
				} elseif ($value >= 75 and $value < 90) {
					$cetiri++;
				} elseif ($value >= 90) {
					$pet++;
				}*/
			}
			echo "<br>";
			echo "<h4>Ukupno lekcija: " .$brojac2 ."</h4>";	
			//$ukupnoLekcije=$dva+$tri+$cetiri+$pet;
			//echo "<h4>Ukupno prošlo lekcije: " .$ukupnoLekcije ."</h4>";				
			$niz_ocjenal = "SELECT  u.email, lg.grade
				FROM mdl_lesson_grades lg
				JOIN mdl_lesson l ON lg.lessonid = l.id
				JOIN mdl_course c ON l.course = c.id
				JOIN mdl_user u ON lg.userid = u.id
				where lessonid = $lessonid AND course = $courseid";			
			$maxl = 0;			
			$ocjenel = $DB->get_records_sql_menu($niz_ocjenal, array('visible' => 1, 'course' => courseid));
			$jedan = 0;
			$dva = 0;
			$tri=0;
			$cetiri=0;
			$pet=0;
			$niz_maxl = "SELECT l.grade
				FROM mdl_lesson l";							
			$maxtestal = $DB->get_records_sql_menu($niz_maxl, array('visible' => 1, 'course' => courseid));			
			foreach ($maxtestal as $keyl => $valuel) {
				$maxl = $keyl;
			}			
			if (count($ocjenel) == 0) {
				echo 'Za tu lekciju nema niti jedna ocjena!';
			} else {
				foreach ($ocjenel as $keyl => $valuel) {
					// raspored za ocjene, 12 je najveća pa se od nje uzimaju vrijednosti i spremaju u pjedine varijable
					if (number_format($valuel / $maxl, 0) < 0.50) {
						$jedan++;
					} elseif ($valuel / $maxl >= 0.50 and $valuel / $maxl < 0.60) {
						$dva++;
					} elseif ($valuel / $maxl >= 0.60 and $valuel / $maxl < 0.75) {
						$tri++;
					} elseif ($valuel / $maxl >= 0.75 and $valuel / $maxl < 0.90) {
						$cetiri++;
					} elseif ($valuel / $maxl >= 0.90) {
						$pet++;
					}
				}
					echo '<br>Nedovoljan (1) - <b>'.$jedan.'</b><br>Dovoljan (2) - <b>'.$dva.'</b><br>Dobar (3) - <b>'.$tri.'</b><br>Vrlo dobar (4) - <b>'.$cetiri.'</b><br>Izvrstan (5) - <b>'.$pet.'</b>';				
			}	
							
				$pieData = array(
					array(
						'Ocjena',
						'Broj ucenika'
					),
					array(
						'Odličan 90-100',
						(double) $pet
					),
					array(
						'Vrlo dobar 75-89',
						(double) $cetiri
					),
					array(
						'Dobar 60-74',
						(double) $tri
					),
					array(
						'Dovoljan 50-59',
						(double) $dva
					),
					array(
						'Nedovoljan 0-49',
						(double) $jedan
					)
				);
			
				$jsonTable1=json_encode($pieData);		
		}
    }
	// kad lekcija nije odabrana
	if ($lessonid==0)
	{
		echo "<br>Niste odabrali lekciju za ovaj kolegij!";
		//nemoj nista ucinti 
	}
} 
else 
{
	echo "Odaberi kolegij!"; //upozorenje kad se ne odabere kolegij
}

?>
<!DOCTYPE html>
<html>
  <head>
    <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
    <script type="text/javascript">
      google.charts.load("current", {packages:["corechart"]});
      google.charts.setOnLoadCallback(drawChart);
	  google.charts.setOnLoadCallback(drawChart1);
      
	  function drawChart() { //ovo su podaci tj. graf za testove
        var data = google.visualization.arrayToDataTable(<?php
echo $jsonTable;
?>);        
          var options = {
          title: 'Stanje:',
          is3D: true
        };
        var chart = new google.visualization.PieChart(document.getElementById('piechart'));
        chart.draw(data, options);		      
      }	  
	  function drawChart1() { //ovo su podaci tj. graf za lekcije
        var data1 = google.visualization.arrayToDataTable(<?php
echo $jsonTable1;
?>);        
          var options2 = {
          title: 'Stanje:',
          is3D: true
        };
        var chart2 = new google.visualization.PieChart(document.getElementById('piechart'));
        chart2.draw(data1, options2);      
      }	  
    </script>
  </head>
  <body>
    <table>
    <tr>
    <td><div id="piechart" style="width: 600px; height: 500px;"></div></td>
    </tr>
    </table>
  </body>
</html>

<?php
echo $OUTPUT->footer();
?> 