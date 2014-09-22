/* Copyright 2014 Jeremie Roy. All rights reserved.
 * License: http://www.opensource.org/licenses/BSD-2-Clause
 */

if (typeof (CCPEVE) !== "undefined" ) {CCPEVE.requestTrust('http://www.yourwebsite.org'); }

function parse_date(str){ 
    var arr = str.split(/[- :]/);
    return new Date(arr[0], arr[1]-1, arr[2], arr[3], arr[4], arr[5]);
}

function shorten_date(str){ 
  var arr = str.split(/[- :]/);
  return arr[3]+":"+arr[4];
}


function update_hostile() {
  EveLiveData.update_local_hostile();
}

function update_clear() {
  EveLiveData.update_local_clear();
}

function toggle_options() {
    var options_div = document.getElementById("header_options");  
    if (options_div.style.display == "block") {
        options_div.style.display = "none";
    } else {
        options_div.style.display = "block";
    }
}

//storage for the global options
var Options = {
    map_mode: 0,
    show_links: true,
    show_names: true,
    show_elapsed_times: true,    
    show_security_status: true,    
    orbit_targets : false
};

var JUMP_CIRCLE_RADIUS = 80;
var NODE_RADIUS = 8;

function set_map_mode(mode) {
    Options.map_mode = mode;
    draw_map();
}

function toggle_show_links() {
    Options.show_links = !Options.show_links;
    draw_map();
}

function toggle_show_names() {
    Options.show_names = !Options.show_names;
    draw_map();
}

function toggle_show_elapsed_times() {
    Options.show_elapsed_times = !Options.show_elapsed_times;
    draw_map();
}


function toggle_show_security_status() {
    Options.show_security_status = !Options.show_security_status;
    draw_map();
}
function toggle_show_intel_panel() {    
    var intel_panel = document.getElementById("intel_container");
    if (intel_panel.style.display == "none") {
        intel_panel.style.display = "inline-block";
    } else {
        intel_panel.style.display = "none";
    }
}


function toggle_orbit_target() {
    Options.orbit_targets  = !Options.orbit_targets;   
}

var Data = {
    max_jumps:1,
    max_x:1,
    min_x:-1,
    max_y:1,
    min_y:-1,
    nodes: [],
    links:[],
    nodes_per_jumps:[]
};

var map_container = document.getElementById('map_container');
  width = map_container.clientWidth;
  height = map_container.clientHeight;

var message_overlay = null;
function setMessageOverlay(text)
{
    removeMessageOverlay();        
    message_overlay = d3.select("svg")
        .append("text")
        .attr("class", "info")
        .attr("x", map_container.clientWidth / 2)
        .attr("y", map_container.clientHeight / 2)
        .attr("dy", ".35em")
        .text("text");
}

function removeMessageOverlay()
{
  if(message_overlay != null)
  {
    message_overlay.remove();
    message_overlay = null;
  }
}

function print_error(txt) 
{
  setMessageOverlay("Error: "+txt);  
}


var security_status_color_V = [-1, -0.9, -0.8, -0.7, -0.6, -0.5, -0.4, -0.3, -0.2, -0.1, 0, 0.1, 0.2, 0.3, 0.4, 0.5, 0.6, 0.7, 0.8, 0.9, 1];
var security_status_color_C = ["#8B0000", "#960001", "#A10002", "#AC0002", "#B80002", "#C30002", "#CF0002", "#DB0001", "#E70001", "#F30001", "#FF0000", "#FF5F00", "#FF8C00", "#FF9500", "#FF9D00", "#FFA500", "#D4C800", "#9BE500", "#00FF00", "#46FF9C", "#00FFFF"];

// unfortunately, this break the IGB
//var security_status_color = d3.scale.linear()
//    .domain(security_status_color_V)
//    .range(security_status_color_C);

function security_status_color(v) {
    var idx = 10 + Math.floor(v * 10);
    return security_status_color_C[idx];
}
//console.log("l: "+security_status_color_V.length);
//console.log("-1: "+security_status_color(-1));
//console.log("1: "+security_status_color(1));
//console.log("0: "+security_status_color(0));

/*
function show_system_menu(d) {
    hide_system_menu();


    var x = translation[0] + scaleFactor * d.x;
    var y = translation[1] + scaleFactor * d.y;

    system_menu = map_svg.append("text")
        .attr("dx", x)
        .attr("dy", y)
        .text("set destination: " + d.name);
}

function hide_system_menu() {
    if (system_menu != null) {
        system_menu.remove();
    }
}*/


//create root svg node
var main_svg = d3.select("#map_container")
    .append("svg")
    .style("width","100%")
    .style("height","100%");

var map_svg = null;
var system_menu = null;

var nodes = null;
var node_names = null;
var node_circles = null;

var links = null;

var circles_ref = null;

var scaleFactor = 1;
var translation = [0, 0];

//append zoom behavior to the map container
d3.select("#map_container")
    .call(zm = d3.behavior.zoom()
        .scaleExtent([0.25, 12])
        .on("zoom", zoom)
        .on("zoomstart", zoom_start)
        .on("zoomend", zoom_end));

function draw_map() {   

    //console.log("draw_map");
    // Remove map layout and data
    if (map_svg != null) {
        map_svg.remove();
    }
   
    map_svg = main_svg.append("g")
        .attr("id", "map_svg");

    //append jumps reference circles
    if (Options.map_mode != 0 ) {
        var radiuses = Array();
        for (var i = 1; i <= Data.max_jumps; ++i) {
            radiuses.push(JUMP_CIRCLE_RADIUS * i);
        }

        circles_ref = map_svg.selectAll(".jump_circle")
            .data(radiuses)
            .enter()
            .append("circle")
            .attr("class", "jump_circle")
            .attr("cx", width / 2)
            .attr("cy", height / 2)
            .attr("r", function (d) {
                return d;
            });
    } else {
        circles_ref = null;
    }

    //setup force system
    var force = d3.layout.force()
        .gravity(0)
        .charge(-700)
        //.charge( function(d) { return Math.pow(4, 1 + Data.max_jumps-d.jumps) * (-100); } )
        //.charge( function(d) { return Math.pow(4, 1 + d.jumps) * (-100); } )
        .linkStrength(1)
        .linkDistance(10)
        //.linkDistance( function(link) { return JUMP_CIRCLE_RADIUS * Math.max(0.1, Math.abs(link.source.jumps - link.target.jumps));  })
        .size([width, height]);

    var decalX = 0;
    var decalY = 0;

    var map_width = Data.max_x - Data.min_x;
    var map_height = Data.max_y - Data.min_y;

    //rescale the systems positions to fit 90% of the map area
    var scale_size = 0.9 * Math.min(width / map_width, height / map_height);
     
    Data.nodes.forEach(function (node) {
        node.px = (node.system.x - Data.min_x) * scale_size;
        node.py = (map_height - node.system.y + Data.min_y) * scale_size;

        if (node.jumps == 0) {
            decalX = width / 2 - node.px;
            decalY = height / 2 - node.py;
            node.fixed = true;
        } else
            node.fixed = (Options.map_mode == 0);
    });

    if (Options.map_mode != 0) {
        //center on current system
        Data.nodes.forEach(function (node) {
            node.px += decalX;
            node.py += decalY;
            node.x = node.px;
            node.y = node.py;
        });
    } else {      
        //center on screen
        Data.nodes.forEach(function (node) {
            node.px += (width - scale_size * map_width) / 2;
            node.py += (height - scale_size * map_height) / 2;
            node.x = node.px;
            node.y = node.py;
        });
    }

    force
        .nodes(Data.nodes)
        .links(Data.links)
        .on("tick", tick);

    if (Options.show_links) {
        links = map_svg.selectAll(".link")
            .data(Data.links)
            .enter()
            .append("line")
            .attr("class", "link");
    } else {
        links = null;
    }

    nodes = map_svg.selectAll(".node")
        .data(Data.nodes)
        .enter()
        .append("g") //set a group for the node
        .attr("class", "node")        
        .attr("id", function (d) {          
            return "system_" + d.system.id;
        });
    //.on("click", function(d) { show_system_menu(d); });
    //.call(force.drag)
    //.on("mousedown", function() { d3.event.stopPropagation(); });

    if (Options.show_security_status) {
        nodes.style("stroke", function (d) {
            return security_status_color(d.system.sec);
        });
    }

    node_circles = nodes.append("circle")
        //.attr("r", function(d) { return 4 + 2*(Data.max_jumps - d.jumps); } )      
        .attr("r", NODE_RADIUS);
        //.attr("id", function (d) {
            //return "system_" + d.id;
        //});   

    if (Options.show_names) {
        node_names = nodes.append("text")
            .attr("text-anchor","middle")
            .text(function (d) {
                return d.system.name;
            });
    }else if(Options.show_elapsed_times)
    {
       node_names = nodes.append("text")
            .attr("text-anchor","middle");
    }

    function tick(e) {
        if (Options.map_mode == 1) {
            nodes.each(function (node) {
                if (node.jumps > 0) {
                    var dx = node.x - width / 2;
                    var dy = node.y - height / 2;
                    var l = Math.sqrt(dx * dx + dy * dy);
                    node.x = (width / 2 + dx / l * node.jumps * JUMP_CIRCLE_RADIUS);
                    node.y = (height / 2 + dy / l * node.jumps * JUMP_CIRCLE_RADIUS);

                } else {
                    node.x = width / 2;
                    node.y = height / 2;
                }
            });
        } else if (Options.map_mode == 2) {
            var index_per_jumps = Array();
            for (var i = 0; i < Data.max_jumps + 1; ++i) {
                index_per_jumps.push(0);
            }
            nodes.each(function (node) {
                if (node.jumps > 0) {

                    var angle = -Math.PI / 2 + Math.PI * 2 * index_per_jumps[node.jumps] / Data.nodes_per_jumps[node.jumps];
                   
                    node.x = width / 2 + Math.cos(angle) * node.jumps * JUMP_CIRCLE_RADIUS;
                    node.y = height / 2 + Math.sin(angle) * node.jumps * JUMP_CIRCLE_RADIUS;
                    index_per_jumps[node.jumps]++;
                } else {
                    node.x = width / 2;
                    node.y = height / 2;
                }
            });
        } 
        /*else if (Options.map_mode == 3) {
            var index_per_jumps = Array();
            for (var i = 0; i < Data.max_jumps + 1; ++i) {
                index_per_jumps.push(0);
            }
            
            var base_count = 10;
            var ref_angle = (Math.PI*2) / base_count;

            nodes.each(function (node) {
                if (node.jumps > 0) {
                    var angle_step = Math.PI * 2 / Data.nodes_per_jumps[node.jumps];
                    angle_step = Math.min(angle_step, ref_angle/node.jumps);

                    var angle_start = (-Math.PI/2);
                    //center it
                    angle_start-= angle_step * (Data.nodes_per_jumps[node.jumps]-1)/2;

                    var angle = angle_start + angle_step * index_per_jumps[node.jumps];
                    
                    node.x = width / 2 + Math.cos(angle) * node.jumps * JUMP_CIRCLE_RADIUS;
                    node.y = height / 2 + Math.sin(angle) * node.jumps * JUMP_CIRCLE_RADIUS;
                    index_per_jumps[node.jumps]++;
                } else {
                    node.x = width / 2;
                    node.y = height / 2;
                }
            });
        }*/
       
    }//end of tick

    if (Options.map_mode == 1) {
        // Run the layout a fixed number of times.    
        force.start();
        var n = 100;
        for (var i = n; i > 0; --i) force.tick();
        force.stop();
    } else {
        force.start();
        force.tick();
        force.stop();
    }

    draw_node_names();

    //update intel layout
    draw_intel();

    zoom();
} //end draw_map

function draw_node_names()
{
   //optimize system name position
    node_names.each(function (d) {
        var d_this = d3.select(this);

        var dx = d.x - width / 2;
        var dy = d.y - height / 2;
        var l2 = dx * dx + dy * dy;
        
        var b = this.getBBox();
        
        if( l2 < 1 ){
          dx = 0; 
          dy = -1;
        }else{
          var l = Math.sqrt(l2);
          dx /= l;
          dy /= l;
        }
        d_this.attr("dx", 12*dx + dx*b.width/2 );
        d_this.attr("dy", 12*dy + (dy+0.5)*b.height/2 );
    });
}

function zoom_start() {}

function zoom_end() {}

function zoom() {

    if (d3.event != null) {
        scaleFactor = d3.event.scale;
        translation = d3.event.translate;
    }

    //diameter = 2*PI*R    
    var radius= NODE_RADIUS;
    for(var i=1;i<Data.nodes_per_jumps.length;++i)
    {
      var perimeter = Math.PI * 2*JUMP_CIRCLE_RADIUS*i * scaleFactor;
      var tmp_radius = (0.5 * perimeter / Data.nodes_per_jumps[i])-1;     
      if(tmp_radius < radius)
      {
        radius = tmp_radius;    
      }      
    }
    if(radius < NODE_RADIUS)
    {      
      node_circles.attr("r", radius);
    }     

    //handle nodes zoom  
    if (nodes != null) {
        nodes.attr("transform", function (d) {
            var x = translation[0] + scaleFactor * d.x;
            var y = translation[1] + scaleFactor * d.y;
            var s = "translate(" + x + "," + y + ")";
            return s;
        });
    }

    //handle links zoom
    if (Options.show_links && links != null) {
        links.attr("x1", function (d) {
            return translation[0] + scaleFactor * d.source.x;
        })
            .attr("y1", function (d) {
                return translation[1] + scaleFactor * d.source.y;
            })
            .attr("x2", function (d) {
                return translation[0] + scaleFactor * d.target.x;
            })
            .attr("y2", function (d) {
                return translation[1] + scaleFactor * d.target.y;
            });
    }

    //handle jumps circle zoom
    if (circles_ref != null) {
        circles_ref.attr("cx", function (d) {
            return translation[0] + scaleFactor * width / 2;
        })
            .attr("cy", function (d) {
                return translation[1] + scaleFactor * height / 2;
            })
            .attr("r", function (d) {
                return scaleFactor * d;
            });
    }
}


var tracked_targets=[];
var t_ar =["t1","t2","t3"];


var target_timer = new Date();
d3.timer(orbit_targets);

function orbit_targets() 
{  
  if(Options.orbit_targets)
  {
    var delta = new Date() - target_timer;
    for (var i = 0; i < tracked_targets.length; i++) {   
        tracked_targets[i].attr("transform", "rotate("+delta*50/360+")");
    }
  }
}

function draw_targets(trackers) {
  tracked_targets=[];

  for (var i = 0; i < t_ar.length; i++) 
  {
    var target_node = d3.select("#target_"+t_ar[i]);   
    if(t_ar[i] in trackers)
    {         
        var system_node = d3.select("#system_" + trackers[t_ar[i]]);
        if(!system_node.empty())
        {
           
          var create = true;
          //should I create a new target node
          if(!target_node.empty() )
          {
              if( target_node.node().parentNode != system_node.node())
              {
                target_node.remove();
              }else{ 
                create = false;                
                tracked_targets.push(target_node);
              }
          }

          if(create)
          {      
              var d = NODE_RADIUS+7;
              var w = 6;
              var a = 45*i*Math.PI/180;
              var n = system_node.append("rect")
                .attr("class", "tracking_target"+" "+t_ar[i])
                //.attr("shape-rendering","crispEdges")
                .attr("id", "target_"+t_ar[i])            
                .attr("x", d*Math.cos(a) - w/2)
                .attr("y", d*Math.sin(a) - w/2)
                .attr("width", w)
                .attr("height", w);
            tracked_targets.push(n);
          }
        }else if(!target_node.empty())
        {
          target_node.remove();
        } 

    }else if(!target_node.empty())
    {
      target_node.remove();
    }    
  }   
}

function draw_intel() {
    //console.log("draw_intel");
    var serverTime = EveLiveData.get_server_time();

    nodes.each(function (node) {
      var intel = EveLiveData.get_intel_by_system(node.system.id);      
      if(intel != null)
      {
        var system_node = d3.select(this);
        var system_circle = system_node.select("circle");
        var seen_at = parse_date(intel.seen_at);

        var timeSpanSeconds = (serverTime - seen_at) * 0.001;

        //console.log("s:"+serverTime+" | "+seen_at);
        //console.log(timeSpanSeconds);
        var oldTime = 30 * 60;
        var middleTime = 15 * 60;
        var freshTime = 5 * 60;
        //intel.status = Math.floor(Math.random() * 3) ;
        //timeSpanSeconds = 0;
        //console.log(intel.status );

        if (timeSpanSeconds > oldTime || intel.status == 3 ) {
            system_circle.attr("class", "gray");
            if(Options.show_elapsed_times)
            {
              var system_text = system_node.select("text");
              system_text.text( function (d) {
                if (Options.show_names) {
                  return d.system.name;
                }else{
                  return "";
                }
              });
            }
        } else
        {
          //0 = clear
          if (intel.status == 0) {
              if (timeSpanSeconds > middleTime)
                  system_circle.attr("class", "oldBlue");
              else if (timeSpanSeconds > freshTime)
                  system_circle.attr("class", "middleBlue");
              else
                  system_circle.attr("class", "freshBlue");
          } else {
              if (timeSpanSeconds > middleTime)
                  system_circle.attr("class", "oldRed");
              else if (timeSpanSeconds > freshTime)
                  system_circle.attr("class", "middleRed");
              else
                  system_circle.attr("class", "freshRed");
          }

          if(Options.show_elapsed_times)
          {
              var system_text = system_node.select("text");
              var elapsed = Math.min(timeSpanSeconds, 30 * 60);
              system_text.text( function (d) {
                
                if (Options.show_names) {
                  return d.system.name+" "+toMMSS(elapsed);
                }else{
                  return toMMSS(elapsed);
                }
              });
          }
        }
      }
    });
    
    if(Options.show_elapsed_times)
    {
      draw_node_names();
    }   
}

function toMMSS (val ) {
    var seconds = Math.floor(val),
        hours = Math.floor(seconds / 3600);
    seconds -= hours * 3600;
    var minutes = Math.floor(seconds / 60);
    seconds -= minutes * 60;

    if (hours < 10) {
        hours = "0" + hours;
    }
    if (minutes < 10) {
        minutes = "0" + minutes;
    }
    if (seconds < 10) {
        seconds = "0" + seconds;
    }
    return /*hours+':'+*/ minutes + ':' + seconds;
}


var local_server_timespan_seconds = 0;
var timer_start = new Date();
var local_status = 0;
var timer_interval = 0;

function update_local_timer() {
    var elapsed = (new Date() - timer_start) * 0.001;
    elapsed += local_server_timespan_seconds;
    var timeElem = document.getElementById("local_timer_text");
    var friendlyElem = document.getElementById("progress_friendly");
    var hostileElem = document.getElementById("progress_hostile");

    if((elapsed > 30 * 60) || local_status==3 )
    {      
      timeElem.innerHTML = "??:??";
      elapsed = 30*60;
    }else
    {
      timeElem.innerHTML = toMMSS(elapsed);
    }   

    var ratio = 1 - elapsed / (30 * 60);
    if (local_status == 0) {
        friendlyElem.style.width = Math.round(100 * ratio) + "%";
        //friendlyElem.setAttribute("style","width:"+Math.floor(100*ratio)+"%");
        hostileElem.style.width = null;
    } else if (local_status == 1) {
        friendlyElem.style.width = null;
        hostileElem.style.width = Math.round(100 * ratio) + "%";
        //hostileElem.setAttribute("style","width:"+Math.floor(100*ratio)+"%");
    }else{
        friendlyElem.style.width = null;
        hostileElem.style.width = null;
    }
}


EveLiveData.tracker1Elem = document.getElementById("tracker_1");
EveLiveData.tracker2Elem = document.getElementById("tracker_2");
EveLiveData.tracker3Elem = document.getElementById("tracker_3");


EveLiveData.on_local_change=function(local_system_id){ 
  Data = EveStaticData.getSystemsWithin(local_system_id, 4);
  draw_map();
  var local_system = EveStaticData.getSystemByID(local_system_id);
  document.getElementById("local_system_name").innerHTML = local_system.name;

};

EveLiveData.on_local_status_change=function(intel){ 
 
  clearInterval(timer_interval);
  if(typeof(intel) !== "undefined")
  {
     //console.log("local:"+intel.system_id+" at:"+intel.seen_at); 
    var serverTime = EveLiveData.get_server_time();  
    var seen_at = parse_date(intel.seen_at);
    var timeSpanSeconds = (serverTime - seen_at) * 0.001;

    local_server_timespan_seconds = timeSpanSeconds;
    timer_start = new Date();
    local_status = intel.status;
    update_local_timer();
    timer_interval = setInterval(update_local_timer, 1000);
  }else{
     console.log("no local intel"); 
    local_status = 3;    
  }
  update_local_timer();
};

EveLiveData.on_new_intel=function(intels){ 
  //new intel loaded
  var intel_panel = d3.select("#intel_container");

  intels.forEach(
    function (intel) {
      var div = intel_panel.insert("div", ":first-child");
      if(intel.status==0)
        div.attr("style","background-color: #0000AA");
      else if(intel.status==1)
        div.attr("style","background-color:#AA0000");

      var system =EveStaticData.getSystemByID(intel.system_id);
      var region =EveStaticData.getRegionByID(system.region_id);
      //div.html(shorten_date(intel.seen_at)+" "+system.name);//+" "+intel.status);          
      div.html(shorten_date(intel.seen_at)+"  -  "+region.name+" - "+system.name);
    }
  ); 
};
  
EveLiveData.on_tracker_change=function(trackers){
   draw_targets(trackers);    
};

EveLiveData.on_intel_update=function(){ 
 draw_intel();
}

EveStaticData.init(function()
  {
    draw_map();
    //load the first intel
    EveLiveData.init();
  });

// window.onresize = function(){
//   console.log("resize event detected!");
// }
