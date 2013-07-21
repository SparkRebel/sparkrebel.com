<?php

namespace PW\UserBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller,
    Symfony\Component\HttpFoundation\Request,
    Sensio\Bundle\FrameworkExtraBundle\Configuration\Route,
    Sensio\Bundle\FrameworkExtraBundle\Configuration\Method,
    Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

class FeaturedBrandsController extends Controller
{
    /*
        Needed to be moved to brandcontroller, since route recognition was loaded in wrong way,
        dont use pw_user_featuredbrands_index route here
    */
}
