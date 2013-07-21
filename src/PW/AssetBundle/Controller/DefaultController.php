<?php

namespace PW\AssetBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller,
    Symfony\Component\HttpFoundation\Request,
    Symfony\Component\DomCrawler\Crawler,
    Sensio\Bundle\FrameworkExtraBundle\Configuration\Route,
    Sensio\Bundle\FrameworkExtraBundle\Configuration\Method,
    Sensio\Bundle\FrameworkExtraBundle\Configuration\Template,
    JMS\SecurityExtraBundle\Annotation\Secure,
    PW\BoardBundle\Document\Board,
    PW\PostBundle\Document\Post;

class DefaultController extends Controller
{
    /**
     * @Method("GET")
     * @Route("/add", name="add")
     * @Template
     */
    public function indexAction()
    {
        return array();
    }

    /**
     * @Method("POST")
     * @Route("/add/site", name="add_site")
     * @Template
     */
    public function siteAction()
    {
        $form = $this->createFormBuilder()
            ->add('URL');

        $asset = $this->get('pw.asset');
        return array(
            'form' => $form->getForm()->createView(),
        );
    }

    /**
     * @Method("POST")
     * @Route("/add/upload", name="image_upload")
     * @Template
     */
    public function uploadAction(Request $req)
    {
        $form = $this->createFormBuilder()
            ->add('file', 'file');

        $asset = $this->get('pw.asset')->addUpload($req, 'form', 'file');
        if ($asset) {
            /* FIXME */
            $image = $req->getUriForPath('/../' . $asset->getUrl());
            return $this->redirect($this->generateUrl('post_add_index', array(
                'type'  => 'upload',
                'image' => $asset->getUrl(),
                'asset' => $asset->getId(),
                'url'   => $asset->getUrl(),
            )));
        }

        return array(
            'form' => $form->getForm()->createView(),
        );
    }

    /**
     * @Method("POST")
     * @Secure(roles="ROLE_USER")
     * @Route("/add/site/fetchimages", name="add_site_fetchimages")
     * @Template
     */
    public function fetchImagesAction()
    {
        $req  = $this->getRequest();
        $post = $req->get('form');
        $url  = $post['URL'];

        if (!preg_match("~^(?:f|ht)tps?://~i", $url)) {
            $url = "http://" . $url;
        }

        $valid = $url && in_array(parse_url($url, PHP_URL_SCHEME), array('https', 'http'));
        if (empty($url) || empty($url) || !$valid) {
            $this->get('session')->setFlash('error', sprintf("url invalid? url provided: %s", $url));
            return $this->redirect($this->generateUrl('add'), 301);
        }
        
        $sparker = new \PW\PostBundle\Model\VideoSparker($url);
        if ($sparker->isValidVideoUrl()) {
            return $this->render('PWAssetBundle:Default:fetchVideo.html.twig', array('url' => $url, 'video' => $sparker));
        }
        
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HEADER, 1);        
        $ret = curl_exec($ch);
        $responseCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        
        if($responseCode !== 200) {          
            $message = $this->getErrorMessage($responseCode, $url);
            $this->get('session')->setFlash('error', sprintf($message, $url));          
            return $this->redirect($this->generateUrl('add'), 301);
        }
        

        list($headers, $html) = explode("\r\n\r\n", $ret, 2);

        if (empty($html)) {
            $this->get('session')->setFlash('error', sprintf("couldn't load url. url provided: %s ", $url));
            return $this->redirect($this->generateUrl('add'), 301);
        }

        $crawler = new Crawler();
        $crawler->addHtmlContent($html);
        $images = array_unique($crawler->filter('img')->extract(array('src')));

        if (count($images) == 0) {
            $this->get('session')->setFlash('error', sprintf("couldn't find any images on that page. url provided: %s", $url));
            return $this->redirect($this->generateUrl('add'), 301);
        }

        // $this->get('session')->setFlash('notice', sprintf("Found %d images", count($images)));
        $that = $this;
        $images = array_map(function($img) use ($url, $that) {
            if ($img === '') {
                return null;
            }
            return $that->getUrl($img, $url);
        }, $images);

        //remove null entries
        $images = array_values(array_filter($images, 'strlen'));

        return compact('images', 'url');
    }

    /**
     * @Method("GET")
     * @Secure(roles="ROLE_ADMIN")
     * @Route("/fix/{id}", name="asset_fix")
     * @Template
     */
    public function fixAction(Request $request, $id)
    {
        $command = "asset:sync ".$id." --verbose --env=prod";
        $this->get('pw.event')->requestJob($command, 'high', 'assets', '', 'feeds');
        return new \Symfony\Component\HttpFoundation\Response('command "'.$command.'" queued');          
    }    
    
    /**
     * Return translated error message for http code
     *
     * @param integer $code 
     * @param string $url 
     * @return string $message
     */
    public function getErrorMessage($code, $url)
    {
        $message = "There was a problem while fetching url You requested: %s";
      
        switch ($code) {
          case 403:
            $message = "We couldn't not fetch content from %s. Please add images from this site manually";          
          break;

          case 404:
            $message = "There url You provided does not exist: %s";
          break;

        }      
        return $message;
    }
    
    public function getUrl($url, $base)
    {
        if (parse_url($url, PHP_URL_SCHEME)) {
            return $url;
        }
        if ($url[0] == '/') {
            return parse_url($base, PHP_URL_SCHEME)
                . '://' . parse_url($base, PHP_URL_HOST)
                . $url;
        }
        return dirname($base) . $url;
    }
       
    
}
