/* Copyright 2014 Jeremie Roy. All rights reserved.
 * License: http://www.opensource.org/licenses/BSD-2-Clause
 */
var EveStaticData = function() {

var factions_url = 'data/factions.json';
var regions_KS_url = 'data/region_KS.json';
	
 var StaticData = {
	factions:[],
	regions:[],
	systems:[]
};

function loadData(onDataLoaded)
{	
	d3.json(factions_url, onFactionsLoaded);

	function onFactionsLoaded (error, json) {
	 	//console.log("onFactionsLoaded: " + error);
		for(var i=0, l=json.factions.length; i<l; ++i)
		{
			var faction = json.factions[i];
			faction.id += json.faction_offset;
			StaticData.factions[faction.id] = faction;
		}

		//console.log(onRegionsLoaded);
		d3.json(regions_KS_url, onRegionsLoaded);
	}

	function onRegionsLoaded (error, json) {
		//console.log("onRegionsLoaded: " + error);
		//var start_time = Date.now();

		for(var reg_i=0, reg_l=json.regions.length; reg_i<reg_l; ++reg_i)
		{
			var region = json.regions[reg_i];
			region.id += json.region_offset;
			if(region.factionID != -1)
			{
				region.factionID += json.faction_offset;
			}

			StaticData.regions[region.id] = region;

			for(var sys_i=0, sys_l=region.systems.length; sys_i<sys_l; ++sys_i)
			{
				var system = region.systems[sys_i];
				system.region_id = region.id;

				if(typeof(system.factionID) === "undefined")
				{
					system.factionID = region.factionID;
				}

				system.id+= json.system_offset;

				if(system.factionID != -1)
				{
					system.factionID += json.faction_offset;
				}

				for(var j=0; j < system.links.length; ++j)
				{
					system.links[j]+=json.system_offset;
				}


				StaticData.systems[system.id] = system;
			}
		}

		onDataLoaded();
	}	
	
}//end initStaticData

return {
	getSystemByID:function(id)
	{
		return StaticData.systems[id];
	},
	getRegionByID:function(id)
	{
		return StaticData.regions[id];
	},
	getFactionByID:function(id)
	{
		return StaticData.factions[id];
	},
	init:function(onDataLoaded){
		loadData(onDataLoaded);
	},

 	getSystemsWithin:function(system_id, max_jumps){
		var nodes_out = [];
		var links_out =[];

		var visited = [];
		visited[system_id] = nodes_out.length;
		var visiting = [system_id];
		
		nodes_out.push({
			system:StaticData.systems[system_id],
			jumps:0
		});

		var system_per_jump = [1];
		for(var jump_id = 0; jump_id < max_jumps; ++jump_id)
		{		
			system_per_jump.push(0);
			var toVisit = [];
			for(var i=0; i<visiting.length; ++i)
			{			
				
				var links = StaticData.systems[visiting[i]].links;
				for(var j=0; j < links.length; ++j)
				{
					if( typeof(visited[links[j]]) === "undefined")
					{
						toVisit.push(links[j]);
						visited[links[j]] = nodes_out.length;

						nodes_out.push({
							system:StaticData.systems[links[j]],
							jumps:jump_id+1
						});
						system_per_jump[jump_id+1]++;
					}
				}
			}
			visiting = toVisit;
		}

		var minX = Number.POSITIVE_INFINITY;
		var maxX = Number.NEGATIVE_INFINITY;
		var minY = Number.POSITIVE_INFINITY;
		var maxY = Number.NEGATIVE_INFINITY;

		for(var i=0, l=nodes_out.length; i<l; ++i)
		{
			var system=nodes_out[i].system;
			if(system.x < minX ) minX = system.x;
			if(system.x > maxX ) maxX = system.x;
			if(system.y < minY ) minY = system.y;
			if(system.y > maxY ) maxY = system.y;
			for(var j=0, k = system.links.length; j<k; ++j)
			{
				if(typeof(visited[system.links[j]]) === "undefined") continue;

				var from = visited[system.id];
				var to = visited[system.links[j]];			
				if(from<to)
				{
					//console.log("f:"+from+"  to:"+to);
					links_out.push({
							source:from,
							target:to
					});
				}
			}
		}
		
		return {
			max_jumps:max_jumps,
			max_x:maxX,
			min_x:minX,
			max_y:maxY,
			min_y:minY,
			nodes: nodes_out,
			links:links_out,
			nodes_per_jumps:system_per_jump
		};
	}
}
}();

var EveLiveData = function() {
	var load_intel_url = 'services/intel.php?g=0';
	var send_intel_hostile_url = 'services/send_intel.php?status=1';
	var send_intel_clear_url = 'services/send_intel.php?status=0';

	var intel_polling_time = 5000;

	function parse_date(str){ 
	    var arr = str.split(/[- :]/);
	    return new Date(arr[0], arr[1]-1, arr[2], arr[3], arr[4], arr[5]);
	}
	
	var local_system_id = null;
	var last_intel_time_str = null;
	var last_intel_time = null;
	var all_intels = [];
	var intel_by_system = [];
	var load_intel_timeout = null;

	var on_update = null;

	function load_intel() {
		clearTimeout(load_intel_timeout);
		//console.log("load_intel");

		var url = load_intel_url;
		
		if(api.tracker1Elem!=null){ url+="&t1="+api.tracker1Elem.value; }			
		if(api.tracker2Elem!=null){ url+="&t2="+api.tracker2Elem.value; }			
		if(api.tracker3Elem!=null){ url+="&t3="+api.tracker3Elem.value; }
		
		if(last_intel_time_str != null)
		{
			url+="&last_update="+last_intel_time_str;
		}

		//console.log(url);	

		d3.json(url,
		  function (error, json) {
		  	//console.log("intel received");		    
		    if(json.error == true) {
		    	console.log("Error:"+json.message);
		      //print_error(json.message);
		      	return;
		    }  
		    var newIntel =  (json.intels.length > 0);
		    var newLocal = false;
		    var newLocalStatus = false;
		    var newTracker = false;

		    if(last_intel_time_str != json.serverTime){
		    	last_intel_time_str = json.serverTime;
		    	last_intel_time = parse_date(last_intel_time_str);
		    }

		    if (local_system_id != json.system_id){
		     	local_system_id = json.system_id;
		     	newLocal = true;
		     	newLocalStatus = true;
		     }

		    json.intels.forEach(
		      function (intel) {		      	
		      	all_intels.push(intel);
		      	if(typeof(intel_by_system[intel.system_id]) === "undefined")
		      	{		      		
		      		intel_by_system[intel.system_id] = intel;
		      		if( intel.system_id == local_system_id )
	      			{
	      				newLocalStatus = true;
	      			}
		      	}else
		      	{	
		      		if( parse_date(intel_by_system[intel.system_id].seen_at) < parse_date(intel.seen_at)  )
		      		{		      			
		      			intel_by_system[intel.system_id] = intel;
		      			if( intel.system_id == local_system_id )
		      			{
		      				newLocalStatus = true;
		      			}
		      		}
		      	}

		      }
		    );

		    //if(json.trackers.length>0)
		    {
		    	newTracker = true;
		    }

		    if(newLocal)
		   	{
		   		api.on_local_change(local_system_id);
		   	}
		   	if(newLocalStatus)
		   	{
				api.on_local_status_change(intel_by_system[local_system_id]);
		   	}
		   	if(newIntel)
		    {
		    	api.on_new_intel(json.intels);
		    }
		   	if(newTracker)
		   	{
		   		api.on_tracker_change(json.trackers);
		   	}	  

 			api.on_intel_update();
		    
		    //relaunch intel polling
		    load_intel_timeout = setTimeout(load_intel, intel_polling_time);
		  }
		);
	}

	function update_local_hostile() {
	    var xhr = new XMLHttpRequest();
	    xhr.open("GET", send_intel_hostile_url, true);
	    xhr.onload = load_intel;
	    xhr.send();
	}

	function update_local_clear() {
	    var xhr = new XMLHttpRequest();
	    xhr.open("GET", send_intel_clear_url, true);
	    xhr.onload = load_intel;
	    xhr.send();
	}

	var api  = {
		// textbox to bind from the view who should contains potentials tracker elements
		tracker1Elem:null,
		tracker2Elem:null,
		tracker3Elem:null,

		// these event function can be replaced at will, if multiple occurs at the same time
		// they occurs in the same order as presented here

		//called whenever the local system change
		on_local_change:function(local_system_id){ console.log("local:"+local_system_id); }, 
		//called whenever the intel status of the local system change
		on_local_status_change:function(intel){ console.log("local:"+local_system_id+" at:"+intel.seen_at); }, 
		//called whenever a new list of intels arrives
		on_new_intel:function(intels){ console.log(intels); },
		//called whenever a tracker change
		on_tracker_change:function(trackers){ console.log(trackers); },		
		//called whenever an update is received from server even if it is empty
		on_intel_update:function(){ },

		init:function(){
			load_intel();
		},
		get_server_time:function(){
			return last_intel_time;
		},
		get_local_system_id:function(){
			return local_system_id;
		},
		get_intel_by_system:function(system_id)
		{
			return intel_by_system[system_id];
		},

		//actions
		update_local_hostile:update_local_hostile,
		update_local_clear:update_local_clear
	};

	return api;
}();
