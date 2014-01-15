<?php
/**
 * Social Graph Examples using Neo4j.
 * 
 * @link https://github.com/agentile/
 * @version 0.1.0
 * @author Anthony Gentile <asgentile@gmail.com>
 */    
namespace CargoMedia;

class SocialGraph {
    
    protected $client;
    /**
     * JSON data
     */
    protected $data = array();
    
    protected $nodes = array();
    
    public function __construct($json_data_file)
    {
        $this->data = json_decode(file_get_contents($json_data_file));
    }
    
    public function newClient()
    {
        if (!$this->client) {
            $this->client = new \Everyman\Neo4j\Client();
        }
        return $this->client;
    }
    
    public function indexData()
    {
        $client = $this->newClient();
        $social = new \Everyman\Neo4j\Index\NodeIndex($client, 'social');
        // start from scratch.
        $client->deleteIndex($social);
        $connections = array();
        
        foreach ($this->data as $person) {
            // Create a node for each person in our social graph
            $node = $client->makeNode()
                ->setProperty('id', $person->id)
                ->setProperty('firstName', $person->firstName)
                ->setProperty('surname', $person->surname)
                ->setProperty('age', $person->age)
                ->setProperty('gender', $person->gender)
                ->save();
                
            // Add to social node index.
            $social->add($node, 'id', $node->getProperty('id'));
                
            // Keep track of connections and nodes as our next step will be to 
            // add these node relationships.
            $connections[$person->id] = $person->friends;
            $this->nodes[$person->id] = $node;
        }
        
        // Create relationships.
        foreach ($connections as $start => $friends) {
            foreach ($friends as $friend) {
                $relationship = $this->nodes[$start]->relateTo($this->nodes[$friend], 'KNOWS')->save();
            }
        }
    }
    
    public function getNodes()
    {
        return $this->nodes;
    }
    
    public function fetchFriends($id)
    {
        // Direct friends
        if (!$id || !isset($this->nodes[$id])) {
            return false;
        } 
        
        $node = $this->nodes[$id];
        
        $results = array();
        
        $friends = $node->getRelationships('KNOWS', \Everyman\Neo4j\Relationship::DirectionOut);
        foreach ($friends as $f) {
            $results[] = $f->getEndNode()->getProperties();
        }

        return $results;
    }
    
    public function fetchFriendsOfFriends($id) 
    {
        $query = "START person=node:social('id:*')
                  MATCH (person)-[:KNOWS]->(f)-[:KNOWS]->(fof)
                  WHERE ID(fof) <> ID(f) AND fof.id != person.id AND person.id = {pid}
                  RETURN distinct fof";
                  
        $query = new \Everyman\Neo4j\Cypher\Query($this->client, $query, array('pid' => $id));
    
        $rows = $query->getResultSet();
        $results = array();
        foreach ($rows as $row) {
            $results[] = $row[0]->getProperties();
        }
        
        // This is clearly not the ideal way to do this, but I can't seem to 
        // fashion the correct query for Neo4j to correctly disregard direct friends in the result set.
        $friends = $this->fetchFriends($id);
        foreach ($results as $k => $v) {
            if (in_array($v, $friends)) {
                unset($results[$k]);
            }
        }
        
        return array_values($results);
    }
    
    public function fetchFriendSuggestions($id)
    {
        
    }
}
