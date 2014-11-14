<?php

/**
 * News_Model_Doctrine_BaseNews
 * 
 * This class has been auto-generated by the Doctrine ORM Framework
 * 
 * @property integer $id
 * @property integer $views
 * @property integer $author_id
 * @property integer $last_editor_id
 * @property string $name
 * @property string $slug
 * @property clob $description
 * @property boolean $publish
 * @property timestamp $publish_date
 * @property integer $photo_root_id
 * @property integer $metatag_id
 * @property integer $video_root_id
 * @property integer $attachment_id
 * @property Doctrine_Collection $Translation
 * @property Doctrine_Collection $Tags
 * 
 * @package    Admi
 * @subpackage News
 * @author     Michał Folga <michalfolga@gmail.com>
 * @version    SVN: $Id: Builder.php 7490 2010-03-29 19:53:27Z jwage $
 */
abstract class News_Model_Doctrine_BaseNews extends Doctrine_Record
{
    public function setTableDefinition()
    {
        $this->setTableName('news_news');
        $this->hasColumn('id', 'integer', 4, array(
             'primary' => true,
             'autoincrement' => true,
             'type' => 'integer',
             'length' => '4',
             ));
        $this->hasColumn('views', 'integer', 4, array(
             'type' => 'integer',
             'length' => '4',
             ));
        $this->hasColumn('author_id', 'integer', 4, array(
             'type' => 'integer',
             'length' => '4',
             ));
        $this->hasColumn('last_editor_id', 'integer', 4, array(
             'type' => 'integer',
             'length' => '4',
             ));
        $this->hasColumn('name', 'string', 255, array(
             'type' => 'string',
             'length' => '255',
             ));
        $this->hasColumn('slug', 'string', 255, array(
             'type' => 'string',
             'length' => '255',
             ));
        $this->hasColumn('description', 'clob', null, array(
             'type' => 'clob',
             ));
        $this->hasColumn('publish', 'boolean', null, array(
             'type' => 'boolean',
             'default' => 1,
             ));
        $this->hasColumn('publish_date', 'timestamp', null, array(
             'type' => 'timestamp',
             ));
        $this->hasColumn('photo_root_id', 'integer', 4, array(
             'type' => 'integer',
             'length' => '4',
             ));
        $this->hasColumn('metatag_id', 'integer', 4, array(
             'type' => 'integer',
             'length' => '4',
             ));
        $this->hasColumn('video_root_id', 'integer', 4, array(
             'type' => 'integer',
             'length' => '4',
             ));
        $this->hasColumn('attachment_id', 'integer', 4, array(
             'type' => 'integer',
             'length' => '4',
             ));

        $this->option('type', 'MyISAM');
        $this->option('collate', 'utf8_general_ci');
        $this->option('charset', 'utf8');
    }

    public function setUp()
    {
        parent::setUp();
        $this->hasMany('News_Model_Doctrine_NewsTranslation as Translation', array(
             'local' => 'id',
             'foreign' => 'id'));

        $this->hasMany('News_Model_Doctrine_Tag as Tags', array(
             'refClass' => 'News_Model_Doctrine_NewsTag',
             'local' => 'news_id',
             'foreign' => 'tag_id'));

        $i18n0 = new Doctrine_Template_I18n(array(
             'fields' => 
             array(
              0 => 'name',
              1 => 'slug',
              2 => 'description',
             ),
             'tableName' => 'news_news_translation',
             'className' => 'NewsTranslation',
             ));
        $timestampable0 = new Doctrine_Template_Timestampable();
        $softdelete0 = new Doctrine_Template_SoftDelete();
        $this->actAs($i18n0);
        $this->actAs($timestampable0);
        $this->actAs($softdelete0);
    }
}