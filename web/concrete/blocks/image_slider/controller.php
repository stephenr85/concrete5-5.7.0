<?php
namespace Concrete\Block\ImageSlider;

use Concrete\Core\Block\BlockController;
use Loader;

class Controller extends BlockController
{
    protected $btTable = 'btImageSlider';
    protected $btExportTables = array('btImageSlider', 'btImageSliderEntries');
    protected $btInterfaceWidth = "600";
    protected $btWrapperClass = 'ccm-ui';
    protected $btInterfaceHeight = "465";
    protected $btCacheBlockRecord = true;
    protected $btCacheBlockOutput = true;
    protected $btCacheBlockOutputOnPost = true;
    protected $btCacheBlockOutputForRegisteredUsers = true;

    public function getBlockTypeDescription()
    {
        return t("Display your images and captions in an attractive slideshow format.");
    }

    public function getBlockTypeName()
    {
        return t("Image Slider");
    }

    public function getSearchableContent()
    {
        $content = '';
        $db = Loader::db();
        $v = array($this->bID);
        $q = 'select * from btImageSliderEntries where bID = ?';
        $r = $db->query($q, $v);
        foreach($r as $row) {
           $content.= $row['title'].' ';
           $content.= $row['description'].' ';
        }
        return $content;
    }

    public function add()
    {
        $this->requireAsset('core/file-manager');
        $this->requireAsset('redactor');
    }

    public function edit()
    {
        $this->requireAsset('core/file-manager');
        $this->requireAsset('redactor');
        $db = Loader::db();
        $query = $db->GetAll('SELECT * from btImageSliderEntries WHERE bID = ? ORDER BY sortOrder', array($this->bID));
        $this->set('rows', $query);
    }

    public function view()
    {
        $db = Loader::db();
        $query = $db->GetAll('SELECT * from btImageSliderEntries WHERE bID = ? ORDER BY sortOrder', array($this->bID));
        $this->set('rows', $query);
    }

    public function duplicate($newBID) {
        $db = Loader::db();
        $v = array($this->bID);
        $q = 'select * from btImageSliderEntries where bID = ?';
        $r = $db->query($q, $v);
        foreach($r as $row) {
            $db->execute('INSERT INTO btImageSliderEntries (bID, fID, linkURL, title, description, sortOrder) values(?,?,?,?,?,?)',
                array(
                    $newBID,
                    $row['fID'],
                    $row['linkURL'],
                    $row['title'],
                    $row['description'],
                    $row['sortOrder']
                )
            );
        }
    }

    public function delete()
    {
        $db = Loader::db();
        $db->execute('DELETE from btImageSliderEntriesWHERE bID = ?', array($this->bID));
        parent::delete();
    }

    public function save($args)
    {
        $db = Loader::db();
        $db->execute('DELETE from btImageSliderEntries WHERE bID = ?', array($this->bID));
        $count = count($args['sortOrder']);
        $i = 0;
        parent::save($args);
        while ($i < $count) {
            $db->execute('INSERT INTO btImageSliderEntries (bID, fID, linkURL, title, description, sortOrder) values(?,?,?,?,?,?)',
                array(
                    $this->bID,
                    $args['fID'][$i],
                    $args['linkURL'][$i],
                    $args['title'][$i],
                    $args['description'][$i],
                    $args['sortOrder'][$i]
                )
            );
            $i++;
        }
    }

}