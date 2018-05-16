<?php
/**
* Plugin Name: BKSZR-adatfrissítő | CSGYK
* Plugin URI: http://www.csgyk.hu
* Description: Ez a beépülőmodul segíti a Corvina-ban található BKSZR adatok frissítését és összekapcsolását a WP-ben
* Version: 0.96.1
* Author: Földesi Mihály
* Author URI: http://www.FoldesiStudio.hu
* License: A "Slug" license name e.g. GPL12
*/

// Plugin verzió száma: https://wordpress.stackexchange.com/questions/361/is-there-a-way-for-a-plug-in-to-get-its-own-version-number/371#371

/* ----- Alapfunkciók | eleje ----- */
function unicode_konvertalas_vedelem($email) {
	// forrás: http://stackoverflow.com/questions/5609604/good-e-mail-link-protection-methods
if (!empty($email))
//az azért van "!empty", mert a mail címnél, ha nem volt megadva kérdőjelet dobott vissza a PHP vala...
 {

    $p = str_split(trim($email));
    $new_mail = '';
    foreach ($p as $val) {
        $new_mail .= '&#'.ord($val).';';
    }
    return $new_mail;
  }

}
// Linket gereál a szövegből....
function domain_linkké($www_url) {
if (!empty($www_url)) {

$_link_eleje = '<a href="' . $www_url . '" title="Klikk ide a weboldal új abalkban való megnyításához!" target="_blank">';
 $_link_vege = '</a>';
  }
    return $_link_eleje . $www_url . $_link_vege;
  }

// Ha van értéke az adott ellátársi térség baloldali táblázatában, csak akkor jeleniti meg azt
// pl.: http://bkszr.csgyk.hu/abaliget/
function adatlapelem_megjelenítés($adatlap_mezonev, $adatlap_mezoertek) {
	//ötlet: Földesi Mihály ----------------------------------------------------------------

	if (!empty($adatlap_mezoertek)) {
			//xor !empty($adatlap_mezoertek)
			// létrehozzuk a táblázatban a sorokat
		return   '<tr>
    <th scope="row">' . $adatlap_mezonev . ':</th>
    <td>' . $adatlap_mezoertek . '</td>
</tr>';

	}
	else {

	}
}


/* ----- Alapfunkciók | vége  ----- */

/* ----- ADMIN menü | eleje ----- */
add_action("admin_menu", "bkszr_plugin_menu");

function bkszr_plugin_menu() {

	$plugin_nev 		= "BKSZR-adatfrissítő";
	$plugin_azonosito	= "bkszr-adatfrissito-csgyk";
	$ikon = ""; //pl: /wp-content/plugins/csgyk-kszr/kepek/FROG_emoji_icon_png_grande.png
	add_menu_page($plugin_nev, $plugin_nev, "administrator", $plugin_azonosito, "bkszr_plugin_admin_tartalom", $icon_url = $ikon);

}
// mysql adartbázisba regisztráljuk az értékeket
function bkszr_plugin_beallitasok_regisztralasa()  {
	register_setting("kszr_beallitasok_csoportja", "bkszr_plugin_beallitasok");

	}
add_action("admin_init", "bkszr_plugin_beallitasok_regisztralasa");

// globálist csinálunk, és késöbb ezt meghívjuk
$mfwp_beallitasok = get_option("bkszr_plugin_beallitasok") ;

function bkszr_plugin_admin_tartalom() {
get_option("bkszr_plugin_beallitasok");

global $mfwp_beallitasok;

ob_start();
	?>
	<div class="wrap">
<h1> BKSZR-adatfrissítő | általános beállítások</h1>
<p>Ez a beépülőmodul segíti a Corvina-ban található BKSZR adatok frissítését és összekapcsolását a WP-ben.
Hamarosan további beállítások érhetőek el. Fejlesztés alatt....<br>Üdv, Földesi Mihály</p>
<!-- <p>Ne nyomd meg az alábbi gombot!</p> -->
<h4>Utoljára frissítette az adatbázist:</h4>
<div class="profile-card">
<p><?php $user_info = get_userdata($mfwp_beallitasok["frissitesi_id"]);
      echo 'Név: ' . $user_info->user_lastname . " " . $user_info->user_firstname .  "<br>\n";
      echo 'Szerepkör: ' . implode(', ', $user_info->roles) . "<br>\n";
    //  echo 'User ID: ' . $user_info->ID . "\n";
			echo 'Idő: ' .  $mfwp_beallitasok["frissitesi_ido"] . "\n";

?>
</p>
</div>
<?php if ( $_GET["feltoltve"] == "ok" ) {

//idekerül a mysql update rész

 ?>
<form method="post" action="options.php" id="firstform">

	<?php settings_fields("kszr_beallitasok_csoportja"); ?>

	<h4>A Corvina CSV-fájl felöltése sikeres!</h4>
	<?php
	// forrás: https://codex.wordpress.org/Function_Reference/wp_get_current_user
	    $current_user = wp_get_current_user();
	    /**
	     * @example Safe usage: $current_user = wp_get_current_user();
	     * if ( !($current_user instanceof WP_User) )
	     *     return;
	     */
	    /*echo 'Felhasználónév: ' . $current_user->user_login . '<br />';
	    echo 'Felhasználó email: ' . $current_user->user_email . '<br />';
	    echo 'User first name: ' . $current_user->user_firstname . '<br />';
	    echo 'User last name: ' . $current_user->user_lastname . '<br />';
	    echo 'User display name: ' . $current_user->display_name . '<br />';
	    echo 'User ID: ' . $current_user->ID . '<br />';
			*/
	?>
	<input id="bkszr_plugin_beallitasok[frissitesi_id]" name="bkszr_plugin_beallitasok[frissitesi_id]" type="text" value="<?php echo $current_user->ID ;  ?>" hidden>
	<input id="bkszr_plugin_beallitasok[frissitesi_ido]" name="bkszr_plugin_beallitasok[frissitesi_ido]" type="text" value="<?php echo date("Y. m. d.");?>" hidden>
<p class="submit">
	<input type="submit" name="submit" id="submit" class="button button-primary" value="Módosítások mentése az adatbázisba">
</p>


</form>
<?php

// Az adattábla frissítése:

// http://stackoverflow.com/questions/13936448/wordpress-update-mysql-table
global $wpdb;
$execut= $wpdb->query( $wpdb->prepare( "UPDATE bkszr_forras
       JOIN bkszr_adatok
       ON bkszr_forras.telepules_kod = bkszr_adatok.telepules_kod
SET    bkszr_adatok.allomany = bkszr_forras.allomany" ) );
print "<!-- Eredmény: ";
var_dump($execut); // eredmény: hánydarab érték frissült
print " db rekord lett frissítve. -->";
} // --> vége: admin.php?page=bkszr-adatfrissito-csgyk&feltoltve=ok
else {
	?>
<!-- CSV-fájlfeltöltő rész | eleje -->
<h4>Frissítés:</h4>

<form method="POST" enctype="multipart/form-data" action="<?php print plugins_url('csgyk-kszr-csv_feltoltes.php', __FILE__ ); ?>" id="test_form" name="test_form">
	<b>Corvina CSV-fájl:</b> <input type="file" name="csv_data" accept=".csv" required />

	<input type="submit" name="submit" value="Feltöltés"  class="button button-primary" />

</form>

<h4>A Corvina <a href="http://corvina.tudaskozpont-pecs.hu:8080/WebStart/CAT/Manager.jnlp">Manager-modul</a> lekérdező kódja:</h4>
<SCRIPT LANGUAGE="JavaScript">

function ClipBoard()
{
holdtext.innerText = copytext.innerText;
Copied = holdtext.createTextRange();
Copied.execCommand("RemoveFormat");
Copied.execCommand("Copy");
}

</SCRIPT>
<pre>
	select currloc as telepules_kod,
	long_name as telepules_nev,
  count (itembarcode) as allomany
      from
      itemstatus, location
       where
	itemstatus.currloc = location.short_name
        and currloc like 'P4/KT%'
	group by currloc, long_name
</pre>
<button class="btn" data-clipboard-action="copy" data-clipboard-target="pre">A kód másolása</button>
    <!-- 2. Include library -->
    <script src="<?php print plugins_url('betoltendo/clipboard.min.js', __FILE__ ); ?>"></script>

    <!-- 3. Instantiate clipboard -->
    <script>
    var clipboard = new Clipboard('.btn');

    clipboard.on('success', function(e) {
        console.log(e);

    });

    clipboard.on('error', function(e) {
        console.log(e);
    });
    </script>

<h4>Változáskövetés (changelog):</h4>
<textarea rows="9" cols="120" readonly>
<?php  }
include_once plugin_dir_path( __FILE__ ) . "changelog.txt";
 ?>
</textarea>

<!-- CSV-fájlfeltöltő rész | vége -->








</div>

	<?php
	echo ob_get_clean();
}
// https://www.youtube.com/watch?v=-WLsE2SNEqM
/* 	----- ADMIN menü | vége ----- */


/*
Upload CSV and Insert into Database Using PHP:
http://www.stepblogging.com/upload-csv-and-insert-into-database-using-php/

utoljára mentve adat mentése az option táblába:
https://youtu.be/zmMbaWz-hvI?t=8m29s

minta menüszerkezet:
https://codex.wordpress.org/Administration_Menus#Using_add_submenu_page
* /

/* 	----- Nyilvános oldalon az adatok listázása | eleje ----- */

function bkszr_plugin_oldal_plussz_tartalom($content) {
//https://www.youtube.com/watch?v=M6paHYuyYzQ

$id = get_the_ID(); // elkéri az adott bejegyzés id-jét...
$kategoria_meghatarozasa = get_the_category( $id );

// if(is_singular()) { --> oldalaknál jelenik meg
	if(is_single() and $kategoria_meghatarozasa[0]->term_id <= 14 ) { // pl ---> is_single("1") --> Abaliget
		global $wpdb;

		// $mysql_szuro = "FROM `bkszr_forras` WHERE `misi_id` = $id";

			 $sor = $wpdb->get_row("SELECT * FROM `bkszr_adatok` where `misi_id` = $id");

// Ha még kézzel írják az állományt én nem frissül le a Corvinából
if ($sor->allomany_kezi !== "") {
	$_konyvtar_allomany_valaszto =  $sor->allomany_kezi;
}

else {
	$_konyvtar_allomany_valaszto =   $sor->allomany;

}

function multiexplode ($string) {
	$delimiters = array(",","ő:", "d:", "a:", "k:", "t:", "p:", ": ");
    $ready = str_replace($delimiters, $delimiters[0], $string);
    $launch = explode($delimiters[0], $ready);
    return  $launch;
}


  if ( $sor->konyvtar_nyitvatartas == !"") {
$nyitvatartas_darabolas = multiexplode($sor->konyvtar_nyitvatartas);
$konyvtar_nyitvatartas_tablazat =
    '<table id="bkszr_tablazat_nyitvatartas" style="margin-top:35px;" align="center">
    <!--<tr>
      <th scope="row">&#160;</th>
      <td>&#160;</td>
    </tr>
    <tr>
    -->
      <th scope="row" style="font-style: italic; border: 0">Nyitvatartás:</th>

    </tr>
    <tr>
      <th scope="row">Hétfő:</th>
      <td>' . $nyitvatartas_darabolas[1] .'</td>
    </tr>
    <tr>
      <th scope="row">Kedd:</th>
      <td>' . $nyitvatartas_darabolas[3] .'</td>
    </tr>
    <tr>
      <th scope="row">Szerda:</th>
      <td>' . $nyitvatartas_darabolas[5] .'</td>
    </tr>
    <tr>
      <th scope="row">Csütörtök:</th>
      <td>' . $nyitvatartas_darabolas[7] .'</td>
    </tr>
    <tr>
      <th scope="row">Péntek:</th>
      <td>' .  $nyitvatartas_darabolas[9].'</td>
    </tr>
    <tr>
      <th scope="row">Szombat:</th>
      <td>' .  $nyitvatartas_darabolas[11] .'</td>
    </tr>
    <tr>
      <th scope="row">Vasárnap:</th>
      <td>' . $nyitvatartas_darabolas[13] .'</td>
    </tr>
    </table>';

  }

else {
  # code...
// A buszos oldalon jelenik meg a táblázat
  $konyvtar_nyitvatartas_tablazat ='
  <tr>
    <th scope="row"><p>Menetrend:</p></th>
    <td> <a href="http://bkszr.csgyk.hu/konyvtarbusz-1/">Busz 1</a> vagy <a href="http://bkszr.csgyk.hu/konyvtarbusz-2/">Busz 2</a> </td>
  </tr>';
}



// a nyilvános oldalon az adattáblázat legenerálása
	$bkszr_tablazat = '<!-- <table id="bkszr_tablazat"> -->' .

  adatlapelem_megjelenítés("Cím", $sor->konyvtar_cim) .
  adatlapelem_megjelenítés("Telefonszám", $sor->konyvtar_telefonszam) .
  '<!--' . adatlapelem_megjelenítés("Könyvtári állomány", $_konyvtar_allomany_valaszto) .'-->'.
  adatlapelem_megjelenítés("Könyvtáros neve", $sor->konyvtaros_nev) .
  adatlapelem_megjelenítés("E-mail cím", unicode_konvertalas_vedelem($sor->konyvtar_email)) .
  adatlapelem_megjelenítés("Település weboldala", domain_linkké($sor->telepules_www)) .
  adatlapelem_megjelenítés("Könyvtár weboldala", domain_linkké($sor->konyvtar_www)) .
  // adatlapelem_megjelenítés("Referens neve", $sor->referens_nev) .

  '

        <th scope="row">Referens neve:</th>
    <td><a href="/elerhetosegek/" title="Ugrás a munkatársak elérhetőségeinek oldalára">' .$sor->referens_nev . '</a></td>
  </tr>
   <!-- </table> -->' .

$konyvtar_nyitvatartas_tablazat

  /* Régi táblázat

   '	<table class="form-table" id="bkszr_tablazat">
	  <tr>
	    <th scope="row"><label for="misi-1">Településnév:</label></th>
	    <td>  ' . $sor->telepules_nev . ' </td>
	    <th scope="row"><label for="misi-2">Könyvtáros neve:</label></th>
	    <td> ' .  $sor->konyvtaros_nev .  '  </td>
	  </tr>
	  <tr>
	    <th scope="row"><label for="misi-3">Könyvtár név:</label></th>
	    <td> ' .  $sor->konyvtar_nev .  '  </td>
	    <th scope="row"><label for="misi-4">Nyitva tartás:</label></th>
	    <td> ' .  $sor->konyvtar_nyitvatartas .  '  </td>
	  </tr>
	  <tr>
	    <th scope="row"><label for="misi-5">Könyvtár kód:</label></th>
	    <td> ' .  $sor->telepules_kod .  '    </td>
	    <th scope="row"><label for="misi-6">Beiratkozottak:</label></th>
	    <td> ' .  $sor->beiratkozottak .  '  </td>
	  </tr>
	  <tr>
	    <th scope="row"><label for="misi-7">Cím:</label></th>
	    <td> ' .  $sor->konyvtar_cim .  '  </td>
	    <th scope="row"><label for="misi-8">Állomány:</label></th>
	    <td> ' .  $_konyvtar_allomany_valaszto .  '  </td>
	  </tr>
	  <tr>
	    <th scope="row"><label for="misi-9">Telefonszám:</label></th>
	    <td> ' .  $sor->konyvtar_telefonszam .  '  </td>
	    <th scope="row"><label for="misi-10">Használók:</label></th>
	    <td> ' .  $sor->hasznalok .  '  </td>
	  </tr>
	  <tr>
	    <th scope="row"><label for="misi-11">E-mail:</label></th>
	    <td> ' . unicode_konvertalas_vedelem($sor->konyvtar_email) . ' </td>
	    <th scope="row"><label for="misi-12">Megjegyzés:</label></th>
	    <td> ' .  $sor->megjegyzes . ' </td>
	  </tr>
	  <tr>
	    <th scope="row"><label for="misi-13">Könyvtár www:</label></th>
	    <td> ' .  $sor->konyvtar_www .  '  </td>
	    <th scope="row"><label for="misi-14">Referens neve:</label></th>
	    <td> ' .  $sor->referens_nev . ' </td>
	  </tr>
	  <tr>
	  <th scope="row"><label for="misi-15">Település www:</label></th>
	  <td> ' .  $sor->telepules_www . ' </td>
	    <td></td>
	    <td>

	</td>
	  </tr>
	</table>' */;

# ez a rész a könyvtárbusz adatlapokhoz készült.
  if ($kategoria_meghatarozasa[0]->term_id == "12" or $kategoria_meghatarozasa[0]->term_id == "13") {
    // elrejti a legördülő városmenüt
    $konyvtarbusz_mezo = " hidden";
    // $corvina_keresobox szélességét változtatja
    $style_szelesseg = 99;
  }

else {
  // $corvina_keresobox szélességét változtatja
    $style_szelesseg = 49.5;
}

	$corvina_keresobox = '
	                     <select name="index0" style="width: ' . $style_szelesseg . '%">
											 <option value="AUTH">Szerző</option>
	                     	<option value="TITL">Cím</option>
	                     	<option value="SUBJ">Tárgyszó</option>
	                     	<option value="AUTS">Szerző, utalókkal bővített </option>
	                     	<option value="SUBS">Tárgyszó, utalókkal bővített </option>
	                     	<option value="PUBL">Kiadó</option>
	                     	<option value="CALL">Raktári jelzet</option>
	                     	<option value="ISBN">ISBN</option>
	                     	<option value="ISSN">ISSN</option>
	                     	<option value="UDCO">ETO</option>
	                     	<option value="BARC">Vonalkód</option>
	                     </select>
	                    <input id="search-text-opac" name="text0" placeholder="Keresés a katalógusban 📚" type="text" required style="width: 98%">
	                  <!--  <input type="hidden" name="varos_nev" value=" '.$sor->telepules_nev.'"> -->
	                    <input type="submit" value="Keresés 🔎">

	            </form>	';
$google_maps_api = '
<script type="text/javascript" src="http://maps.google.com/maps/api/js?sensor=false&key=AIzaSyA09GsnZvEnKoebujZmlz7h3LcWzJGpbuI"></script>

<script type="text/javascript">
var geocoder;
var map;
var konyvtar_cim = "' .  $sor->konyvtar_cim .  '";

function initialize() {
  geocoder = new google.maps.Geocoder();
  var latlng = new google.maps.LatLng(46.074654, 18.241986);
  var myOptions = {
    zoom: 15,
    center: latlng,
    mapTypeControl: true,
		panControl:true,
	 zoomControl:true,
	 mapTypeControl:true,
	 scaleControl:true,
	 streetViewControl:true,
	 overviewMapControl:true,
	 rotateControl:true,
    mapTypeControlOptions: {
      style: google.maps.MapTypeControlStyle.DROPDOWN_MENU
    },
    navigationControl: true,
    mapTypeId: google.maps.MapTypeId.ROADMAP
  };
  map = new google.maps.Map(document.getElementById("Google_Terkep"), myOptions);
  if (geocoder) {
    geocoder.geocode({
      "address": konyvtar_cim
    }, function(results, status) {
      if (status == google.maps.GeocoderStatus.OK) {
        if (status != google.maps.GeocoderStatus.ZERO_RESULTS) {
          map.setCenter(results[0].geometry.location);

          var infowindow = new google.maps.InfoWindow({
            content: "<b> ' . $sor->telepules_nev . ' könyvtára</b>" +  "<br>" + konyvtar_cim + "<br>"
          //  +  "Easter Egg: <center><img src=\"' . plugins_url( 'kepek/Tumblr_static_nyan_cat_animation_new.gif', __FILE__ ) . '\" width=\"110\"></center>"
            ,
            size: new google.maps.Size(150, 50)
          });

          var marker = new google.maps.Marker({
            position: results[0].geometry.location,
            map: map,
            title: "' . $sor->telepules_nev . ' könyvtára",
						icon:"' . plugins_url( 'kepek/csgyk-marker.png', __FILE__ ) . '",

          });
          google.maps.event.addListener(marker, "click", function() {
            infowindow.open(map, marker);
						map.setZoom(16);
  				map.setCenter(marker.getPosition());

          });

        } else {
          alert("A jelen pillanatban nincsen eredmény. Kérlek, hogy vedd fel a webmesterrel a kapocsalatot!");
        }
      } else {
        alert("A Geocode visszafejtése a következők miatt sikertelen volt: " + status + "Kérlek, hogy vedd fel a webmesterrel a kapocsalatot!");
      }
    });
  }
}
google.maps.event.addDomListener(window, "load", initialize);
</script>


<h1>Térkép</h1><hr>

 <div id="Google_Terkep" style="width:100%; height:300px"></div>
';
// A kategória név kiderítése - kétlépcsőben
 /* v01:
  $kategoria_id = $wpdb->get_row("SELECT `object_id`, `term_taxonomy_id`  FROM `wp_term_relationships` where `object_id` = $id");
  $kategoria_nev = $wpdb->get_row("SELECT `term_id`, `name` FROM `wp_terms` where `term_id` = $kategoria_id->term_taxonomy_id");
*/
// v02: - egylépcsőben
// $kategoria_meghatarozasa = get_the_category( $id );
$tovabbi_telepulesek = '<h1>A ' . $kategoria_nev ->name .  $kategoria_meghatarozasa[0]->cat_name . ' további települései:</h1><hr> | ';

//listázás
$args = array( 'category' =>  $kategoria_meghatarozasa[0]->term_id, 'order'=> 'ASC', 'orderby' => 'title', 'post_status' => 'any', 'posts_per_page' => -1 );
                                                                                                  //https://codex.wordpress.org/Template_Tags/get_posts
$postslist = get_posts( $args );
foreach ( $postslist as $post ) :
      setup_postdata( $post );
    $tovabbi_telepulesek .= '<a href="' .  get_permalink( $post->ID) . '" ';
      $tovabbi_telepulesek .=  'title="' . get_the_title($post->ID) . '">' . get_the_title($post->ID) . '</a>'; // <!-- Könyvtári Szolgáltató Hely -->
     $tovabbi_telepulesek .= " | ";
    endforeach;
wp_reset_postdata();

// -----------------------------| adatok hozzárakása a content-hez |-----------------------------

$content .= "<!-- BKSZR-adatok  / Készítette: Földesi Mihály (CSGYK) | eleje -->\n";
$content .='</div><div id="jobboldalt"><table id="bkszr_tablazat">' . $bkszr_tablazat . '</table></div>';
            global $post;
            $morestring = '<!--more-->';
            $explodemore = explode($morestring, $post->post_content);
            /* echo apply_filters($explodemore[0]); // before the more-tag */
$content .= '<div id="more_utan">' .  $explodemore[1] ."</div>" ; // after the more-tag

$content .= '<div id="corvina_kereso"><h1>Corvina-kereső</h1><hr>
							<!-- BKSZR-adatok | kereső -->
							<form method="get" id="searchform22" action="'. $_SERVER['REQUEST_URI'].'#CT">';

              // ha könyvtárbuszkategóriaában jelenik meg az adatlap (id: 12 vagy 13), akkor nincsen város drodown menu
$content .='<select name="LOCA" style="width: 49%"' . $konyvtarbusz_mezo . '>' . "\n";
				// MySQL adatok lekérdezése ABC sorrendben
				$eredmeny = $wpdb->get_results( $wpdb->prepare("SELECT `misi_id`, `telepules_nev`, `telepules_kod` FROM `bkszr_adatok` ORDER BY `telepules_nev` ASC"));
									foreach ( $eredmeny as $sor ):

										if($sor->misi_id == $id)   {
											// ez a rész teszi a mezőt kiválasztva a rengeteg elem közül
																$content .= 	'<option selected value="'.  $sor->telepules_kod . '">'.$sor->telepules_nev.'</option>' . "\n";
									            }
									            else {
																$content .= 	'<option value="'.  $sor->telepules_kod . '">'.$sor->telepules_nev.'</option>' . "\n";
									            }

									endforeach;

$content .= "</select>";

$content .= $corvina_keresobox;

if(isset($_GET['LOCA'])) {
	$keresendo_tipus = $keresendo_kifejezes  = "";
	$corvina_tk_url = "http://corvina.tudaskozpont-pecs.hu/WebPac.kszr/CorvinaWeb?";

  $keresendo_helyiseg = atalakitas($_GET["LOCA"]);
	$ksfnsklnf_helyid = $_GET['LOCA'];
   $keresendo_tipus = atalakitas($_GET["index0"]);
   $keresendo_kifejezes = atalakitas($_GET["text0"]);
	 // a keresés alapján meghatározzuk a városnevet ;)
	 $varos_nev =  $wpdb->get_row("SELECT `telepules_kod`, `telepules_nev` FROM `bkszr_adatok` where `telepules_kod` =  '$ksfnsklnf_helyid' ");

	 $content .= '<h1 id="CT">Corvina találat | ' . $varos_nev->telepules_nev . '</h1><hr>';
	 $content .= '<iframe src="' . $corvina_tk_url . "LOCA=" .$keresendo_helyiseg . "&index0=" . $keresendo_tipus . "&text0="  . $keresendo_kifejezes . '&action=find"'	. ' width="1024" height="555" frameborder="0"></iframe>';

}
$content .= '</div><div id="terkep">' . $google_maps_api . '</div>';
$content .= '<div id="tovabbi_et">' .  $tovabbi_telepulesek . '</div>';
$content .="<!-- BKSZR-adatokak megjelenítő WordPress plug-in / Készítette: Földesi Mihály (CSGYK) | vége -->\n";
}


	return $content_tabla .$content;



}
// a HTML kódót csinál a Corvina iFrame-hez ;)
function atalakitas($adat) {
   $adat = trim($adat);
   $adat = stripslashes($adat);
   $adat = htmlspecialchars($adat);
   return $adat;
}

// a nyilvános oldalt legenereálja
add_filter("the_content", "bkszr_plugin_oldal_plussz_tartalom");



/* 	----- Nyilvános oldalon az adatok listázása | vége ----- */

/* ----- Egyedi cím megadása | eleje ----- */


function egyedi_title_ertek ( $title ) {
  $id = get_the_ID(); // elkéri az adott bejegyzés id-jét...
  $kategoria_meghatarozasa = get_the_category( $id );

        if (is_single() and $kategoria_meghatarozasa[0]->term_id <= 14 ) {
            $plusz_cim .= " Könyvtári Szolgáltató Hely";
            $title .= get_the_title() . $plusz_cim . " &#8211; " . get_bloginfo('name'); // pl.: Abaliget Könyvtári Szolgáltató Hely - BKSZR

            return $title;
        }
}


add_filter( 'pre_get_document_title', 'egyedi_title_ertek', 10, 2 );

/* ----- Egyedi cím megadása | vége ----- */

/* 	----- A főoldalon mutatja a térképet | eleje ----- */

function egyedi_fooldal_kiegeszites( $tartalom ) {
  // több marker a geocode alapján: http://jsfiddle.net/P2QhE/4267/
  if ( is_front_page() ) {

    global $wpdb;
    $tartalom .= '
    <script type="text/javascript" src="http://maps.google.com/maps/api/js?sensor=false&key=AIzaSyA09GsnZvEnKoebujZmlz7h3LcWzJGpbuI"></script>
    <script type="text/javascript" src="https://code.jquery.com/jquery-3.1.0.min.js"></script>


    <script type="text/javascript">
    $(document).ready(function () {
      var map;
      var elevator;
      var myOptions = {
          zoom: 8,
          center: new google.maps.LatLng(46.074654, 18.241986),
          mapTypeId: "terrain"
      };
      map = new google.maps.Map($("#map_canvas")[0], myOptions);

      var addresses = [';

  //MySQL adatok lekérdezése ABC sorrendben
				$eredmeny = $wpdb->get_results( $wpdb->prepare("SELECT `misi_id`, `telepules_nev`, `telepules_kod` FROM `bkszr_adatok` ORDER BY `telepules_nev` ASC"));
									foreach ( $eredmeny as $sor ):

																$tartalom .= 	'["' . $sor->telepules_nev . ', Hungary"], ';


									endforeach;

    $tartalom .= '];

      for (var x = 0; x < addresses.length; x++) {
          $.getJSON("http://maps.googleapis.com/maps/api/geocode/json?address="+addresses[x]+"&sensor=false", null, function (data) {
              var p = data.results[0].geometry.location
              var latlng = new google.maps.LatLng(p.lat, p.lng);
              new google.maps.Marker({
                  position: latlng,
                  map: map
              });

          });
      }

  });
    </script>

<!--
    <h1>Térkép</h1><hr>

     <div id="map_canvas" style="width:100%; height:300px"></div>
     -->
    ';
  }

  return $tartalom;

}


// a főoldalon az oldalt legenereálja
add_filter( 'the_content', 'egyedi_fooldal_kiegeszites' );

/* 	----- A főoldalon mutatja a térképet | vége ----- */

/* ----- Post szerkesztő felület | eleje ----- */
//  https://www.youtube.com/watch?v=t5TeK_t3wp0
// https://developer.wordpress.org/plugins/metadata/creating-custom-meta-boxes/

add_action( 'add_meta_boxes', 'myplugin_add_custom_box' );
function myplugin_add_custom_box() {
    $screens = array( 'post', 'my_cpt' );
    foreach ( $screens as $screen ) {
        add_meta_box(
            'myplugin_box_id',            // Unique ID
            'BKSZR-adatok | <i>béta béka</i> 🐸',      // Box title
            'myplugin_inner_custom_box',  // Content callback
             $screen                      // post type
        );
    }
}

/* --- Szerkesztőfelület tartalma --- */
function myplugin_inner_custom_box( $post ) {

		// a WP csatlakozik a BKSZR adattáblázathoz
	global $wpdb;

	/* --- ez a rekord frissítéshez tartozik --- */
	//Meghatározzuk az aktuális bejegyzés számát a WP-től
	$id = get_the_ID();
	// $id = 281;




$mappahely =  plugin_dir_path( __FILE__ );
print $konyvtar_nev;



	/* --- ez a lekérdezéshez tartozik --- */
	//egyéb paraméterek
	$mysql_szuro = "FROM `bkszr_forras` WHERE `misi_id` = $id";

	//if ($id == 15353 and empty($_GET)) {
	 $eredmeny = $wpdb->get_results("SELECT  * FROM `bkszr_adatok`  where `misi_id` = $id");

if ( empty($eredmeny)) {
  print "A bejegyzéshez nem található megfelelő rekord a BKSZR adatbázisban. 😢 A hiba orvoslásához vedd fel a kapcsolatot a webmesterrel! 🚑 Hibakód: " . $id . " 🔧";
}
	foreach($eredmeny as $sor) 	{ // <-- listázás elkezdése...

?>
<form method="post" action="<?php print plugins_url('csgyk-kszr_feldolgoz.php', __FILE__ ); ?>" name="bkszr_frissites_urlap" id="misike">
</form>
<form method="post" action="<?php print plugins_url('csgyk-kszr_feldolgoz.php', __FILE__ ); ?>" name="bkszr_frissites_urlap" id="firstform">
<table class="form-table">
	<input value="<?print $id; ?>" name="kszr_id_misi" hidden> <!-- <?=$alma?> php short form... -->
  <tr>
    <th scope="row"><label for="misi-1">🏠 Településnév:</label></th>
    <td><input value="<?php print $sor->telepules_nev; ?>" placeholder="***" name="telepules_nev" id="misi-1"></td>
    <th scope="row"><label for="misi-2">👧 Könyvtáros neve:</label></th>
    <td><input value="<?php print $sor->konyvtaros_nev; ?>" placeholder="***" name="konyvtaros_nev" id="misi-2"></td>
  </tr>
  <tr>
    <th scope="row"><label for="misi-3">🏤 Könyvtár név:</label></th>
    <td><input value="<?php print $sor->konyvtar_nev; ?>" placeholder="***" name="konyvtar_nev" id="misi-3"></td>
    <th scope="row"><label for="misi-4">⏰ Nyitva tartás:</label></th>
    <td><textarea placeholder="Hétfő: 10:00-16:00, Kedd: 12:00-18:00, ... vesszővel válasszuk el az napokat!" name="konyvtar_nyitvatartas" id="misi-4"><?php print $sor->konyvtar_nyitvatartas; ?></textarea></td>
  </tr>
  <tr>
    <th scope="row"><label for="misi-5">🌀 Könyvtár kód:</label></th>
    <td><input value="<?php print $sor->telepules_kod; ?>" placeholder="***" name="telepules_kod" id="misi-5" readonly></td>
    <th scope="row"><label for="misi-6">🚻 Beiratkozottak:</label></th>
    <td><input value="<?php print $sor->beiratkozottak; ?>" placeholder="***" name="beiratkozottak" id="misi-6"></td>
  </tr>
  <tr>
    <th scope="row"><label for="misi-7">✉️ Cím:</label></th>
    <td><input value="<?php print $sor->konyvtar_cim; ?>" placeholder="***" name="konyvtar_cim" id="misi-7"></td>
    <th scope="row"><label for="misi-8">📊 Automatikus állomány:</label></th>
    <td><input value="<?php print $sor->allomany; ?>" placeholder="***" name="allomany" id="misi-8" readonly></td>
  </tr>
	<tr>
		<th scope="row"><label for="misi-9">☎️ Telefonszám:</label></th>
		<td><input value="<?php print $sor->konyvtar_telefonszam; ?>" placeholder="***" name="konyvtar_telefonszam" id="misi-9"></td>
		<th scope="row"> <label for="misi-16">📚 Kézi állomány:</label></th>
		<td>	<input value="<?php print $sor->allomany_kezi; ?>" placeholder="***" name="allomany_kezi" id="misi-16"></td>
	</tr>
  <tr>
		<th scope="row"><label for="misi-10">👪 Használók:</label></th>
		<td><input value="<?php print $sor->hasznalok; ?>" placeholder="***" name="hasznalok" id="misi-10"></td>
		<th scope="row"><label for="misi-11">📧 E-mail:</label></th>
		<td><input value="<?php print $sor->konyvtar_email; ?>" placeholder="***" name="konyvtar_email" id="misi-11"></td>
  </tr>
  <tr>
		<th scope="row"><label for="misi-12">📜 Megjegyzés:</label></th>
		<td><input value="<?php print $sor->megjegyzes; ?>" placeholder="***" name="megjegyzes" id="misi-12"></td>
		<th scope="row"><label for="misi-13">🌍 Könyvtár www:</label></th>
    <td><input value="<?php print $sor->konyvtar_www; ?>" placeholder="http://" name="konyvtar_www" id="misi-13"></td>
  </tr>
  <tr>
		<th scope="row"><label for="misi-14">👩 Referens neve:</label></th>
		<td><input value="<?php print $sor->referens_nev; ?>" placeholder="***" name="referens_nev" id="misi-14"></td>
		<th scope="row"><label for="misi-15">🌍 Település www:</label></th>
	  <td><input value="<?php print $sor->telepules_www; ?>" placeholder="http://" name="telepules_www" id="misi-15"></td>
  </tr>
  <tr>
  <th scope="row"><label for="misi-16">🍔 Szolgáltatások:</label></th></th>
  <td>
    <select multiple title="Crtl + klikk segítségével van lehetőségünk több mezőt kijelölni!">
  <option value="volvo">Kávé</option>
  <option value="saab">Tea</option>
  <option value="opel">Wi-Fi</option>
  <option value="audi">Parkoló</option>
</select>
</td>
    <td></td>
    <td>
<button id="subbut" class="button button-primary" style="width: 100%;" onclick="myFunction()">Az adatok mentése 💾</button>

</td>
  </tr>
</table>

</form>
<!-- ez a script csinálja az ajax-ot | eleje
<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.0/jquery.min.js"></script>
<script>
$(document).ready(function() {
    $("#subbut").click(function() {
        $.post($("#firstform").attr("action"), $("#firstform").serialize(),
          function(data) {
            $("#msg").append(data);
            $.post($("#misike").attr("action"), $("#misike").serialize(),
              function(data) {
                $("#msg").append(data);
              });
          });
      });
  });

</script>
-->
<script>
function myFunction() {
    document.getElementById("firstform").submit();
}
</script>


<!-- // http://stackoverflow.com/questions/25063901/update-wordpress-plugin-table-rows-with-php-forms -->


<!-- <div id="msg"></div> -->
<!-- ez a script csinálja az ajax-ot | vége -->


<?php
  } // <--- listázás vége
  $plugin_data = get_plugin_data( __FILE__ );
  $plugin_version = $plugin_data['Version'];

  print "<p class='small'>Ez a beépülőmodul segíti a Corvina-ban található BKSZR adatok frissítését és összekapcsolását a WP-ben.<br>Verzió: <a title='Klikk ide a verzíókövetési információ megtekintéséhez!' href='/wp-admin/admin.php?page=bkszr-adatfrissito-csgyk' </a> " . $plugin_version . "</a> | Készítette: Földesi Mihály</p>";



} // <---  poszt tartalom vége
/* ----- Post szerkesztő felület | vége ----- */
?>
