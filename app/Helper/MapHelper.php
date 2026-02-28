<?php

namespace App\Helper;


class MapHelper
{
   public static function generateGoogleMapLink($lattitude, $longitude): string
   {
      return "https://www.google.com/maps?q={$lattitude},{$longitude}";
   }
}
