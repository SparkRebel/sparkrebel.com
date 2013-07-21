<?php

namespace PW\ApplicationBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use JMS\SecurityExtraBundle\Annotation\Secure;

class DefaultController extends Controller
{
    /**
     * @Method("GET")
     * @Route("/_ga_exclude")
     * @Template
     */
    public function gaExcludeAction()
    {
        return array();
    }

    /**
     * @Secure(roles="ROLE_ADMIN")
     * @Method("GET")
     * @Template
     */
    public function emailViewAction(Request $request, $type = 'welcome', $output = 'both')
    {
        $user = $this->container->get('pw_user.user_manager')->getRepository()->findOneBy(array());
        switch ($type) {
            case 'welcome':
                $template = $this->container->get('twig')->loadTemplate('PWUserBundle:Register:welcome.email.twig');
                $context  = compact('user');
                break;
            case 'signup':
                $template = $this->container->get('twig')->loadTemplate('PWUserBundle:Register:signup.email.twig');
                $context  = compact('user');
                break;
            case 'missyou':
                $template = $this->container->get('twig')->loadTemplate('PWUserBundle:Register:missyou.email.twig');
                $context  = compact('user');
                break;
            case 'activity':
                $limit = $request->query->get('limit', 5);
                $notifications = $this->container->get('pw_activity.notification_manager')->getRepository()->createQueryBuilder()->eagerCursor(true)->limit($limit)->getQuery()->execute();
                $count = $this->container->get('pw_activity.notification_manager')->getRepository()->createQueryBuilder()->count()->getQuery()->execute();
                $template = $this->container->get('twig')->loadTemplate('PWActivityBundle:Notification:summary.email.twig');
                $context = compact('user', 'notifications', 'count');
                break;
            default:
                throw new \RuntimeException("Unknown mail type: {$type}");
                break;
        }

        $subject = $template->renderBlock('subject', $context);
        switch ($output) {
            case 'html':
                return new Response($template->renderBlock('body_html', array_merge($context, array('subject' => $subject))));
                break;
            case 'text':
                return new Response('<pre>' . $template->renderBlock('body_text', array_merge($context, array('subject' => $subject))) . '</pre>');
                break;
            case 'both':
            default:
                return array('type' => $type, 'subject' => $subject);
                break;
        }
    }
}
