<?php if(!isset($zip_code)) $zip_code = '53593'; ?>
<h2>Weather for zip code <?php echo $zip_code; ?></h2>
<div class="widget-content">
	<div id="weather">
		<div id="wx_module_3133">
		   <a href="http://www.weather.com/weather/local/<?php echo $zip_code; ?>">Weather Forecast for <?php echo $zip_code; ?></a>
		</div>
		
		<script type="text/javascript">
		
		   /* Locations can be edited manually by updating 'wx_locID' below.  Please also update */
		   /* the location name and link in the above div (wx_module) to reflect any changes made. */
		   var wx_locID = '<?php echo $zip_code; ?>';
		
		   /* If you are editing locations manually and are adding multiple modules to one page, each */
		   /* module must have a unique div id.  Please append a unique # to the div above, as well */
		   /* as the one referenced just below.  If you use the builder to create individual modules  */
		   /* you will not need to edit these parameters. */
		   var wx_targetDiv = 'wx_module_3133';
		
		   /* Please do not change the configuration value [wx_config] manually - your module */
		   /* will no longer function if you do.  If at any time you wish to modify this */
		   /* configuration please use the graphical configuration tool found at */
		   /* https://registration.weather.com/ursa/wow/step2 */
		   var wx_config='SZ=300x250*WX=FWC*LNK=SSNL*UNT=F*BGC=ffffff*MAP=null|null*DN=newdata.crinet.com*TIER=0*PID=1274415683*MD5=2345ee0316a8517ce1c80b1e8a151067';
		
		   document.write('<scr'+'ipt src="'+document.location.protocol+'//wow.weather.com/weather/wow/module/'+wx_locID+'?config='+wx_config+'&proto='+document.location.protocol+'&target='+wx_targetDiv+'"></scr'+'ipt>');  
		</script>
	</div>
</div>
<div class = "widget-bottom"><?php echo anchor(site_url('auth/edit_user'), 'If you are not seeing your local forecast, please add your zip code to your profile'); ?></div>