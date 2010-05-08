/**
 * Copyright (C) 2010 Justgizzmo.com
 * License: http://www.gnu.org/licenses/gpl.html GPL version 2 or higher
 */

/**
 * The base map object
 */
var UserMap = {
	/**
	 * The Main Google Map Object
	 */
	theMap: null,

	/**
	 * The default settings for the map
	 */
	defaults: {},

	/**
	 * The provided options
	 */
	options: {},

	/**
	 * Set up the main map.
	 */
	init: function()
	{
		// extend the options with the defaults
		var opts = $.extend({}, UserMap.defaults, UserMap.options);

		// the map canvas
		var mapCanvas = $('#user_map_canvas');

		// make the map canvas the correct height
		mapCanvas.height(opts.height)

		UserMap.theMap = new google.maps.Map(mapCanvas[0], {
			center: new google.maps.LatLng(opts.latlng[0], opts.latlng[1]),
			zoom: opts.zoom,
			MapTypeId: google.maps.MapTypeId.ROADMAP,
		});
	},

	/**
	 * A simpler way to create a icon
	 */
	makeIcon: function(img,size,anchor)
	{
		// defaults
		var img = img || 'icons/white.png',
			size = size || [20, 20],
			anchor = anchor || [4, 17];

		// return the 'MarkerImage'
		return new google.maps.MarkerImage(
			'usermap/img/'+img,
			new google.maps.Size(size[0],size[1]),
			new google.maps.Point(0,0),
			new google.maps.Point(anchor[0],anchor[1])
		);
	},

	/**
	 * A easy way to create the shadow image for markers
	 */
	makeShadow: function()
	{
		return UserMap.makeIcon('shadow.png', [35,20],[9,14]);
	}
};

/**
 * The Main Map
 */
UserMap.main = {

	/**
	 * Cache
	 */
	markers: [],
	infowindowCache: [],

	/**
	 * When you click the username on the list
	 */
	click: function(i)
	{
		var marker = UserMap.main.markers[i];
		if (typeof marker !== 'undefined')
			google.maps.event.trigger(marker,'click');
	},

	/**
	 * Set everything up
	 */
	init: function()
	{
		// init the map.
		UserMap.init();

		// extend the options with the defaults
		var opts = $.extend({}, UserMap.defaults, UserMap.options);

		// Make the user list scroll
		$('#punusermap #usermap_userlist .box').css('max-height',UserMap.defaults.height);

		// admin save location link
		$('#um_admin').click(function(){
			var lat = UserMap.theMap.getCenter().lat(),
				lng = UserMap.theMap.getCenter().lng(),
				zoom = UserMap.theMap.getZoom(),
				href = $(this).attr('href');

			$(this).attr('href',href.replace(/\&(.*)$/, '&lat='+lat+'&lng='+lng+'&z='+zoom));
		});

		// set up some vars
		var list = [],
			bounds = new google.maps.LatLngBounds(),
			infowindow = new google.maps.InfoWindow();

		// close the infowindow when a few things happen
		function closeinfowindow()
		{
			$('#usermap_userlist li.isactive').removeClass('isactive');
			infowindow.close();
		}
		google.maps.event.addListener(UserMap.theMap, 'click', closeinfowindow);
		google.maps.event.addListener(UserMap.theMap, 'rightclick', closeinfowindow);
		google.maps.event.addListener(UserMap.theMap, 'zoom_changed', closeinfowindow);

		// grab the userlist json!
		$.getJSON('usermap/list.php', function(data)
		{
			// check for errors
			if (data.error)
				return console.log(data.error);

			// look though the markers
			$.each(data, function(i,item)
			{
				var point = new google.maps.LatLng(item.point[0],item.point[1]);

				// save the marker to the userlist array
				list.push('<li id="u'+item.id+'"><a style="background-image:url(usermap/img/icons/'+item.icon+')" href="javascript:UserMap.main.click('+item.id+');">'+item.name+'</a></li>');
				UserMap.main.markers[item.id] = marker;

				//extend the bounds
				bounds.extend(point);

				// make the marker
				var marker = new google.maps.Marker({
					map: UserMap.theMap,
					position: point,
					icon: UserMap.makeIcon('icons/'+item.icon),
					shadow: UserMap.makeShadow(),
					title: item.name
				});

				// info window listener
				google.maps.event.addListener(marker, 'click', function(event)
				{
					// close the info window
					closeinfowindow()

					// if the info window has been opened before
					if (UserMap.main.infowindowCache[item.id])
					{
						infowindow.setContent(UserMap.main.infowindowCache[item.id]);
						infowindow.open(UserMap.theMap,marker);
					}
					else
					{
						// request the info window
						$.getJSON('usermap/list.php?id='+item.id, function(data)
						{
							UserMap.main.infowindowCache[item.id] = '<div id="infowindow"><h2><a href="profile.php?id='+item.id+'">'+item.name+'</a></h2><div class="box"><div class="inbox">'+data[0].html+'</div></div></div>';
							infowindow.setContent(UserMap.main.infowindowCache[item.id]);
							infowindow.open(UserMap.theMap,marker);
						});
					}

					$('#usermap_userlist li#u'+item.id).addClass('isactive');
				});
			});

			// fill the userlist
			if (list.length > 0)
				$('#usermap_userlist .inbox').html('<ul>'+list.join('')+'</ul>');

			// set the map
			if (opts.fitzoom && data.length != 0)
				UserMap.theMap.fitBounds(bounds);

			// if a id was provided, open its infowindow
			if (opts.id)
				UserMap.main.click(opts.id)
		});
	}
};


/**
 * The Profile Map
 */
UserMap.profile = {
	/**
	 * var that holds the location marker.
	 */
	loc_marker: null,

	/**
	 * the loc_marker options
	 */
	loc_marker_opts: {},

	/**
	 * Set up the profile map
	 */
	init: function()
	{
		// init the map.
		UserMap.init();

		// extend the options with the defaults
		var opts = $.extend({}, UserMap.defaults, UserMap.options);

		// set the marker options
		UserMap.profile.loc_marker_opts = {
			icon: UserMap.makeIcon('marker.png',	[35,35],[11,33]),
			shadow: UserMap.makeShadow(),
			draggable: true,
			map: UserMap.theMap
		};

		if (UserMap.options.latlng)
		{
			UserMap.profile.loc_marker_opts.position = new google.maps.LatLng(
				UserMap.options.latlng[0],
				UserMap.options.latlng[1]
			)
			UserMap.profile.loc_marker = new google.maps.Marker(UserMap.profile.loc_marker_opts);
			UserMap.profile.create_listeners();
		}

		google.maps.event.addListener(UserMap.theMap, 'click', function(event)
		{
			// if the marker doesnt exists, create it
			if (UserMap.profile.loc_marker == null)
			{
				UserMap.profile.loc_marker_opts.position = event.latLng;
				UserMap.profile.loc_marker = new google.maps.Marker(UserMap.profile.loc_marker_opts);
				UserMap.profile.create_listeners();
			}
			// otherwise update just move it.
			else
				UserMap.profile.loc_marker.setPosition(event.latLng);

			window.setTimeout(function() {UserMap.theMap.panTo(event.latLng);}, 200);
			$('#um_lat').val(event.latLng.lat());
			$('#um_lng').val(event.latLng.lng());
		});
	},

	create_listeners: function()
	{
		google.maps.event.addListener(UserMap.profile.loc_marker, 'dragend', function(event)
		{
			window.setTimeout(function() {UserMap.theMap.panTo(event.latLng);}, 200);
			$('#um_lat').val(event.latLng.lat());
			$('#um_lng').val(event.latLng.lng());
		});

		google.maps.event.addListener(UserMap.profile.loc_marker, 'click', function()
		{
			UserMap.profile.loc_marker.setMap(null);
			UserMap.profile.loc_marker = null;
			$('#um_lat').val('');
			$('#um_lng').val('');
		});
	},

/* doesnt return the correct location, atleast not for me anyways.
	find_location: function()
	{
		$.getScript('http://www.google.com/jsapi', function()
		{
			var client = google.loader.ClientLocation;

			if (client && client.latitude && client.longitude)
			{
				// console.log('we have a location', client);

				var client_latlng = new google.maps.LatLng(client.latitude,client.longitude);

				// if the marker doesnt exists, create it
				if (UserMap.profile.loc_marker == null)
				{
					UserMap.profile.loc_marker_opts.position = client_latlng;
					UserMap.profile.loc_marker = new google.maps.Marker(UserMap.profile.loc_marker_opts);
					UserMap.profile.create_listeners();
				}

				// otherwise update just move it.
				else
					UserMap.profile.loc_marker.setPosition(client_latlng);

				UserMap.theMap.setZoom(14)
				window.setTimeout(function() {UserMap.theMap.panTo(client_latlng);}, 200);
				$('#um_lat').val(client_latlng.lat());
				$('#um_lng').val(client_latlng.lng());
			}
		});
	},
 */

};