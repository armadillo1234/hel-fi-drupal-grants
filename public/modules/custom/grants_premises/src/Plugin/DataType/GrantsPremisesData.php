<?php

namespace Drupal\grants_premises\Plugin\DataType;

use Drupal\Core\TypedData\Plugin\DataType\Map;
use Drupal\grants_metadata\Plugin\DataType\DataFormatTrait;

/**
 * Premises DataType.
 *
 * @DataType(
 * id = "grants_premises",
 * label = @Translation("Premises"),
 * definition_class = "\Drupal\grants_premises\TypedData\Definition\GrantsPremisesDefinition"
 * )
 */
class GrantsPremisesData extends Map {

  use DataFormatTrait;

}
