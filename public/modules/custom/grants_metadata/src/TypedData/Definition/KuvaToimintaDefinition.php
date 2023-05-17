<?php

namespace Drupal\grants_metadata\TypedData\Definition;

use Drupal\Core\TypedData\ComplexDataDefinitionBase;
use Drupal\Core\TypedData\DataDefinition;
use Drupal\Core\TypedData\ListDataDefinition;
use Drupal\grants_budget_components\TypedData\Definition\GrantsBudgetInfoDefinition;

/**
 * Define Yleisavustushakemus data.
 */
class KuvaToimintaDefinition extends ComplexDataDefinitionBase {

  use ApplicationDefinitionTrait;

  /**
   * Base data definitions for all.
   *
   * @return array
   *   Property definitions.
   */
  public function getPropertyDefinitions(): array {
    if (!isset($this->propertyDefinitions)) {

      $info = &$this->propertyDefinitions;

      foreach ($this->getBaseProperties() as $key => $property) {
        $info[$key] = $property;
      }

      /* Avustustiedot */

      $info['subventions'] = ListDataDefinition::create('grants_metadata_compensation_type')
        ->setLabel('compensationArray')
        ->setSetting('jsonPath', [
          'compensation',
          'compensationInfo',
          'compensationArray',
        ]);

      $info['tulevat_vuodet_joiden_ajalle_monivuotista_avustusta_on_haettu_ta'] = DataDefinition::create('string')
        ->setLabel('Tulevat vuodet joiden ajalle monivuotista avustusta on haettu tai myönnetty')
        ->setSetting('jsonPath', [
          'compensation',
          'compensationInfo',
          'generalInfoArray',
          'yearsForMultiYearApplication',
        ])
        ->setSetting('webformDataExtracter', [
          'service' => 'grants_metadata.atv_schema',
          'method' => 'returnRelations',
          'mergeResults' => TRUE,
          'arguments' => [
            'relations' => [
              'slave' => 'kyseessa_on_monivuotinen_avustus',
              'master' => 'tulevat_vuodet_joiden_ajalle_monivuotista_avustusta_on_haettu_ta',
              'type' => 'boolean',
            ],
          ],
        ]);

      $info['erittely_kullekin_vuodelle_haettavasta_avustussummasta_'] = DataDefinition::create('string')
        ->setLabel('Erittely kullekin vuodelle haettavasta avustussummasta.')
        ->setSetting('jsonPath', [
          'compensation',
          'compensationInfo',
          'generalInfoArray',
          'breakdownOfYearlySums',
        ]);

      $info['ensisijainen_taiteen_ala'] = DataDefinition::create('string')
        ->setLabel('Ensisijainen taiteenala')
        ->setSetting('jsonPath', [
          'compensation',
          'compensationInfo',
          'generalInfoArray',
          'primaryArt',
        ]);

      $info['kyseessa_on_festivaali_tai_tapahtuma'] = DataDefinition::create('boolean')
        ->setLabel('Kyseessä on festivaali')
        ->setSetting('jsonPath', [
          'compensation',
          'compensationInfo',
          'generalInfoArray',
          'isFestival',
        ])
        ->setSetting('typeOverride', [
          'dataType' => 'string',
          'jsonType' => 'bool',
        ]);

      $info['hankkeen_tai_toiminnan_lyhyt_esittelyteksti'] = DataDefinition::create('string')
        ->setLabel('Hankkeen tai toiminnan lyhyt esittelyteksti')
        ->setSetting('jsonPath', [
          'compensation',
          'compensationInfo',
          'generalInfoArray',
          'purpose',
        ]);

      $info['kokoaikainen_henkilosto'] = DataDefinition::create('integer')
        ->setLabel('Kokoaikainen henkilöstö')
        ->setSetting('jsonPath', [
          'compensation',
          'communityInfo',
          'generalCommunityInfoArray',
          'staffPeopleFulltime',
        ])->setSetting('valueCallback', [
          '\Drupal\grants_handler\Plugin\WebformHandler\GrantsHandler',
          'convertToInt',
        ])
        ->setSetting('typeOverride', [
          'dataType' => 'string',
          'jsonType' => 'int',
        ]);

      $info['osa_aikainen_henkilosto'] = DataDefinition::create('integer')
        ->setLabel('Osa-aikainen henkilöstö')
        ->setSetting('jsonPath', [
          'compensation',
          'communityInfo',
          'generalCommunityInfoArray',
          'staffPeopleParttime',
        ])->setSetting('valueCallback', [
          '\Drupal\grants_handler\Plugin\WebformHandler\GrantsHandler',
          'convertToInt',
        ])
        ->setSetting('typeOverride', [
          'dataType' => 'string',
          'jsonType' => 'int',
        ]);

      $info['vapaaehtoinen_henkilosto'] = DataDefinition::create('integer')
        ->setLabel('Vapaaehtoinen henkilöstö')
        ->setSetting('jsonPath', [
          'compensation',
          'communityInfo',
          'generalCommunityInfoArray',
          'staffPeopleVoluntary',
        ])->setSetting('valueCallback', [
          '\Drupal\grants_handler\Plugin\WebformHandler\GrantsHandler',
          'convertToInt',
        ])
        ->setSetting('typeOverride', [
          'dataType' => 'string',
          'jsonType' => 'int',
        ]);

      $info['taiteellisen_toiminnan_tilaa_omistuksessa_tai_ymparivuotisesti_p'] = DataDefinition::create('boolean')
        ->setLabel('Taiteellisen toiminnan tilaa omistuksessa tai ympärivuotisesti päävuokralaisena.')
        ->setSetting('jsonPath', [
          'compensation',
          'communityInfo',
          'generalCommunityInfoArray',
          'isOwnerOrPrimaryTenantOfArtpremises',
        ])
        ->setSetting('typeOverride', [
          'dataType' => 'string',
          'jsonType' => 'bool',
        ]);

      $info['tila'] = ListDataDefinition::create('grants_premises')
        ->setLabel('Tilat')
        ->setSetting('jsonPath', [
          'compensation',
          'communityInfo',
          'artPremisesArray',
        ])
        ->setSetting('fullItemValueCallback', [
          'service' => 'grants_premises.service',
          'method' => 'processPremises',
        ])
        ->setSetting('fieldsForApplication', [
          'premiseName',
          'premiseType',
          'isOthersUse',
          'premiseName',
          'isOwnedByApplicant',
          'postCode',
          'isOwnedByCity'
        ]);

      $info['tapahtuma_tai_esityspaivien_maara_helsingissa'] = DataDefinition::create('integer')
        ->setLabel('Tapahtuma- tai esityspäivien määrä Helsingissä.')
        ->setSetting('jsonPath', [
          'compensation',
          'activityInfo',
          'plannedActivityInfoArray',
          'eventDaysCountHki',
        ])
        ->setSetting('valueCallback', [
          '\Drupal\grants_handler\Plugin\WebformHandler\GrantsHandler',
          'convertToInt',
        ])
        ->setSetting('typeOverride', [
          'dataType' => 'string',
          'jsonType' => 'int',
        ]);

      $info['kantaesitysten_maara'] = DataDefinition::create('integer')
        ->setLabel('Kantaesitysten määrä.')
        ->setSetting('jsonPath', [
          'compensation',
          'activityInfo',
          'plannedActivityInfoArray',
          'firstPublicPerformancesCount',
        ])
        ->setSetting('valueCallback', [
          '\Drupal\grants_handler\Plugin\WebformHandler\GrantsHandler',
          'convertToInt',
        ])
        ->setSetting('typeOverride', [
          'dataType' => 'string',
          'jsonType' => 'int',
        ]);

      $info['ensi_iltojen_maara_helsingissa'] = DataDefinition::create('integer')
        ->setLabel('Ensi-iltojen määrä Helsingissä.')
        ->setSetting('jsonPath', [
          'compensation',
          'activityInfo',
          'plannedActivityInfoArray',
          'premiereCountHki',
        ])
        ->setSetting('valueCallback', [
          '\Drupal\grants_handler\Plugin\WebformHandler\GrantsHandler',
          'convertToInt',
        ])
        ->setSetting('typeOverride', [
          'dataType' => 'string',
          'jsonType' => 'int',
        ]);

      $info['festivaalin_tai_tapahtuman_kohdalla_tapahtuman_paivamaarat'] = DataDefinition::create('string')
        ->setLabel('Tapahtuman tai festivaalin kohdalla tapahtuman päivämäärät.')
        ->setSetting('jsonPath', [
          'compensation',
          'activityInfo',
          'plannedActivityInfoArray',
          'eventOrFestivalDates',
        ]);

      $info['hanke_alkaa'] = DataDefinition::create('string')
        ->setLabel('Hanke alkaa.')
        ->setSetting('jsonPath', [
          'compensation',
          'activityInfo',
          'plannedActivityInfoArray',
          'projectStartDate',
        ])
        ->setSetting('typeOverride', [
          'dataType' => 'string',
          'jsonType' => 'datetime',
        ])
        ->setSetting('valueCallback', [
          'service' => 'grants_metadata.converter',
          'method' => 'convertDates',
          'arguments' => [
            'dateFormat' => 'c',
          ],
        ]);
      $info['hanke_loppuu'] = DataDefinition::create('string')
        ->setLabel('Hanke loppuu.')
        ->setSetting('jsonPath', [
          'compensation',
          'activityInfo',
          'plannedActivityInfoArray',
          'projectEndDate',
        ])
        ->setSetting('typeOverride', [
          'dataType' => 'string',
          'jsonType' => 'datetime',
        ])
        ->setSetting('valueCallback', [
          'service' => 'grants_metadata.converter',
          'method' => 'convertDates',
          'arguments' => [
            'dateFormat' => 'c',
          ],
        ]);

      $info['muut_keskeiset_toimintamuodot'] = DataDefinition::create('string')
        ->setLabel('Muut keskeiset toimintamuodot.')
        ->setSetting('jsonPath', [
          'compensation',
          'activityInfo',
          'plannedActivityInfoArray',
          'otherKeyActivities',
        ]);

      $info['kyseessa_on_festivaali_tai_tapahtuma'] = DataDefinition::create('boolean')
        ->setLabel('Kyseessä on festivaali.')
        ->setSetting('jsonPath', [
          'compensation',
          'activityInfo',
          'plannedActivityInfoArray',
          'isEventOrFestival',
        ])
        ->setSetting('typeOverride', [
          'dataType' => 'string',
          'jsonType' => 'bool',
        ]);

      $info['tapahtuma_tai_esityspaivien_maara_helsingissa_toteutuneet'] = DataDefinition::create('integer')
        ->setLabel('	Tapahtuma- tai esityspäivien määrä Helsingissä.')
        ->setSetting('jsonPath', [
          'compensation',
          'activityInfo',
          'plannedActivityInfoArray',
          'performanceCountHki',
        ])
        ->setSetting('valueCallback', [
          '\Drupal\grants_handler\Plugin\WebformHandler\GrantsHandler',
          'convertToInt',
        ])
        ->setSetting('typeOverride', [
          'dataType' => 'string',
          'jsonType' => 'int',
        ]);

      /* Esitykset */
      $info['esitykset_maara_helsingissa'] = DataDefinition::create('integer')
        ->setLabel('Määrä Helsingissä.')
        ->setSetting('jsonPath', [
          'compensation',
          'activityInfo',
          'plannedActivityInfoArray',
          'performanceCountHki',
        ])
        ->setSetting('valueCallback', [
          '\Drupal\grants_handler\Plugin\WebformHandler\GrantsHandler',
          'convertToInt',
        ])
        ->setSetting('typeOverride', [
          'dataType' => 'string',
          'jsonType' => 'int',
        ]);
      $info['esitykset_maara_kaikkiaan'] = DataDefinition::create('integer')
        ->setLabel('Määrä Helsingissä.')
        ->setSetting('jsonPath', [
          'compensation',
          'activityInfo',
          'plannedActivityInfoArray',
          'performanceCountAll',
        ])
        ->setSetting('valueCallback', [
          '\Drupal\grants_handler\Plugin\WebformHandler\GrantsHandler',
          'convertToInt',
        ])
        ->setSetting('typeOverride', [
          'dataType' => 'string',
          'jsonType' => 'int',
        ]);
      $info['esitykset_kavijamaara_helsingissa'] = DataDefinition::create('integer')
        ->setLabel('Määrä Helsingissä.')
        ->setSetting('jsonPath', [
          'compensation',
          'activityInfo',
          'plannedActivityInfoArray',
          'performanceVisitorsHki',
        ])
        ->setSetting('valueCallback', [
          '\Drupal\grants_handler\Plugin\WebformHandler\GrantsHandler',
          'convertToInt',
        ])
        ->setSetting('typeOverride', [
          'dataType' => 'string',
          'jsonType' => 'int',
        ]);
      $info['esitykset_kavijamaara_kaikkiaan'] = DataDefinition::create('integer')
        ->setLabel('Määrä Helsingissä.')
        ->setSetting('jsonPath', [
          'compensation',
          'activityInfo',
          'plannedActivityInfoArray',
          'performanceVisitorsAll',
        ])
        ->setSetting('valueCallback', [
          '\Drupal\grants_handler\Plugin\WebformHandler\GrantsHandler',
          'convertToInt',
        ])
        ->setSetting('typeOverride', [
          'dataType' => 'string',
          'jsonType' => 'int',
        ]);

      /* Näyttelyt */
      $info['nayttelyt_maara_helsingissa'] = DataDefinition::create('integer')
        ->setLabel('Määrä Helsingissä.')
        ->setSetting('jsonPath', [
          'compensation',
          'activityInfo',
          'plannedActivityInfoArray',
          'exhibitionCountHki',
        ])
        ->setSetting('valueCallback', [
          '\Drupal\grants_handler\Plugin\WebformHandler\GrantsHandler',
          'convertToInt',
        ])
        ->setSetting('typeOverride', [
          'dataType' => 'string',
          'jsonType' => 'int',
        ]);
      $info['nayttelyt_maara_kaikkiaan'] = DataDefinition::create('integer')
        ->setLabel('Määrä Helsingissä.')
        ->setSetting('jsonPath', [
          'compensation',
          'activityInfo',
          'plannedActivityInfoArray',
          'exhibitionCountAll',
        ])
        ->setSetting('valueCallback', [
          '\Drupal\grants_handler\Plugin\WebformHandler\GrantsHandler',
          'convertToInt',
        ])
        ->setSetting('typeOverride', [
          'dataType' => 'string',
          'jsonType' => 'int',
        ]);
      $info['nayttelyt_kavijamaara_helsingissa'] = DataDefinition::create('integer')
        ->setLabel('Määrä Helsingissä.')
        ->setSetting('jsonPath', [
          'compensation',
          'activityInfo',
          'plannedActivityInfoArray',
          'exhibitionVisitorsHki',
        ])
        ->setSetting('valueCallback', [
          '\Drupal\grants_handler\Plugin\WebformHandler\GrantsHandler',
          'convertToInt',
        ])
        ->setSetting('typeOverride', [
          'dataType' => 'string',
          'jsonType' => 'int',
        ]);
      $info['nayttelyt_kavijamaara_kaikkiaan'] = DataDefinition::create('integer')
        ->setLabel('Määrä Helsingissä.')
        ->setSetting('jsonPath', [
          'compensation',
          'activityInfo',
          'plannedActivityInfoArray',
          'exhibitionVisitorsAll',
        ])
        ->setSetting('valueCallback', [
          '\Drupal\grants_handler\Plugin\WebformHandler\GrantsHandler',
          'convertToInt',
        ])
        ->setSetting('typeOverride', [
          'dataType' => 'string',
          'jsonType' => 'int',
        ]);

      /* Työpajat */
      $info['tyopaja_maara_helsingissa'] = DataDefinition::create('integer')
        ->setLabel('Määrä Helsingissä.')
        ->setSetting('jsonPath', [
          'compensation',
          'activityInfo',
          'plannedActivityInfoArray',
          'workshopCountHki',
        ])
        ->setSetting('valueCallback', [
          '\Drupal\grants_handler\Plugin\WebformHandler\GrantsHandler',
          'convertToInt',
        ])
        ->setSetting('typeOverride', [
          'dataType' => 'string',
          'jsonType' => 'int',
        ]);
      $info['tyopaja_maara_kaikkiaan'] = DataDefinition::create('integer')
        ->setLabel('Määrä Helsingissä.')
        ->setSetting('jsonPath', [
          'compensation',
          'activityInfo',
          'plannedActivityInfoArray',
          'workshopCountAll',
        ])
        ->setSetting('valueCallback', [
          '\Drupal\grants_handler\Plugin\WebformHandler\GrantsHandler',
          'convertToInt',
        ])
        ->setSetting('typeOverride', [
          'dataType' => 'string',
          'jsonType' => 'int',
        ]);
      $info['tyopaja_kavijamaara_helsingissa'] = DataDefinition::create('integer')
        ->setLabel('Määrä Helsingissä.')
        ->setSetting('jsonPath', [
          'compensation',
          'activityInfo',
          'plannedActivityInfoArray',
          'workshopVisitorsHki',
        ])
        ->setSetting('valueCallback', [
          '\Drupal\grants_handler\Plugin\WebformHandler\GrantsHandler',
          'convertToInt',
        ])
        ->setSetting('typeOverride', [
          'dataType' => 'string',
          'jsonType' => 'int',
        ]);
      $info['tyopaja_kavijamaara_kaikkiaan'] = DataDefinition::create('integer')
        ->setLabel('Määrä Helsingissä.')
        ->setSetting('jsonPath', [
          'compensation',
          'activityInfo',
          'plannedActivityInfoArray',
          'workshopVisitorsAll',
        ])
        ->setSetting('valueCallback', [
          '\Drupal\grants_handler\Plugin\WebformHandler\GrantsHandler',
          'convertToInt',
        ])
        ->setSetting('typeOverride', [
          'dataType' => 'string',
          'jsonType' => 'int',
        ]);

      /*
       *
       * Toteutuneet määrät.
       *
       *
       */

      $info['oliko_kyseessa_festivaali_tai_tapahtuma_'] = DataDefinition::create('boolean')
        ->setLabel('Kyseessä on festivaali.')
        ->setSetting('jsonPath', [
          'compensation',
          'activityInfo',
          'realizedActivityInfoArray',
          'isEventOrFestival',
        ])
        ->setSetting('typeOverride', [
          'dataType' => 'string',
          'jsonType' => 'bool',
        ]);

      /* Esitykset */
      $info['esitykset_maara_kaikkiaan_toteutuneet'] = DataDefinition::create('integer')
        ->setLabel('Määrä kaikkiaan.')
        ->setSetting('jsonPath', [
          'compensation',
          'activityInfo',
          'realizedActivityInfoArray',
          'performanceCountAll',
        ])
        ->setSetting('valueCallback', [
          '\Drupal\grants_handler\Plugin\WebformHandler\GrantsHandler',
          'convertToInt',
        ])
        ->setSetting('typeOverride', [
          'dataType' => 'string',
          'jsonType' => 'int',
        ]);
      $info['esitykset_maara_helsingissa_toteutuneet'] = DataDefinition::create('integer')
        ->setLabel('Määrä Helsingissä.')
        ->setSetting('jsonPath', [
          'compensation',
          'activityInfo',
          'realizedActivityInfoArray',
          'performanceCountHki',
        ])
        ->setSetting('valueCallback', [
          '\Drupal\grants_handler\Plugin\WebformHandler\GrantsHandler',
          'convertToInt',
        ])
        ->setSetting('typeOverride', [
          'dataType' => 'string',
          'jsonType' => 'int',
        ]);

      $info['esitykset_kavijamaara_helsingissa_toteutuneet'] = DataDefinition::create('integer')
        ->setLabel('Määrä Helsingissä.')
        ->setSetting('jsonPath', [
          'compensation',
          'activityInfo',
          'realizedActivityInfoArray',
          'performanceVisitorsHki',
        ])
        ->setSetting('valueCallback', [
          '\Drupal\grants_handler\Plugin\WebformHandler\GrantsHandler',
          'convertToInt',
        ])
        ->setSetting('typeOverride', [
          'dataType' => 'string',
          'jsonType' => 'int',
        ]);
      $info['esitykset_kavijamaara_kaikkiaan_toteutuneet'] = DataDefinition::create('integer')
        ->setLabel('Määrä Helsingissä.')
        ->setSetting('jsonPath', [
          'compensation',
          'activityInfo',
          'realizedActivityInfoArray',
          'performanceVisitorsAll',
        ])
        ->setSetting('valueCallback', [
          '\Drupal\grants_handler\Plugin\WebformHandler\GrantsHandler',
          'convertToInt',
        ])
        ->setSetting('typeOverride', [
          'dataType' => 'string',
          'jsonType' => 'int',
        ]);

      /* Näyttelyt */
      $info['nayttelyt_maara_helsingissa_toteutuneet'] = DataDefinition::create('integer')
        ->setLabel('Määrä Helsingissä.')
        ->setSetting('jsonPath', [
          'compensation',
          'activityInfo',
          'realizedActivityInfoArray',
          'exhibitionCountHki',
        ])
        ->setSetting('valueCallback', [
          '\Drupal\grants_handler\Plugin\WebformHandler\GrantsHandler',
          'convertToInt',
        ])
        ->setSetting('typeOverride', [
          'dataType' => 'string',
          'jsonType' => 'int',
        ]);
      $info['nayttelyt_maara_kaikkiaan_toteutuneet'] = DataDefinition::create('integer')
        ->setLabel('Määrä Helsingissä.')
        ->setSetting('jsonPath', [
          'compensation',
          'activityInfo',
          'realizedActivityInfoArray',
          'exhibitionCountAll',
        ])
        ->setSetting('valueCallback', [
          '\Drupal\grants_handler\Plugin\WebformHandler\GrantsHandler',
          'convertToInt',
        ])
        ->setSetting('typeOverride', [
          'dataType' => 'string',
          'jsonType' => 'int',
        ]);
      $info['nayttelyt_kavijamaara_helsingissa_toteutuneet'] = DataDefinition::create('integer')
        ->setLabel('Määrä Helsingissä.')
        ->setSetting('jsonPath', [
          'compensation',
          'activityInfo',
          'realizedActivityInfoArray',
          'exhibitionVisitorsHki',
        ])
        ->setSetting('valueCallback', [
          '\Drupal\grants_handler\Plugin\WebformHandler\GrantsHandler',
          'convertToInt',
        ])
        ->setSetting('typeOverride', [
          'dataType' => 'string',
          'jsonType' => 'int',
        ]);
      $info['nayttelyt_kavijamaara_kaikkiaan_toteutuneet'] = DataDefinition::create('integer')
        ->setLabel('Määrä Helsingissä.')
        ->setSetting('jsonPath', [
          'compensation',
          'activityInfo',
          'realizedActivityInfoArray',
          'exhibitionVisitorsAll',
        ])
        ->setSetting('valueCallback', [
          '\Drupal\grants_handler\Plugin\WebformHandler\GrantsHandler',
          'convertToInt',
        ])
        ->setSetting('typeOverride', [
          'dataType' => 'string',
          'jsonType' => 'int',
        ]);

      /* Työpajat */
      $info['tyopaja_maara_helsingissa_toteutuneet'] = DataDefinition::create('integer')
        ->setLabel('Määrä Helsingissä.')
        ->setSetting('jsonPath', [
          'compensation',
          'activityInfo',
          'realizedActivityInfoArray',
          'workshopCountHki',
        ])
        ->setSetting('valueCallback', [
          '\Drupal\grants_handler\Plugin\WebformHandler\GrantsHandler',
          'convertToInt',
        ])
        ->setSetting('typeOverride', [
          'dataType' => 'string',
          'jsonType' => 'int',
        ]);
      $info['tyopaja_maara_kaikkiaan_toteutuneet'] = DataDefinition::create('integer')
        ->setLabel('Määrä Helsingissä.')
        ->setSetting('jsonPath', [
          'compensation',
          'activityInfo',
          'realizedActivityInfoArray',
          'workshopCountAll',
        ])
        ->setSetting('valueCallback', [
          '\Drupal\grants_handler\Plugin\WebformHandler\GrantsHandler',
          'convertToInt',
        ])
        ->setSetting('typeOverride', [
          'dataType' => 'string',
          'jsonType' => 'int',
        ]);
      $info['tyopaja_kavijamaara_helsingissa_toteutuneet'] = DataDefinition::create('integer')
        ->setLabel('Määrä Helsingissä.')
        ->setSetting('jsonPath', [
          'compensation',
          'activityInfo',
          'realizedActivityInfoArray',
          'workshopVisitorsHki',
        ])
        ->setSetting('valueCallback', [
          '\Drupal\grants_handler\Plugin\WebformHandler\GrantsHandler',
          'convertToInt',
        ])
        ->setSetting('typeOverride', [
          'dataType' => 'string',
          'jsonType' => 'int',
        ]);
      $info['tyopaja_kavijamaara_kaikkiaan_toteutuneet'] = DataDefinition::create('integer')
        ->setLabel('Määrä Helsingissä.')
        ->setSetting('jsonPath', [
          'compensation',
          'activityInfo',
          'realizedActivityInfoArray',
          'workshopVisitorsAll',
        ])
        ->setSetting('valueCallback', [
          '\Drupal\grants_handler\Plugin\WebformHandler\GrantsHandler',
          'convertToInt',
        ])
        ->setSetting('typeOverride', [
          'dataType' => 'string',
          'jsonType' => 'int',
        ]);

      $info['toteutuneet_kantaesitysten_maara'] = DataDefinition::create('integer')
        ->setLabel('Kantaesitysten määrä.')
        ->setSetting('jsonPath', [
          'compensation',
          'activityInfo',
          'plannedActivityInfoArray',
          'firstPublicPerformancesCount',
        ])
        ->setSetting('valueCallback', [
          '\Drupal\grants_handler\Plugin\WebformHandler\GrantsHandler',
          'convertToInt',
        ])
        ->setSetting('typeOverride', [
          'dataType' => 'string',
          'jsonType' => 'int',
        ]);

      $info['toeutuneet_ensi_iltojen_maara_helsingissa'] = DataDefinition::create('integer')
        ->setLabel('Ensi-iltojen määrä Helsingissä.')
        ->setSetting('jsonPath', [
          'compensation',
          'activityInfo',
          'plannedActivityInfoArray',
          'premiereCountHki',
        ])
        ->setSetting('valueCallback', [
          '\Drupal\grants_handler\Plugin\WebformHandler\GrantsHandler',
          'convertToInt',
        ])
        ->setSetting('typeOverride', [
          'dataType' => 'string',
          'jsonType' => 'int',
        ]);

      $info['toteutuneet_tila'] = ListDataDefinition::create('grants_premises')
        ->setLabel('Tilat')
        ->setSetting('jsonPath', [
          'compensation',
          'communityInfo',
          'realizedPremisesArray',
        ])
        ->setSetting('fullItemValueCallback', [
          'service' => 'grants_premises.service',
          'method' => 'processPremises',
        ])
        ->setSetting('fieldsForApplication', [
          'premiseName',
          'premiseType',
          'isOthersUse',
          'premiseName',
          'isOwnedByApplicant',
          'postCode',
          'isOwnedByCity'
        ]);

      /* Toiminnan lähtökohdat */
      $info['toiminta_taiteelliset_lahtokohdat'] = DataDefinition::create('string')
        ->setLabel('Kuvaa toiminnan taiteellisia lähtökohtia ja tavoitteita, taiteellista ammattimaisuutta sekä asemaa taiteen kentällä.')
        ->setSetting('jsonPath', [
          'compensation',
          'activityBasisInfo',
          'activityBasisArray',
          'toiminta_taiteelliset_lahtokohdat',
        ]);

      $info['toiminta_tasa_arvo'] = DataDefinition::create('string')
        ->setLabel('Miten monimuotoisuus ja tasa-arvo toteutuu ja näkyy toiminnan järjestäjissä ja organisaatioissa sekä toiminnan sisällöissä? Minkälaisia toimenpiteitä, resursseja ja osaamista on asian edistämiseksi?')
        ->setSetting('jsonPath', [
          'compensation',
          'activityBasisInfo',
          'activityBasisArray',
          'toiminta_tasa_arvo',
        ]);

      $info['toiminta_saavutettavuus'] = DataDefinition::create('string')
        ->setLabel('Miten toiminta tehdään kaupunkilaiselle sosiaalisesti, kulttuurisesti, kielellisesti, taloudellisesti, fyysisesti, alueellisesti tai muutoin mahdollisimman saavutettavaksi? Minkälaisia toimenpiteitä, resursseja ja osaamista on asian edistämiseksi?')
        ->setSetting('jsonPath', [
          'compensation',
          'activityBasisInfo',
          'activityBasisArray',
          'toiminta_saavutettavuus',
        ]);

      $info['toiminta_yhteisollisyys'] = DataDefinition::create('string')
        ->setLabel('Miten toiminta vahvistaa yhteisöllisyyttä, verkostomaista yhteistyöskentelyä ja miten kaupunkilaisten on mahdollista osallistua toiminnan eri vaiheisiin? Minkälaisia toimenpiteitä, resursseja ja osaamista on asian edistämiseksi?')
        ->setSetting('jsonPath', [
          'compensation',
          'activityBasisInfo',
          'activityBasisArray',
          'toiminta_yhteisollisyys',
        ]);

      $info['toiminta_kohderyhmat'] = DataDefinition::create('string')
        ->setLabel('Keitä toiminnalla tavoitellaan? Miten kyseiset kohderyhmät aiotaan tavoittaa ja mitä osaamista näiden kanssa työskentelyyn on?')
        ->setSetting('jsonPath', [
          'compensation',
          'activityBasisInfo',
          'activityBasisArray',
          'toiminta_kohderyhmat',
        ]);

      $info['toiminta_ammattimaisuus'] = DataDefinition::create('string')
        ->setLabel('Kuvaa toiminnan järjestämisen ammattimaisuutta ja organisoimista')
        ->setSetting('jsonPath', [
          'compensation',
          'activityBasisInfo',
          'activityBasisArray',
          'toiminta_ammattimaisuus',
        ]);

      $info['toiminta_ekologisuus'] = DataDefinition::create('string')
        ->setLabel('Miten ekologisuus huomioidaan toiminnan järjestämisessä? Minkälaisia toimenpiteitä, resursseja ja osaamista on asian edistämiseksi?')
        ->setSetting('jsonPath', [
          'compensation',
          'activityBasisInfo',
          'activityBasisArray',
          'toiminta_ekologisuus',
        ]);

      $info['toiminta_yhteistyokumppanit'] = DataDefinition::create('string')
        ->setLabel('Nimeä keskeisimmät yhteistyökumppanit ja kuvaa yhteistyön muotoja ja ehtoja.')
        ->setSetting('jsonPath', [
          'compensation',
          'activityBasisInfo',
          'activityBasisArray',
          'toiminta_yhteistyokumppanit',
        ]);

      $info['organisaatio_kuuluu_valtionosuusjarjestelmaan'] = DataDefinition::create('boolean')
        ->setLabel('Organisaatio kuuluu valtionosuusjärjestelmään (VOS).')
        ->setSetting('jsonPath', [
          'compensation',
          'budgetInfo',
          'budgetInfoArray',
          'isPartOfVOS',
        ])
        ->setSetting('typeOverride', [
          'dataType' => 'string',
          'jsonType' => 'bool',
        ]);
      $info['organisaatio_kuului_valtionosuusjarjestelmaan'] = DataDefinition::create('boolean')
        ->setLabel('Organisaatio kuului valtionosuusjärjestelmään (VOS).')
        ->setSetting('jsonPath', [
          'compensation',
          'budgetInfo',
          'budgetInfoArray',
          'wasPartOfVOS',
        ])
        ->setSetting('typeOverride', [
          'dataType' => 'string',
          'jsonType' => 'bool',
        ]);

      $info['budgetInfo'] = GrantsBudgetInfoDefinition::create('grants_budget_info')
        ->setSetting('propertyStructureCallback', [
          'service' => 'grants_budget_components.service',
          'method' => 'processBudgetInfo',
          'webform' => TRUE,
        ])
        ->setSetting('webformDataExtracter', [
          'service' => 'grants_budget_components.service',
          'method' => 'extractToWebformData',
          'mergeResults' => TRUE,
        ])
        ->setSetting('jsonPath', ['compensation', 'budgetInfo'])
        ->setPropertyDefinition(
          'menot_yhteensa',
          GrantsBudgetInfoDefinition::getStaticCostDefinition()
        )
        ->setPropertyDefinition(
          'suunnitellut_menot',
          GrantsBudgetInfoDefinition::getStaticCostDefinition()
        )
        ->setPropertyDefinition(
          'toteutuneet_tulot_data',
          GrantsBudgetInfoDefinition::getStaticIncomeDefinition()
        );

      $info['members_applicant_person_local'] = DataDefinition::create('integer')
        ->setLabel('Helsinkiläisiä henkilöjäseniä yhteensä')
        ->setSetting('defaultValue', "")
        ->setSetting('jsonPath', [
          'compensation',
          'communityInfo',
          'generalCommunityInfoArray',
          'membersPersonLocal',
        ])->setSetting('valueCallback', [
          '\Drupal\grants_handler\Plugin\WebformHandler\GrantsHandler',
          'convertToInt',
        ])
        ->setSetting('typeOverride', [
          'dataType' => 'string',
          'jsonType' => 'int',
        ]);

      $info['members_applicant_person_global'] = DataDefinition::create('integer')
        ->setLabel('Henkilöjäseniä yhteensä')
        ->setSetting('defaultValue', "")
        ->setSetting('jsonPath', [
          'compensation',
          'communityInfo',
          'generalCommunityInfoArray',
          'membersPersonGlobal',
        ])->setSetting('valueCallback', [
          '\Drupal\grants_handler\Plugin\WebformHandler\GrantsHandler',
          'convertToInt',
        ])
        ->setSetting('typeOverride', [
          'dataType' => 'string',
          'jsonType' => 'int',
        ]);

      $info['members_applicant_community_local'] = DataDefinition::create('integer')
        ->setLabel('Helsinkiläisiä yhteisöjäseniä yhteensä')
        ->setSetting('defaultValue', "")
        ->setSetting('jsonPath', [
          'compensation',
          'communityInfo',
          'generalCommunityInfoArray',
          'membersCommunityLocal',
        ])->setSetting('valueCallback', [
          '\Drupal\grants_handler\Plugin\WebformHandler\GrantsHandler',
          'convertToInt',
        ])
        ->setSetting('typeOverride', [
          'dataType' => 'string',
          'jsonType' => 'int',
        ]);

      $info['members_applicant_community_global'] = DataDefinition::create('integer')
        ->setLabel('Yhteisöjäsenet')
        ->setSetting('jsonPath', [
          'compensation',
          'communityInfo',
          'generalCommunityInfoArray',
          'membersCommunityGlobal',
        ])->setSetting('valueCallback', [
          '\Drupal\grants_handler\Plugin\WebformHandler\GrantsHandler',
          'convertToInt',
        ])
        ->setSetting('typeOverride', [
          'dataType' => 'string',
          'jsonType' => 'int',
        ]);

      $info['kokoaikainen_henkilotyovuosia'] = DataDefinition::create('integer')
        ->setLabel('Kokoaikaisten henkilötyövuodet')
        ->setSetting('jsonPath', [
          'compensation',
          'communityInfo',
          'generalCommunityInfoArray',
          'staffManyearsFulltime',
        ])->setSetting('valueCallback', [
          '\Drupal\grants_handler\Plugin\WebformHandler\GrantsHandler',
          'convertToInt',
        ])
        ->setSetting('typeOverride', [
          'dataType' => 'string',
          'jsonType' => 'int',
        ])
        ->setSetting('defaultValue', 0);

      $info['osa_aikainen_henkilotyovuosia'] = DataDefinition::create('integer')
        ->setLabel('Osa-aikaisten henkilötyövuodet')
        ->setSetting('jsonPath', [
          'compensation',
          'communityInfo',
          'generalCommunityInfoArray',
          'staffManyearsParttime',
        ])->setSetting('valueCallback', [
          '\Drupal\grants_handler\Plugin\WebformHandler\GrantsHandler',
          'convertToInt',
        ])
        ->setSetting('typeOverride', [
          'dataType' => 'string',
          'jsonType' => 'int',
        ])
        ->setSetting('defaultValue', 0);

      $info['organisaatio_kuuluu_valtionosuusjarjestelmaan_vos_'] = DataDefinition::create('boolean')
        ->setLabel('Organisaatio kuuluu valtionosuusjärjestelmään (VOS).')
        ->setSetting('jsonPath', [
          'compensation',
          'budgetInfo',
          'budgetInfoArray',
          'isPartOfVOS',
        ])
        ->setSetting('typeOverride', [
          'dataType' => 'string',
          'jsonType' => 'bool',
        ]);

      $info['organisaatio_kuului_valtionosuusjarjestelmaan_vos_'] = DataDefinition::create('boolean')
        ->setLabel('Organisaatio kuuluu valtionosuusjärjestelmään (VOS).')
        ->setSetting('jsonPath', [
          'compensation',
          'budgetInfo',
          'budgetInfoArray',
          'wasPartOfVOS',
        ])
        ->setSetting('typeOverride', [
          'dataType' => 'string',
          'jsonType' => 'bool',
        ]);

      $info['sisaltyyko_toiminnan_toteuttamiseen_jotain_muuta_rahanarvoista_p'] = DataDefinition::create('string')
        ->setLabel('Sisältyykö toiminnan toteuttamiseen jotain muuta rahanarvoista panosta tai vaihtokauppaa, joka ei käy ilmi budjetista?')
        ->setSetting('jsonPath', [
          'compensation',
          'budgetInfo',
          'budgetInfoArray',
          'otherValuables',
        ]);

    }
    return $this->propertyDefinitions;
  }

  /**
   * Override property definition.
   *
   * @param string $name
   *   Property name.
   *
   * @return \Drupal\Core\TypedData\DataDefinitionInterface|void|null
   *   Property definition.
   */
  public function getPropertyDefinition($name) {
    $retval = parent::getPropertyDefinition($name);
    return $retval;
  }

}
