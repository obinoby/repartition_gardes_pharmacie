<?php
/*
	Auteur : Ben Souverbie
	Licence : GPLv3
	Usage : non commercial, ce produit ne peut pas être vendu
	Date : 2018-10-17
*/

        include( "fonctions.php" ) ;

        if (! @$pharmvisible = $_REQUEST['pharm'] )  {
                $pharmvisible = 0 ;
        }

	calgenabo( $pharmvisible ) ;
?>
