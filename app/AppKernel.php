<?php

use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Config\Loader\LoaderInterface;

class AppKernel extends Kernel
{
    public function registerBundles()
    {
        $bundles = array(
            //
            // Standard
            new Symfony\Bundle\FrameworkBundle\FrameworkBundle(),
            new Symfony\Bundle\SecurityBundle\SecurityBundle(),
            new Symfony\Bundle\TwigBundle\TwigBundle(),
            new Symfony\Bundle\MonologBundle\MonologBundle(),
            new Symfony\Bundle\SwiftmailerBundle\SwiftmailerBundle(),
            new Symfony\Bundle\AsseticBundle\AsseticBundle(),
            new Doctrine\Bundle\DoctrineBundle\DoctrineBundle(),
            new Sensio\Bundle\FrameworkExtraBundle\SensioFrameworkExtraBundle(),
            new JMS\AopBundle\JMSAopBundle(),
            new JMS\DiExtraBundle\JMSDiExtraBundle($this),
            new JMS\SecurityExtraBundle\JMSSecurityExtraBundle(),
            //
            // Third-party
            new Doctrine\Bundle\MongoDBBundle\DoctrineMongoDBBundle(),
            new Doctrine\Bundle\MongoDBSoftDeleteBundle\DoctrineMongoDBSoftDeleteBundle(),
            new Doctrine\Bundle\FixturesBundle\DoctrineFixturesBundle(),
            new Stof\DoctrineExtensionsBundle\StofDoctrineExtensionsBundle(),
            new JMS\SerializerBundle\JMSSerializerBundle($this),
            new FOS\UserBundle\FOSUserBundle(),
            new FOS\FacebookBundle\FOSFacebookBundle(),
            new FOS\OAuthServerBundle\FOSOAuthServerBundle(),
            new Snc\RedisBundle\SncRedisBundle(),
            new FOQ\ElasticaBundle\FOQElasticaBundle(),
            new Avalanche\Bundle\ImagineBundle\AvalancheImagineBundle(),
            new Knp\Bundle\PaginatorBundle\KnpPaginatorBundle(),
            new Knp\Bundle\MenuBundle\KnpMenuBundle(),
            new Stfalcon\Bundle\TinymceBundle\StfalconTinymceBundle(),
            new Genemu\Bundle\FormBundle\GenemuFormBundle(),
            new Craue\TwigExtensionsBundle\CraueTwigExtensionsBundle(),
            new NSM\Bundle\EmojiBundle\NSMEmojiBundle(),
            new Sensio\Bundle\BuzzBundle\SensioBuzzBundle(),
            new Cybernox\AmazonWebServicesBundle\CybernoxAmazonWebServicesBundle(),
            new Defrag\PheanstalkBundle\DefragPheanstalkBundle(),
            //
            // Local
            new PW\ApplicationBundle\PWApplicationBundle(),
            new PW\AdminBundle\PWAdminBundle(),
            new PW\UserBundle\PWUserBundle(),
            new PW\AssetBundle\PWAssetBundle(),
            new PW\CategoryBundle\PWCategoryBundle(),
            new PW\FlagBundle\PWFlagBundle(),
            new PW\ItemBundle\PWItemBundle(),
            new PW\OutfitBundle\PWOutfitBundle(),
            new PW\TagBundle\PWTagBundle(),
            new PW\FeedbackBundle\PWFeedbackBundle(),
            new PW\PostBundle\PWPostBundle(),
            new PW\BoardBundle\PWBoardBundle(),
            new PW\ActivityBundle\PWActivityBundle(),
            new PW\StoreBundle\PWStoreBundle(),
            new PW\FeatureBundle\PWFeatureBundle(),
            new PW\InviteBundle\PWInviteBundle(),
            new PW\SearchBundle\PWSearchBundle(),
            new PW\CmsBundle\PWCmsBundle(),
            new PW\ApiBundle\PWApiBundle(),
            new PW\CelebBundle\PWCelebBundle(),
            new PW\StatsBundle\PWStatsBundle(),
            new PW\GettyImagesBundle\PWGettyImagesBundle(),
            new PW\PicScoutBundle\PWPicScoutBundle(),
            new PW\JobBundle\PWJobBundle(),
            new PW\MailerBundle\PWMailerBundle(),
            new PW\BannerBundle\PWBannerBundle(),
            new PW\TaggingBundle\PWTaggingBundle(),
            new PW\NewsletterBundle\PWNewsletterBundle(),
        );

        if ($this->getEnvironment() !== 'prod') {
            $bundles[] = new RaulFraile\Bundle\LadybugBundle\RaulFraileLadybugBundle();
            $bundles[] = new Liip\FunctionalTestBundle\LiipFunctionalTestBundle();
            $bundles[] = new Symfony\Bundle\WebProfilerBundle\WebProfilerBundle();
            $bundles[] = new Sensio\Bundle\DistributionBundle\SensioDistributionBundle();
            $bundles[] = new Sensio\Bundle\GeneratorBundle\SensioGeneratorBundle();
            $bundles[] = new Leek\GitDebugBundle\LeekGitDebugBundle();
        }

        return $bundles;
    }

    public function registerContainerConfiguration(LoaderInterface $loader)
    {
        $loader->load(__DIR__.'/config/config_'.$this->getEnvironment().'.yml');
    }
}
