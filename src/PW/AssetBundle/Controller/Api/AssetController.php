<?php

namespace PW\AssetBundle\Controller\Api;

use Symfony\Bundle\FrameworkBundle\Controller\Controller,
    Symfony\Component\HttpFoundation\Request,
    Symfony\Component\HttpKernel\Exception\HttpException,
    FOS\RestBundle\Controller\Annotations\Prefix,
    FOS\RestBundle\Controller\Annotations\NamePrefix,
    FOS\RestBundle\Controller\Annotations\View,
    FOS\RestBundle\View\RouteRedirectView,
    FOS\RestBundle\View\View AS FOSView;

/**
 * @todo Migrate '1.0' in URL to headers
 * @Prefix("/1.0")
 * @NamePrefix("api_")
 */
class AssetController extends Controller
{
    /**
     * @View
     */
    public function postImagesAction(Request $request)
    {
        $uploadedFile = $request->files->get('file');
        if (!$uploadedFile) {
            throw new HttpException(400, "New image is invalid or not found.");
        }

        /* @var $asset \PW\AssetBundle\Document\Asset */
        $asset = $this->get('pw.asset')->addUploadedFile($uploadedFile);

        return array(
            'asset' => $asset
        );
    }
}
