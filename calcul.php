<?php
/*
	Auteur : Ben Souverbie
	Licence : GPLv3
	Usage : non commercial, ce produit ne peut pas être vendu
	Date : 2018-10-17
*/

	include( "fonctions.php" ) ;

	@$force = $_REQUEST['force'] ;

	if (! @$startyear = $_REQUEST['startyear'] )  {
		$startyear = date('Y')+1 ;
	}


	$query = "SELECT id,nom,preference,lundi FROM pharmaciens ;" ;
	$result= mysql_query( $query ) ;

	$nbpharmaciens = 0 ;
	while( $line = mysql_fetch_row( $result ) ) {
		$pharmaciens[$nbpharmaciens] = $line ;
		$nbpharmaciens++ ;
	}

	$equit     = floor($nbpharmaciens/2) + $nbpharmaciens%2 ;

	$endyear   = $startyear + $equit ;

	$continue = true ;
	if ( $force != 1 )  {
		## vérifie si le calendrier est déjà rempli
		$query = "SELECT pharmacien FROM calendrier WHERE `date`='$startyear-01-02'" ;
		list($first_day) = mysql_fetch_row(mysql_query( $query )) ;
		if ( $first_day != 0 )  {
			echo "L'année $startyear semble déjà calculée.<br />" ;
			echo "<a href='calcul.php?force=1&startyear=$startyear' target='_parent'>Cliquer ici pour forcer la génération</a>" ;
			$continue = false ;
		}
	}

	if ( $continue ) {

		$startdate = "$startyear-01-02" ;
		$enddate   = "$endyear-01-01" ;

		echo "$startdate > $enddate<br />" ;

		$jours[1] = "detlun" ;
		$jours[2] = "detmar" ;
		$jours[3] = "detmer" ;
		$jours[4] = "detjeu" ;
		$jours[5] = "detven" ;
		$jours[6] = "detsam" ;
		$jours[7] = "detsun" ;

		// $query = "INSERT INTO  `tourdegarde`.`calendrier` (`date`,`pharmacien`,`type`,`day`)
		// 								 VALUES
		// 								 		('2007-12-25', '2',   '3', '2'),
		// 								 		('2008-01-01', '1',   '3', '2'),
		// 								 		('2008-12-25', '4',   '3', '4'),
		// 								 		('2009-01-01', '3',   '3', '4'),
		// 								 		('2009-12-25', '6',   '3', '5'),
		// 								 		('2010-01-01', '5',   '3', '5'),
		// 								 		('2010-12-25', '8',   '3', '6'),
		// 								 		('2011-01-01', '7',   '3', '6'),
		// 								 		('2011-12-25', '1',   '3', '7'),
		// 								 		('2012-01-01', '9',   '3', '7'),
		// 								 		('2012-12-25', '11',  '3', '2'),
		// 								 		('2013-01-01', '10',  '3', '2'),
		// 								 		('2013-12-25', '13',  '3', '3'),
		// 								 		('2014-01-01', '12',  '3', '1') ;" ;
		//
		// $result = mysql_query( $query ) ;

		//log_page($startdate ;
		echo "Nettoyage de la période<br />" ;
		cleandb($startdate) ;
	/*
	2013 coste - remia
	2012 lochet - boucher
	2011 blanchard - bataille
	2010 espinas - faure
	2009 fresquet - agostini
	2008 boissy - voltes
	2007 pierron - blanchard


	/*
		23
		espinas - boucher
		blanchard - gros
		lochet - coste
	*/

		echo "Mise en place des jours fériés<br />" ;
		$start = $startyear ;
		for ($year=$start ; $year<=$endyear ; $year++ )  {
			log_page("Preparation de l'annee $year<br>\n" , "calculs" ) ;
			prepare_year($year) ;
		}
	//	*/


		log_page("<!DOCTYPE HTML PUBLIC \"-//W3C//DTD HTML 4.01 Transitional//EN\" \"http://www.w3.org/TR/html4/loose.dtd\">" , "calculs" ) ;
		log_page("<html>" , "calculs" ) ;
		log_page("	<head>" , "calculs" ) ;
		log_page("		<title>Calcul des tours de garde</title>" , "calculs" ) ;
		log_page("		<link href=\"style.css\" rel=\"stylesheet\" type=\"text/css\">" , "calculs" ) ;
		log_page("	</head>" , "calculs" ) ;

		log_page("	<body>" , "calculs" ) ;
		log_page("		<table class='calendar'>" , "calculs" ) ;

		log_page("<tr><td colspan=10>On a $nbpharmaciens pharmaciens.<br>\nIl faut donc $equit annees pour repartir les Noel et Nouvel An<br>\n" , "calculs" ) ;
		log_page("Annee de depart : $startyear<br>\n" , "calculs" ) ;
		log_page("Annee de fin    : $endyear<br><br><br><br></td></tr>\n" , "calculs" ) ;

		//On commence par attribuer les jours fériés importants (Noel (id=10) et jour de l'an (id=0))
		echo "Mise en place des gardes : Noel et Jour de l'an<br />" ;
		$query = "SELECT `date` FROM calendrier WHERE type=3 AND `date`>='$startdate' AND `date`<'$enddate' AND pharmacien=0 ;" ;
		$result= mysql_query($query) ;

		log_page("<tr><td colspan=10>$query</td></tr>" , "calculs" ) ;
		$c = 0 ;
		while ( list($date) = mysql_fetch_row( $result ) )  {
			if ( $c % 2 == 0 )  {
				$colour = "#DDF" ;
			} else {
				$colour = "#BBF" ;
			}
			$c++ ;
			log_page("<tr bgcolor='$colour'><td>$date</td><td>" , "calculs" ) ;
			$pharm = get_best_match( $date, 'detbig', 'detbig' ) ;

			log_page("</td><td><b>".get_name($pharm)."</b></td></tr>\n" , "calculs" ) ;
			update_garde_day_ferie( $date, $pharm ) ;
			//update_dets( $pharm, $date, 'detbig' ) ;

		}

		log_page("<tr><td colspan=10>---------------------------Fin de calcul des jours importants--------------------</td></tr>" , "calculs" ) ;

	//*
		//On attribue maintenant les jours fériés normaux
		echo "Mise en place des gardes : fériés<br />" ;
		$query = "SELECT `date` FROM calendrier WHERE type=2 AND `date`>='$startdate' AND `date`<'$enddate' AND pharmacien=0 ;" ;
		$result= mysql_query($query) ;

		log_page("<tr><td colspan=10>$query</td></tr>" , "calculs" ) ;
		$c = 0 ;
		while ( list($date) = mysql_fetch_row( $result ) )  {
			if ( $c % 2 == 0 )  {
				$colour = "#DDF" ;
			} else {
				$colour = "#BBF" ;
			}
			$c++ ;
			log_page("<tr bgcolor='$colour'><td>$date</td><td>" , "calculs" ) ;
			$pharm = get_best_match( $date, 'detfer', 'detfer' ) ;

			log_page("</td><td><b>".get_name($pharm)."</b></td></tr>\n" , "calculs" ) ;
			update_garde_day_ferie( $date, $pharm ) ;
			//update_dets( $pharm, $date, 'detfer' ) ;

		}
	//*
		//On attribue ensuite les dimanches
		echo "Mise en place des gardes : dimanches<br />" ;
		//$query = "SELECT `date` FROM calendrier WHERE type=1 AND `date`>='$startdate' AND `date`<'$enddate' AND pharmacien=0 ORDER BY Rand();" ;
		$query = "SELECT `date` FROM calendrier WHERE type=1 AND `date`>='$startdate' AND `date`<'$enddate' AND pharmacien=0 ORDER BY RAND() ;" ;
		$result= mysql_query($query) ;

		log_page("<tr><td colspan=10>$query</td></tr>" , "calculs" ) ;
		$c = 0 ;
		while ( list($date) = mysql_fetch_row( $result ) )  {
			if ( $c % 2 == 0 )  {
				$colour = "#DDF" ;
			} else {
				$colour = "#BBF" ;
			}
			$c++ ;
			log_page("<tr bgcolor='$colour'><td>$date</td><td>" , "calculs" ) ;
			$pharm = get_best_match( $date, 'detsun', 'detsun' ) ;

			log_page("</td><td><b>".get_name($pharm)."</b></td></tr>\n" , "calculs" ) ;
			$datetab = explode('-',$date) ;
			$prevday = date('Y-m-d', mktime(0,0,0,$datetab[1],$datetab[2]-1,$datetab[0])) ;
			update_garde_day( $prevday, $pharm ) ;
			//updatedet( $pharm, 'detday', -1 ) ;
			//updatedet( $pharm, 'detsun', -1 ) ;

		}
	//*
		//On attribue enfin les jours ordinaires
		echo "Mise en place des gardes : Jours<br />" ;
		$query = "SELECT `date`,day FROM calendrier WHERE type=0 AND `date`>='$startdate' AND `date`<'$enddate' AND pharmacien=0 ;" ;
		$result= mysql_query($query) ;

		log_page("<tr><td colspan=10>$query</td></tr>" , "calculs" ) ;
		$c = 0 ;
		while ( list($date,$day) = mysql_fetch_row( $result ) )  {
			if ( $c % 2 == 0 )  {
				$colour = "#DDF" ;
			} else {
				$colour = "#BBF" ;
			}
			$c++ ;

			log_page("<tr bgcolor='$colour'><td>".$jours[$day]." - $date</td><td>" , "calculs" ) ;
			$pharm = get_best_match( $date, $jours[$day], "detday" ) ;

			log_page("</td><td><b>".get_name($pharm)."</b></td></tr>\n" , "calculs" ) ;
			update_garde_day( $date, $pharm ) ;
			//updatedet( $pharm, $jours[$day], -1 ) ;
			//updatedet( $pharm, 'detday', -1 ) ;

		}
	//*/

		log_page("	</table>" , "calculs" ) ;
		log_page("</body>" , "calculs" ) ;
		log_page("</html>" , "calculs" ) ;
		echo "Fin<br />" ;
		echo "Voir le <a href='display.php?page=calculs' target='_parent'>détail des calculs</a><br />" ;
	}
?>
