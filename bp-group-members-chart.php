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

// Add the google chart API javascripts and stylesheets
function addHeaderCode() {
  echo '<link type="text/css" rel="stylesheet" href="' . get_bloginfo('wpurl') . '/wp-content/plugins/bp-group-members-chart/css/orgchart.css" />' . "\n";
  echo '<link type="text/css" rel="stylesheet" href="' . get_bloginfo('wpurl') . '/wp-content/plugins/bp-group-members-chart/css/uien.css" />' . "\n";

  echo '<script type="text/javascript" src="' . get_bloginfo('wpurl') . '/wp-content/plugins/bp-group-members-chart/js/a" >></script>' . "\n";
  echo '<script type="text/javascript" src="' . get_bloginfo('wpurl') . '/wp-content/plugins/bp-group-members-chart/js/formatendefaultenuienorgcharten.js" ></script>' . "\n";
  echo '<script type="text/javascript" src="https://www.google.com/jsapi"></script>' . "\n";  
}

add_action('wp_head', 'addHeaderCode', 1);

// Tell wordpress to register the shortcode
add_shortcode("bp-group-members-chart", "bp_group_members_chart_handler");

function bp_group_members_chart_handler() {
  //run function that actually does the work of the plugin
  $output = bp_group_members_chart_function();

//send back text to replace shortcode in post
  return $output;
}

// Find the top level groups
function getRoot($name, $groups)  {
  foreach ( $groups as $potentialRoot) {
     if ($potentialRoot->name === $name)
     {
       return $potentialRoot;
     }
  }
}

// Get the children for the given groups
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


// Get the number of people in a group given the group ID
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

// Determine the number of people in a group and all its subgroups
function getMemberCount($group, $allGroups, $members) {

  $children = getChildrenForGroup($group, $allGroups);
  if (count($children) == 0)
  {
     // No Children, get the member count
     $group->members = getMemberCountByGroupId($group->id, $members);
     //   $group->members = 10;
  }
  else
  {
     foreach ($children as $child)
     {
        getMemberCount($child, $allGroups, $members);
        $group->members += $child->members;
     }
  }
  
  return $allGroups;
}



// Build the chart
function bp_group_members_chart_function() {
  //process plugin  

  // Get data from the database
  global $wpdb;
  $groups = $wpdb->get_results( "SELECT * FROM wp_bp_groups;" );
  $members = $wpdb->get_results( "SELECT * FROM wp_bp_groups_members;" );
  $root = getRoot("Melbourne", $groups);  

  // Build up results table
  $groups = getMemberCount($root, $groups, $members);

  // Render Output  

  $output .= "   <div id='chart_div'></div>";
  
  $output .= "<script type=\"text/javascript\">";
  $output .= "function draw() {";
  $output .= "  var data = new google.visualization.DataTable();";
  $output .= "  data.addColumn('string', 'Name');";
  $output .= "  data.addColumn('string', 'Manager');";
  $output .= "  data.addColumn('string', 'ToolTip');";
  $output .= "  data.addRows([";
  $output .= "      [{ v: '".$root->name."', f: '".$root->name."<div style=\"color:red; font-style:italic\">President</div>' }, '', 'The President'],";
  $output .= "      [{ v: 'Jim', f: 'Jim<div style=\"color:red; font-style:italic\">Vice President</div>' }, '".$root->name."', 'VP'],";
  $output .= "      ['Alice', '".$root->name."', ''],";
  $output .= "      ['Bob', 'Jim', 'Bob Sponge'],";
  $output .= "      ['Carol', 'Bob', '']";
  $output .= "  ]);";
  $output .= "  var chart = new google.visualization.OrgChart(document.getElementById('chart_div'));";
  $output .= "   chart.draw(data, { allowHtml: true });";
  $output .= "}";
  $output .= "</script>";
  
  //send back text to calling function
  return $output;
}


//wp_enqueue_script('a',                                plugins_url( '/js/a' , __FILE__ ), false, '1.1', true );
//wp_enqueue_script('formatendefaultenuienorgcharten',  plugins_url( '/js/formatendefaultenuienorgcharten.js' , __FILE__ ), false, '1.1', true );
//wp_enqueue_script('jsapi',                            'https://www.google.com/jsapi' , false, '1.1', true );

wp_enqueue_script('renderGroupMembersChart',          plugins_url( '/js/wp.js' , __FILE__ ), false, '1.1', true );

?>

