#!/bin/sh

########### Init ##############
FROM="gardes@mondomaine.net"
SUBJECT="[GARDES] Votre tour de garde commence aujourd'hui"
########### Fin init ##########

TODAY=`date +%Y-%m-%d`

MYSQL_SERVER=$(cat connect.php | awk '{$1=$1};1' | grep "^\$SERVEUR"     | cut -d\" -f2 | cut -d\" -f1)
MYSQL_BASE=$(  cat connect.php | awk '{$1=$1};1' | grep "^\$BASE"        | cut -d\" -f2 | cut -d\" -f1)
MYSQL_IDENT=$( cat connect.php | awk '{$1=$1};1' | grep "^\$IDENTIFIANT" | cut -d\" -f2 | cut -d\" -f1)
MYSQL_PASS=$(  cat connect.php | awk '{$1=$1};1' | grep "^\$PASS"        | cut -d\" -f2 | cut -d\" -f1)

NOM=`mysql -h $MYSQL_SERVER --user=${MYSQL_IDENT} --password=${MYSQL_PASS} --skip-column-names -e "SELECT Nom FROM pharmaciens, calendrier WHERE calendrier.pharmacien=pharmaciens.id AND calendrier.date='$TODAY'" $MYSQL_BASE`
ID=`mysql  -h $MYSQL_SERVER --user=${MYSQL_IDENT} --password=${MYSQL_PASS} --skip-column-names -e "SELECT id  FROM pharmaciens, calendrier WHERE calendrier.pharmacien=pharmaciens.id AND calendrier.date='$TODAY'" $MYSQL_BASE`

MSG="<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Transitional//EN\" \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd\">

<html lang=\"en\" xmlns=\"http://www.w3.org/1999/xhtml\">
  <head>
    <title>Alerte D&eacute;but de garde</title>
    <meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\" />
    <meta name=\"author\" content=\"Gardes\" />
    <meta name=\"copyright\" content=\"&copy; gardes.souverbie.fr\" />
    <link rel=\"help\" href=\"http://gardes.souverbie.fr\" />
  </head>
  <body style=\"font-family: Verdana,sans-serif; font-size: 11px;color:#000;\">
  
Bonjour pharmacie $NOM,<br />
<br />
Selon le syst&egrave;me de r&eacute;partition des tours de gardes, vous commencez votre garde ce matin.<br />
Si toutefois vous avez proc&eacute;d&eacute; &agrave; un &eacute;change, merci de vous assurer que votre confr&egrave;re prend bien son tour en lui faisant suivre le présent message.<br />
<br />
Pour prendre votre tour de garde, merci de proc&eacute;der au changement du num&eacute;ro de renvoi d'appel via ce lien :<br />
<a href=\"https://dro.orange.fr/authentification?target=https%3A%2F%2Fb.espaceclientv3.orange.fr%2F?page=octav-voip-ra-premium\">Page de configuration Orange-Pro</a><br />
<br />
Si vous souhaitez consulter le calendrier des tours de gardes, il est disponible via ce lien :<br />
<a href=\"http://gardes.souverbie.fr/?pharm=$ID\">Calendrier</a><br />
<br />
Cordialement,<br />
Votre syst&egrave;me de r&eacute;partition des tours de garde<br />
<br /><br />
<span style=\"font-family: Verdana,sans-serif; font-size: 10px;color:#FAA;\"><u><b>Note importante :</b></u></span><br />
<span style=\"font-family: Verdana,sans-serif; font-size: 10px;color:#AAA;\">Ce syst&egrave;me d'alertes est conçu comme un rappel. Il ne se substitue pas à votre propre organisation.<br />Nous ne saurions &ecirc;tre tenu pour responsable si le tour de garde n'est pas pris en charge pour la seule raison que l'alerte par courriel n'a pas &eacute;t&eacute; donn&eacute;e. En effet, ce syst&egrave;me ne dispose d'aucun secours ; en cas de panne aucune alerte de garde ne sera distribu&eacute;.<br />Merci d'avertir <a href='mailto:gardes@souverbie.fr'>l'administrateur</a> si un dysfonctionnement est remarqu&eacute;</span>
<br /><br />
<span style=\"font-family: Verdana,sans-serif; font-size: 10px;color:#AAA;\"><b>Si vous ne souhaitez plus &ecirc;tre averti par courriel, merci de le notifier &agrave; <a href='mailto:gardes@souverbie.fr'>l'administrateur</a></b></span><br />

   </body>
</html>"

#set -x
LANG=fr_FR

# ARG
TO=`mysql -h $MYSQL_SERVER --user=${MYSQL_IDENT} --password=${MYSQL_PASS} --skip-column-names -e "SELECT mail FROM pharmaciens, calendrier WHERE calendrier.pharmacien=pharmaciens.id AND calendrier.date='$TODAY'" $MYSQL_BASE`


cat <<EOF | /usr/sbin/sendmail -t
From: ${FROM}
To: ${TO}
MIME-Version: 1.0
Content-Type: multipart/mixed; boundary=frontier
Subject: ${SUBJECT}

--frontier
Content-Type: text/html; charset=UTF-8
Content-Transfer-Encoding: 7bit

${MSG}
--frontier--
EOF


