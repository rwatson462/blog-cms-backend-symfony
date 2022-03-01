<?php

namespace RWA\JWT;

use RWA\Util\Base64;
use Exception;

/**
 * A JWT Token
 */
class Token {

   protected array $header = [];
   protected array $payload = [];
   protected string $signature = '';

   public function __construct(
      protected string $token_string = ''
   ) {
      $this->parseToken();
   }

   // Split the token up into its various part and decode contents as best we can
   public function parseToken(): void {
      $token_parts = explode('.', $this->token_string);
      if(sizeof($token_parts) !== 3) throw new Exception('Invalid token given');

      $this->header = json_decode(Base64::URLDecode($token_parts[0]),true);
      $this->payload = json_decode(Base64::URLDecode($token_parts[1]), true);
      $this->signature = Base64::URLDecode($token_parts[2]);
   }

   public function getTokenType(): string {
      return $this->header['typ'] ?? null;
   }
   public function getAlgorithm(): string {
      return $this->header['alg'] ?? null;
   }
   
   public function getCustomHeader(string $prop): string {
      return $this->header[$prop] ?? null;
   }

   public function getValue(string $prop): string {
      return $this->payload[$prop] ?? null;
   }

   public function getSignature(): string {
      return $this->signature;
   }

   // validates that a token is signed with the given secret
   public function validateSignature($secret): string {
      return $this->generateSignature($secret) === $this->signature;
   }

   /**
    * creates a signature from the current token, returns the signature, doesn't store it
    * this can be used to validate that a signature is correct
    */
   public function generateSignature($secret): string {
      $encoded_header = Base64::URLEncode(json_encode($this->header));
      $encoded_payload = Base64::URLEncode(json_encode($this->payload));
      return hash_hmac('sha256', $encoded_header.'.'.$encoded_payload, $secret);
   }
}