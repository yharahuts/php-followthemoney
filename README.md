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