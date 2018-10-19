<?php
/*
	Auteur : Ben Souverbie
	Licence : GPLv3
	Usage : non commercial, ce produit ne peut pas être vendu
	Date : 2018-10-17
*/

	// Variables de connexion a  MySQL
	$SERVEUR 	= "localhost";
	$BASE 		= "gardes";
	$IDENTIFIANT	= "gardes";
	$PASS 		= "monsupermotdepasse";

	// Connexion a la base
	$connexion = @mysql_connect($SERVEUR, $IDENTIFIANT, $PASS);
	if (!$connexion)
	{
		echo "IMPOSSIBLE DE SE CONNECTER A LA BASE";
	    exit;
	}
	if (!mysql_select_db ($BASE,$connexion))
	{
		echo "LA BASE $BASE N'EXISTE PAS";
		exit;
	}
?>
