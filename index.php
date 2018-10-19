<?php
/*
	Auteur : Ben Souverbie
	Licence : GPLv3
	Usage : non commercial, ce produit ne peut pas être vendu
	Date : 2018-10-17
*/

/*
- Attribuer Noel et jour de l'an en premier
-- Définir la période de grace pour ces jours là, en fonction du nombre de Pharmaciens
- Attribuer les autres jours fériés en tenant compte de la période de grace
-- Définir la période de grace pour les jour fériés classiques, en fonction du nombre de Pharmaciens
- Attribuer par roulement les jours standard en tenant compte des jours de grace
--> PY ne travaille pas le 23/9/13
--> V à Livron ne travaille pas le 23/9/13
*/

	include( "fonctions.php" ) ;

	$query = "SELECT id,nom,preference FROM pharmaciens ;" ;
	$result= mysql_query( $query ) ;

	$nbpharmaciens = 0 ;
	$reload = "" ;
	while( $line = mysql_fetch_row( $result ) ) {
		$pharmaciens[$nbpharmaciens] = $line ;
		$nbpharmaciens++ ;
	}


	if (! @$startyear = $_REQUEST['an'] )  {
		$startyear = date("Y");
	}

	if (! @$pharmvisible = $_REQUEST['pharm'] )  {
		$pharmvisible = 0 ;
	}

	if (! @$getics = $_REQUEST['getics'] )  {
		$getics = 0 ;
	}

	if (! @$change_name = $_REQUEST['change_name'] )  {
		$change_name = "" ;
	}

        if (! @$valider = $_REQUEST['valider'] )  {
                $valider = "nope" ;
        }

	if ( $valider == "Appliquer" & $change_name != "" & $pharmvisible != 0 )  {
		$query = "UPDATE pharmaciens SET nom='$change_name' WHERE id=$pharmvisible ;" ;
		mysql_query( $query ) ;
//		echo $query ;
	}

	$nom_mois[1]  = "Janvier" ;
	$nom_mois[2]  = "F&eacute;vrier" ;
	$nom_mois[3]  = "Mars" ;
	$nom_mois[4]  = "Avril" ;
	$nom_mois[5]  = "Mai" ;
	$nom_mois[6]  = "Juin" ;
	$nom_mois[7]  = "Juillet" ;
	$nom_mois[8]  = "Ao&ucirc;t" ;
	$nom_mois[9]  = "Septembre" ;
	$nom_mois[10] = "Octobre" ;
	$nom_mois[11] = "Novembre" ;
	$nom_mois[12] = "D&eacute;cembre" ;

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
	<head>
		<title>Calendrier des tours de garde</title>
		<link href="style.css" rel="stylesheet" type="text/css" media="screen">
		<link href="print.css" rel="stylesheet" type="text/css" media="print">
	</head>

	<body <?=$reload ; ?>>
<?php

// Choix :
	//echo "<a href='index.php?insert=calcul.php&an=$startyear' target='_parent'>Relancer un calcul &agrave; partir du jour actuel</a> (toutes les gardes &agrave; venir seront modifi&eacute;es)<br>\n" ;
	//echo "<a href='display.php?page=calculs' target='_blank'>Afficher le r&eacute;sum&eacute; du dernier calcul</a><br><br>\n" ;

//cartouche d'en-tête
	$heading = "<a href='index.php?an=".($startyear-1)."&pharm=$pharmvisible'><b>".($startyear-1)."</b></a>\n - &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span id='titre'>$startyear</span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; - \n<a href='index.php?an=".($startyear+1)."&pharm=$pharmvisible'><b>".($startyear+1)."</b></a><br>\n" ;

	if ( 0 == $pharmvisible )  {
//		$heading .= "<a href='index.php?an=$startyear&pharm=0'><b>Aucun</b></a>" ;
		$selected = "selected" ;
	} else {
//		$heading .= "<a href='index.php?an=$startyear&pharm=0'>Aucun</a>" ;
		$selected = "" ;
	}

	$heading .= "<form method='get'>\n" ;
	$heading .= "	<intput type='hidden' name='an' value='$startyear'>\n" ;
//	$heading .= "	<intput type='hidden' name='pharm' value='$pharmvisible'>\n" ;
	$heading .= "	<intput type='hidden' name='getics' value='$getics'>\n" ;

	$heading .= "		<select name='pharm' onchange='this.form.submit()'>\n" ;
	$heading .= "			<option value='0' $selected>Aucun</option>\n" ;
	$query = "SELECT id, nom FROM pharmaciens ORDER BY nom ASC ;" ;
	$result= mysql_query( $query ) ;
	$ics = false ;
	$formulaire = "" ;
	while ( list($id,$nom) = mysql_fetch_row( $result ) )  {
		if ( $id == $pharmvisible )  {
			$selected = "selected" ;
			$formulaire = "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type='text' name='change_name' value='$nom'>" ;
			$ics = calgen($pharmvisible,$startyear) ;
		} else {
			$selected = "" ;
//			$heading .= " - <a href='index.php?an=$startyear&pharm=$id'>$nom</a>" ;
		}
		$heading .= "			<option value='$id' $selected>$nom</option>\n" ;
	}
	$heading .= "		</select>" ;

	if ( $pharmvisible != 0 )  {
		$heading .= $formulaire ;
		$heading .= " - <input type='submit' name='valider' value='Appliquer'>" ;
//		$heading .= "</form>" ;
	}
	$heading .= "</form>" ;

	$heading .= "<br>\n\n" ;

	echo $heading ;
	if ( $ics )  {
		echo "<a href='ics/$pharmvisible/cal$startyear.ics'>T&eacute;l&eacute;charger le calendrier de l'ann&eacute;e au format ICS</a><br>\n" ;
	}
	echo "<a href='gencal_abo.php?pharm=$pharmvisible'>Lien pour abonnement ICS dynamique</a><br>\n\n" ;
	echo "<a href='calcul.php?startyear=$startyear' target='_blank'>Lancer la génération pour les prochaines années</a><br>\n\n" ;

	$nbcol = 2 ;
	$nblig = 3 ;

	echo "<table class='content' width='600'>\n <tr>\n" ;
	for ($mois=1 ; $mois<=12 ; $mois++ )  {
		echo "  <td valign='top'>\n" ;
		$jour = 1 ;
		$firstday = date('N', mktime(0,0,0,$mois,1,$startyear)) ;
		$lastday  = date('t', mktime(0,0,0,$mois,1,$startyear)) ;
		$monthnm  = date('M', mktime(0,0,0,$mois,1,$startyear)) ;


		//echo "<div id='calendar'>\n" ;
		echo "<br><b>".$nom_mois[$mois]." $startyear</b><br>\n" ;
?>
		<table class='calendar'>
			<tr>
				<td width='150'>Lun</td>
				<td width='150'>Mar</td>
				<td width='150'>Mer</td>
				<td width='150'>Jeu</td>
				<td width='150'>Ven</td>
				<td width='150'>Sam</td>
				<td width='150'>Dim</td>
			</tr>
<?php
		$firstline = 0 ;
		for ($l=0 ; $l<6 ; $l++ )  {
			echo "			<tr>\n" ;
			for ($d=0 ; $d<7 ; $d++ )  {
				$day = date('d', mktime(0,0,0,$mois,$jour-$firstday+1,$startyear)) ;
				$m   = date('n', mktime(0,0,0,$mois,$jour-$firstday+1,$startyear)) ;

				$date = date('Y-m-d', mktime(0,0,0,$mois,$jour-$firstday+1,$startyear)) ;
				$query = "SELECT type, pharmacien, nom FROM calendrier, pharmaciens WHERE pharmaciens.id=calendrier.pharmacien AND date='$date' ;" ;
				$result= mysql_query( $query ) ;
				list( $type, $pharm, $nom ) = mysql_fetch_row( $result ) ;

				$paire = get_paire( $startyear , $mois , $jour-$firstday+1 ) ;
				date('W',mktime(0,0,0,$mois,$jour-$firstday+1,$startyear)) % 2 ;
				if ( $paire == 0 )  {
					$colour = "#AAFFAA" ;
				} else {
					$colour = "#DDFFDD" ;
				}
				if ( $pharm == $pharmvisible || $pharmvisible == 0 )  {
					$cotext = "#4433DD" ;
				} else {
					$cotext = "#CFCFCF" ;
				}

				if ( $m != $mois )  {
					$colour = "#DDDDDD" ;
					$cotext = "#CFCFCF" ;
				} else {
					$date = date('Y-m-d', mktime(0,0,0,$mois,$jour-$firstday+1,$startyear)) ;
					$query = "SELECT type, pharmacien, nom FROM calendrier, pharmaciens WHERE pharmaciens.id=calendrier.pharmacien AND date='$date' ;" ;
					//$query = "SELECT type, pharmacien FROM calendrier WHERE date='$date' ;" ;
					$result= mysql_query( $query ) ;
					list( $type, $pharm, $nom ) = mysql_fetch_row( $result ) ;
					if ($type != 0)  {
						$red = dechex(16-$type*4) ;
						$colour="#FF".$red.$red.$red.$red ;
					}
				}

				echo "				<td bgcolor='$colour' valign='top' width='150' height='50'><font color='$cotext'>$day<br><b>" ;
				if ($pharm > 0 )  {
					echo $nom ;
					//echo $pharm ;
				}
				echo "</b></font></td>\n" ;
				$jour++;
			}
			echo "			</tr>\n" ;
		}
		echo "		</table>\n" ;
		//echo "</div>\n" ;
		echo "  </td>\n" ;
		if ( $mois%$nbcol === 0 )  {
			echo " </tr>\n <tr>\n" ;
		} else {
			echo "  <td>&nbsp;&nbsp;</td>\n" ;
		}
		if ( $mois%($nblig*$nbcol) === 0 && $mois+($nbcol*$nblig)<12)  {
			echo " </tr>\n</table>\npage<br>\n<table class='content' width='600'>\n <tr>\n" ;
		} elseif ( $mois%($nblig*$nbcol) === 0  && $mois<12)  {
			echo " </tr>\n</table>\n<br>$heading\n<table width='600'>\n <tr>\n" ;
		} elseif ( $mois%($nblig*$nbcol) === 0 ) {
			echo " </tr>\n</table>\n" ;
		}
	}
?>

	</body>
</html>
