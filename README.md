# Helsinki grants applications

![CI pipeline](https://github.com/City-of-Helsinki/hel-fi-drupal-grants/actions/workflows/test.yml/badge.svg)

This project offers citizens a way to apply for different city grants for their associations or themselves...

## Environments

Env | Branch    | Url
------ |-----------| -----
local   | *         | [https://hel-fi-drupal-grant-applications.docker.so/](https://hel-fi-drupal-grant-applications.docker.so/)
development   | *         | [https://avustukset.dev.hel.ninja](https://avustukset.dev.hel.ninja)
testing   | develop   | [https://avustukset.test.hel.ninja](https://avustukset.test.hel.ninja)
staging   | release/* | [https://avustukset.stage.hel.ninja](https://avustukset.stage.hel.ninja)
production   | main      | [https://avustukset.hel.fi](https://avustukset.dev.hel.ninja) (https://nginx-avustusasiointi-prod.apps.platta.hel.fi/ before release)


## Requirements

You need to have these applications installed to operate on all environments:

- [Docker](https://github.com/druidfi/guidelines/blob/master/docs/docker.md)
- [Stonehenge](https://github.com/druidfi/stonehenge)
- For the new person: Your SSH public key needs to be added to servers

## Create and start the environment

For the first time (new project):

``
$ make new
``

And following times to create and start the environment:

``
$ make fresh
``

NOTE: Change these according of the state of your project.

## Login to Drupal container

This will log you inside the app container:

```
$ make shell
```

## Enable debugging
To enable xdebug, run `export XDEBUG_ENABLE=true` before (re)starting your project. More info in [docker-composer.yml](./docker-compose.yml)


## Links & information
Works is done & issues tracked [on our Jira board](https://helsinkisolutionoffice.atlassian.net/browse/AU).

[Production monitoring dashboard](https://console-openshift-console.apps.platta.hel.fi/k8s/cluster/projects/hki-kanslia-aok-lomaketyokalu-prod).


## Changelog
Can be found from [here](CHANGELOG.md).
