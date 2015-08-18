# EVELiveIntel - Live Intel Map for EVE Online

## What is it ?
An online map that centralize and visualize Intel knowledge (hostile players positions) in the game EVE Online.

This knowledge consist of a status for every system:  
Blue: no enemies - Red: enemies - Gray: no info or outdated info

* The map is dynamic, so whenever someone update the Intel status of a system every other connected players will see it within 5 seconds.
* The map is fancy and support drag and zoom (mousewheel) and various systems layouts for increased readability.
* The map works in the IGB and can be synchronized with an external browser (Tested with Chrome, Firefox, Safari and IE) including Android and IOS browsers (phone and tablets).
* The map is not organized by region but centered around your actual position and the neighboring systems, if you jump in another system, the map will detect it and adapt accordingly. So the map cover the whole known EVE universe, except I'm afraid the wormholes.
Supporting WH is definitely doable but would require this tool to became a mapping tool as well and thus may clutter the UI.

## Overview

![map overview](https://raw.githubusercontent.com/jeremieroy/EVELiveIntel/master/images/map_overview.png)

"Dude, that security status is confusing and I can't see the intel well !"
Sure, disable it.
Open the options and uncheck "Show security status":
![map options](https://raw.githubusercontent.com/jeremieroy/EVELiveIntel/master/images/map_classic_options.png)

Is that what you wanted ?
![map no security](https://raw.githubusercontent.com/jeremieroy/EVELiveIntel/master/images/map_classic_no_sec.png)

"Ok, but what about a highly connected system... like Jita ?"
![map Jita](https://raw.githubusercontent.com/jeremieroy/EVELiveIntel/master/images/map_jita.png)

"This is a mess, I cannot see what is close to me or what is far !"
That is what the radial layout is for. 
Open the options and select the "Radial" map layout.
It will reorganize that mess so that every system that is at X jumps of you (using shortest path) is visually at X jumps, and optimize the system position so that they don't overlap too much.
![map Radial](https://raw.githubusercontent.com/jeremieroy/EVELiveIntel/master/images/map_jita_radial.png)

"Mmh, that is cool, but dude, I want to play, I cannot waste half of my screen with that map all the time.
Also I need to have a very quick understanding of the situation so that I can react quickly."
I hear you my friend, our pixels are precious, that is what the Tactical layout is for.
In this mode, all the systems are spread out evenly along the "jumps circles".
Let's zoom out, disable the system names, links and security status. (And the browser bars)
And let me show you how it look in game:  
![map in IGB](https://raw.githubusercontent.com/jeremieroy/EVELiveIntel/master/images/map_in_game.png)

## Browser synchronization:
You can synchronize your in game browser with an out of game browser (e.g. on a tablet) using an identification token, these token are random and unique for each game session.  
![tokens options](https://raw.githubusercontent.com/jeremieroy/EVELiveIntel/master/images/tracker_option.png)
How to:
* log on the map with the IGB
* retrieve your identification token on the option panel
* log on the map with another browser and refer the token at login

Here is the map on an Ipad 2:
![Map on Ipad2](https://raw.githubusercontent.com/jeremieroy/EVELiveIntel/master/images/map_on_ipad.png)
Here is the map on a Samsung Galaxy S4:
![Map on S4](https://raw.githubusercontent.com/jeremieroy/EVELiveIntel/master/images/map_on_s4.png)

## Follow friends
You can also follow friendly players if they give you their identification token:  
Just add their token as Tracker 1,2 or 3 in the option panel.  
![tracked friends options](https://raw.githubusercontent.com/jeremieroy/EVELiveIntel/master/images/tracker_in_map.png)

## Quick guide:
1. go to the map website (see with your Corporation)
2. enter the password (see with your Corporation)
3. trust the website
4. Look at the map, play with the options and find the layout you like.
4b. (optional) make a bookmark or set the map as homepage
5. Look at locals if you enemies (war target), report them by clicking the "Hostile" button.
   If you don't, report it by clicking the "Clear" button.
   If you stay in the system remember to refresh that information. The timer counter and the gauge in the buttons will help you remember it.
6. There is no 6.
7. If you see a red dot on the map, react immediately. The radial and tactical layout will help you assess whether the target is far or close form you.
   Dock, flee, join a fleet. At your judgment.
8. Fly safe.


## Installation:
1. generate the minimalist database using the sql file.
2. edit "intelMap.js" first line to update your website URL (for the trust popup).
3. edit "security.php" to setup a suitable password
4. copy the content of the website folder on your webserver
5. share the map url to your corporation members (and give them the password)

## About security:
The security model of the map is purposely simple and stupid. That way big corporations that have their own security model can easily replace this minimalist system by their own.

## 3rd Party Libraries
* D3.js Data-Driven Documents
http://d3js.org/
Library released under BSD license. Copyright 2013 Mike Bostock.

## Tech stuff:
* I used php as the server tech because it works on every (cheap) web hosting solution.
* I use polling on database for the same reason, a live table in memory would be better but this require a more expensive web hosting solution.


## Thanks:
* Eve University for they are awesome.
* D3.js for the same reason.

[License (BSD 2-clause)](https://github.com/jeremieroy/EVELiveIntel/LICENSE)
-------------------------------------------------------------------------------
Copyright (c) 2014, Roy Jeremie. All rights reserved.

Redistribution and use in source and binary forms, with or without modification,
are permitted provided that the following conditions are met:

   1. Redistributions of source code must retain the above copyright notice, this
      list of conditions and the following disclaimer.

   2. Redistributions in binary form must reproduce the above copyright notice,
      this list of conditions and the following disclaimer in the documentation
      and/or other materials provided with the distribution.

THIS SOFTWARE IS PROVIDED BY COPYRIGHT HOLDER ``AS IS'' AND ANY EXPRESS OR
IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED WARRANTIES OF
MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT
SHALL COPYRIGHT HOLDER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT,
INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT
LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR
PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY,
WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE
OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED
OF THE POSSIBILITY OF SUCH DAMAGE.
