<?php

namespace Drupal\grants_applicant_info\Plugin\DataType;

use Drupal\Core\TypedData\Plugin\DataType\Map;
use Drupal\grants_metadata\Plugin\DataType\DataFormatTrait;

/**
 * ApplicantInfo DataType.
 *
 * @DataType(
 * id = "applicant_info",
 * label = @Translation("Applicant info"),
 * definition_class = "\Drupal\grants_applicant_info\TypedData\Definition\ApplicantInfoDefinition"
 * )
 */
class ApplicantInfoData extends Map {

  use DataFormatTrait;

}
