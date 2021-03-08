<?php



	abstract class WordPress_Shortcut_Helper
	{
		public $shortcode_name = "";
		
		public function shortcode_function()
		{
			ob_start();
			
			$this->shortcode_handler();
			
			return ob_get_clean();
		}
		
		abstract protected function shortcode_handler();
		
		function __construct($shortcode)
		{
			$this->shortcode_name= $shortcode;
			// Register a new shortcode: 
			add_shortcode( $shortcode, array($this,'shortcode_function'));
		}

	}
