### Neo4j Social Graph Example ###

This sample code uses Neo4j to gather friends, friends of friends, and suggested friends 
from an initial JSON dataset.

Follow the instructions in the INSTALL document to acquire needed dependencies. 

Then one can run 

```
php index.php

php -S localhost:8000 router.php &

curl http://localhost:8000/user/{id}
curl http://localhost:8000/user/{id}/friends
curl http://localhost:8000/user/{id}/fof
curl http://localhost:8000/user/{id}/friend-suggesstions
```
