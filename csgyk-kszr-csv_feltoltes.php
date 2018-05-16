
<head>
<meta charset="UTF-8">
</head>
<body><p>Ez nem CSV kiterjesztés! Kérlek, válassz egy másik fájlt!</p>
  <button onclick="goBack()">Ugrás vissza!</button>

<script>
function goBack() {
    window.history.back();
}
</script>
<?php
$tablazat_neve = "bkszr_forras";
include_once('../../../wp-config.php');
//$dbConnection  = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
// Test the connection:
if (mysqli_connect_errno()){
    // Connection Error
    exit("Couldn't connect to the database: ".mysqli_connect_error());
}
/*
	define('_DB_HOST_NAME','localhost');
	define('_DB_USER_NAME','root');
	define('_DB_PASSWORD','');
	define('_DB_DATABASE_NAME','csorba100');
*/
	 $dbConnection = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);


if(isset($_POST['submit'])){
		if($_FILES['csv_data']['name']){
			$arrFileName = explode('.',$_FILES['csv_data']['name']);
			if($arrFileName[1] == 'csv'){
				// kiüríti a táblázatot, mielőtt feltöltődnének az újabb sorok...
				$sql_kiurites = "TRUNCATE TABLE $tablazat_neve; ";
				mysqli_query($dbConnection,$sql_kiurites);

				$handle = fopen($_FILES['csv_data']['tmp_name'], "r");
				while (($data = fgetcsv($handle, 1000, ";")) !== FALSE) {
					$item1 = mysqli_real_escape_string($dbConnection,$data[1]); // település kód
          $item2 = mysqli_real_escape_string($dbConnection,$data[2]); // település név
					$item3 = mysqli_real_escape_string($dbConnection,$data[3]); // állomány
					// $item4 = mysqli_real_escape_string($dbConnection,$data[0]); // id

					$import="INSERT into $tablazat_neve(telepules_kod,telepules_nev,allomany) values('$item1','$item2','$item3')";

					mysqli_query($dbConnection,$import);
				}
				fclose($handle);
				print "Az importálás elkészült az ideiglenes táblázatba.";
        header("Location: /wp-admin/admin.php?page=bkszr-adatfrissito-csgyk&feltoltve=ok");

			}
		}
	}
?>
<!--
<form method="POST" enctype="multipart/form-data" action="<?php print plugins_url('csgyk-kszr-csv_feltoltes.php', __FILE__ ); ?>" id="misike">
	Upload CSV: <input type="file" name="csv_data" /> <input type="submit" name="submit" value="import" />
</form>
-->
</body>
