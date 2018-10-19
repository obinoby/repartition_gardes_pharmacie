<?php
/*
	Auteur : Ben Souverbie
	Licence : GPLv3
	Usage : non commercial, ce produit ne peut pas être vendu
	Date : 2018-10-17
*/

	include( "connect.php" ) ;

	function calgen( $pharm, $year )  {
		//Cette fonction génère un fichier ics pour l'année en cours
		$query = "SELECT `date` FROM  `calendrier` WHERE `pharmacien`=$pharm AND `date` LIKE  '".$year."-%' ORDER BY `date` ASC ;" ;
		$result= mysql_query( $query ) ;

		$ics = "BEGIN:VCALENDAR\nMETHOD:PUBLISH\nVERSION:2.0\nX-WR-TIMEZONE:Europe/Paris\nCALSCALE:GREGORIAN\n" ;

		while ( list($date)=mysql_fetch_row( $result ) )  {
			$deb = @date( "Ymd", @strtotime($date)) ;
			$fin = @date( "Ymd", @strtotime($date)+86400) ;
			#echo $deb." > ".$fin ;

			$ics .= "BEGIN:VEVENT\nDTEND;VALUE=DATE:$fin\nTRANSP:TRANSPARENT\nSUMMARY:Garde\nDTSTART;VALUE=DATE:$deb\nLOCATION:Pharmacie\nSEQUENCE:4\nBEGIN:VALARM\nTRIGGER:PT9H\nX-APPLE-DEFAULT-ALARM:TRUE\nATTACH;VALUE=URI:Basso\nACTION:AUDIO\nEND:VALARM\nEND:VEVENT\n" ;
		}

		$ics .= "END:VCALENDAR" ;

		file_put_contents("ics/$pharm/cal$year.ics", $ics) ;

		return true ;
	}

        function calgenabo( $pharm )  {
                //Cette fonction génère une sortie ics sur 1.5 an (6 mois en arriere et 1 an en avant)
		$brdebut = date("Y-m-d",strtotime("-6 months"));
		$brfin   = date("Y-m-d",strtotime("12 months"));

		if ( $pharm == 0 )  {
			$squery="" ;
		} else {
			$squery="calendrier.pharmacien=$pharm AND" ;
		}
		/*		$query = "SELECT type, pharmacien, nom FROM calendrier, pharmaciens WHERE pharmaciens.id=calendrier.pharmacien AND date='$date' ;" ;
                                $result= mysql_query( $query ) ;
                                list( $type, $pharm, $nom ) = mysql_fetch_row( $result ) ; */

                $query = "SELECT calendrier.date, pharmaciens.nom FROM  calendrier, pharmaciens WHERE $squery pharmaciens.id=calendrier.pharmacien AND date>'$brdebut' AND date<'$brfin' ORDER BY date ASC ;" ;
                $result= mysql_query( $query ) or die ("Erreur de base de donnees") ;

                $ics = "BEGIN:VCALENDAR\nMETHOD:PUBLISH\nVERSION:2.0\nX-WR-TIMEZONE:Europe/Paris\nCALSCALE:GREGORIAN\n\n" ;

                while ( list($date,$nom)=mysql_fetch_row( $result ) )  {
                        $deb = date( "Ymd", strtotime($date)) ;
                        $fin = date( "Ymd", strtotime($date)+86400) ;
                        if ( $pharm == 0 )  {
                        	$summary=$nom ;
                	} else {
                        	$summary="Garde" ;
                	}

                        $ics .= "BEGIN:VEVENT\nDTEND;VALUE=DATE:$fin\nTRANSP:TRANSPARENT\nSUMMARY:$summary\nDTSTART;VALUE=DATE:$deb\nLOCATION:Pharmacie\nSEQUENCE:4\nBEGIN:VALARM\nTRIGGER:PT9H\nX-APPLE-DEFAULT-ALARM:TRUE\nATTACH;VALUE=URI:Basso\nACTION:AUDIO\nEND:VALARM\nEND:VEVENT\n\n" ;
                }

                $ics .= "END:VCALENDAR" ;

                echo $ics ;

                return true ;
        }

	function cleandb($date)  {
		$query = "update `calendrier` set pharmacien=0 WHERE `date`>='$date';" ;
		$res   = mysql_query($query) or die ($query) ;
		$query = "DELETE FROM `Pages` WHERE `page` = 'calculs' ;" ;
		$res   = mysql_query( $query ) ;


		//echo $query ;

		$query = "SELECT id FROM `pharmaciens` ;" ;
		$result= mysql_query($query) or die ($query) ;
		while ( list($id) = mysql_fetch_row( $result ) )  {
			$ordre = rand(0,100) ;
			$query = "update `pharmaciens` set
						detsun=(SELECT -COUNT( pharmacien ) FROM  `calendrier` WHERE `pharmacien`=$id AND `type`=1 AND `date`>='2014-01-01'),
						detfer=(SELECT -COUNT( pharmacien ) FROM  `calendrier` WHERE `pharmacien`=$id AND `type`=2 AND `date`>='2014-01-01'),
						detday=(SELECT -COUNT( pharmacien ) FROM  `calendrier` WHERE `pharmacien`=$id AND `type`=0 AND `date`>='2014-01-01'),
						detbig=(SELECT -COUNT( pharmacien ) FROM  `calendrier` WHERE `pharmacien`=$id AND `type`=3 AND `date`>='2014-01-01'),

						detlun=(SELECT -COUNT( pharmacien ) FROM  `calendrier` WHERE `pharmacien`=$id AND `day`=1 AND `date`>='2014-01-01'),
						detmar=(SELECT -COUNT( pharmacien ) FROM  `calendrier` WHERE `pharmacien`=$id AND `day`=2 AND `date`>='2014-01-01'),
						detmer=(SELECT -COUNT( pharmacien ) FROM  `calendrier` WHERE `pharmacien`=$id AND `day`=3 AND `date`>='2014-01-01'),
						detjeu=(SELECT -COUNT( pharmacien ) FROM  `calendrier` WHERE `pharmacien`=$id AND `day`=4 AND `date`>='2014-01-01'),
						detven=(SELECT -COUNT( pharmacien ) FROM  `calendrier` WHERE `pharmacien`=$id AND `day`=5 AND `date`>='2014-01-01'),
						detsam=(SELECT -COUNT( pharmacien ) FROM  `calendrier` WHERE `pharmacien`=$id AND `day`=6 AND `date`>='2014-01-01'),
						ordre=$ordre
					  WHERE id=$id ;" ;
			$res   = mysql_query($query) or die ($query) ;
		}
	}

	function joursferies($annee)  {
		/*
		Paramètre fourni : l'année pour laquelle on veut la liste des jours fériés
		En sortie : un tableau à deux dimensions : tableau[x][y]
					- x représente le numéro identifiant du jour férié
					- y représent la donnée (0=nom,1=année,2=mois,3=jour)
		*/

		//Calcul de paques selon l'algorithme de Oudin
		$g = $annee % 19 ;
		$c = floor($annee / 100) ;
		$c4= $c/4 ;
		$e = floor((8*$c+13)/25) ;
		$h = (19*$g+$c-$c4-$e+15)%30;
		$k = floor($h/28) ;
		$p = floor(29/($h+1)) ;
		$q = floor((21-$g)/11) ;
		$i = ($k*$p*$q-1)*$k+$h ;
		$b = floor($annee/4)+$annee ;
		$j1= $b+$i+2+$c4-$c ;
		$j2= $j1 % 7 ;
		$r = 28+$i-$j2 ;
		//paques est le $r mars de $annee
		//si $r>31 alors ($r-31) avril de $annee


		$lpac[0] = date("n", mktime(0,0,0,3,$r+1,$annee)) ;
		$lpac[1] = date("j", mktime(0,0,0,3,$r+1,$annee)) ;

		$ferie[0][0]  = "Jour de l'an" ;
		$ferie[0][1]  = $annee ;
		$ferie[0][2]  = 1 ;
		$ferie[0][3]  = 1 ;

		$ferie[1][0]  = "Lundi de Pâques" ;
		$ferie[1][1]  = $annee ;
		$ferie[1][2]  = $lpac[0] ;
		$ferie[1][3]  = $lpac[1] ;

		$ferie[2][0]  = "Fête du travail" ;
		$ferie[2][1]  = $annee ;
		$ferie[2][2]  = 5 ;
		$ferie[2][3]  = 1 ;

		$ferie[3][0]  = "8 Mai 1945" ;
		$ferie[3][1]  = $annee ;
		$ferie[3][2]  = 5 ;
		$ferie[3][3]  = 8 ;

		$ferie[4][0]  = "Jeudi de l'Ascension" ;
		$ferie[4][1]  = $annee ;
		$ferie[4][2]  = date("n", mktime(0,0,0,$lpac[0],$lpac[1]+38,$annee)) ;
		$ferie[4][3]  = date("j", mktime(0,0,0,$lpac[0],$lpac[1]+38,$annee)) ;

		$ferie[5][0]  = "Lundi de Pentecôte" ;
		$ferie[5][1]  = $annee ;
		$ferie[5][2]  = date("n", mktime(0,0,0,$lpac[0],$lpac[1]+49,$pac[0])) ;
		$ferie[5][3]  = date("j", mktime(0,0,0,$lpac[0],$lpac[1]+49,$pac[0])) ;

		$ferie[6][0]  = "Fête Nationale" ;
		$ferie[6][1]  = $annee ;
		$ferie[6][2]  = 7 ;
		$ferie[6][3]  = 14 ;

		$ferie[7][0]  = "Assomption" ;
		$ferie[7][1]  = $annee ;
		$ferie[7][2]  = 8 ;
		$ferie[7][3]  = 15 ;

		$ferie[8][0]  = "La Toussain" ;
		$ferie[8][1]  = $annee ;
		$ferie[8][2]  = 11 ;
		$ferie[8][3]  = 1 ;

		$ferie[9][0]  = "Armistice" ;
		$ferie[9][1]  = $annee ;
		$ferie[9][2]  = 11 ;
		$ferie[9][3]  = 11 ;

		$ferie[10][0] = "Noël" ;
		$ferie[10][1] = $annee ;
		$ferie[10][2] = 12 ;
		$ferie[10][3] = 25 ;

		return $ferie ;
	}

	function prepare_year($year)  {
		/*
		Paramètre fourni : l'année pour laquelle on veut créer la liste de jours en base
		En sortie : rien
		*/
		//Determine si l'année est bissextile
		$b = date('L', mktime(0,0,0,1,1,$year)) ;
		$nbjours = 365+$b ;

		//insere tous les jours feries dans la base
		$f = joursferies($year) ;
		for ( $i=0 ; $i<11 ; $i++ )  {
			$poids = 2 ;
			if ( $i==0 || $i==10 ) {
				$poids = 3 ;
			}
			$day   = date('N', mktime(0,0,0,$f[$i][2],$f[$i][3],$f[$i][1])) ;
			$query = "INSERT IGNORE INTO  `calendrier` (`date`, `type`,`day`)
							VALUES ('".$f[$i][1]."-".$f[$i][2]."-".$f[$i][3]."',  '$poids', $day);" ;
			$result= mysql_query($query) or die ($query) ;
		}

		// détermine le premier dimanche
		$jour=0 ;
		$dimanche = 0 ;
		while ($dimanche != 7)  {
			$jour++;
			$dimanche=date('N', mktime(0,0,0,1,$jour,$year)) ;
		}

		//insere tous les dimanches dans la base
		for ($j=$jour ; $j<=$nbjours ; $j=$j+7 )  {
			$d   = date('Y-m-d', mktime(0,0,0,1,$j,$year)) ;
			$day = date('N',     mktime(0,0,0,1,$j,$year)) ;
			$query = "INSERT IGNORE INTO  `calendrier` (`date`, `type`,`day`)
							VALUES ('$d',  '1', $day);" ;
			$result= mysql_query($query) or die ($query) ;
		}

		//insere tous les autres jours dans la base
		for ($j=1 ; $j<=$nbjours ; $j++ )  {
			$d   = date('Y-m-d', mktime(0,0,0,1,$j,$year)) ;
			$day = date('N',     mktime(0,0,0,1,$j,$year)) ;
			$query = "INSERT IGNORE INTO  `calendrier` (`date`, `type`,`day`)
							VALUES ('$d',  '0', $day);" ;
			$result= mysql_query($query) or die ($query) ;
		}

	}

	function get_graced_pharms($date)  {
		/*
		Paramètre fourni : la date du jour à vérifier
		En sortie : une liste des pharmaciens graciés
		*/

		echo "coucou" ;

	}

	function update_day($date, $pharm)  {
		$query = "UPDATE calendrier SET  pharmacien=$pharm WHERE  `date`='$date' ;" ;
		$res   = mysql_query($query) or die ($query) ;

		$query = "SELECT type,day FROM calendrier WHERE `date`='$date' ;" ;
		$res   = mysql_query($query) or die ($query) ;
		list($type,$day)  = mysql_fetch_row( $res ) ;

		switch ($type)  {
			case 3:
				$det = 'detbig' ;
				break ;
			case 2:
				$det = 'detfer' ;
				break ;
			case 1:
				$det = 'detsun' ;
				break ;
			case 0:
				$det = 'detday' ;
				break ;
		}
		//echo "---- Type : $type - Day : $day - Det : $det " ;
		$det2 = "" ;
		if ( $det != 'detsun' )  {
			switch ($day)  {
				case 1:
					$det2 = 'detlun' ;
					break ;
				case 2:
					$det2 = 'detmar' ;
					break ;
				case 3:
					$det2 = 'detmer' ;
					break ;
				case 4:
					$det2 = 'detjeu' ;
					break ;
				case 5:
					$det2 = 'detven' ;
					break ;
				case 6:
					$det2 = 'detsam' ;
					break ;
				case 7:
					$det2 = 'detsun' ;
					break ;
			}
			//echo "- Det2 : $det2" ;
			$det2 = ", $det2=$det2-1" ;
		}
		//echo "<br>" ;
		$query = "UPDATE pharmaciens SET  $det=$det-1 $det2 WHERE  id=$pharm ;" ;
		$res   = mysql_query($query) or die ($query) ;
		//echo " >>> $query<br>" ;
	}

	function update_garde_day( $date, $pharm )  {
		/*
		Fonction récursive afin de gérer les jours de gardes enchainés
		Paramètre fourni :
			- la date du jour à attribuer
			- l'id du pharmacien à qui sera attribué le jour
		En sortie : rien
		*/

		//echo "<b>-- Update-garde-day( $date , $pharm )</b><br>" ;
		//met à jour le jour en question avec l'id du pharmacien
		update_day($date, $pharm) ;

		//vérifie si le jour suivant peu être pris par un autre pharmacien (type=0)
		$datetab = explode('-',$date) ;
		$nextday = date('Y-m-d', mktime(0,0,0,$datetab[1],$datetab[2]+1,$datetab[0])) ;

		$query = "SELECT type FROM calendrier WHERE `date`='$nextday' ;" ;
		$res   = mysql_query($query) ;
		$type  = mysql_fetch_row( $res ) ;
		if ( $type[0] > 0 ) {
			//le jour suivant ne peut être pris par un autre pharmacien
			//attribution du jour suivant au pharmacien
			update_garde_day( $nextday, $pharm ) ;
		}

	}

	function update_garde_day_ferie( $date, $pharm )  {
		/*
		Fonction récursive afin de gérer les jours de gardes enchainés pour les jour fériés
		Paramètre fourni :
			- la date du jour à attribuer
			- l'id du pharmacien à qui sera attribué le jour
		En sortie : rien
		*/

		//echo "<b>- Update-garde-day-ferie( $date , $pharm )</b><br>" ;
		//réupere la date de la veille et le lendemain
		$datetab = explode('-',$date) ;
		$prevday = date('Y-m-d', mktime(0,0,0,$datetab[1],$datetab[2]-1,$datetab[0])) ;
		$query = "SELECT type FROM calendrier WHERE `date`='$prevday' ;" ;
		$res   = mysql_query($query) ;
		$type  = mysql_fetch_row( $res ) ;
		if ( $type[0] > 0 ) {
			//le jour précédent est aussi un jour spécial, décalage d'un jour suplémentaire
			update_garde_day_ferie( $prevday, $pharm ) ;
		} else {
			//le jour précédent est un jour normal, attribution de ce jour
			//le système attribuera en cascade les jours spéciaux qui suivent
			update_garde_day( $prevday, $pharm ) ;
		}
	}

	function update_dets( $pharm, $date, $type )  {
		$datetab = explode('-',$date) ;
		$day = date('N',mktime(0,0,0,$datetab[1],$datetab[2],$datetab[0])) ;
		$det = -1 ;
		if ( $day==1 )  {
			//si le jour est un lundi, la dette diminue de 1 + 1 dimanche + 1 samedi
			$det_day = -1 ;
			$det_sun = -1 ;
		} else if ( $day==6 )  {
			//si le jour est un samedi, la dette diminue de 1 + 1 dimanche + 1 vendredi
			$det_day = -1 ;
			$det_sun = -1 ;
		} else if ( $day==7 )  {
			//si le jour est un dimanche, le samedi sera attribué aussi, la dette diminue de 1 + 1 samedi
			$det_day = -1 ;
			$det_sun = 0 ;
		} else {
			//si le jour est en semaine, la dette diminue de 1 + 1 pour le lundi
			$det_day = -1 ;
			$det_sun = 0 ;
		}
		updatedet( $pharm, $type, $det ) ;
		if ( $det_day != 0 && $type != 'detday' )  {
			updatedet( $pharm, 'detday', $det_day ) ;
		}
		if ( $det_sun != 0  && $type != 'detsun' )  {
			updatedet( $pharm, 'detsun', $det_sun ) ;
		}
	}

	function get_graced($pharm,$type,$date)  {
		$datetab = explode('-',$date) ;
		$supl="" ;

		$q = "SELECT domapp,grace,domapp2,grace2 FROM graces WHERE type='$type' ;" ;
		$r = mysql_query( $q ) or die ($q) ;

		list($domapp,$grace,$domapp2,$grace2) = mysql_fetch_row( $r ) ;

		//log_page("$domapp, $grace, $domapp2, $grace2<br>" , "calculs" ) ;

		if ( $domapp2 < 10 )  {
			$deb = date('Y-m-d',mktime(0,0,0,$datetab[1],$datetab[2]-$grace2,$datetab[0])) ;
			$fin = date('Y-m-d',mktime(0,0,0,$datetab[1],$datetab[2]+$grace2,$datetab[0])) ;
			$supl= "OR (date>='$deb' AND date<='$fin' AND type=$domapp2 AND pharmacien=$pharm)" ;
		}

		$deb = date('Y-m-d',mktime(0,0,0,$datetab[1],$datetab[2]-$grace,$datetab[0])) ;
		$fin = date('Y-m-d',mktime(0,0,0,$datetab[1],$datetab[2]+$grace,$datetab[0])) ;

		//log_page("$deb, $fin<br>" , "calculs" ) ;


		$query  = "SELECT count(pharmacien) FROM calendrier
					WHERE (date>='$deb' AND date<='$fin' AND type>=$domapp AND pharmacien=$pharm)
						  $supl ;" ;
		//log_page($query." >>> ", "calculs") ;
		$result = mysql_query( $query ) or die ( $query ) ;
		$ok     = mysql_fetch_row( $result ) ;

		return ($ok[0]<1) ;
	}

	function get_best_match( $date, $type, $type2 )  {

		$datetab = explode('-',$date) ;
		$day = date('N',mktime(0,0,0,$datetab[1],$datetab[2],$datetab[0])) ;

		//On vérifie qu'il peut prendre cette date à cause des lundis non travaillés
		$det = 0 ;
		if ( $day==1 )  {
			$det = 1 ;
		} else if ( $day==2 && $type2 != 'detday' )  {
			//si le jour est un mardi, alors le lundi sera attribué aussi, on test sur le lundi qui précède
			$datetab[2] = $datetab[2]-1 ;
			$det = 1 ;
		} else if ( $day==6 && $type2 != 'detday' )  {
			//si le jour est un samedi, alors le dimanche sera pris avec. On test sur le lundi qui suit
			$datetab[2] = $datetab[2]+2 ;
			$det = 1 ;
		} else if ( $day==7 && $type2 != 'detday' )  {
			//si le jour est un dimanche. On test sur le lundi qui suit
			$datetab[2] = $datetab[2]+1 ;
			$det = 1 ;
		}

		$c      = 0     ;
		$cc     = 0     ;
		$sortie = false ;
		$tof    = true  ;
		$graced = false ;
		while ( $tof && $cc<15 )  {

			if ( $cc == 14 )  {
				$c = 0 ;
				log_page("------> Personne n'est dispo. On repart avec la plus grosse dette, m&ecirc;me si graci&eacute;<br>" , "calculs" ) ;
			} else {
				$c = $cc ;
			}
			//On récupère le pharmacien qui à la plus grosse dette (le premier de la liste quand égalité)
			$pharm=getmaxdet($type,$type2,$c) ;
			if ( $c>0 )  {
				log_page("---> Graci&eacute; !!<br>---> la plus grosse dette suivante : <b>".get_name($pharm)."</b> $c / $cc<br>" , "calculs" ) ;
			} else {
				log_page("---> la plus grosse dette : <b>".get_name($pharm)."</b> $c / $cc<br>" , "calculs" ) ;
			}
			if ( $det == 1 )  {
				$ccc = $c ;
				while ( get_pharm_schedule($pharm) == get_paire($datetab[0],$datetab[1],$datetab[2]) )  {
					log_page("---> mais il ne peut pas prendre cette garde<br>" , "calculs" ) ;
					$c++ ;
					if ( $c == 14 && $ccc > 0 )  {
						$c = 0 ;
						$sortie = true ;
						log_page("------> Personne n'est dispo. On repart avec la plus grosse dette, m&ecirc;me si graci&eacute;<br>" , "calculs" ) ;
					}
					//et on cherche le suivant
					$pharm=getmaxdet($type,$type2,$c) ;
					log_page("---> la plus grosse dette suivante : <b>".get_name($pharm)."</b> $c / $cc<br>" , "calculs" ) ;
				}
				if ( $cc<14 && !$sortie )  {
					$cc = $c ;
				}
			}
			$graced = get_graced($pharm,$type,$date) ;
			$tof = (! $graced) ;
			$cc++ ;
		}
		return $pharm ;
	}

	function get_paire( $an, $mo, $jo )  {

		$starttime = @mktime(0,0,0,9,23,2013)/604800 ;
		$datetime  = @mktime(0,0,0,$mo,$jo,$an)/604800 ;
		$diff      = ($datetime-$starttime)%2 ;

		return $diff ;
	}

	function get_pharm_schedule( $pharm )  {
		$query = "SELECT lundi FROM pharmaciens WHERE id=$pharm ;" ;
		$res   = mysql_query($query) ;
		$sch   = mysql_fetch_row( $res ) ;

		return $sch[0] ;
	}

	function getmaxdet($det,$det2,$ord)  {

		if ( $det == 'detbig')  {
			$query = "detbig" ;
		} else if ( $det == 'detfer')  {
			$query = "detfer+detbig" ;
		} else if ( $det == 'detsun')  {
			$query = "detfer+detbig+detsun" ;
		} else {
			$query = "$det2+$det" ;
		}

		$query = "SELECT id,($query) as thedet FROM pharmaciens ORDER BY thedet DESC, ordre ASC ;" ;
		//echo $query ;
		$res   = mysql_query($query) or die ($query) ;
		for ( $i=0; $i<=$ord ; $i++ )  {
			$pharm = mysql_fetch_row( $res ) ;
		}

		return $pharm[0] ;
	}

	function updatedet( $pharm, $det, $dir)  {
		$query = "UPDATE pharmaciens SET  $det=$det+$dir WHERE  id=$pharm ;" ;
		$res   = mysql_query($query) or die ($query) ;
	}

	function log_page( $text, $page )  {
		if ( $text != "" && $text != " " )  {
			$text = addslashes( $text ) ;
			$query = "INSERT INTO `Pages` ( `text`, `page` ) VALUES ( '$text', '$page' ) ;" ;
			$r     = mysql_query( $query ) ;

			//echo $query."<br>" ;
		}
	}

	function get_name( $pharm )  {
		$query = "SELECT Nom FROM `pharmaciens` WHERE id=$pharm ;" ;
		$result = mysql_query( $query ) or die ( $query ) ;
		$nom = mysql_fetch_row( $result ) ;
		return $nom[0] ;
	}
?>
