<?php

namespace App\Controller;

use RWA\JWT\Token;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ArticleController extends AbstractController
{
   /**
    * @Route("/articles", methods={"GET", "OPTIONS"})
    */
   public function getAllArticles(Request $request): Response
   {
      header('Access-Control-Allow-Origin: *');
      header('Access-Control-Request-Method: GET');
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

      // check that username is given in payload
      if(!$token->getValue('username')) return new Response('No/invalid token provided (6)', 401);

      // validation
      // todo fetch user record from database
      // this would usually be a single SQL statement or something
      if($token->getValue('username') !== 'rob.watson') return new Response('Unknown user', 401);
      // we would now go to the database to fetch this user's password
      // this is what _would_ be fetched from the database:
      $user_password = hash('sha256', 'password');

      if(!$token->validateSignature($user_password))  return new Response('Authorisation failed', 401);

      if(10 > $token->getValue('access-level')) return new Response('No access allowed');

      // if we get here, the token is valid!  The username/password combination can be considered correct
      header('Content-type: application/json');
      return new Response(json_encode([
         [
            'id'        => 1,
            'title'     => 'Test article',
            'url'       => '/articles/test-article',
            'published' => false,
            'deleted'   => false
         ],
         [
            'id'        => 2,
            'title'     => 'First real article',
            'url'       => '/articles/first-real-article',
            'published' => true,
            'deleted'   => false
         ],
         [
            'id'        => 3,
            'title'     => 'Old article we do not want',
            'url'       => '/articles/old-article-we-do-not-want',
            'published' => false,
            'deleted'   => true
         ]
      ]));
   }
}
