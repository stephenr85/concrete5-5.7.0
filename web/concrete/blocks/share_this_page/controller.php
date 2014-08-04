<?php

namespace Concrete\Block\ShareThisPage;
use \Concrete\Core\Block\BlockController;
use Concrete\Core\Sharing\ShareThisPage\ServiceList;
use Concrete\Core\Sharing\ShareThisPage\Service;
use Database;
use Core;

defined('C5_EXECUTE') or die("Access Denied.");

class Controller extends BlockController
{

    public $helpers = array('form');

    protected $btInterfaceWidth = 400;
    protected $btCacheBlockOutput = true;
    protected $btCacheBlockOutputOnPost = true;
    protected $btCacheBlockOutputForRegisteredUsers = true;
    protected $btInterfaceHeight = 400;
    protected $btTable = 'btShareThisPage';

    public function getBlockTypeDescription()
    {
        return t("Allows users to share this page with social networks.");
    }

    public function getBlockTypeName()
    {
        return t("Share This Page");
    }

    public function edit()
    {
        $selected = $this->getSelectedServices();
        $services = array();
        foreach($selected as $s) {
            $services[] = $s->getHandle();
        }

        $this->set('selected', json_encode($services));
        $this->set('services', ServiceList::get());
    }

    public function add()
    {
        $this->edit();
    }

    protected function getSelectedServices()
    {
        $links = array();
        $db = Database::get();
        $services = $db->GetCol('select service from btShareThisPage where bID = ? order by displayOrder asc',
            array($this->bID)
        );
        foreach($services as $service) {
            $ss = Service::getByHandle($service);
            if (is_object($ss)) {
                $links[] = $ss;
            }
        }
        return $links;
    }

    /*
    public function export(\SimpleXMLElement $blockNode)
    {
        foreach($this->getSelectedLinks() as $link) {
            $linkNode = $blockNode->addChild('link');
            $linkNode->addAttribute('service', $link->getServiceObject()->getHandle());
        }
    }

    public function getImportData($blockNode)
    {

        $args = array();
        foreach($blockNode->link as $link) {
            $link = Link::getByServiceHandle((string) $link['service']);
            $args['slID'][] = $link->getID();
        }
        return $args;
    }

    public function duplicate($newBlockID)
    {
        $db = Database::get();
        foreach($this->getSelectedLinks() as $link) {
            $db->insert('btSocialLinks', array('bID' => $newBlockID, 'slID' => $link->getID(), 'displayOrder' => $this->displayOrder));
        }
    }
    */

    public function validate()
    {
        $e = Core::make('helper/validation/error');
        $service = $this->post('service');
        if (count($service) == 0) {
            $e->add(t('You must choose at least one service.'));
        }
        return $e;
    }

    public function save($args)
    {
        $db = Database::get();
        $db->delete('btShareThisPage', array('bID' => $this->bID));
        $services = $args['service'];

        $statement = $db->prepare('insert into btShareThisPage (bID, service, displayOrder) values (?, ?, ?)');
        $displayOrder = 0;
        foreach($services as $service) {
            $statement->bindValue(1, $this->bID);
            $statement->bindValue(2, $service);
            $statement->bindValue(3, $displayOrder);
            $statement->execute();
            $displayOrder++;
        }
    }

    public function delete()
    {
        $db = Database::get();
        $db->delete('btShareThisPage', array('bID' => $this->bID));
    }

    public function view()
    {
        $this->requireAsset('css', 'font-awesome');
        $selected = $this->getSelectedServices();
        $this->set('selected', $selected);
    }

}
