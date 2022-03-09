<?php

namespace App\Controller;

use RWA\JWT\Token;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Repository\BlogArticleRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class PublishController extends AbstractController
{
   /**
    * @Route("/publish/:id", methods={"POST"})
    */
   public function publishArticle(int $id, BlogArticleRepository $repository): Response
   {
      // todo check JWT

      // find article with given id
      $article = $repository->find($id);
      if(!$article) return new Response('Article not found', 404);

      // publish it by converting the markdown content to html and storing in the published table
      

      header('Content-type: application/json');
      return new Response(json_encode(['status'=>'ok']));
   }
}