<?php

namespace App\Controller;

use RWA\JWT\Token;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class LoginController extends AbstractController
{
   /**
    * @Route("/login", methods={"POST", "OPTIONS"})
    */
   public function login(Request $request): Response
   {
      header('Access-Control-Allow-Origin: *');
      header('Access-Control-Request-Method: POST');
      header('Access-Control-Allow-Headers: authorization, content-type');

      // early escape for pre-flight CORS check
      if($request->isMethod('OPTIONS')) {
         return new Response();
      }


      // todo extract all this to a JWT class
      $headers = getallheaders();
      $jwt_header = $headers['Authorization'] ?? null;
      if(!$jwt_header) return new Response('No/invalid token provided (1)', 401);

      $jwt_token = explode(' ', $jwt_header)[1] ?? null;
      if(!$jwt_token) return new Response('No/invalid token provided (2)', 401);

      $token = new Token($jwt_token);

      // only support 'jwt' tokens with 'hs256' encoding
      if('jwt' !== $token->getTokenType() || 'hs256' !== $token->getAlgorithm()) return new Response('No/invalid token provided (5)', 401);

      // validation
      // todo fetch user record from database
      if($token->getValue('username') !== 'rob.watson') return new Response('Username/password combination does not match (1)', 401);
      // we would now go to the database to fetch this user's password
      // this is what _would_ be fetched from the database:
      $user_password = hash('sha256', 'password');

      if(!$token->validateSignature($user_password))  return new Response('Username/password combination does not match (2)', 401);


      // if we get here, the token is valid!  The username/password combination can be considered correct
      return new Response('{"userLevel":10}');
   }
}