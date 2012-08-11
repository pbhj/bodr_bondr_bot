<?php

/*
bodr_bondr_bot by Nicholas Sideras is licensed under a Creative Commons Attribution 3.0 Unported License.
*/


header("Content-Type: text/plain");


/*
function get_default_subreddits()

function get_data($url)

function get_subreddit($url)

function check_if_submitted($subreddit, $url)

*/


//it's probably worth moving this information to a file outside your web accessible directory
$username = 'USER_NAME_HERE';
$password = 'PASSWORD_HERE';

$default_subreddit = 'bodr';
$nondefault_subreddit = 'bondr';
$user_agent = 'bodr_bondr_bot by /u/novembersierra';
ini_set('user_agent', 'User-Agent: '.$user_agent);



require_once("externals/reddit-php-sdk/reddit.php");
$reddit = new reddit($username, $password);

print_r($reddit);
echo "\n----\n\n";




$bestof_json = json_decode(get_data('http://www.reddit.com/r/bestof.json'));


foreach ( $bestof_json->data->children as $child )
{
	$pd = array(
		'domain' => $child->data->domain,
		'title' => $child->data->title,
		'permalink' => $child->data->permalink,
		'url' => $child->data->url,
		'author' => $child->data->author,
		'original_subreddit' => null,
		'filter_to_subreddit' => null,
		'already_submitted' => null
	);

if (strcasecmp($pd['domain'], 'self.bestof') != 0) //as long as it isn't a text post
{

$pd['original_subreddit'] = get_subreddit($pd['url']); //determine subreddit


if(in_array($pd['original_subreddit'], get_default_subreddits()))
{
//is from a default subreddit
$pd['filter_to_subreddit'] = $default_subreddit;
}else
{
//isn't from a default subreddit
$pd['filter_to_subreddit'] = $nondefault_subreddit;
}

/*$pd['already_submitted'] = check_if_submitted($pd['bestof'], $pd['url']);*/


$title = $pd['title'];
$link = $pd['url'];
$subreddit = $pd['filter_to_subreddit'];

$storyResponse = $reddit->createStory($title, $link, $subreddit);

if (strcasecmp($storyResponse, 'that link has already been submitted') == 0){ //as long as it hasn't been submitted
echo 'response: ' . $storyResponse."\r";

//$commentResponse = $reddit->addComment(get_story_name($storyResponse), "Original /r/bestof post:\n\n[$title](".$pd['permalink'].") by /u/".$pd['author']);

//print_r($commentResponse);

}

  print_r($pd);
   echo "\n----\n\n";

}


   //print_r($child->data);
}






function get_data($url)
{
  $ch = curl_init();
  $timeout = 5;
  curl_setopt($ch,CURLOPT_URL,$url);
  curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
  curl_setopt($ch,CURLOPT_CONNECTTIMEOUT,$timeout);
  curl_setopt($ch,CURLOPT_USERAGENT,$user_agent);
  $data = curl_exec($ch);
  curl_close($ch);
  return $data;
}


function get_default_subreddits()
{

$reddit_html = get_data('http://www.reddit.com/');


libxml_use_internal_errors(true); //hide validation errors
$xmlDoc = new DOMDocument();
$xmlDoc->loadHTML($reddit_html);

$xpath = new DOMXPath($xmlDoc);


$classname="choice";

$tags = $xpath->query("//*[contains(@class, '$classname')]");

$default_subreddits = array();

foreach ($tags as $tag) {
   $default_subreddits[] = trim($tag->nodeValue);
}

unset($default_subreddits[0]); //remove combined lists of subreddits from html
array_pop($default_subreddits); //remove 'edit subscriptions' tag

return $default_subreddits;

}


function get_subreddit($url)
{
$pattern = '/\/r\/(.*)\/comments/';
preg_match($pattern, $url, $matches, PREG_OFFSET_CAPTURE);
return $matches[1][0];
}

function get_story_name($url)
{
$pattern = '/\/comments\/(.{1,10})\//';
preg_match($pattern, $url, $matches, PREG_OFFSET_CAPTURE);
return $matches[1][0];
}


function check_if_submitted($subreddit, $url)
{
$headers =  get_headers('http://www.reddit.com/r/'.$subreddit.'/submit?url='.$url, 1);
$location = $headers['Location'];
print_r($headers);

if(strpos($location, '?already_submitted=true') === false)
{
  return 'false';
} else
{
  return 'true';
}
}



?>