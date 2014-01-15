### Debian/Ubuntu Installation of Neo4j ###

```
sudo apt-get install openjdk-6-jre-headless
curl -O http://dist.neo4j.org/neo4j-community-1.8.3-unix.tar.gz
tar -xf neo4j-community-1.8.3-unix.tar.gz
rm neo4j-community-1.8.3-unix.tar.gz
neo4j-community-1.8.3/bin/neo4j start
```

### Composer Installation ###

```
curl -sS https://getcomposer.org/installer | php
```

### Use Composer to Install PHP Neo4j Library ###

```
php composer.phar install
```
