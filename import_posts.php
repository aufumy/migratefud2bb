<?php

require('./bb-load.php');
global $bbdb;
set_time_limit(0);

/**
 * $limit the number of posts to import in one go
 * $thread_id edit the $thread_id, if script is re-run to the last $thread_id imported
 * $topic_id edit the $topic_id, if script is re-run to the last $topic_id imported
 */
$thread_id = 0;
$topic_id = 0;
$limit = 10000;

// Map fud forum id to wp forum id
$forums = array(7 => 1, 8 => 2, 9 => 3, 10 => 4, 15 => 5, 17 => 6, 18 => 7, 25 => 8);

// Grab all users from FUDforum
$sql = "SELECT DISTINCT m.id, t.forum_id, mu.bb_users_ID, m.thread_id, m.ip_addr, m.post_stamp, m.subject, m.foff, m.length, m.file_id FROM fud26_msg m LEFT JOIN map_users mu ON m.poster_id = mu.fud_users_id JOIN fud26_thread t ON t.id = m.thread_id WHERE t.forum_id IN (7,8,9,10,15,17,18,25) AND m.id NOT IN (SELECT fud_msg_id FROM map_posts) ORDER BY m.thread_id, m.id LIMIT $limit";

$posts = $bbdb->get_results($sql);
foreach($posts as $obj) {
  $bb_current_user = bb_set_current_user($obj->bb_users_ID);

  // Find appropriate forum id
  $forum_id = $forums[$obj->forum_id];

  $date = date('Y-m-d H:i:s', $obj->post_stamp);

  // If new thread, then create topic
  if ($obj->thread_id != $thread_id) {
    $thread_id   = $obj->thread_id;
    $topic_title = $obj->subject;
    $topic_id    = bb_insert_topic(array('topic_title' => $topic_title, 'forum_id' => $forum_id, 'topic_time' => $date));
    $sql0 = "UPDATE bb_topics SET topic_start_time = '$date' WHERE topic_id = $topic_id";
    $bbdb->query($sql0);
  }

  $fp = fopen('msg_'. $obj->file_id, 'rb');
  fseek($fp, $obj->foff);
  $post_text = fread($fp, $obj->length);

  $bb_post_id = bb_insert_post(array('topic_id' => $topic_id, 'post_text' => $post_text));
  if (empty($bb_post_id)) {
    echo "<hr/>empty bb post id, fud msg id is $obj->id, thread id is $obj->thread_id<br/>";
    continue;
  }
  echo "bb post id is $bb_post_id <br/>"; //AF

  printf("%d %d %d %d %s<br/>\n", $obj->id, $obj->thread_id, $topic_id, $bb_post_id, $obj->subject);


  // Update users joined date and alias
  $sql1 = "UPDATE bb_posts SET post_time = '$date', poster_ip = '$obj->ip_addr' WHERE post_id = $bb_post_id";
  $bbdb->query($sql1);

  $sql3 = "UPDATE bb_topics SET topic_time = '$date' WHERE topic_id = $topic_id";
  $bbdb->query($sql3);

  $sql2 = "INSERT INTO map_posts (fud_msg_id, fud_thread_id, fud_file_id, bb_posts_post_id) VALUES ($obj->id, $obj->thread_id, $obj->file_id, $bb_post_id)";
  $bbdb->query($sql2);
}
?>
