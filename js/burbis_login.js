


jQuery(document).ready(function() {           //wrapper

	
	
    jQuery("#buyon_login_form").submit(function() {             //event	
	
		
		var a = jQuery(this).children('#ka').val();
	
		var b = jQuery(this).children('#pr').val();
		
		if (a!= "" && b != "")
		{
		
        var this2 = this;                      //use in callback
		
        jQuery.post(my_ajax_obj_login.ajax_url, {         //POST request
           _ajax_nonce: my_ajax_obj_login.nonce,     //nonce
            action: "burbis_login",            //action
            user:a,
			pass:b,//data
			return_page:my_ajax_obj_login.url,
        }, function(data) {                    //callback
						
			
			
			
			if (data == 0)
			{
				
				 location.reload();
			} else
           if (data == 1)
		   {
			   location.reload();
		   } else	   
		   if (data == 2)
		   {
			   alert('Kullanıcı Adı ve Parola Yanlış');
		   }
		   else
		   {
			  alert(data);//("Kullanıcı Adı/Parola Hatalı");  
		   }
        },);
		}
		else 
			alert('Kullanıcı Adı ve Parola Girin');
		
		return false;
    });
	
	
	
});

function logout() {
	
	
	showLoading();
	
	jQuery.post(my_ajax_obj_login.ajax_url, {         //POST request
           _ajax_nonce: my_ajax_obj_login.nonce,     //nonce
            action: "burbis_logout",            //action
            return_page:my_ajax_obj_login.url,
        }, function(data) {

			
			if (data == 0)
			{
				//location.href = "/buyon/index.php/Login2";
				location.href = WPURLS.siteurl+"/index.php/Login2";
			}

			//hideLoading();
		});
	
	return false;
}

