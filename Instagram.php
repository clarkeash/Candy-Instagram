<?php
/**
 * @plugin Instagram
 * @description Display latest Instagram images. Use {{instagram}}
 * @author Ashley Clarke
 * @authorURI http://www.ashleyclarke.me/
 * @copyright 2012 (C) Ashley Clarke  
 * @version 1.0
 * @since 0.7.4
 */

class Instagram {

	public static function install(){

		$dbh = new CandyDB();
 		$sth = $dbh->prepare("INSERT INTO ".DB_PREFIX."options (option_key, option_value) VALUES (?, ?)");
 		$sth->execute(array('instagram_user', ''));

 		$sth = $dbh->prepare("INSERT INTO ".DB_PREFIX."options (option_key, option_value) VALUES (?, ?)");
 		$sth->execute(array('instagram_token', ''));

 		$sth = $dbh->prepare("INSERT INTO ".DB_PREFIX."options (option_key, option_value) VALUES (?, ?)");
 		$sth->execute(array('instagram_count', '3'));

	}

	public static function candyHead(){
		$html = '<link rel="stylesheet" type="text/css" href="'.URL_PATH.'plugins/Instagram/css/instagram.css" />';
		return $html;
	}

	private static function fetchData($url){
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
	    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	    curl_setopt($ch, CURLOPT_TIMEOUT, 20);
	    $result = curl_exec($ch);
	    curl_close($ch); 
	    return $result;
	}

	private static function getImages(){

		$dbh = new CandyDB();
		$sth = $dbh->prepare("SELECT option_value FROM ".DB_PREFIX."options WHERE `option_key`=?");
		$sth->execute(array('instagram_user'));
		$user = $sth->fetchColumn();


		$sth = $dbh->prepare("SELECT option_value FROM ".DB_PREFIX."options WHERE `option_key`=?");
		$sth->execute(array('instagram_token'));
		$token = $sth->fetchColumn();

		$sth = $dbh->prepare("SELECT option_value FROM ".DB_PREFIX."options WHERE `option_key`=?");
		$sth->execute(array('instagram_count'));
		$count = $sth->fetchColumn();

		$result = self::fetchData("https://api.instagram.com/v1/users/".$user."/media/recent/?access_token=".$token."&count=".$count);
		return $result;
	}

	private static function parseImages(){

		$images = json_decode(self::getImages());
		
		$html = '<ul class="instagram">';

		foreach ($images->data as $pic) {
			
			$html .= '<li>';
			$html .= '<a href="'.$pic->link.'" title="View on Instagram">';
			$html .= '<img src="'.$pic->images->thumbnail->url.'" alt="'.$pic->caption->text.'" />'; 
			$html .= '</a>';
			$html .= '</li>';
		}

		$html .= '</ul>';

		return $html;

	}

	public static function addShorttag(){
		$results = self::parseImages();
		return array('{{instagram}}' => $results);
	}

	public static function adminSettings(){
 		
 		$dbh = new CandyDB();
		$sth = $dbh->prepare("SELECT option_value FROM ".DB_PREFIX."options WHERE `option_key`=?");
		$sth->execute(array('instagram_user'));
		$user = $sth->fetchColumn();

		$sth = $dbh->prepare("SELECT option_value FROM ".DB_PREFIX."options WHERE `option_key`=?");
		$sth->execute(array('instagram_token'));
		$token = $sth->fetchColumn();

		$sth = $dbh->prepare("SELECT option_value FROM ".DB_PREFIX."options WHERE `option_key`=?");
		$sth->execute(array('instagram_count'));
		$count = $sth->fetchColumn();
		$instagram_url = "https://api.instagram.com/oauth/authorize/?client_id=d9cdf4edb31a4d03aba9828ce3716bae&redirect_uri=http://ashleyclarke.me/instagram/index.php&response_type=code";

		$html = "<h3>Instagram Settings</h3>";

 		$html .= "<ul>";

 		$html .= "<li>";
 		$html .= "<label>Instagram User ID</label>";
 		$html .= "<input type='text' name='instagram_user' value='$user'/>";
 		$html .= "</li>";

 		$html .= "<li>";
 		$html .= "<label>Instagram Token</label>";
 		$html .= "<input type='text' name='instagram_token' value='$token'/>";
 		$html .= "</li>";

 		$html .= "<li>";
 		$html .= "<label>Image Limit</label>";
 		$html .= "<input type='text' name='instagram_count' value='$count'/>";
 		$html .= "</li>";

 		$html .= "</ul>";
 		$html .= "<br><a href='".$instagram_url."' class='button' target='_blank'>Get Instagram Details</a>";


 		return $html;
 	}
 	
 	public static function saveSettings(){
 		$user = $_POST['instagram_user'];
 		$token = $_POST['instagram_token'];
 		$limit = $_POST['instagram_count'];
 		 		
 		$dbh = new CandyDB();
 		$dbh->exec('UPDATE '. DB_PREFIX .'options SET option_value="'. $user .'" WHERE option_key="instagram_user"');
 		$dbh->exec('UPDATE '. DB_PREFIX .'options SET option_value="'. $token .'" WHERE option_key="instagram_token"');
 		$dbh->exec('UPDATE '. DB_PREFIX .'options SET option_value="'. $limit .'" WHERE option_key="instagram_count"');
 		
 	}

}




