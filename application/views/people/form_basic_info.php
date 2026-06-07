<div class="row">
	<div class="col-md-12">

		<div class="form-group">
			<?php 
			$required = ($controller_name == "suppliers") ? "" : "required";
			echo form_label(lang('common_first_name').':', 'first_name',array('class'=>$required.' col-sm-3 col-md-3 col-lg-2 control-label ')); ?>
			<div class="col-sm-9 col-md-9 col-lg-10">
				<?php echo form_input(array(
					'class'=>'form-control',
					'name'=>'first_name',
					'id'=>'first_name',
					'value'=>$person_info->first_name)
				);?>
			</div>
		</div>

		<div class="form-group">
			<?php echo form_label(lang('common_last_name').':', 'last_name',array('class'=>' col-sm-3 col-md-3 col-lg-2 control-label ')); ?>
			<div class="col-sm-9 col-md-9 col-lg-10">
			<?php echo form_input(array(
				'class'=>'form-control',
				'name'=>'last_name',
				'id'=>'last_name',
				'value'=>$person_info->last_name)
			);?>
			</div>
		</div>

		<?php if ($controller_name == "customers" || $controller_name == "suppliers") { 
			$company_label = ($controller_name == "suppliers") ? lang('suppliers_company_name') : lang('customers_company_name');
			$company_required = ($controller_name == "suppliers") ? 'required ' : '';
		?>
		<div class="form-group">	
			<?php echo form_label($company_label.':', 'company_name',array('class'=>$company_required.'col-sm-3 col-md-3 col-lg-2 control-label')); ?>
			<div class="col-sm-9 col-md-9 col-lg-10">
				<?php echo form_input(array(
					'name'=>'company_name',
					'id'=>'company_name',
					'class'=>'company_names form-control',
					'value'=>$person_info->company_name)
				);?>
				</div>
			</div>
		<?php } ?>

		<div class="form-group">
			<?php echo form_label(lang('common_email').':', 'email',array('class'=>'col-sm-3 col-md-3 col-lg-2 control-label '.($controller_name == 'employees' || $controller_name == 'login' ? 'required' : 'not_required'))); ?>
			<div class="col-sm-9 col-md-9 col-lg-10">
			<?php echo form_input(array(
				'class'=>'form-control',
				'name'=>'email',
				'type'=>'text',
				'id'=>'email',
				'value'=>$person_info->email)
				);?>
			</div>
		</div>
		<div class="form-group">	
			<?php echo form_label(lang('common_phone_number').':', 'phone_number',array('class'=>'col-sm-3 col-md-3 col-lg-2 control-label ')); ?>
			<div class="col-sm-9 col-md-9 col-lg-10">
			<?php echo form_input(array(
				'class'=>'form-control',
				'name'=>'phone_number',
				'id'=>'phone_number',
				'value'=>$person_info->phone_number));?>
			</div>
		</div>
		<div class="form-group">	
		<?php echo form_label(lang('common_choose_avatar').':', 'image_id',array('class'=>'col-sm-3 col-md-3 col-lg-2 control-label ')); ?>
			<div class="col-sm-9 col-md-9 col-lg-10">
	      		<ul class="list-unstyled avatar-list">
					<li>
						<input type="file" name="image_id" id="image_id" class="filestyle" >&nbsp;
					</li>
					<li>
						<?php echo $person_info->image_id ? '<div id="avatar">'.img(array('src' => app_file_url($person_info->image_id),'class'=>'img-polaroid img-polaroid-s')).'</div>' : '<div id="avatar">'.img(array('src' => base_url().'assets/img/avatar.png','class'=>'img-polaroid','id'=>'image_empty')).'</div>'; ?>		
					</li>		
				</ul>
			</div>
		</div>
	
	<?php if($person_info->image_id) {  ?>

	<div class="form-group">
	<?php echo form_label(lang('common_del_image').':', 'del_image',array('class'=>'col-sm-3 col-md-3 col-lg-2 control-label')); ?>
		<div class="col-sm-9 col-md-9 col-lg-10">
		<?php echo form_checkbox(array(
			'name'=>'del_image',
			'id'=>'del_image',
			'class'=>'delete-checkbox', 
			'value'=>1
		));
		echo '<label for="del_image"><span></span></label> ';
		
		?>
		</div>
	</div>

	<?php }  ?>



<div class="form-group">	
<?php echo form_label(lang('common_address_1').':', 'address_1',array('class'=>'col-sm-3 col-md-3 col-lg-2 control-label ')); ?>
	<div class="col-sm-9 col-md-9 col-lg-10">
	<?php echo form_input(array(
		'class'=>'form-control',
		'name'=>'address_1',
		'id'=>'address_1',
		'value'=>$person_info->address_1));?>
	</div>
</div>

			<div class="form-group">	
<?php echo form_label(lang('common_address_2').':', 'address_2',array('class'=>'col-sm-3 col-md-3 col-lg-2 control-label ')); ?>
	<div class="col-sm-9 col-md-9 col-lg-10">
	<?php echo form_input(array(
		'class'=>'form-control',
		'name'=>'address_2',
		'id'=>'address_2',
		'value'=>$person_info->address_2));?>
	</div>
</div>

			<div class="form-group">	
<?php echo form_label(lang('common_city').':', 'city',array('class'=>'col-sm-3 col-md-3 col-lg-2 control-label ')); ?>
	<div class="col-sm-9 col-md-9 col-lg-10">
	<?php echo form_input(array(
		'class'=>'form-control ',
		'name'=>'city',
		'id'=>'city',
		'value'=>$person_info->city));?>
	</div>
</div>

			<div class="form-group">	
<?php echo form_label(lang('common_state').':', 'state',array('class'=>'col-sm-3 col-md-3 col-lg-2 control-label ')); ?>
	<div class="col-sm-9 col-md-9 col-lg-10">
	<?php echo form_input(array(
		'class'=>'form-control ',
		'name'=>'state',
		'id'=>'state',
		'value'=>$person_info->state));?>
	</div>
</div>

			<div class="form-group">	
<?php echo form_label(lang('common_zip').':', 'zip',array('class'=>'col-sm-3 col-md-3 col-lg-2 control-label ')); ?>
	<div class="col-sm-9 col-md-9 col-lg-10">
	<?php echo form_input(array(
		'class'=>'form-control ',
		'name'=>'zip',
		'id'=>'zip',
		'value'=>$person_info->zip));?>
	</div>
</div>

			<div class="form-group">	
<?php echo form_label(lang('common_latitude').':', 'latitude',array('class'=>'col-sm-3 col-md-3 col-lg-2 control-label ')); ?>
	<div class="col-sm-9 col-md-9 col-lg-10">
	<?php echo form_input(array(
		'class'=>'form-control ',
		'name'=>'latitude',
		'id'=>'latitude',
		'step'=>'any',
		'placeholder'=>'16.4322',
		'value'=>$person_info->latitude ? (float)$person_info->latitude : ''));?>
	</div>
</div>

			<div class="form-group">	
<?php echo form_label(lang('common_longitude').':', 'longitude',array('class'=>'col-sm-3 col-md-3 col-lg-2 control-label ')); ?>
	<div class="col-sm-9 col-md-9 col-lg-10">
	<?php echo form_input(array(
		'class'=>'form-control ',
		'name'=>'longitude',
		'id'=>'longitude',
		'step'=>'any',
		'placeholder'=>'102.8236',
		'value'=>$person_info->longitude ? (float)$person_info->longitude : ''));?>
	</div>
</div>

			<div class="form-group">
				<div class="col-sm-offset-3 col-md-offset-3 col-lg-offset-2 col-sm-9 col-md-9 col-lg-10">
					<div class="input-group">
						<input type="text" id="map-search" class="form-control" placeholder="<?php echo lang('common_search_address'); ?>">
						<span class="input-group-btn">
							<button class="btn btn-default" type="button" id="map-search-btn"><i class="ion-search"></i></button>
						</span>
					</div>
				</div>
			</div>

			<div class="form-group">
				<div class="col-sm-offset-3 col-md-offset-3 col-lg-offset-2 col-sm-9 col-md-9 col-lg-10">
					<div id="location-map" style="height: 320px; border-radius: 4px; border: 1px solid #ddd; z-index: 1;"></div>
					<p class="help-block"><?php echo lang('common_click_on_map_to_set_location'); ?></p>
				</div>
			</div>

<link rel="stylesheet" href="<?php echo base_url(); ?>assets/css/leaflet.css" />
<style>
	.leaflet-container { font-family: inherit; }
</style>
<script src="<?php echo base_url(); ?>assets/js/leaflet.js"></script>

<script>
(function() {
	var latField = document.getElementById('latitude');
	var lngField = document.getElementById('longitude');
	var mapContainer = document.getElementById('location-map');
	
	if (!mapContainer) return;
	
	var lat = parseFloat(latField.value) || 16.4322;
	var lng = parseFloat(lngField.value) || 102.8236;
	
	var map = L.map('location-map', {
		center: [lat, lng],
		zoom: latField.value ? 15 : 6,
		zoomControl: true
	});
	
	L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
		attribution: '&copy; <a href="https://openstreetmap.org/copyright">OpenStreetMap</a>',
		maxZoom: 19
	}).addTo(map);
	
	// Fix: set marker icon path (Leaflet default path breaks with local files)
	delete L.Icon.Default.prototype._getIconUrl;
	L.Icon.Default.mergeOptions({
		iconRetinaUrl: '<?php echo base_url(); ?>assets/img/marker-icon-2x.png',
		iconUrl: '<?php echo base_url(); ?>assets/img/marker-icon.png',
		shadowUrl: '<?php echo base_url(); ?>assets/img/marker-shadow.png',
	});
	
	var marker = L.marker([lat, lng], {
		draggable: true
	}).addTo(map);
	
	if (!latField.value) {
		map.removeLayer(marker);
		marker = null;
	}
	
	function setPosition(lat, lng) {
		latField.value = lat.toFixed(8);
		lngField.value = lng.toFixed(8);
		
		if (marker) {
			marker.setLatLng([lat, lng]);
		} else {
			marker = L.marker([lat, lng], {draggable: true}).addTo(map);
			marker.on('dragend', function() {
				var pos = marker.getLatLng();
				setPosition(pos.lat, pos.lng);
			});
		}
		map.setView([lat, lng], 15);
	}
	
	map.on('click', function(e) {
		setPosition(e.latlng.lat, e.latlng.lng);
	});
	
	if (marker) {
		marker.on('dragend', function() {
			var pos = marker.getLatLng();
			setPosition(pos.lat, pos.lng);
		});
	}
	
	// Nominatim search
	var searchInput = document.getElementById('map-search');
	var searchBtn = document.getElementById('map-search-btn');
	
	function doSearch() {
		var query = searchInput.value.trim();
		if (!query) return;
		
		searchBtn.innerHTML = '<i class="ion-load-c"></i>';
		
		fetch('https://nominatim.openstreetmap.org/search?format=json&q=' + encodeURIComponent(query) + '&limit=5&countrycodes=', { headers: { 'User-Agent': 'PHPPOS/1.0' } })
			.then(function(r) { return r.json(); })
			.then(function(data) {
				searchBtn.innerHTML = '<i class="ion-search"></i>';
				if (data.length > 0) {
					var r = data[0];
					setPosition(parseFloat(r.lat), parseFloat(r.lon));
					searchInput.value = r.display_name;
				} else {
					alert('No results found');
				}
			})
			.catch(function() {
				searchBtn.innerHTML = '<i class="ion-search"></i>';
				alert('Search failed');
			});
	}
	
	searchBtn.addEventListener('click', doSearch);
	searchInput.addEventListener('keypress', function(e) {
		if (e.key === 'Enter') {
			e.preventDefault();
			doSearch();
		}
	});
	
	// If we have lat/lng, reverse geocode on load
	if (latField.value) {
		fetch('https://nominatim.openstreetmap.org/reverse?format=json&lat=' + lat + '&lon=' + lng, { headers: { 'User-Agent': 'PHPPOS/1.0' } })
			.then(function(r) { return r.json(); })
			.then(function(data) {
				if (data && data.display_name) {
					searchInput.value = data.display_name;
				}
			})
			.catch(function() {});
	}
})();
</script>


	<div class="form-group">	
<?php echo form_label(lang('common_comments').':', 'comments',array('class'=>'col-sm-3 col-md-3 col-lg-2 control-label ')); ?>
	<div class="col-sm-9 col-md-9 col-lg-10">
	<?php echo form_textarea(array(
		'name'=>'comments',
		'id'=>'comments',
		'class'=>'form-control text-area',
		'value'=>$person_info->comments,
		'rows'=>'5',
		'cols'=>'17')		
	);?>
	</div>
</div>
<?php
if ($this->Location->get_info_for_key('mailchimp_api_key') && $controller_name != "login")
{
	$this->load->helper('mailchimp');
?>
			<div class="form-group">
	<div class="column">	
		<?php echo form_label(lang('common_mailing_lists').':', 'mailchimp_mailing_lists',array('class'=>'col-sm-3 col-md-3 col-lg-2 control-label ')); ?>
	</div>
	
    <div class="column">
		<ul style="list-style: none; float:left;">
	<?php
	foreach(get_all_mailchimps_lists() as $list)
	{
		echo '<li>';
		echo form_checkbox(array('name'=> 'mailing_lists[]',
		'id' => $list['id'],
		'value' => $list['id'],
		'checked' => email_subscribed_to_list($person_info->email, $list['id']),
		'label'	=> $list['id']));
		
		echo '<label for="'.$list['id'].'"><span></span></label> '.$list['name'];
		echo '</li>';
	}
	?>
	</ul>
	</div>
	<div class="cleared"></div>
</div>
<?php
}
?> 
	</div><!-- /col-md-12 -->
</div><!-- /row -->