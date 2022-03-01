<?php

namespace RWA\Util;

class Base64 {
   public static function Decode(string $str): string {
      return base64_decode($str);
   }

   public static function Encode(string $str): string {
      return base64_encode($str);
   }

   public static function URLEncode(string $str): string {
      return str_replace('+','-', str_replace('/','_', self::encode($str)));
   }

   public static function URLDecode(string $str): string {
      return self::decode(str_replace('_','/', str_replace('-','+', $str)));
   }
}