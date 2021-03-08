<?php 

require_once("include/WordPressShortcutHelper.php");
require_once("include/tools.php");

//AYARLAR NELER OLACAK 
// google_recaptcha_site_key 

//--------------LOGIN-----------------------------------------------------------------
class burbis_custom_login_form extends WordPress_Shortcut_Helper{
	
	function burbis_custom_login_formu()
	{
		
		 echo '
		<style>
		div {
			margin-bottom:2px;
		}
		 
		input{
			margin-bottom:4px;
		}
		</style>
		';
		
		$username = isset($_POST['username'])?$_POST['username']:"";
		$password = isset($_POST['password'])?$_POST['password']:"";
				
		echo "<h3>Kullanıcı Girişi</h3><p>Lütfen BURBİS Kullanıcı adı ve şifrenizi giriniz</p>";
		
		global $reg_errors;
		//Hataları yaz
		if ( is_wp_error( $reg_errors ) ) {
		 
			foreach ( $reg_errors->get_error_messages() as $error ) {
			 
				echo '<div class="text-danger" style="background-color:indianred;padding:10px;margin-bottom:10px;color:white;border-radius:2px">';
				echo '<strong></strong>';
				echo $error . '<br/>';
				echo '</div>';			 
			} 	
		}
		 


		$captcha_use = get_option("buyon_login_recaptcha_use");

		if ($captcha_use)
		{
			echo '<script src="https://www.google.com/recaptcha/api.js" async defer></script>';
		}
		
		
		

			
		echo '
		<form action="' . $_SERVER['REQUEST_URI'] . '" method="post">
			
				<div class="buyon_login_form_row">
				<label for="username">Kullanıcı Adı <strong>*</strong></label>
				<input type="text" name="username" value="' . $username . '">
				</div>
				<div class="buyon_login_form_row">			
					<label for="password">Parola <strong>*</strong></label></td><td>
					<input type="password" name="password" value="">
				</div>';

		
		if ($captcha_use)
		{
			$captcha_site_key = get_option("buyon_login_recaptcha_site_key");
			echo '
			<div class="buyon_login_form_row buyon_login_form_captcha_row"">
				<div class="g-recaptcha" data-sitekey="'.$captcha_site_key.'"></div>
			</div><br/>';
		}

		echo '<input type="submit" class="blog-btn-sm"  name="buyon_login_submit2" value="Giriş Yap"/>
			</form>';
		
		
	}

	//shortcode çağrıldığında çalışacak metod
	protected function shortcode_handler()
	{
		
		global $reg_errors;		

		if (!is_user_logged_in()) 
		{	
			$this->burbis_custom_login_formu();
		} else
		{
			$this->kullanici_ekrani();
		}
	
		if ( is_wp_error( $reg_errors ) ) {
		   
			return false;
		}
		
		return true;		
	}

	
	function BurbisConnect($username,$password)
	{
		
		$url = "http://bursa.meb.gov.tr/burbis/burbisdigergiris.asmx?wsdl";

		$options = array(
			"soap_version" => SOAP_1_2,
			"cache_wsdl" => WSDL_CACHE_NONE,
			"exceptions" => false
		);

		$proje_id = get_option("burbis_login_proje_id");

		$params = array(
			'id' => $proje_id,
			'kadi'         => $username,
			'sifre'         => $password,
		);
		
	
		try {
	
			$client = new SoapClient($url,$options);
			$result = $client->__soapCall("kullanicigiris", array("kullanicigiris" => $params), null);
			
			return $result->kullanicigirisResult;
		}
		catch (Exception $e) 
		{
			echo "<h2>Exception Error!</h2>";
			echo $e->getMessage();
			die();
		}
		
	}

	function Burbis2Connect($username,$password) {

		$url = 'http://bursa.meb.gov.tr/burbis2/Servisler/Giris';

		$proje_id = get_option("burbis_login_proje_id");

		$login_data = array(
			"Kod" =>  "10b789cb-9154-c1b5-0879-28eec027c6a6",
			"Kadi" => $username,
			"Parola" => $password,
		);

			
		$postdata = json_encode($login_data);

		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $postdata);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
		$result_user_data = curl_exec($ch);
		curl_close($ch);

		return $result_user_data;

	}

	
	function burbis_bilgileri_ekle($burbis_result,$user_id)
	{
		//Kullanıcı Bilgileri Güncelle
		$adsoyad = Tools::AdSoyadBul($burbis_result->AdSoyad);
						
		$new_userdata = array(
			'ID' => $user_id,							
			'first_name'    =>   $adsoyad[0],
			'last_name'     =>   $adsoyad[1],
			'display_name' => $burbis_result->AdSoyad,
		);
		
		wp_update_user($new_userdata);
		
		//Diğer Bilgileri Kullanıcıya Kaydet
		add_user_meta( $user_id, 'brans', $burbis_result->Brans);
		add_user_meta( $user_id, 'TC', $burbis_result->TC);							
		add_user_meta( $user_id,'ilce',$burbis_result->Ilce);
		add_user_meta( $user_id,'gorev',$burbis_result->Unvan);
		//add_user_meta( $user_id,'gorevYeri',$gorevYeri);
		add_user_meta( $user_id,'okul',$burbis_result->KurumAdi);
		//add_user_meta( $user_id,'mudurHizmetSuresi',$mudurHizmetSuresi);
		//add_user_meta( $user_id,'mudurYrdHizmetSuresi',$mudurYrdHizmetSuresi);
		add_user_meta( $user_id,'cinsiyet',$burbis_result->Cinsiyet);
		//add_user_meta( $user_id,'egitim',$egitim);
		add_user_meta( $user_id,'telefon',$burbis_result->CepTel);
		//add_user_meta( $user_id,'kurumTuru',$kurumTuru);

	}

	function burbis_bilgileri_guncelle($burbis_result,$user_id)
	{
		
		update_user_meta( $user_id, 'brans', $burbis_result->Brans);
		update_user_meta( $user_id, 'TC', $burbis_result->KullaniciAdi);							
		update_user_meta( $user_id,'ilce',$burbis_result->Ilce);
		update_user_meta( $user_id,'gorev',$burbis_result->Unvan);							
		update_user_meta( $user_id,'okul',$burbis_result->KurumAdi);							
		update_user_meta( $user_id,'telefon',$burbis_result->CepTel);
		update_user_meta( $user_id,'cinsiyet',$burbis_result->Cinsiyet);
						
		
		// E-Posta güncellensin
		if ($burbis_result->EPosta != get_userdata($user_id)->user_email)
		{
			$update_userdata = array(
				'ID' => $user_id,							
				'user_email'=>$burbis_result->EPosta
			);
			
			wp_update_user($update_userdata);
			
		}
		
	}
	
	function login_and_redirect($user_id)
	{
		wp_clear_auth_cookie();
		do_action('wp_login', $user_id);
		wp_set_current_user($user_id);
		wp_set_auth_cookie($user_id, true);
		$redirect_to = $_SERVER['REQUEST_URI'];					
		
		$go_url = get_option("buyon_login_sonra_url"); // Nereye gidecel
		if ($go_url <> '') $redirect_to = $go_url;
		
		wp_safe_redirect($redirect_to);

		//Kullanıcı Girişi Yap
				
				// if ($burbis_result->HataKodu !=0 && $user_id) 
				// {
				// 	$login_data = array();
				// 	$login_data['user_login'] = sanitize_user($username);
				// 	$login_data['user_password'] = esc_attr($password);
					
					

				// 	$user = wp_signon( $login_data, false );
					
				// 	if ( is_wp_error($user) ) {
				// 		global $reg_errors;
						
				// 		$reg_errors->add('field', $user->get_error_message());
				// 	} else {    
				// 		wp_clear_auth_cookie();
				// 		do_action('wp_login', $user->ID);
				// 		wp_set_current_user($user->ID);
				// 		wp_set_auth_cookie($user->ID, true);
				// 		$redirect_to = $_SERVER['REQUEST_URI'];
				// 		wp_safe_redirect($redirect_to);
				// 		exit;
				// 	}
				// }
	}

		//Giriş işlemini yapacak fonksyion
	function burbis_login_complete() {
		
		if (isset($_POST['buyon_login_submit2'])) {
			
			global $reg_errors;
			$reg_errors = new WP_Error;
				
			$username = isset($_POST['username'])?$_POST['username']:"";
			$password = isset($_POST['password'])?$_POST['password']:"";

			$captcha_use = get_option("buyon_login_recaptcha_use"); // Captcha kullanılıyor mu?

			if ($username=="" || $password =="")
			{				
				$reg_errors->add('field', "Kullanıcı Adı/Parola Eksik");
				return;
			}

			if ($captcha_use)
			{
				$captcha = isset($_POST['g-recaptcha-response'])?$_POST["g-recaptcha-response"]:"";
				if ($captcha == "")
				{
					$reg_errors->add('field', "Lütfen Doğrulama Kutusunu İşaretleyin");
					return;
				}
				if (!$this->dogrulama_kodu_kontrol())
				{
					
					$reg_errors->add('User',"Lütfen doğrulama kodunu işaretleyiniz.");
					return false;				
				}
			}

			
			// HERŞEY YOLUNDA
				

			// Kullanıcı Burbis te varmı kontrol et
			$burbis_result = $this->BurbisConnect($username,$password);
				
			//Kullanıcı yerelde kullanıcı var mı kontrol et
			$user_id = username_exists($username);
				
			//print_r($burbis_result);			
			
			if ($burbis_result->HataKodu==0) //Kullanıcı Burbis te varsa
			{				
				$user_email = $burbis_result->EPosta;
				$phone_number = $burbis_result->CepTel;					
				
				//Kullanıcı Burbis te var ama e-posta,telefon bilgileri burbis te bulunamadıysa hata ver
				if (empty(trim($user_email)) || empty(trim($phone_number)))
				{
					$reg_errors->add('User',"BURBİS'ten alınan kullanıcı bilgilerinde E-Posta adresi veya cep telefonu bilgisi bulunamadı. Lütfen BURBİS'e girerek bu bilgilerinizin güncelleyiniz 
					yada okul/İl BURBİS yöneticisi tarafından bilgilerinizin güncellenmesini sağlayın.");

					return;						
				}			
				
				//Yerel kullanıcı yoksa kullanıcıyı sisteme kaydet
				if (!$user_id) 
				{
					
					$random_password = wp_generate_password( $length=12, $include_standard_special_chars=false );
					$user_id = wp_create_user( $username, $random_password, $user_email );

					//Kullanıcı Başarılı Bir Şekilde Oluştuysa
					if (!is_wp_error($user_id))
					{				
					    $this->burbis_bilgileri_ekle($burbis_result,$user_id);
						//Login Ol
						$this->login_and_redirect($user_id);
						
						exit;
					} else
					{
						if (email_exists($user_email))
							$reg_errors->add('field', "E-Posta hesabı başka kullanıcı tarafından kullanılıyor");
					}
				} 
				else {
					
					//Değişen bilgiler varsa bilgileri güncelle
					$this->burbis_bilgileri_guncelle($burbis_result,$user_id);					
					
					//Yerel Kullanıcı Olarak Girişi Yap
					$this->login_and_redirect($user_id);
						
					exit;						
				}
					
					//Kullanıcı girişi yapacak					
					
			} else { // Burbis ten hata döndü
					
					// Birde site kullanıcısı olarak yoksa hata yapalım giriş yapalım  
					
						switch ($burbis_result->HataKodu)
						{
							case 3:$reg_errors->add('User','BURBİS Kullanıcısı Bulunamadı');break;
							case 4:$reg_errors->add('User','Şifre doğru değil');break;
							case 5:
							case 6:
							case 7:$reg_errors->add('User',$burbis_result->HataMesaji);break;
							default:$reg_errors->add('User','Bilinmeyen Bir Hata Oluştu');
						}												
									
				}				
			
				
				
			
		}
		
		
	}


	function __construct()
	{		
		parent::__construct("burbis_custom_login_form");

		//Sayfa Gösterilmeden önce çalışabilmesi için
		add_action( 'after_setup_theme', array($this,'burbis_login_complete') );	
		
	}

	function dogrulama_kodu_kontrol()
	{
		$captcha_secret_key = get_option("buyon_login_recaptcha_secret_key");

		//RE-CAPTCHA kontrolü
		$url = 'https://www.google.com/recaptcha/api/siteverify';
		$data = array(
			'secret' => $captcha_secret_key, 
			'response' => $_POST["g-recaptcha-response"]
		);
		$options = array(
			'http' => array (
				'method' => 'POST',
				'header' => "Content-Type: application/x-www-form-urlencoded\r\n",				
				'content' => http_build_query($data)
			)
		);
		$context  = stream_context_create($options);
		$verify = file_get_contents($url, false, $context);
		$captcha_success=json_decode($verify);

		return $captcha_success->success;
		
	}
	
	// KULLANICI EKRANI
	function kullanici_ekrani()
	{		
		if (is_user_logged_in())
		{		
			$user = wp_get_current_user();
			
			echo "<div style='margin-bottom:50px'>Merhaba ".$user->display_name."</div>";			
			
			echo "<div class='button_bar' style='margin-bottom:50px'><a href='#' id='buyon_logout' onclick='javascript:logout();' class='blog-btn-sm'>Çıkış Yap</a></div>";
		}
		
	}
}

class burbis2_custom_login_form extends WordPress_Shortcut_Helper{
	
	function burbis2_custom_login_formu()
	{
		
		 echo '
		<style>
		div {
			margin-bottom:2px;
		}
		 
		input{
			margin-bottom:4px;
		}
		</style>
		';
		
		$username = isset($_POST['username'])?$_POST['username']:"";
		$password = isset($_POST['password'])?$_POST['password']:"";
				
		echo "<h3>Kullanıcı Girişi</h3><p>Lütfen BURBİS Kullanıcı adı ve şifrenizi giriniz</p>";
		
		global $reg_errors;
		//Hataları yaz
		if ( is_wp_error( $reg_errors ) ) {
		 
			foreach ( $reg_errors->get_error_messages() as $error ) {
			 
				echo '<div class="text-danger" style="background-color:indianred;padding:10px;margin-bottom:10px;color:white;border-radius:2px">';
				echo '<strong></strong>';
				echo $error . '<br/>';
				echo '</div>';			 
			} 	
		}
		 


		$captcha_use = get_option("buyon_login_recaptcha_use");

		if ($captcha_use)
		{
			echo '<script src="https://www.google.com/recaptcha/api.js" async defer></script>';
		}
		
		
		

			
		echo '
		<form action="' . $_SERVER['REQUEST_URI'] . '" method="post">
			
				<div class="buyon_login_form_row">
				<label for="username">Kullanıcı Adı <strong>*</strong></label>
				<input type="text" name="username" value="' . $username . '">
				</div>
				<div class="buyon_login_form_row">			
					<label for="password">Parola <strong>*</strong></label></td><td>
					<input type="password" name="password" value="">
				</div>';

		
		if ($captcha_use)
		{
			$captcha_site_key = get_option("buyon_login_recaptcha_site_key");
			echo '
			<div class="buyon_login_form_row buyon_login_form_captcha_row"">
				<div class="g-recaptcha" data-sitekey="'.$captcha_site_key.'"></div>
			</div><br/>';
		}

		echo '<input type="submit" class="blog-btn-sm"  name="buyon_login_submit2" value="Giriş Yap"/>
			</form>';
		
		
	}

	//shortcode çağrıldığında çalışacak metod
	protected function shortcode_handler()
	{
		
		global $reg_errors;
		

		if (!is_user_logged_in()) 
		{	
			$this->burbis2_custom_login_formu();
		} else
		{
			$this->kullanici_ekrani();
		}
	
		if ( is_wp_error( $reg_errors ) ) {
		   
			return false;
		}
		
		return true;		
	}

	
	function BurbisConnect($username,$password)
	{
		$url = 'http://bursa.meb.gov.tr/burbis2/Servisler/Giris';

		$proje_id = get_option("burbis_login_proje_id");

		$login_data = array(
			"Kod" =>  "10b789cb-9154-c1b5-0879-28eec027c6a6",
			"Kadi" => $username,
			"Parola" => $password,
		);

			
		$postdata = json_encode($login_data);

		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $postdata);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
		$result_user_data = curl_exec($ch);
		curl_close($ch);

		return $result_user_data;

	}

	
	function burbis_bilgileri_ekle($burbis_result,$user_id)
	{
		//Kullanıcı Bilgileri Güncelle
		$adsoyad = Tools::AdSoyadBul($burbis_result->AdSoyad);
						
		$new_userdata = array(
			'ID' => $user_id,							
			'first_name'    =>   $adsoyad[0],
			'last_name'     =>   $adsoyad[1],
			'display_name' => $burbis_result->AdSoyad,
		);
		
		wp_update_user($new_userdata);
		
		//Diğer Bilgileri Kullanıcıya Kaydet
		add_user_meta( $user_id, 'brans', $burbis_result->Brans);
		add_user_meta( $user_id, 'TC', $burbis_result->TC);							
		add_user_meta( $user_id,'ilce',$burbis_result->Ilce);
		add_user_meta( $user_id,'gorev',$burbis_result->Unvan);
		//add_user_meta( $user_id,'gorevYeri',$gorevYeri);
		add_user_meta( $user_id,'okul',$burbis_result->KurumAdi);
		//add_user_meta( $user_id,'mudurHizmetSuresi',$mudurHizmetSuresi);
		//add_user_meta( $user_id,'mudurYrdHizmetSuresi',$mudurYrdHizmetSuresi);
		add_user_meta( $user_id,'cinsiyet',$burbis_result->Cinsiyet);
		//add_user_meta( $user_id,'egitim',$egitim);
		add_user_meta( $user_id,'telefon',$burbis_result->CepTel);
		//add_user_meta( $user_id,'kurumTuru',$kurumTuru);

	}

	function burbis_bilgileri_guncelle($burbis_result,$user_id)
	{
		
		update_user_meta( $user_id, 'brans', $burbis_result->Brans);
		update_user_meta( $user_id, 'TC', $burbis_result->KullaniciAdi);							
		update_user_meta( $user_id,'ilce',$burbis_result->Ilce);
		update_user_meta( $user_id,'gorev',$burbis_result->Unvan);							
		update_user_meta( $user_id,'okul',$burbis_result->KurumAdi);							
		update_user_meta( $user_id,'telefon',$burbis_result->CepTel);
		update_user_meta( $user_id,'cinsiyet',$burbis_result->Cinsiyet);
						
		
		// E-Posta güncellensin
		if ($burbis_result->EPosta != get_userdata($user_id)->user_email)
		{
			$update_userdata = array(
				'ID' => $user_id,							
				'user_email'=>$burbis_result->EPosta
			);
			
			wp_update_user($update_userdata);
			
		}
		
	}
	
	function login_and_redirect($user_id)
	{
		wp_clear_auth_cookie();
		do_action('wp_login', $user_id);
		wp_set_current_user($user_id);
		wp_set_auth_cookie($user_id, true);
		$redirect_to = $_SERVER['REQUEST_URI'];					
		
		$go_url = get_option("buyon_login_sonra_url"); // Nereye gidecel
		if ($go_url <> '') $redirect_to = $go_url;
		
		wp_safe_redirect($redirect_to);
	}

		//Giriş işlemini yappacak fonksyion
	function burbis_login_complete() {
		
		if (isset($_POST['buyon_login_submit2'])) {
			
			global $reg_errors;
			$reg_errors = new WP_Error;
				
			$username = isset($_POST['username'])?$_POST['username']:"";
			$password = isset($_POST['password'])?$_POST['password']:"";

			$captcha_use = get_option("buyon_login_recaptcha_use"); // Captcha kullanılıyor mu?

			if ($username=="" || $password =="")
			{				
				$reg_errors->add('field', "Kullanıcı Adı/Parola Eksik");
				return;
			}

			if ($captcha_use)
			{
				$captcha = isset($_POST['g-recaptcha-response'])?$_POST["g-recaptcha-response"]:"";
				if ($captcha == "")
				{
					$reg_errors->add('field', "Lütfen Doğrulama Kutusunu İşaretleyin");
					return;
				}
				if (!$this->dogrulama_kodu_kontrol())
				{
					
					$reg_errors->add('User',"Lütfen doğrulama kodunu işaretleyiniz.");
					return false;				
				}
			}

			
			// HERŞEY YOLUNDA
				

			// Kullanıcı Burbis te varmı kontrol et
			$burbis_result = $this->BurbisConnect($username,$password);
				
			//Kullanıcı yerelde kullanıcı var mı kontrol et
			$user_id = username_exists($username);
				
			//print_r($burbis_result);			
			
			if ($burbis_result->HataKodu==0) //Kullanıcı Burbis te varsa
			{				
				$user_email = $burbis_result->EPosta;
				$phone_number = $burbis_result->CepTel;					
				
				//Kullanıcı Burbis te var ama e-posta,telefon bilgileri burbis te bulunamadıysa hata ver
				if (empty(trim($user_email)) || empty(trim($phone_number)))
				{
					$reg_errors->add('User',"BURBİS'ten alınan kullanıcı bilgilerinde E-Posta adresi veya cep telefonu bilgisi bulunamadı. Lütfen BURBİS'e girerek bu bilgilerinizin güncelleyiniz 
					yada okul/İl BURBİS yöneticisi tarafından bilgilerinizin güncellenmesini sağlayın.");

					return;						
				}			
				
				//Yerel kullanıcı yoksa kullanıcıyı sisteme kaydet
				if (!$user_id) 
				{
					
					$random_password = wp_generate_password( $length=12, $include_standard_special_chars=false );
					$user_id = wp_create_user( $username, $random_password, $user_email );

					//Kullanıcı Başarılı Bir Şekilde Oluştuysa
					if (!is_wp_error($user_id))
					{				
					    $this->burbis_bilgileri_ekle($burbis_result,$user_id);
						//Login Ol
						$this->login_and_redirect($user_id);
						
						exit;
					} else
					{
						if (email_exists($user_email))
							$reg_errors->add('field', "E-Posta hesabı başka kullanıcı tarafından kullanılıyor");
					}
				} 
				else {
					
					//Değişen bilgiler varsa bilgileri güncelle
					$this->burbis_bilgileri_guncelle($burbis_result,$user_id);					
					
					//Yerel Kullanıcı Olarak Girişi Yap
					$this->login_and_redirect($user_id);
						
					exit;						
				}
					
					//Kullanıcı girişi yapacak					
					
			} else { // Burbis ten hata döndü
					
					// Birde site kullanıcısı olarak yoksa hata yapalım giriş yapalım  
					
						switch ($burbis_result->HataKodu)
						{
							case 3:$reg_errors->add('User','BURBİS Kullanıcısı Bulunamadı');break;
							case 4:$reg_errors->add('User','Şifre doğru değil');break;
							case 5:
							case 6:
							case 7:$reg_errors->add('User',$burbis_result->HataMesaji);break;
							default:$reg_errors->add('User','Bilinmeyen Bir Hata Oluştu');
						}												
									
				}				
			
				
				
			
		}
		
		
	}	

	function __construct()
	{		
		parent::__construct("burbis_custom_login_form");
		
		//Sayfa Gösterilmeden önce çalışabilmesi için
		add_action( 'after_setup_theme', array($this,'burbis_login_complete') );	
		
	}

	function dogrulama_kodu_kontrol()
	{
		$captcha_secret_key = get_option("buyon_login_recaptcha_secret_key");

		//RE-CAPTCHA kontrolü
		$url = 'https://www.google.com/recaptcha/api/siteverify';
		$data = array(
			'secret' => $captcha_secret_key, 
			'response' => $_POST["g-recaptcha-response"]
		);
		$options = array(
			'http' => array (
				'method' => 'POST',
				'header' => "Content-Type: application/x-www-form-urlencoded\r\n",
				
				'content' => http_build_query($data)
			)
		);
		$context  = stream_context_create($options);
		$verify = file_get_contents($url, false, $context);
		$captcha_success=json_decode($verify);

		return $captcha_success->success;
		
	}
	
	// KULLANICI EKRANI
	function kullanici_ekrani()
	{		
		if (is_user_logged_in())
		{		
			$user = wp_get_current_user();
			
			echo "<div style='margin-bottom:50px'>Merhaba ".$user->display_name."</div>";			
			
			echo "<div class='button_bar' style='margin-bottom:50px'><a href='#' id='buyon_logout' onclick='javascript:logout();' class='blog-btn-sm'>Çıkış Yap</a></div>";
		}
		
	}
}





if (!is_admin())
{
	$kullanici_login = new burbis2_custom_login_form();	
}