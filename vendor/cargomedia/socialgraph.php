<?php
/**
 * Social Graph Examples using Neo4j.
 * 
 * @link https://github.com/agentile/neo4j-social-graph-example
 * @version 0.1.0
 * @author Anthony Gentile <asgentile@gmail.com>
 */    
namespace CargoMedia;

class SocialGraph {
    
    /**
     * Neo4j client object
     */
    protected $client;
    
    /**
     * JSON data
     */
    protected $data = array();
    
    /**
     * Neo4j indices objects
     */
    protected $indices = array();
    
    /**
     * Constructor!
     */
    public function __construct($json_data_file)
    {
        $this->data = json_decode(file_get_contents($json_data_file));
    }
    
    /**
     * Neo4j client object getter
     * 
     * @return object
     */
    public function getClient()
    {
        if (!$this->client) {
            $this->client = new \Everyman\Neo4j\Client();
        }
        return $this->client;
    }
    
    /**
     * Neo4j index object getter
     * 
     * @return object
     */
    public function getIndex($name)
    {
        if (!isset($this->indices[$name])) {
            $this->indices[$name] = new \Everyman\Neo4j\Index\NodeIndex($this->getClient(), $name);
        }
        
        return $this->indices[$name];
    }
    
    /**
     * Index our initial JSON data
     * 
     * @return null
     */
    public function indexData()
    {
        $client = $this->getClient();
        $social = $this->getIndex('social');
        
        // Delete any existing data.
        $client->deleteIndex($social);
        $connections = array();
        $nodes = array();
        
        foreach ($this->data as $user) {
            // Create a node for each user in our social graph
            $node = $client->makeNode()
                ->setProperty('id', $user->id)
                ->setProperty('firstName', $user->firstName)
                ->setProperty('surname', $user->surname)
                ->setProperty('age', $user->age)
                ->setProperty('gender', $user->gender)
                ->setProperty('friends', $user->friends)
                ->save();
                
            // Add to social node index.
            $social->add($node, 'id', $node->getProperty('id'));
                
            // Keep track of connections and nodes as our next step will be to 
            // add these node relationships.
            $connections[$user->id] = $user->friends;
            $nodes[$user->id] = $node;
        }
        
        // Create relationships.
        foreach ($connections as $start => $friends) {
            foreach ($friends as $friend) {
                $relationship = $nodes[$start]->relateTo($nodes[$friend], 'KNOWS')->save();
            }
        }
    }
    
    /**
     * fetchUser
     * 
     * @param $id integer user id
     * 
     * @return mixed
     */
    public function fetchUser($id)
    {
        if (!$this->isInt($id)) {
            return false;
        } 
        
        $ret = array();
        
        try {
            $social = $this->getIndex('social');
            $ret = $social->findOne('id', $id)->getProperties();
        } catch (Exception $e) {
            throw new Exception("Failed to retrieve user details.");
        }
        
        return $ret;
    }
    
    /**
     * fetchFriends
     * 
     * @param $id integer user id
     * 
     * @return mixed
     */
    public function fetchFriends($id)
    {
        if (!$this->isInt($id)) {
            return false;
        }  
        
        $social = $this->getIndex('social');
        $node = $social->findOne('id', $id);
        
        $results = array();
        
        try {
            // First level relationship.
            $friends = $node->getRelationships('KNOWS', \Everyman\Neo4j\Relationship::DirectionOut);
            foreach ($friends as $f) {
                $results[] = $f->getEndNode()->getProperties();
            }
        } catch (Exception $e) {
            throw new Exception("Failed to retrieve friends.");
        }

        return $results;
    }
    
    /**
     * fetchFriendsOfFriends
     * 
     * @param $id integer user id
     * 
     * @return mixed
     */
    public function fetchFriendsOfFriends($id) 
    {
        if (!$this->isInt($id)) {
            return false;
        } 
        
        // I can't quite seem to fashion the Cypher query to correctly 
        // disregard the direct friends. So we will do it after the fact 
        // not ideal, I know.
        // TODO: restructure queries to properly disregard direct friends
        // WHERE (NOT (fof) IN((user)-[:KNOWS]->())) isn't working right.
        $query = "START user=node:social('id:*')
                  MATCH (user)-[:KNOWS]->(f)-[:KNOWS]->(fof)
                  WHERE fof.id <> user.id AND user.id = {pid}
                  RETURN distinct fof";
          
        try {
            $query = new \Everyman\Neo4j\Cypher\Query($this->getClient(), $query, array('pid' => (int) $id));
        
            $rows = $query->getResultSet();
            $results = array();
            foreach ($rows as $row) {
                $results[] = $row[0]->getProperties();
            }
        } catch (Exception $e) {
            throw new Exception("Failed to retrieve friends of friends.");
        }
        
        // Remove direct friends from the result set. Not a solution at scale.
        $friends = $this->fetchFriends($id);
        foreach ($results as $k => $v) {
            if (in_array($v, $friends)) {
                unset($results[$k]);
            }
        }
        
        return array_values($results);
    }
    
    /**
     * fetchFriendSuggestions
     * 
     * @param $id integer user id
     * 
     * @return mixed
     */
    public function fetchFriendSuggestions($id)
    {
        if (!$id) {
            return false;
        } 
        
        $results = $this->fetchFriendsOfFriends($id);
        $user = $this->fetchUser($id);
        $friends = isset($user['friends']) ? $user['friends'] : array();
        
        // Doing this in PHP as opposed to Neo4j is not ideal/scalable 
        // alas I am not familiar enough with Neo4j to craft the correct
        // cypher query (which seems to be a pita either way)
        foreach ($results as $k => $v) {
            // Only keep the friends of friends that know at least 2 direct friends 
            // of the user in question.
            if (count(array_intersect($friends, $v['friends'])) < 2) {
                unset($results[$k]);
            }
        }
        
        // Now of this set, which of these know at least 2 direct friends of the user?
        return array_values($results);
    }
    
    /**
     * Validate value is integer.
     */
    public function isInt($value)
    {
        if (is_int($value)) {
            return true;
        }
        
        // otherwise, must be numeric, and must be same as when cast to int
        return is_numeric($value) && $value == (int) $value;
    }
}

/**
 *
 * Base Exception class for CargoMedia
 *
 * @package CargoMedia
 *
 */
class Exception extends \Exception
{
}
