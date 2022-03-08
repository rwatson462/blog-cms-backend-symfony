<?php

namespace App\Controller;

use App\Entity\BlogArticle;
use App\Repository\BlogArticleRepository;
use Doctrine\Persistence\ManagerRegistry;
use RWA\JWT\Token;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ArticleController extends AbstractController
{
   /**
    * @Route("/articles", methods={"GET"})
    */
   public function getAllArticles(BlogArticleRepository $repository): Response
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
      header('Cache-control: no-cache');
      $articles = array_map( function($article) {
         return [
            'id' => $article->getId(),
            'title' => $article->getTitle(),
            'url' => $article->getSlug(),
            'author' => $article->getAuthor(),
            'published' => $article->getPublished(),
            'deleted' => $article->getDeleted()
         ];
      }, $repository->findAll());
      return new Response(json_encode($articles));
   }

   /**
    * @Route("/articles/{id}", methods={"GET"})
    */
   public function getArticle(int $id, BlogArticleRepository $repository): Response
   {
      // todo validate jwt token
      $article = $repository->find($id);

      if(!$article) return new Response('Article not found', 404);

      $article = [
         'id' => $article->getId(),
         'title' => $article->getTitle(),
         'url' => $article->getSlug(),
         'author' => $article->getAuthor(),
         'content' => $article->getContent(),
         'published' => $article->getPublished(),
         'deleted' => $article->getDeleted()
      ];

      header('Content-type: application/json');
      return new Response(json_encode($article));
   }


   /**
    * @Route("/articles", methods={"POST"})
    */
   public function createArticle(Request $request, ManagerRegistry $doctrine): Response
   {
      // todo validate jwt token
      // todo save new article to database

      // we're creating an article
      // database::insert
      $entityManager = $doctrine->getManager();

      $article = json_decode($request->getContent(),true)['article'] ?? null;
      $blog_article = new BlogArticle();
      $blog_article->setTitle($article['title']);
      $blog_article->setSlug($article['url']);
      $blog_article->setContent($article['content']);
      $blog_article->setAuthor('Roberto');
      $blog_article->setPublished(false);
      $blog_article->setDeleted(false);

      // queue for saving
      $entityManager->persist($blog_article);
      // actually commit to database
      $entityManager->flush();

      // this apparently is now populated after saving
      $id = $blog_article->getId();

      header('Content-type: application/json');
      return new Response(json_encode(['result' => 'ok', 'id' => $id]));
   }

   /**
    * @Route("/articles/{id}", methods={"POST"})
    */
   public function updateArticle(int $id, Request $request, BlogArticleRepository $repository, ManagerRegistry $doctrine): Response
   {
      // todo validate jwt token
      // todo update existing article in database

      // we're updating an existing article
      // database::update
      $blog_article = $repository->find($id);

      if(!$blog_article) return new Response('Article not found', 404);

      $new_article = json_decode($request->getContent(),true)['article'] ?? null;

      if(!$new_article) return new Response('No article data given', 404);

      $blog_article->setSlug($new_article['url']);
      $blog_article->setTitle($new_article['title']);
      $blog_article->setContent($new_article['content']);

      $entityManager = $doctrine->getManager();
      $entityManager->persist($blog_article);
      $entityManager->flush();

      header('Content-type: application/json');
      return new Response(json_encode(['result' => 'ok']));
   }
}
