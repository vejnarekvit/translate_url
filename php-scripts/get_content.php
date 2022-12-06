<?php 
require_once(dirname(__FILE__) . "/../../../../wp-load.php");


if (isset($_GET) && array_key_exists('post', $_GET) && array_key_exists('lang', $_GET)) {
  // target lang and getting the content via postID
  $lang = $_GET['lang'];
  $postID = $_GET['post'];
  $currentPost = get_post($postID);
  $content = $currentPost->post_content;


  $dom = new DomDocument(); // Create new Document
  libxml_use_internal_errors(true);
  $dom->loadHTML(mb_convert_encoding($content, 'HTML-ENTITIES', 'UTF-8'), LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD ); // Load content of curr
  $links = $dom->getElementsByTagName('a'); // post and selecting link

  $all = [];
  $i = 0;
  $counter = 0;
  $noNeedToResetBlocks = false;

  if (!empty($links)) {
    // for each found link in post content
    foreach($links as $link){
      $linkHref = $link->getAttribute('href'); // getting attribute href from link in curr iteration

      // check if link is empty or is to #
      if(strlen(trim($linkHref)) !== 0 && $linkHref !== '#'){
        $ID = url_to_postid($linkHref); // get postid from url from curr post

        // check if url_to_postid() response is not 0
        if ($ID !== 0) {
          if (function_exists('pll_get_post')) {
            $postInTargetLang = pll_get_post($ID, $lang); // get post by id and language
            // check if curr post id is equal to target post id
            if ($postInTargetLang) {
              $link->setAttribute('href', get_the_permalink($postInTargetLang)); // set attribute href of <a> to permalink of target post
              $counter++;
            } else {
              $all['errors']['Link_not_translated'][$i] = 'Tato url adresa nebyla přeložena (neobsahuje požadovaný překlad)--> '.$linkHref.' (ID příspěvku: '.$ID.', Název příspěvku: '.get_the_title($ID).')';
            }
          } else {
            $all['errors']['Polylang_not_working'][$i] = 'Polylang function pll_get_post() is not working. Prosím kontaktujte webového vývojáře (Víťu) :).';
          }
        } else {
          $all['errors']['bad_domain'][$i] = 'Tato url adresa nebyla přeložena, protože je z jiné domény (musíte přeložit ručně) --> '.$linkHref;
        }
      } else {
        $all['errors']['emtpy_href'][$i] = 'Příspěvek obsahuje prázdné odkazy!';
      }
      $i++;
    }
  } else {
    $all['errors']['no_href'][0] = 'Příspěvek neobsahuje žádné odkazy';
    $noNeedToResetBlocks = true;
  }

  if ($counter === 0) {
    $all['errors']['no_href'][0] = 'Příspěvek neobsahuje žádné odkazy';
    $noNeedToResetBlocks = true;
  }
  if ($noNeedToResetBlocks != true) {
    $content = $dom->saveHTML();
    $all['content'] = $content;
  }
  // save HTML and print result
  header('Content-Type: application/json');
  echo json_encode($all);
}
?>