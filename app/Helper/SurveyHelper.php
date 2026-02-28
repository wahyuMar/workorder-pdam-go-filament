<?php

namespace App\Helper;

use App\Models\Survey;

class SurveyHelper
{
   public static function generateNoSurvey(): string
   {
      $lastId = Survey::max('id') ?? 0;
      return 'SRV-' . date('Ymd') . '-'
         . str_pad($lastId + 1, 4, '0', STR_PAD_LEFT);
   }
}
