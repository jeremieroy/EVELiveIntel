<?php 
/* Copyright 2014 Jeremie Roy. All rights reserved.
 * License: http://www.opensource.org/licenses/BSD-2-Clause
 */
session_start(); /// initialize session
include("./include/security.php");
check_logged();

//var_dump($_SESSION);
?>
<!DOCTYPE html>
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, minimum-scale=1" />
<title>Eve Online Live Intel</title>
<style>

html, body { 
  margin: 0;
  color:#bfbfbf;
  background: #121212;
}
div, button{
  margin:0;
  padding:0;
}

.header_intel {
  overflow: hidden;
  position: absolute;  
  height:34px;
  width:100%;
  left: 0; right: 0; top: 0;
  background: #010101;
  border-bottom: #414141 solid 1px;
  font: 12px "Trebuchet MS", Helvetica, sans-serif;
}

.header_intel>div {
  margin:3px;
}

.header_intel a {
  text-decoration: underline;
  color:#bfbfbf;
  font: 10px "Trebuchet MS", Helvetica, sans-serif;
  margin-left: 10px;
}

.local_system_name
{
  font-weight: bold;
  color: #FFFFFF;
}

.header_options {  
  overflow: hidden;
  position: absolute;
  display: none;  
  left: 0; right: 0; top: 34px;
}

.header_options label{
  display: block;
  clear:left;
}

.box {
  float: left;
  height:125px;
  padding: 6px;
  padding-bottom: 10px; 
  background: #010101;
  border: #414141 solid 1px;
  font: 12px "Trebuchet MS", Helvetica, sans-serif;
}

.box h1 {
    color:#f2f2f2;
    text-align:center;
    margin-top:0px;
    margin-bottom:5px;
    padding-bottom:5px;
    border-bottom: #414141 solid 1px;
    font: 12px "Trebuchet MS", Helvetica, sans-serif;
}
.box h1 span { 
  font-weight: bold;
}
.box>div {   
   margin-bottom:5px;
}

.track_token{ 
    clear:left;
    color:#f2f2f2;
    text-align:center;     
    padding-top: 5px;
    border-top: #414141 solid 1px;
    font: 12px "Trebuchet MS", Helvetica, sans-serif;
}

.progress_button
{ 
  cursor:pointer;
  display: inline-block;
  font: 12px "Trebuchet MS", Helvetica, sans-serif;
  color:#bfbfbf;  
  border: #414141 solid 1px;
  background-color:#313131;
  border-radius: 2px; 
  -webkit-border-radius:2px; 
  width:50px;
  position: relative;
  vertical-align: middle;
  margin:3px;  

}

.progress_button div {     
  width: 0%; /* Adjust with JavaScript */
  height: 18px;
  border-radius: 2px;
  -webkit-border-radius:2px;
  text-align: center; 
}

.progress_button span {
  display: inline-block;
  position: absolute;
  width: 100%;
  left: 0;
}

.hostile div{
  background-color: #AA0000;
}

.hostile:hover {
  color:#ffffff;
  background-color:#AA0000;
}

.friendly div{
  background-color: #0000AA;
}

.friendly:hover {
  color:#ffffff;
  background-color:#0000AA;
}

#local_timer_text
{
  padding-left:1px;
  padding-right:1px;
  /*border: #414141 solid 1px;*/
}

.legend
{
  border: gray solid 1px;
  padding:3px 3px;
  margin: 1px;
  width:45px;
  color:#FFFFFF;
  font-size:10px;
  text-align:center;
  display: inline-block;
}

.gray { fill: gray; background-color:gray; }
.oldBlue { fill: #7070E0; background-color:#7070E0; }
.middleBlue { fill: #1414FF; background-color:#1414FF;}
.freshBlue { fill: #0000AA; background-color:#0000AA;}

.oldRed { fill: #E58989; background-color:#E58989; }
.middleRed { fill: #FF1414; background-color:#FF1414;}
.freshRed { fill: #AA0000; background-color:#AA0000;}


input[type="radio"]{
float:left;
}

input[type="checkbox"]{
float:left;
}

input[type="text"]{
margin-left: 4px;
width:70px;
}

.map_container {
  overflow:hidden;
  position: absolute;
  top: 35px; bottom: 0;
  left: 0; right: 0;
  background-color:#121212;
  z-index:-1;
}

.intel_container {
overflow-y:scroll;
position: absolute;
display:inline-block;
float: right;
top: 35px;
right:0px;
bottom: 0;
border-left: #414141 solid 1px;

background-color:#121212;
}
.intel_container div {
  overflow: none;
  font: 11px "Trebuchet MS", Helvetica, sans-serif;
  margin-left: 0px;
  padding-left: 3px;
  padding-right: 3px;
  color:#FFFFFF;
  border-bottom: #414141 solid 1px;
  border-right: #414141 solid 1px;
}

.info {
  fill: #FFFFFF;
  stroke-width: 0px;
  font: 18px sans-serif;
  text-anchor: middle;  
}

.node {
  shape-rendering:"crispEdges";
  fill: gray;
  stroke: #FFFFFF;
  stroke-width: 2px; 
}

.node text {
  fill: #FFFFFF;
  stroke-width: 0px;
  pointer-events: none;
  font: 11px "Trebuchet MS", Helvetica, sans-serif;
}

.link {
  stroke: #FFFFFF;
  stroke-opacity: .2;
}

.jump_circle
{  
  stroke: gray;
  fill:none;
}

.tracking_target
{  
  fill: #00FF00;
  stroke: #272822;
  stroke-width: 1px; 
}

.t1{ color:#66D9EF; fill:#66D9EF; }
.t2{ color:#F92672; fill:#F92672; }
.t3{ color:#A6E22E; fill:#A6E22E; }
.t4{ color:#FD971F; fill:#FD971F; }


/*
var security_status_color_V = [-1, -0.9, -0.8, -0.7, -0.6, -0.5, -0.4, -0.3, -0.2, -0.1, 0, 0.1, 0.2, 0.3, 0.4, 0.5, 0.6, 0.7, 0.8, 0.9, 1];
var security_status_color_C = ["#8B0000", "#960001", "#A10002", "#AC0002", "#B80002", "#C30002", "#CF0002", "#DB0001", "#E70001", "#F30001", "#FF0000", "#FF5F00", "#FF8C00", "#FF9500", "#FF9D00", "#FFA500", "#D4C800", "#9BE500", "#00FF00", "#46FF9C", "#00FFFF"];
*/
.grad3
{
height:10px;
width:100%;
background: -webkit-linear-gradient(right, #8B0000, #960001, #A10002, #AC0002, #B80002, #C30002, #CF0002, #DB0001, #E70001, #F30001, #FF0000, #FF5F00, #FF8C00, #FF9500, #FF9D00, #FFA500, #D4C800, #9BE500, #00FF00, #46FF9C, #00FFFF); /* For Safari 5.1 to 6.0 */
background: -o-linear-gradient(right, #8B0000, #960001, #A10002, #AC0002, #B80002, #C30002, #CF0002, #DB0001, #E70001, #F30001, #FF0000, #FF5F00, #FF8C00, #FF9500, #FF9D00, #FFA500, #D4C800, #9BE500, #00FF00, #46FF9C, #00FFFF); /* For Opera 11.1 to 12.0 */
background: -moz-linear-gradient(right, #8B0000, #960001, #A10002, #AC0002, #B80002, #C30002, #CF0002, #DB0001, #E70001, #F30001, #FF0000, #FF5F00, #FF8C00, #FF9500, #FF9D00, #FFA500, #D4C800, #9BE500, #00FF00, #46FF9C, #00FFFF); /* For Firefox 3.6 to 15 */
background: linear-gradient(right, #8B0000, #960001, #A10002, #AC0002, #B80002, #C30002, #CF0002, #DB0001, #E70001, #F30001, #FF0000, #FF5F00, #FF8C00, #FF9500, #FF9D00, #FFA500, #D4C800, #9BE500, #00FF00, #46FF9C, #00FFFF); /* Standard syntax (must be last) */
}

.grad1
{
height:10px;
width:100%;
background: -webkit-linear-gradient(left, #0000AA, #1414FF, #7070E0, gray);
background: -o-linear-gradient(left, #0000AA, #1414FF, #7070E0, gray);
background: -moz-linear-gradient(left, #0000AA, #1414FF, #7070E0, gray);
background: linear-gradient(left, #0000AA, #1414FF, #7070E0, gray);
}

.grad2
{
height:10px;
width:100%;
background: -webkit-linear-gradient(left, #AA0000, #FF1414, #E58989, gray);
background: -o-linear-gradient(left, #AA0000, #FF1414, #E58989, gray);
background: -moz-linear-gradient(left, #AA0000, #FF1414, #E58989, gray);
background: linear-gradient(left, #AA0000, #FF1414, #E58989, gray);
}

</style>
</head>
<body>
<div class="header_intel" id="header_intel">  
    <div>    
    <div class="progress_button hostile" onclick="update_hostile();"><div id="progress_hostile"><span>Hostile</span></div></div>    
    <span id="local_timer_text">??:??</span>  
    <div type="button" class="progress_button friendly" onclick="update_clear();"><div id="progress_friendly"><span>Clear</span></div></div>
    
    <span id="local_system_name" class="local_system_name">Unknown</span>
    <a style="cursor:pointer;" onclick="toggle_options()">options</a>
    <a style="cursor:pointer;" href="logout.php">logout</a>    
  </div>
</div>

<div class="header_options" id="header_options">
  <div class = "box">
    <h1>Map layout</h1>
    <div>
      <label><input type="radio" name="group1" value="layout_classic" onclick="set_map_mode(0);" checked />Classic</label>
      <label><input type="radio" name="group1" value="layout_radial" onclick ="set_map_mode(1);" />Radial</label>
      <label><input type="radio" name="group1" value="layout_tactical" onclick ="set_map_mode(2);" />Tactical</label>
      <!--<label><input type="radio" name="group1" value="layout_tactical" onclick ="set_map_mode(3);" />Stacked</label>-->
    </div>    
  </div>  
  <div class = "box">
    <h1>Map options</h1>
    <div>
      <label><input type="checkbox" onclick ="toggle_show_links();" checked />Show links</label>
      <label><input type="checkbox" onclick ="toggle_show_names();" checked />Show names</label>
      <label><input type="checkbox" onclick ="toggle_show_elapsed_times();" checked />Show elapsed time</label>
      <label><input type="checkbox" onclick ="toggle_show_security_status();" checked />Show security status</label>
      <label><input type="checkbox" onclick ="toggle_show_intel_panel();" checked />Show intel panel</label>
    </div>
  </div>  

  <div class = "box">
    <h1>Legend</h1>
    <div>
      <div>      
        <span class="legend freshBlue">Clear</span>
        <span class="legend middleBlue">> 5 min</span>
        <span class="legend oldBlue">> 15 min</span>
        <span class="legend gray">> 30 min</span>
      </div>
      <div>
        <span class="legend freshRed">Hostile</span>
        <span class="legend middleRed">> 5 min</span>
        <span class="legend oldRed">> 15 min</span>
        <span class="legend gray">> 30 min</span>
      </div>
    </div>
    <!--
    <div class="grad1"></div>
    <div class="grad2"></div>
    <div class="grad3"></div>
  -->
  </div>
  <div class = "box">
    <h1>
<?php if( isset($_SESSION["unique_id"])) { 
        echo "My token:  <b>".$_SESSION["unique_id"]."</b>";
      }else{
        echo "Tracked token:  <b>".$_SESSION["track_token"]."</b>";
      }
?>
    </h1>
    <div>
      <label class="t1">Tracker 1<input type="text" id="tracker_1"/></label>        
      <label class="t2">Tracker 2<input type="text" id="tracker_2"/></label>
      <label class="t3">Tracker 3<input type="text" id="tracker_3"/></label>
      <label><input type="checkbox" onclick ="toggle_orbit_target();" />Orbit</label>      
    </div>
  </div> 
</div> 

<div class="map_container" id="map_container"></div>
<div class="intel_container" id="intel_container"></div>

<?php
echo '<script>';
if( isset($_GET["system_name"]) )
{
  echo 'var forced_system_name="'.$_GET["system_name"].'";';  
}

if( isset($_GET["track_token"]) )
{
  echo 'var track_token="'.$_GET["track_token"].'";';  
}
echo '</script>';
?>
<!-- 
<script src="http://d3js.org/d3.v3.min.js"></script> 
<script src="bench.js" ></script>
<script src="intelMap.js" ></script>
<script src="mapData.js" ></script> 
 -->
<script src="d3.min.js" ></script>
<script src="mapData.min.js" ></script>  
<script src="intel_map.min.js" ></script> 












