<?php

namespace PW\ItemBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand,
    Symfony\Component\Console\Input\InputArgument,
    Symfony\Component\Console\Input\InputInterface,
    Symfony\Component\Console\Output\OutputInterface;

/**
 * FeedItemStep2Command
 */
class FeedItemStep2Command extends ContainerAwareCommand
{
    protected $dm;
    protected $repos = array();

    protected function configure()
    {
        $this
            ->setName('feed:item:step2')
            ->setDescription('Image processing')
            ->setDefinition(array(
                new InputArgument(
                    'id',
                    InputArgument::REQUIRED,
                    'The feed-item fid'
                )
            ));
    }

    protected function setupRepos()
    {
        $this->dm = $this->getContainer()->get('doctrine_mongodb.odm.document_manager');

        $this->repos['Item']     = $this->dm->getRepository('PWItemBundle:Item');
        $this->repos['FeedItem'] = $this->dm->getRepository('PWItemBundle:FeedItem');
    }

    /**
     * @param InputInterface  $input  instance
     * @param OutputInterface $output instance
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->setupRepos();

        $id   = $input->getArgument('id');
        $item = $this->processItem($id);

        $output->writeln("<info>Step #2 complete for:</info> {$id} - {$item->getName()}");
        return true;
    }

    /**
     * Accepts a feed item and finds or creates the assets it relies upon
     *
     * @param mixed $feedItem instance or id
     *
     * @return generated/updated item
     */
    protected function processItem($feedItem)
    {
        if ($this->getContainer()->get('kernel')->getEnvironment() !== 'test') {
            $this->getContainer()->get('pw.event')->setMode('foreground');
        }

        if (!is_object($feedItem)) {
            $id = $feedItem;
            $feedItem = $this->repos['FeedItem']->findOneBy(array('fid' => $id));
            if (!$feedItem) {
                throw new \Exception("Feed Item $id doesn't exist");
            }
        }
        $fid = $feedItem->getFid();
        $images = $feedItem->getImages();
        $imagePrimary = $feedItem->getMainImage();
		if(empty($imagePrimary)) {
			$feedItem->setMainImage($images[0]);
			$imagePrimary = $images[0];
		}
        $link = $feedItem->getLink();

        $asset = $this->getContainer()->get('pw.asset');

        $imageRefs = $feedItem->getImagesRef();
        if ($imageRefs) {
            foreach ($imageRefs as $image => $hash) {
                if (is_array($hash)) {
                    unset($imageRefs[$image]);
                    $image = $hash['original'];
                    $hash = $hash['sha1'];
                }
                $imageRefs[$image] = $asset->addFeedImage($image, $link, $hash);
                if (!$imageRefs[$image]) {
                    unset($imageRefs[$image]);
                }
            }
        }

        if ($imagePrimary && !empty($imageRefs[$imagePrimary])) {
            $mainImage = $imageRefs[$imagePrimary];
            unset($imageRefs[$imagePrimary]);
        } else {
            if (count($imageRefs) > 0) {
                $mainImage = array_shift($imageRefs);
            } else {
                throw new \Exception("No Images supplied for Feed Item $id");
            }
        }

        $item = $this->repos['Item']->findOneBy(array('feedId' => $fid));
        $item->setImagePrimary($mainImage);

        foreach ($imageRefs as $image) {
            $item->addImages($image);
        }

        $this->dm->persist($item);

        $feedItem->setStatus('step3');
        $this->dm->persist($feedItem);
        $this->dm->flush(null, array('safe' => false, 'fsync' => false));

        return $item;
    }
}
