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
		$startyear = 2014;
	}

	if (! @$pharmvisible = $_REQUEST['pharm'] )  {
		$pharmvisible = 0 ;
	}

	if ( @$submit = $_REQUEST['Valider'] )  {
		$day = $_REQUEST['day'] ;
		$sun = $_REQUEST['sun'] ;
		$fer = $_REQUEST['fer'] ;
		$fer2= $_REQUEST['fer2'] ;
		$big = $_REQUEST['big'] ;

		$q = "UPDATE graces SET grace=$day WHERE type='detday' ;" ;
		$r = mysql_query( $q ) or die ($q) ;
		$q = "UPDATE graces SET grace=$day WHERE type='detlun' ;" ;
		$r = mysql_query( $q ) or die ($q) ;
		$q = "UPDATE graces SET grace=$day WHERE type='detmar' ;" ;
		$r = mysql_query( $q ) or die ($q) ;
		$q = "UPDATE graces SET grace=$day WHERE type='detmer' ;" ;
		$r = mysql_query( $q ) or die ($q) ;
		$q = "UPDATE graces SET grace=$day WHERE type='detjeu' ;" ;
		$r = mysql_query( $q ) or die ($q) ;
		$q = "UPDATE graces SET grace=$day WHERE type='detven' ;" ;
		$r = mysql_query( $q ) or die ($q) ;

		$q = "UPDATE graces SET grace=$sun WHERE type='detsun' ;" ;
		$r = mysql_query( $q ) or die ($q) ;

		$q = "UPDATE graces SET grace=$big WHERE type='detbig' ;" ;
		$r = mysql_query( $q ) or die ($q) ;

		$q = "UPDATE graces SET grace=$fer, grace2=$fer2 WHERE type='detfer' ;" ;
		$r = mysql_query( $q ) or die ($q) ;
	}

	if ( @$insert = $_REQUEST['insert'] )  {
		$reload = " onLoad='window.location.href = \"index.php?an=$startyear\";'" ;
		include( $insert ) ;
	}


?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
	<head>
		<title>Calendrier des tours de garde</title>
		<link href="style.css" rel="stylesheet" type="text/css">
	</head>

	<body <?=$reload ; ?>>
<?php

// Choix :
	//echo "<a href='index.php?insert=calcul.php&an=$startyear' target='_parent'>Relancer un calcul &agrave; partir du jour actuel</a> (toutes les gardes &agrave; venir seront modifi&eacute;es)<br>\n" ;
	echo "<a href='display.php?page=calculs' target='_blank'>Afficher le r&eacute;sum&eacute; du dernier calcul</a><br><br>\n" ;

//cartouche d'en-tête
	echo "<a href='index.php?an=".($startyear-1)."&pharm=$pharmvisible'><b>".($startyear-1)."</b></a> - " ;
	echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Le calendrier de l'ann&eacute;e $startyear avec $nbpharmaciens pharmaciens :&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;" ;
	echo " - <a href='index.php?an=".($startyear+1)."&pharm=$pharmvisible'><b>".($startyear+1)."</b></a><br>" ;


	// Cartouche sur l'annee en cours
	echo "<div id='cartouche'>\n" ;
	echo "Affichage sur l'ann&eacute;e choisie<br>\n" ;
	echo "<table class='cartouche'>\n" ;
	echo "  <tr>\n    <td class='cartouche'><b>Nom</b></td>\n    <td class='cartouche'><b>Lundi</b></td>\n    <td class='cartouche'><b>Mardi</b></td>\n    <td class='cartouche'><b>Mercredi</b></td>\n" ;
	echo "    <td class='cartouche'><b>Jeudi</b></td>\n    <td class='cartouche'><b>Vendredi</b></td>\n    <td class='cartouche'><b>Samedi</b></td>\n    <td class='cartouche'><b>Dimanche</b></td>\n    <td class='cartouche'><b>F&eacute;ri&eacute;</b></td>\n  </tr>\n" ;

	$query = "SELECT id,nom FROM pharmaciens ORDER BY nom ASC ;" ;
	$res= mysql_query( $query ) ;

	while( list( $id, $nom ) = mysql_fetch_row( $res ) ) {
		echo "  <tr>\n    <td>$nom</td>\n" ;

		$query  = "SELECT `day`,count(date) FROM `calendrier` WHERE pharmacien=$id AND type<2 AND `date` LIKE '$startyear-%' GROUP BY day ORDER BY day ASC ;" ;
		$result = mysql_query( $query ) ;
		$ok = true ;
		for ( $i=1 ; $i<8 ; $i++ )  {
			if ( $ok )  {
				list( $day, $nb ) = mysql_fetch_row( $result ) ;
			}
			$ok=false ;
			if ( $day == $i ) {
				echo "    <td>$nb</td>\n" ;
				$ok=true ;
			} else {
				echo "    <td></td>\n" ;
			}
		}

		$query  = "SELECT count(date) FROM `calendrier` where pharmacien=$id and type>1 AND `date` LIKE '$startyear-%' ;" ;
		$result = mysql_query( $query ) ;
		list( $nb ) = mysql_fetch_row( $result ) ;
		echo "    <td>$nb</td>\n" ;

		echo "  </tr>\n" ;
	}
	echo "</table>\n" ;
	echo "</div>\n" ;


	// Cartouche global
	echo "<div id='cartouche'>\n" ;
	echo "Affichage global<br>\n" ;
	echo "<table class='cartouche'>\n" ;
	echo "  <tr>\n    <td class='cartouche'><b>Nom</b></td>\n    <td class='cartouche'><b>Lundi</b></td>\n    <td class='cartouche'><b>Mardi</b></td>\n    <td class='cartouche'><b>Mercredi</b></td>\n" ;
	echo "    <td class='cartouche'><b>Jeudi</b></td>\n    <td class='cartouche'><b>Vendredi</b></td>\n    <td class='cartouche'><b>Samedi</b></td>\n    <td class='cartouche'><b>Dimanche</b></td>\n    <td class='cartouche'><b>F&eacute;ri&eacute;</b></td>\n  </tr>\n" ;

	$query = "SELECT id,nom FROM pharmaciens ORDER BY nom ASC ;" ;
	$res= mysql_query( $query ) ;

	while( list( $id, $nom ) = mysql_fetch_row( $res ) ) {
		echo "  <tr>\n    <td><a href='index.php?an=$startyear&pharm=$id'>$nom</a></td>\n" ;

		$query  = "SELECT `day`,count(date) FROM `calendrier` WHERE pharmacien=$id AND type<2 AND `date`>'2013-12-31' GROUP BY day ORDER BY day ASC ;" ;
		$result = mysql_query( $query ) ;
		$ok = true ;
		for ( $i=1 ; $i<8 ; $i++ )  {
			if ( $ok )  {
				list( $day, $nb ) = mysql_fetch_row( $result ) ;
			}
			$ok=false ;
			if ( $day == $i ) {
				echo "    <td>$nb</td>\n" ;
				$ok=true ;
			} else {
				echo "    <td></td>\n" ;
			}
		}

		$query  = "SELECT count(date) FROM `calendrier` where pharmacien=$id and type>1 AND `date`>'2013-12-31' ;" ;
		$result = mysql_query( $query ) ;
		list( $nb ) = mysql_fetch_row( $result ) ;
		echo "    <td>$nb</td>\n" ;

		echo "  </tr>\n" ;
	}
	echo "</table>\n" ;
	echo "</div>\n" ;
// Fin du cartouche
?>
	Mode de calcul :<br>
	<form method='get' action='index.php'>
		<input type='hidden' name='an' value='<?=$startyear; ?>'>
		<input type='hidden' name='insert' value='calcul.php'>
	<table class='cartouche'>
		<tr>
			<td class='cartouche'>Type</td>
			<td class='cartouche'>P&eacute;riode de grace / m&ecirc;me type (jours)</td>
			<td class='cartouche'>P&eacute;riode de grace / importants (jours)</td>
		</tr>
<?php
	$q = "SELECT domapp,grace FROM graces WHERE type='detday' ;" ;
	$r = mysql_query( $q ) or die ($q) ;
	list($domapp,$grace) = mysql_fetch_row( $r ) ;
?>
		<tr>
			<td>Jours normaux</td><td><input type='text' name='day' value='<?=$grace ; ?>'></td>
		</tr>
<?php
	$q = "SELECT domapp,grace FROM graces WHERE type='detsun' ;" ;
	$r = mysql_query( $q ) or die ($q) ;
	list($domapp,$grace) = mysql_fetch_row( $r ) ;
?>
		<tr>
			<td>Week-Ends</td><td><input type='text' name='sun' value='<?=$grace ; ?>'></td>
		</tr>
<?php
	$q = "SELECT domapp,grace,domapp2,grace2 FROM graces WHERE type='detfer' ;" ;
	$r = mysql_query( $q ) or die ($q) ;
	list($domapp,$grace,$domapp2,$grace2) = mysql_fetch_row( $r ) ;
?>
		<tr>
			<td>Jours f&eacute;ri&eacute;s</td>
			<td><input type='text' name='fer'  value='<?=$grace ; ?>'></td>
			<td><input type='text' name='fer2' value='<?=$grace2 ; ?>'></td>
		</tr>
<?php
	$q = "SELECT domapp,grace FROM graces WHERE type='detbig' ;" ;
	$r = mysql_query( $q ) or die ($q) ;
	list($domapp,$grace) = mysql_fetch_row( $r ) ;
?>
		<tr>
			<td>Jours importants</td><td><input type='text' name='big' value='<?=$grace ; ?>'></td>
		</tr>
		<tr>
			<td colspan=3><input type='submit' name='Valider' value='ok'></td>
	</table>
	</form>


<?php
	for ($mois=1 ; $mois<=12 ; $mois++ )  {
		$jour = 1 ;
		$firstday = date('N', mktime(0,0,0,$mois,1,$startyear)) ;
		$lastday  = date('t', mktime(0,0,0,$mois,1,$startyear)) ;
		$monthnm  = date('M', mktime(0,0,0,$mois,1,$startyear)) ;


		echo "<div id='calendar'>\n" ;
		echo "Mois : $monthnm - $startyear<br>\n" ;
?>
		<table class='calendar' width='400' height='400'>
			<tr>
				<td>Lun</td>
				<td>Mar</td>
				<td>Mer</td>
				<td>Jeu</td>
				<td>Ven</td>
				<td>Sam</td>
				<td>Dim</td>
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
					$colour = "#AFA" ;
				} else {
					$colour = "#DFD" ;
				}
				if ( $pharm == $pharmvisible || $pharmvisible == 0 )  {
					$cotext = "#43D" ;
				} else {
					$cotext = "#CFCFCF" ;
				}

				if ( $m != $mois )  {
					$colour = "#DDD" ;
					$cotext = "#CFCFCF" ;
				} else {
					$date = date('Y-m-d', mktime(0,0,0,$mois,$jour-$firstday+1,$startyear)) ;
					$query = "SELECT type, pharmacien, nom FROM calendrier, pharmaciens WHERE pharmaciens.id=calendrier.pharmacien AND date='$date' ;" ;
					//$query = "SELECT type, pharmacien FROM calendrier WHERE date='$date' ;" ;
					$result= mysql_query( $query ) ;
					list( $type, $pharm, $nom ) = mysql_fetch_row( $result ) ;
					if ($type != 0)  {
						$red = dechex(16-$type*4) ;
						$colour="#F".$red.$red ;
					}
				}

				echo "				<td bgcolor='$colour' valign='top'><font color='$cotext'>$day<br><b>" ;
				if ($pharm > 0 )  {
					echo $nom ;
					//echo $pharm ;
				}
				echo "</b></font></td>\n" ;
				$jour++;
			}
			echo "			</tr>\n" ;
		}
		echo "		</table>\n<br></div>\n" ;
	}
	echo "<div id='calendar'>\n" ;
	echo "<a href='index.php?an=".($startyear-1)."'><b>".($startyear-1)."</b></a> - " ;
	echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Le calendrier de l'ann&eacute;e $startyear avec $nbpharmaciens pharmaciens :&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;" ;
	echo " - <a href='index.php?an=".($startyear+1)."'><b>".($startyear+1)."</b></a><br>" ;
	echo "</div>" ;
?>

	</body>
</html>
