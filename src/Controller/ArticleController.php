<?php

namespace App\Controller;

use RWA\JWT\Token;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ArticleController extends AbstractController
{
   private static array $articles = [
      [
         'id'        => 1,
         'title'     => 'Test article',
         'url'       => '/articles/test-article',
         'content'   => "#Test article\n\nThis is a test.  Repeat, _this is a *test*_",
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
      ];


   /**
    * @Route("/articles", methods={"GET"})
    */
   public function getAllArticles(Request $request): Response
   {
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
      return new Response(json_encode(self::$articles));
   }

   /**
    * @Route("/articles/{id}", methods={"GET"})
    */
   public function getArticle(int $id, Request $request): Response
   {
      // todo validate jwt token
      // todo fetch specific article from database

      // array keys are preserved, so this might not give us $arr[0] to use
      $arr = array_filter(self::$articles, function($article) use ($id) {
         return $article['id'] === $id;
      });
      // so reset is needed to fetch "the first item" from the array
      $article = reset($arr);

      // reset returns false if it doesn't work as we want it to
      if($article === false) return new Response('Article not found', 404);

      header('Content-type: application/json');
      return new Response(json_encode($article));
   }


   /**
    * @Route("/articles", methods={"POST"})
    */
   public function createArticle(Request $request): Response
   {
      // todo validate jwt token
      // todo save new article to database

      // we're creating an article
      // database::insert
      $article = json_decode($request->getContent(),true);

      header('Content-type: application/json');
      return new Response(json_encode(['result' => 'ok']));
   }

   /**
    * @Route("/articles/{id}", methods={"PUT"})
    */
   public function updateArticle(int $id, Request $request): Response
   {
      // todo validate jwt token
      // todo update existing article in database

      // we're updating an existing article
      // database::update
      $article = json_decode($request->getContent(),true);

      header('Content-type: application/json');
      return new Response(json_encode(['result' => 'ok']));
   }
}
