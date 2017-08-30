<?php

namespace EMS\ClientHelperBundle\EMSBackendBridgeBundle\Twig;

use EMS\ClientHelperBundle\EMSBackendBridgeBundle\Elasticsearch\ClientRequest;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class HelperExtension extends AbstractExtension
{
    /**
     * {@inheritdoc}
     */
    public function getFilters()
    {
        return [
            new TwigFilter('emsch_ouuid', array($this, 'getOuuid')),
            new TwigFilter('dump', array($this, 'dump')),
        ];
    }
    
    /**
     * {@inheritdoc}
     */
    public function getOuuid($emsLink)
    {
        return ClientRequest::getOuuid($emsLink);
    }
    
    public function dump($object) {
        if(function_exists('dump')){
            dump($object);
        }
    }

        /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'emsch_helper';
    }
}
