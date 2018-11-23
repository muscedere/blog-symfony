<?php
/**
 * Created by PhpStorm.
 * User: wilder
 * Date: 22/11/18
 * Time: 17:28
 */

namespace App\Controller;


use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use App\Entity\Article;
use App\Entity\Category;
use App\Form\CategoryType;
use Symfony\Component\HttpFoundation\Request;
use App\Form\ArticleType;



class BlogController extends AbstractController
{
    /**
     * Show all row from article's entity
     *
     * @Route("/blog/form", name="blog_index")
     * @return Response A response instance
     */
    public function index(): Response
    {
        $articles = $this->getDoctrine()
            ->getRepository(Article::class)
            ->findAll();
        if (!$articles) {
            throw $this->createNotFoundException(
                'No article found in article\'s table.'
            );
        }
        return $this->render(
            'blog/index.html.twig', [
                'articles' => $articles,
            ]
        );
    }

    /**
     * form add Article
     *
     * @Route("/form/article", name="blog_addArticle")
     * @return Response A response instance
     */
    public function addArticle (Request $request): Response
    {
        $article = new Article ();
        $form = $this->createForm(
            ArticleType::class, $article);
        $form->handleRequest($request);
        $em = $this->getDoctrine()
            ->getManager();
        if($form->isSubmitted()){
            $em->persist($article);
            $em->flush();}
        return $this->render(
            'blog/addArticle.html.twig', [
                'form' => $form->createView(),
            ]
        );
    }

    /**
     * form add Category
     *
     * @Route("/form/category", name="blog_addCategory")
     * @return Response A response instance
     */
    public function addCategory(Request $request): Response
    {
        $category = new Category ();
        $form = $this->createForm(
            CategoryType::class, $category);
        $form->handleRequest($request);
        $em = $this->getDoctrine()
            ->getManager();
        if ($form->isSubmitted()) {
            $em->persist($category);
            $em->flush();
        }
        return $this->render(
            'blog/searchCategory.html.twig', [
                'form' => $form->createView(),
            ]
        );
    }

    /**
     * @Route("/category/{category}", name="blog_show_category")
     */
    public function showByCategory(string $category)
    {
        $category = $this->getDoctrine()
            ->getRepository(Category::class)
            ->findOneByName($category);
        $articles = $category->getArticles();
        return $this->render('blog/category.html.twig', ['articles' => $articles, 'category' => $category]);
    }

    /**
     * @Route("/category/all", name="blog_show_category")
     */
    public function showAllByCategory()
    {
        $categories = $this->getDoctrine()
            ->getRepository(Category::class)
            ->findAll();

        $articles = $this->getDoctrine()
            ->getRepository(Article::class)
            ->findBy(
                array('category' => $categories)
            );
        return $this->render('blog/showCategories.html.twig', ['categories' => $categories, 'articles' => $articles]);
    }

    /**
     * Getting a article with a formatted slug for title
     *
     * @param string $slug The slugger
     *
     * @Route("/blog/{slug<^[a-z0-9-]+$>}",
     *     defaults={"slug" = null},
     *     name="blog_show")
     * @return Response A response instance
     */
    public function show($slug): Response
    {
        if (!$slug) {
            throw $this
                ->createNotFoundException('No slug has been sent to find an article in article\'s table.');
        }

        $slug = preg_replace(
            '/-/',
            ' ', ucwords(trim(strip_tags($slug)), "-")
        );

        $article = $this->getDoctrine()
            ->getRepository(Article::class)
            ->findOneBy(['title' => mb_strtolower($slug)]);

        if (!$article) {
            throw $this->createNotFoundException(
                'No article with ' . $slug . ' title, found in article\'s table.'
            );
        }

        return $this->render(
            'blog/show.html.twig',
            [
                'article' => $article,
                'slug' => $slug,
            ]
        );
    }
}