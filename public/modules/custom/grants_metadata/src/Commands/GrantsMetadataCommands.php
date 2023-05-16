<?php

namespace Drupal\grants_metadata\Commands;

use Consolidation\OutputFormatters\StructuredData\RowsOfFields;
use Drupal\helfi_atv\AtvService;
use Drush\Commands\DrushCommands;

/**
 * A Drush commandfile.
 *
 * In addition to this file, you need a drush.services.yml
 * in root of your module, and a composer.json file that provides the name
 * of the services file to use.
 *
 * See these files for an example of injecting Drupal services:
 *   - http://cgit.drupalcode.org/devel/tree/src/Commands/DevelCommands.php
 *   - http://cgit.drupalcode.org/devel/tree/drush.services.yml
 */
class GrantsMetadataCommands extends DrushCommands {

  public AtvService $atvService;


  public function __construct(AtvService $atvService) {
    parent::__construct();

    $this->atvService = $atvService;

  }

  /**
   * Command description here.
   *
   * @param $users
   *   Comma separated list of users.
   *
   * @usage grants_metadata-commandName foo
   *   Usage description
   *
   * @command grants_metadata:deleteUserDataAtv
   * @aliases duda
   */
  public function deleteUserDocs(
    array $users){

    $d = 'asdf';
    $this->logger()->success(dt(print_r($users,1)));
  }

  /**
   * Command description here.
   *
   * @param $arg1
   *   Argument description.
   * @param array $options
   *   An associative array of options whose values come from cli, aliases,
   *   config, etc.
   *
   * @option option-name
   *   Description
   * @usage grants_metadata-commandName foo
   *   Usage description
   *
   * @command grants_metadata:commandName
   * @aliases foo
   */
  public function commandName($arg1, $options = ['option-name' => 'default']) {
    $this->logger()->success(dt('Achievement unlocked.'));
  }

  /*
  /**
   * An example of the table output format.
   *
   * @param array $options An associative array of options whose values come from cli, aliases, config, etc.
   *
   * @field-labels
   *   group: Group
   *   token: Token
   *   name: Name
   * @default-fields group,token,name
   *
   * @command grants_metadata:token
   * @aliases token
   *
   * @filter-default-field name
   * @return \Consolidation\OutputFormatters\StructuredData\RowsOfFields
    * /
  public function token($options = ['format' => 'table']) {
    $all = \Drupal::token()->getInfo();
    foreach ($all['tokens'] as $group => $tokens) {
      foreach ($tokens as $key => $token) {
        $rows[] = [
          'group' => $group,
          'token' => $key,
          'name' => $token['name'],
        ];
      }
    }
    return new RowsOfFields($rows);
  }
  */
}
