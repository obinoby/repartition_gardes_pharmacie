<?php
/*
	Auteur : Ben Souverbie
	Licence : GPLv3
	Usage : non commercial, ce produit ne peut pas être vendu
	Date : 2018-10-17
*/

	include( "fonctions.php" ) ;

	if ( @ $page = $_REQUEST["page"] )  {
		$query  = "SELECT id, text FROM Pages WHERE page='$page' ORDER BY id ASC ;" ;
		$result = mysql_query( $query ) or die ( $query ) ;

		while ( list( $id, $text )=mysql_fetch_row( $result ) )  {
			echo stripslashes($text) ;
		}
	}
?>
