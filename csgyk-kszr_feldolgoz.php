<?php // ini_set("display_errors", "on"); error_reporting(E_ALL); ?>

<?php
// a WP csatlakozik a BKSZR adattáblázathoz
$konyvtar_nev = $_POST["konyvtar_nev"];
 $konyvtar_cim = $_POST["konyvtar_cim"] ;
	$konyvtar_telefonszam = $_POST["konyvtar_telefonszam"];
	 $konyvtar_email = $_POST["konyvtar_email"];
		$konyvtar_www = $_POST["konyvtar_www"];
		 $konyvtaros_nev = $_POST["konyvtaros_nev"] ;
			$konyvtar_nyitvatartas = $_POST["konyvtar_nyitvatartas"];
			 $beiratkozottak = $_POST["beiratkozottak"];
				$hasznalok = $_POST["hasznalok"];
				  $megjegyzes = $_POST["megjegyzes"];
					$referens_nev = $_POST["referens_nev"];
					 $telepules_www = $_POST["telepules_www"];
           $kszr_id_misi = $_POST["kszr_id_misi"];
           $konyvtar_kezi_allomany = $_POST["allomany_kezi"];


?>
<?php
/*
 print "konyvtar_nev = " .  $konyvtar_nev . "<br>";
 print "konyvtar_cim = " .  $konyvtar_cim . "<br>";
	print "konyvtar_telefonszam = " .  $konyvtar_telefonszam . "<br>";
	 print "konyvtar_email = " .  $konyvtar_email . "<br>";
		print "konyvtar_www = " .  $konyvtar_www . "<br>";
		 print "konyvtaros_nev = " .  $konyvtaros_nev . "<br>";
			print "konyvtar_nyitvatartas = " .  $konyvtar_nyitvatartas . "<br>";
			 print "beiratkozottak = " .  $beiratkozottak . "<br>";
				print "hasznalok = " .  $hasznalok . "<br>";
				 print "megjegyzes = " .  $megjegyzes . "<br>";
					print "referens_nev = " .  $referens_nev . "<br>";
					 print "telepules_www = " .  $telepules_www . "<br>";
*/
include_once('../../../wp-config.php');
$conn  = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
// Test the connection:
if (mysqli_connect_errno()){
    // Connection Error
    exit("Couldn't connect to the database: ".mysqli_connect_error());
}



// codex.wordpress.org/Class_Reference/wpdb#Examples
	$adatok_frissites_sql =
	"UPDATE `bkszr_adatok` SET
	`konyvtar_nev` = '$konyvtar_nev',
	 `konyvtar_cim` = '$konyvtar_cim',
	  `konyvtar_telefonszam` = '$konyvtar_telefonszam',
		 `konyvtar_email` = '$konyvtar_email',
		  `konyvtar_www` = '$konyvtar_www',
			 `konyvtaros_nev` = '$konyvtaros_nev',
			  `konyvtar_nyitvatartas` = '$konyvtar_nyitvatartas',
				 `beiratkozottak` = '$beiratkozottak',
         `allomany_kezi` = '$konyvtar_kezi_allomany',
				  `hasznalok` = '$hasznalok',
					 `megjegyzes` = '$megjegyzes',
					  `referens_nev` = '$referens_nev',
						 `telepules_www` = '$telepules_www'
						 WHERE `bkszr_adatok`.`misi_id` = $kszr_id_misi";
// UTF8-ra állítja a dolgokat :)
mysqli_set_charset($conn,"utf8");
if ($conn->query($adatok_frissites_sql) === TRUE) {
    echo "A frissítés folyamatban... " . "<br><br> Kész!";

// visszaugik az szerkesztési felületre - pure js + prue php -jeee :P
header('Location: ' . $_SERVER['HTTP_REFERER']);

} else {
    echo "Vedd fel a kapocslatot Földesi Mihállyal!<br><br> Hibaüznet: " . $conn->error;

    print "konyvtar_nev = " .  $konyvtar_nev;
 print "konyvtar_cim = " .  $konyvtar_cim;
	print "konyvtar_telefonszam = " .  $konyvtar_telefonszam;
	 print "konyvtar_email = " .  $konyvtar_email;
		print "konyvtar_www = " .  $konyvtar_www;
		 print "konyvtaros_nev = " .  $konyvtaros_nev;
			print "konyvtar_nyitvatartas = " .  $konyvtar_nyitvatartas;
      print "konyvtar_kezi_allomany = " .  $konyvtar_kezi_allomany;

			 print "beiratkozottak = " .  $beiratkozottak;
				print "hasznalok = " .  $hasznalok;
				 print "megjegyzes = " .  $megjegyzes;
					print "referens_nev = " .  $referens_nev;
					 print "telepules_www = " .  $telepules_www;
}

$conn->close();

?>
