<?php

require('./bb-load.php');

global $bbdb;

set_time_limit(0);

// Grab all users from FUDforum
$sql = 'SELECT id, login, alias, name, email, location, interests, occupation, time_zone, join_date, home_page, level_id FROM fud26_users WHERE id != 1 AND id NOT IN (SELECT fud_users_id FROM map_users) ORDER BY id';
#$sql = 'SELECT id, login, alias, name, email, location, interests, occupation, time_zone, join_date, home_page, level_id FROM fud26_users WHERE id != 1 AND id NOT IN (SELECT fud_users_id FROM map_users) LIMIT 10';

$users = $bbdb->get_results($sql);
foreach($users as $obj) {
  printf("%d %s\n", $obj->id, $obj->login);

  // Create a new BBpress user
  $uid = bb_new_user($obj->login, $obj->email, $obj->home_page);
  $names = explode(' ', $obj->name);
  $num = count($names);
  if (! is_scalar($uid)) {
    echo "ERROR";
    print_r($uid);
    continue;
  }
  echo "<br/>Create a new BBPress user $uid | $obj->login | $obj->email | $obj->home_page  <br/>";

  // Break up name into first and last names
  if ($num <=1) {
    bb_update_meta($uid, 'first_name', $obj->name, 'user');
  } else {
    $last_name = $names[$num-1];
    unset($names[$num-1]);
    $first_name = implode(' ', $names);
    bb_update_meta($uid, 'first_name', $first_name, 'user');
    bb_update_meta($uid, 'last_name', $last_name, 'user');
  }

  // Add users location, occupation, interests, timezone
  bb_update_meta($uid, 'from', $obj->location, 'user');
  bb_update_meta($uid, 'occ', $obj->occupation, 'user');
  bb_update_meta($uid, 'interest', $obj->interests, 'user');
  bb_update_meta($uid, 'time_zone', $obj->time_zone, 'user' );
  echo "Update user meta info <br/>";

  // Update users joined date and alias
  $date = date('Y-m-d H:i:s', $obj->join_date);    
  $sql1 = "UPDATE wp_users SET user_registered = '$date', display_name = '$obj->alias' WHERE ID = $uid";
  $bbdb->query($sql1);

  // Update users role
  switch ($obj->level_id) {
    case 1:
      $role = 'seniormember';
      break;
    case 2:
      $role = 'member';
      break;
    case 3:
      $role = 'juniormember';
      break;
  }
  $user_obj = new BP_User($uid);
  $user_obj->set_role($role);

# insert into map_users (fud_users_id, bb_users_ID) values (10213, 9580);

  $sql2 = "INSERT INTO map_users (fud_users_id, bb_users_ID) values ($obj->id, $uid)";
  $bbdb->query($sql2);
}
?>
