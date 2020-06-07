# Neogen

[![Build Status](https://travis-ci.org/neoxygen/neo4j-neogen.svg?branch=master)](https://travis-ci.org/neoxygen/neo4j-neogen)

## Graph Generator for Neo4j

The library ease the generation of test graphs. The [faker](https://github.com/fzaninotto/faker) library is also used to generate random property values.

You can define your graph model in YAML or in a slightly modified Cypher pattern.

## This is in development

The library is in its early stage and is targetted to be used in development environment.

This library is the heart of the popular [Graphgen web application](http://graphgen.neoxygen.io)

## Usage

### Download the library :

```bash
git clone https://github.com/graphaware/neo4j-neogen

cd neogen
```

### Define your graph model in Cypher, refer to Graphgen for documentation reference :

```cypher
(p:Person:User {uuid: uuid, dob:{dateTimeBetween:["-65years","-18 years"]}} *10)
(c:Company {name: company, slogan: catchPhrase} *5)
(p)-[:WORKS_AT *n..1]->(c)
```

### Define your graph model in YAML :

```yaml
connection:
  scheme: http
  host: localhost
  port: 7474

nodes:
  persons:
    label: Person
    count: 50
    properties:
      firstname: firstName
      lastname: lastName

  companies:
    label: Company
    count: 10
    properties:
      name: company
      description: catchPhrase

relationships:
  person_works_for:
    start: persons
    end: companies
    type: WORKS_AT
    mode: n..1

  friendships:
    start: persons
    end: persons
    type: KNOWS
    mode: n..n
```

#### Run the generate command :

```bash
vendor/bin/neogen generate
```

or you may want to export the generation queries to a file, handy for importing it in the Neo4j Console :

```bash
./vendor/bin/neogen generate --export="myfile.cql"
```

See the results in your graph browser !

#### Quick configuration precisions:

* When defining properties types (like company, firstName, ...), these types refers to the standard [faker](https://github.com/fzaninotto/faker) types.
* count define the number of nodes you want
* relationship mode : 1 for only one existing relationship per node, random for randomized number of relationships

#### Properties parameters

Sometimes you'll maybe want to define some parameters for your properties, for e.g. to have a realistic date of birth for `Person` nodes,
you may want to be sure that the date will be between 50 years ago and 18 years ago if you set dob for people working for a company.

```yaml
nodes:
  persons:
    label: Person
    count: 10
    properties:
      firstname: firstName
      date_of_birth: { type: "dateTimeBetween", params: ["-50 years", "-18 years"]}

relationships:
    person_works_for:
        start: persons
        end: companies
        type: WORKS_AT
        mode: random
        properties:
            since: { type: "dateTimeBetween", params: ["-10 years", "now"]}
```

### Define your graph model in Cypher :

```
//eg:
(person:Person {firstname:firstName, lastname:lastName} *30)-[:KNOWS *n..n]->(person)
(person)-[:WORKS_AT *n..1]->(company:Company {name:company, slogan:catchPhrase} *5)
```

For a complete description, see the [Graphgen documentation](http://graphgen.neoxygen.io/documentation)

Generating the graph from a cypher pattern :

```bash
./bin/neogen generate-cypher --source="pattern.cypher" --export="export.gen"
```

Or you may want to import the graph directly in an accessible neo4j database :

```bash
./bin/neogen generate-cypher --source="pattern.cypher" --export-db="localhost:7474"
```


---

## Development

Contributions, feedbacks, requirements welcome. Shoot me by opening issues or PR or on twitter : [@ikwattro](https://twitter.com/ikwattro)

