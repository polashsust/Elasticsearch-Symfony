The goal is to have an api that sends queries to an elasticserach instance on localhost. The api should be based on
Symfony 4.4, which is pre-installed.

Please commit regularly so we can see the growth of the project.

#### Endpoints:

```/rest/get-objects-from-selected-tags/{type}/{tagids}```

This should get all elements of {type} (required parameter) with matching {tagids} (optional parameter, tagids are
"and", not "or")

**Bonus** for: ```/rest/get-facets-of-tags/{tagids}```

This is for a facet search. Facet search means, that you will give it filter parameters and it returns all possible
other filters that yield results in combination with the requested filters with the numbers of matching documents for the combined filters.

See https://telekom.jobs as an example: Open the advanced search, select a filter and see how the other select-fields
will have their options changed.

#### Tasks:

- Write a cli tool that inserts the elasticsearch dump from .wiki/elasticsearchdata.json into an elasticsearch index,
  the index name should be configurable.

- Write an api to implement the endpoints. The response shall be a json object with only the contents of _source and the
  _id as key. Multiple ids should be accepted, comma seperated. The type is the document type in the index, if no tagids are transmitted, all documents should be returned. The results should be ordered by the field "sortingpriority".

- As a bonus, you can write the second endpoint to return facet results for tagids.

The .wiki folder also has sample requests for querying the api, these work as they are in Phpstorm.

Go on with this task as long as you think is reasonable, we are aware, that this might take longer to complete than you are able to invest.

We advise you to use the configured docker-compose configuration which is included. Please be aware that the installed elasticsearch instance needs a minute or two to start up after the containers finished booting.

The docker-compose configuration has PHP 7.4 and all necessary extensions installed (since out team mostly uses php 7.3 you might want to stick to the 7.3 syntax), composer is available from the cli.

The composer.json/composer.lock should have all packages required for the task, if you feel, you need further packages, feel free to install them.

Be aware, that there might be permission-issues within the docker-containers for writing in the var folder, these might need to be fixed from within the container.

To access the shell in the php container run: ```docker-compose exec php ash```

If you find any errors in this setup, please write me an email - marcus.haase@milchundzucker.de
