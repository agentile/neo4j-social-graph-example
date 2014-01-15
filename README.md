### Neo4j Social Graph Example ###

This sample code uses Neo4j to gather friends, friends of friends, and suggested friends 
from an initial JSON dataset.

Follow the instructions in the INSTALL document to acquire needed dependencies. 

Then one can run the following commands to index the data.json into Neo4j and start up a PHP server for testing.

```
php index.php

php -S localhost:8000 router.php &
```

### API endpoints ###

```
curl http://localhost:8000/user/{id}

{
    "results": {
        "friends": [
            2
        ],
        "gender": "male",
        "age": 28,
        "surname": "Crowe",
        "firstName": "Paul",
        "id": 1
    },
    "elapsed_time": 0.014249086380005
}

```

```
curl http://localhost:8000/user/{id}/friends

{
    "results": [
        {
            "friends": [
                1,
                3
            ],
            "gender": "male",
            "age": 23,
            "surname": "Fitz",
            "firstName": "Rob",
            "id": 2
        }
    ],
    "elapsed_time": 0.018579959869385
}
```

```
curl http://localhost:8000/user/{id}/fof

{
    "results": [
        {
            "friends": [
                2,
                4,
                5,
                7
            ],
            "gender": "male",
            "surname": "O'Carolan",
            "firstName": "Ben",
            "id": 3
        }
    ],
    "elapsed_time": 0.035104036331177
}
```

```
curl http://localhost:8000/user/{id}/friend-suggesstions

{
    "results": [
        {
            "friends": [
                12,
                14,
                20
            ],
            "gender": "female",
            "age": 28,
            "surname": "Daly",
            "firstName": "Lisa",
            "id": 13
        },
        {
            "friends": [
                5,
                10,
                19,
                20
            ],
            "gender": "female",
            "age": 28,
            "surname": "Phelan",
            "firstName": "Sandra",
            "id": 11
        }
    ],
    "elapsed_time": 0.051030158996582
}

```
