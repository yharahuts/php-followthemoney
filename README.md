Little lib to handle [FollowTheMoney](https://github.com/alephdata/followthemoney) data format.

Methods and interfaces probably will be rewritten at some time.

### Demo

Pack statements to model: 
```
cat demo/statements.jsonl | docker compose run --rm -T php-cli php demo/statements-to-model.php
```

Unpack model to statements:
```
cat demo/model.json | docker compose run --rm -T php-cli php demo/model-to-statements.php
```

### Benchmark

Random sample of 1M entities from a FtM dump:
```
docker compose run --rm php-cli shuf -n 1000000 -o ingest/sample-1m.ftm.json ingest/opensanctions-ru_egrul-entities.ftm.json
```

Time of `EntitySchema::fromArray()`, `unpack` and `pack` per entity (schema dir is optional, defaults to the submodule):
```
docker compose run --rm php-cli php -d pcov.enabled=0 benchmark/statements.php ingest/sample-1m.ftm.json [schema dir]
```