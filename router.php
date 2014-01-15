<?php
/**
 * Lets do some dead simple url to function routing for our PHP server API
 * 
 * @link https://github.com/agentile/neo4j-social-graph-example
 * @version 0.1.0
 * @author Anthony Gentile <asgentile@gmail.com>
 */    
require 'vendor/autoload.php';
require 'vendor/cargomedia/socialgraph.php';

// Routing url to functions. Foregoing a full fledged PSR-2 router 
// and fully fleshed out API Class for this task. Seems overkill to show off 
// API with proper http verbs, versioning, rate limiting, etc for the tiven task.
$routing = array(
    'replace' => array(
        // replacements
        '{:action}' => '([a-z-]+)',
        '{:alpha}' => '([a-zA-Z]+)',
        '{:alnum}' => '([a-zA-Z0-9]+)',
        '{:controller}' => '([a-z-]+)',
        '{:digit}' => '([0-9]+)',
        '{:param}' => '([^/]+)',
        '{:params}' => '(.*)',
        '{:slug}' => '([a-zA-Z0-9-]+)',
        '{:word}' => '([a-zA-Z0-9_]+)',
    ),
    'rewrite' => array(
        'user/{:digit}' => 'user:$1',
        'user/{:digit}/friends' => 'friends:$1',
        'user/{:digit}/fof' => 'fof:$1',
        'user/{:digit}/friend-suggestions' => 'suggest:$1',
    )
);

/**
 * Route to function map
 *
 * @param $routing array
 * @param $uri string
 *
 * @return mixed
 */
function routeToFunction($routing, $uri)
{
    $replace = (isset($routing['replace'])) ? $routing['replace'] : array();
    $rewrite = (isset($routing['rewrite'])) ? $routing['rewrite'] : array();
    
    // check for rewrites
    $static = trim($uri, '/');
    
    foreach ($rewrite as $start => $end) {
        $pattern = str_replace(
            array_keys($replace),
            array_values($replace),
            trim($start, '/')
        );
        $pattern = '#^' . trim($pattern, '/') . '$#';
        if (preg_match($pattern, $static)) {
            $rw = trim($end, '/');
            $newpath = preg_replace($pattern, $rw, $static);
            $parts = explode(':', $newpath);
            return $parts;
        }
    }
    
    return $uri;
}

// Keep track of request time.
$time_start = microtime(true);
$results = array();

$info = routeToFunction($routing, $_SERVER['REQUEST_URI']);

// If rout to function match, call corresponding function
if (is_array($info)) {
    $func = $info[0];
    $id = $info[1];
    $sg = new CargoMedia\SocialGraph('data.json');
    try {
        switch ($func) {
            case 'user':
                // user/{id} API call. (user details)
                $results['results'] = $sg->fetchUser($id);
            break;
            case 'friends':
                // user/{id}/friends API call. (friends of a given user)
                $results['results'] = $sg->fetchFriends($id);
            break;
            case 'fof':
                // user/{id}/fof API call. (friends of friend for user)
                $results['results'] = $sg->fetchFriendsOfFriends($id);
            break;
            case 'suggest':
                // user/{id}/friend-suggestions API call. (friend suggestions for a user)
                $results['results'] = $sg->fetchFriendSuggestions($id);
            break;
        }
    } catch (Exception $e) {
        $results['error'] = $e->getMessage();
    }
}

$elapsed_time = microtime(true) - $time_start;
$results['elapsed_time'] = $elapsed_time;

// JSON response
header('Content-type: application/json');
echo json_encode($results, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

