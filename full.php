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

	$start = $_REQUEST["start"] ;
	$end   = $_REQUEST["end"  ] ;

	$query = "SELECT type, pharmacien, nom, date FROM calendrier, pharmaciens WHERE pharmaciens.id=calendrier.pharmacien AND date>='$start' AND date<='$end' ORDER BY date ASC ;" ;
	$result= mysql_query( $query ) or die ( $query ) ;
	$c = 0 ;
	while ( list( $type, $pharm, $nom, $date ) = mysql_fetch_row( $result ) )  {
		//echo "$date - $type - $pharm - $nom <br>" ;
		if ( $c == 0 )  {
			echo "[" ;
		} else {
			echo ",
" ;
		}
		echo "{
	title: '$nom',
	start: '$date',
	color: '#F00'
}" ;
		$c++ ;
	}
	echo "]" ;
?>
