<?php
/*
Plugin Name: BuddyPress group members chart
Plugin URI: http://www.github.com/sparklingsoftware/bp-group-members-chart
Description: Adds the [bp-group-members-chart] shortcode that will display a group org-chart using google api's
Version: 0.1 BETA
Author: Stephan Dekker
Author URI: http://www.stephandekker.com
*/

/*
BuddyPress group members chart (Wordpress Plugin)
Copyright (C) 2014 Stephan Dekker
Contact me at http://www.stephandekkker.com

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program. If not, see <http://www.gnu.org/licenses/>.
*/


function addHeaderCode() {
  echo '<link type="text/css" rel="stylesheet" href="' . get_bloginfo('wpurl') . '/wp-content/plugins/bp-group-members-chart/css/orgchart.css" />' . "\n";
  echo '<link type="text/css" rel="stylesheet" href="' . get_bloginfo('wpurl') . '/wp-content/plugins/bp-group-members-chart/css/uien.css" />' . "\n";
  
  echo '<script type="text/javascript" src="' . get_bloginfo('wpurl') . '/wp-content/plugins/bp-group-members-chart/js/a" >></script>' . "\n";
  echo '<script type="text/javascript" src="' . get_bloginfo('wpurl') . '/wp-content/plugins/bp-group-members-chart/js/formatendefaultenuienorgcharten.js" ></script>' . "\n";
  echo '<script type="text/javascript" src="' . get_bloginfo('wpurl') . '/wp-content/plugins/bp-group-members-chart/js/jsapi" ></script>' . "\n";
  echo '<script type="text/javascript" src="' . get_bloginfo('wpurl') . '/wp-content/plugins/bp-group-members-chart/js/stef.js" ></script>' . "\n";
  
}

add_action('wp_head', 'addHeaderCode', 1);

//tell wordpress to register the demolistposts shortcode
add_shortcode("bp-group-members-chart", "bp_group_members_chart_handler");

function bp_group_members_chart_handler() {
  //run function that actually does the work of the plugin
  $output = bp_group_members_chart_function();

//send back text to replace shortcode in post
  return $output;
}

function getRoot($name, $groups)  {
  foreach ( $groups as $potentialRoot) {
     if ($potentialRoot->name === $name)
     {
       return $potentialRoot;
     }
  }
}

function getChildrenForGroup($group, $allGroups) {

  $children = array();
  foreach ( $allGroups as $potentialChild ) {
    if ($potentialChild->parent_id == $group->id)
    {
       array_push($children, $potentialChild);
    }   
  }
  
  return $children;
}


function getMemberCountByGroupId($groupId, $members) {
  $count = 0;
  foreach ( $members as $member) {
     if ($member->group_id === $groupId)
     {
        $count += 1;
     }
  }
  return $count;
}


function getMemberCount($group, $allGroups, $members) {

  $children = getChildrenForGroup($group, $allGroups);
  if (count($children) == 0)
  {
     // No Children, get the member count
     $group->members = getMemberCountByGroupId($group->id, $members);
//       $group->members = 10;
  }
  else
  {
     
     foreach ($children as $child)
     {
        getMemberCount($child, $allGroups);
        $group->members += $child->members;
     }
  }
  
  return $allGroups;
}

function bp_group_members_chart_function() {
  //process plugin  

  $output = "Start plugin<br/>";

  // Get data from the database
  global $wpdb;
  $groups = $wpdb->get_results( "SELECT * FROM wp_bp_groups;" );
  $members = $wpdb->get_results( "SELECT * FROM wordpress.wp_bp_groups_members;" );
  $root = getRoot("Global", $groups);  

  // Build up results table
  $groups = getMemberCount($root, $groups, $members);

  // Create Output
  $output .= "<table>";
  foreach ( $groups as $group ) 
  {
     $output .= "<tr>";     
     $output .= "<td>". $group->name . "</td><td>". $group->members . "</td>";
     $output .= "</tr>";
  }

  $output .= "</table>";

  //send back text to calling function
  return $output;
}
?>

