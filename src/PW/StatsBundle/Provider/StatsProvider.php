<?php

namespace PW\StatsBundle\Provider;

use PW\StatsBundle\Document\Stats,
    PW\PostBundle\Document\Post,
    PW\UserBundle\Document\User,
    PW\BoardBundle\Document\Board;

/**
 * StatsProvider
 */
class StatsProvider
{
    /**
     * @var \Doctrine\ODM\MongoDB\DocumentManager
     */
    protected $dm;

    /**
     * Called automatically
     *
     * @param Object $eventManager instance
     */
    public function setEventManager(\PW\ApplicationBundle\Model\EventManager $eventManager = null)
    {
        $this->event = $eventManager;
    }

    /**
     * Called automatically
     *
     * @param Object $dm Document manager instance
     */
    public function setDocumentManager(\Doctrine\ODM\MongoDB\DocumentManager $dm)
    {
        $this->dm = $dm;
    }

    public function updateSummary(Stats $stat, $date)
    {
        $doc = $stat->getReference();
        $id  = $date . ':' . $stat->getAction(). ':'
            . $doc->getId();

        $ref = $this->dm->createDBRef($doc);
        $ref['_doctrine_class_name'] = get_class($doc);
        $this->dm->createQueryBuilder('PWStatsBundle:Summary')
               ->update()
               ->upsert(true)
               ->field('id')->equals($id)
               ->field('total')->inc(1)
               ->field('reference')->set($ref)
               ->field('date')->set($date)
            ->getQuery(array('upsert' => true))
            ->execute();

        $this->dm->flush();
    }

    public function Record($action, $doc, $user = NULL, $ip = NULL)
    {
        $stat = new Stats;
        $stat->setAction($action);
        if ($user && $user instanceof User) {
            $stat->setUser($user);
        }

        $stat->setReference($doc);

        if ($ip) {
            $stat->setIp(ip2long($ip));
        }

        // save it
        $this->dm->persist($stat);
        $this->dm->flush();

        // summary
        $ts = $stat->getCreated()->getTimestamp();
        $this->updateSummary($stat, date('Y-m-d', $ts));
        $this->updateSummary($stat, date('Y-m', $ts));

    }

    
    public function isHttpUserAgentBot()
    {
        $bots = array('facebook', 'google', 'adsense', 'mediapartners-google', 'googlebot', 'yandex',
            'bingbot', 'msn', 'abacho', 'abcdatos', 'abcsearch', 'acoon',
            'adsarobot', 'aesop', 'ah-ha',
            'alkalinebot', 'almaden', 'altavista', 'antibot', 'anzwerscrawl', 'aol', 'search', 'appie', 'arachnoidea',
            'araneo', 'architext', 'ariadne', 'arianna', 'ask', 'jeeves', 'aspseek', 'asterias', 'astraspider', 'atomz',
            'augurfind', 'backrub', 'baiduspider', 'bannana_bot', 'bbot', 'bdcindexer', 'blindekuh', 'boitho', 'boito',
            'borg-bot', 'bsdseek', 'christcrawler', 'computer_and_automation_research_institute_crawler', 'coolbot',
            'cosmos', 'crawler', 'crawler@fast', 'crawlerboy', 'cruiser', 'cusco', 'cyveillance', 'deepindex', 'denmex',
            'dittospyder', 'docomo', 'dogpile', 'dtsearch', 'elfinbot', 'entire', 'esismartspider', 'exalead',
            'excite', 'ezresult', 'fast', 'fast-webcrawler', 'fdse', 'felix', 'fido', 'findwhat', 'finnish', 'firefly',
            'firstgov', 'fluffy', 'freecrawl', 'frooglebot', 'galaxy', 'gaisbot', 'geckobot', 'gencrawler', 'geobot',
            'gigabot', 'girafa', 'goclick', 'goliat', 'griffon', 'gromit', 'grub-client', 'gulliver',
            'gulper', 'henrythemiragorobot', 'hometown', 'hotbot', 'htdig', 'hubater', 'ia_archiver', 'ibm_planetwide',
            'iitrovatore-setaccio', 'incywincy', 'incrawler', 'indy', 'infonavirobot', 'infoseek', 'ingrid', 'inspectorwww',
            'intelliseek', 'internetseer', 'ip3000.com-crawler', 'iron33', 'jcrawler', 'jeeves', 'jubii', 'kanoodle',
            'kapito', 'kit_fireball', 'kit-fireball', 'ko_yappo_robot', 'kototoi', 'lachesis', 'larbin', 'legs',
            'linkwalker', 'lnspiderguy', 'look.com', 'lycos', 'mantraagent', 'markwatch', 'maxbot', 'mercator', 'merzscope',
            'meshexplorer', 'metacrawler', 'mirago', 'mnogosearch', 'moget', 'motor', 'muscatferret', 'nameprotect',
            'nationaldirectory', 'naverrobot', 'nazilla', 'ncsa', 'netnose', 'netresearchserver', 'ng/1.0',
            'northerlights', 'npbot', 'nttdirectory_robot', 'nutchorg', 'nzexplorer', 'odp', 'openbot', 'openfind',
            'osis-project', 'overture', 'perlcrawler', 'phpdig', 'pjspide', 'polybot', 'pompos', 'poppi', 'portalb',
            'psbot', 'quepasacreep', 'rabot', 'raven', 'rhcs', 'robi', 'robocrawl', 'robozilla', 'roverbot', 'scooter',
            'scrubby', 'search.ch', 'search.com.ua', 'searchfeed', 'searchspider', 'searchuk', 'seventwentyfour',
            'sidewinder', 'sightquestbot', 'skymob', 'sleek', 'slider_search', 'slurp', 'solbot', 'speedfind', 'speedy',
            'spida', 'spider_monkey', 'spiderku', 'stackrambler', 'steeler', 'suchbot', 'suchknecht.at-robot', 'suntek',
            'szukacz', 'surferf3', 'surfnomore', 'surveybot', 'suzuran', 'synobot', 'tarantula', 'teomaagent', 'teradex',
            't-h-u-n-d-e-r-s-t-o-n-e', 'tigersuche', 'topiclink', 'toutatis', 'tracerlock', 'turnitinbot', 'tutorgig',
            'uaportal', 'uasearch.kiev.ua', 'uksearcher', 'ultraseek', 'unitek', 'vagabondo', 'verygoodsearch', 'vivisimo',
            'voilabot', 'voyager', 'vscooter', 'w3index', 'w3c_validator', 'wapspider', 'wdg_validator', 'webcrawler',
            'webmasterresourcesdirectory', 'webmoose', 'websearchbench', 'webspinne', 'whatuseek', 'whizbanglab', 'winona',
            'wire', 'wotbox', 'wscbot', 'www.webwombat.com.au', 'xenu', 'link', 'sleuth', 'xyro', 'yahoobot', 'yahoo!',
            'slurp', 'yellopet-spider', 'zao/0', 'zealbot', 'zippy', 'zyborg'
        );
        
        $user_agent = strtolower($_SERVER['HTTP_USER_AGENT']);
        $log_date = '['. date('Y-m-d H:i:s') .']';
        file_put_contents('/tmp/StatsProvider_isHttpUserAgentBot.log', $log_date.' user agent: '.$user_agent."\n", FILE_APPEND); // log for now
        foreach($bots as $bot) {
            if (strpos($user_agent, $bot) !== false) {
                file_put_contents('/tmp/StatsProvider_isHttpUserAgentBot.log', $log_date." found bot!: $bot\n", FILE_APPEND); // log for now
                return true;
            }
        }
        return false;
    }
}
