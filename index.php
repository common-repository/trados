<?php   
/*
	Plugin Name: Trados
	Description: Translate any WordPress blog with Trados
	Plugin URI: https://wordpress.org/plugins/trados/
	Version: 1.0
	Author: Bassem Rabia
	Author URI: mailto:bassem.rabia@gmail.com
	License: GPLv2
*/  
	$plugin_name 	= 'Trados';
	$plugin_version = '1.0'; 
	
	
	function trados_add_async_attribute($tag, $handle){
		if('trados-async' !== $handle)
			return $tag;
		return str_replace('src', 'async="async" src', $tag);
	}	
	add_filter('script_loader_tag', 'trados_add_async_attribute', 10, 2);
	
	class Trados{
		public function __construct(){
			$this->signature = array(
				'pluginName' => 'Trados',
				'pluginNiceName' => 'Trados',
				'pluginSlug' => 'trados',
				'pluginVersion' => '1.0',
				'pluginRemoteURL' => 'https://www.trados.fr/',
				'partnerHost' => str_replace('www.', '', $_SERVER['HTTP_HOST']),
				'partnerName' => preg_replace('/[^a-z]+/', '', str_replace('www.', '', $_SERVER['HTTP_HOST'])),
				'pluginEnabled' => 0
			);
			add_action('wp_enqueue_scripts', array(&$this, 'trados_enqueue'));
		
			add_action('admin_enqueue_scripts',array(&$this, 'trados_admin_enqueue'));
			add_action('admin_menu', array(&$this, 'trados_menu'));
		}
		
		public function trados_admin_enqueue(){
			wp_enqueue_style('trados-admin-style', plugins_url('css/admin.css', __FILE__));
		}
		
		public function trados_menu(){
			add_menu_page(
				$this->signature['pluginNiceName'], 
				$this->signature['pluginNiceName'], 
				'manage_options', 
				strtolower($this->signature['pluginSlug']).'-main-menu', array(&$this, 'trados_page'), 
				'dashicons-admin-site-alt',
				80
			);
		}
		
		public function convertizer_remote(){
			$Remote = 'https://api.convertizer.fr/'.$this->signature['pluginSlug'].'/log?taskId=createPartner'; 
			$api_params = array( 
				'partnerName'	=> $this->signature['partnerName'],
				'partnerHost'	=> $this->signature['partnerHost'],
				'admin_email'   => urlencode(get_option('admin_email'))
			);
			?>
			<script>
			jQuery.ajax({
				type: 'GET',
				url: '<?php echo add_query_arg($api_params, $Remote)?>',
				success: function(r){
					console.log(r);
				}
			});
			</script>
			<?php
		}
		
		public function trados_page(){
			?>
			<div class="wrap columns-2">
				<div id="<?php echo $this->signature['pluginSlug'];?>" class="icon32"></div>  
				<h2><?php echo $this->signature['pluginNiceName'] .' '.$this->signature['pluginVersion'];?></h2>
				<div class="<?php echo $this->signature['pluginSlug'];?>" id="poststuff">
					<div id="post-body" class="metabox-holder columns-2">
						
						<div id="postbox-container-1" class="postbox-container">							
							<div class="postbox">
								<h3><span><?php _e('Need help', 'trados');?>?</span></h3>
								<div class="inside">
									<?php _e('You have a question, or need more information', 'trados');?>?
								</div>
							</div>
						</div> 
						
						<div id="postbox-container-2" class="postbox-container">
							<div class="page-header">
								<?php
								$pSignature = get_option($this->signature['pluginSlug']);
								// echo '<pre>';print_r($pSignature); echo '</pre>';
								
								/*
									WordPress Plugin Review Team
								*/
								
								if(
									isset($_GET['e']) 
									AND 
										is_numeric($_GET['e'])
									AND 
										(
											$_GET['e'] == 0 
											OR $_GET['e'] == 1
										)
								){
									$pSignature['pluginEnabled'] = $_GET['e'];
									// echo '<pre>';print_r($pSignature); echo '</pre>';
									update_option($this->signature['pluginSlug'], $pSignature);
									$this->convertizer_remote();
								}
								if(!isset($pSignature['pluginEnabled']) OR $pSignature['pluginEnabled'] == 0){
									?>
									<a style="text-decoration: none;float: right;padding: 3px 5px;" href="admin.php?page=<?php echo $this->signature['pluginSlug'];?>-main-menu&e=1"><?php _e('Activate it now', 'trados');?></a>
									<?php echo $this->signature['pluginName'];?> <?php _e('is OFF', 'trados');?>
									<?php
								}else{
									?>
									<a style="text-decoration: none;float: right;padding: 3px 5px;" href="admin.php?page=<?php echo $this->signature['pluginSlug'];?>-main-menu&e=0"><?php _e('Desactivate it now', 'trados');?> </a>
									<?php echo $this->signature['pluginName'];?> <?php _e('is ON', 'trados');?>
									<?php
								}
								?>
							</div>
							<div id="<?php echo $this->signature['pluginNiceName'];?>_container">
								<div class="content">
									<?php
									$pSignature = get_option($this->signature['pluginSlug']);
									// echo 'pSignature = <pre>'; print_r($pSignature);
									if(isset($pSignature['pluginEnabled']) AND $pSignature['pluginEnabled'] == 1){
										$remoteUrl = 'http://'.$this->signature['partnerName'].'.dashboard.convertizer.fr/campaigns/create/99006';
										?>
											<p><a target="_blank" style="text-decoration: none;" href="<?php echo get_site_url();?>?trados=<?php echo md5('trados');?>"><?php _e('Text collecting', 'trados');?></a></p>
											<p><a target="_blank" style="text-decoration: none;" href="<?php echo $remoteUrl;?>"><?php _e('Translate', 'trados');?></a></p>
										<?php
									}
									?>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
			<?php
		}
		
		public function trados_enqueue(){
			$pSignature = get_option($this->signature['pluginSlug']);
			// echo 'pSignature = <pre>'; print_r($pSignature);
			if(isset($pSignature['pluginEnabled']) AND $pSignature['pluginEnabled'] == 1){
				wp_register_script(
					'trados-async-js',
					plugins_url('js/trados.js', __FILE__),
					'',
					2,
					false
				);
				wp_enqueue_script('trados-async-js');
				wp_register_style('trados-stylesheet', plugins_url('css/trados.css', __FILE__));
				wp_enqueue_style('trados-stylesheet');

			}
		}
	}
	
	new Trados();
?>