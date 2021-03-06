<?php

/**
 * BaseDcPage
 * 
 * This class has been auto-generated by the Doctrine ORM Framework
 * 
 * @property integer $id
 * @property string $urlId
 * @property string $title
 * @property clob $htmlContent
 * @property boolean $published
 * 
 * @package    ##PACKAGE##
 * @subpackage ##SUBPACKAGE##
 * @author     ##NAME## <##EMAIL##>
 * @version    SVN: $Id: Builder.php 7490 2010-03-29 19:53:27Z jwage $
 */
abstract class BaseDcPage extends Doctrine_Record
{
    public function setTableDefinition()
    {
        $this->setTableName('dc_page');
        $this->hasColumn('id', 'integer', null, array(
             'type' => 'integer',
             'autoincrement' => true,
             'primary' => true,
             ));
        $this->hasColumn('urlId', 'string', 255, array(
             'type' => 'string',
             'notnull' => true,
             'length' => '255',
             ));
        $this->hasColumn('title', 'string', 255, array(
             'type' => 'string',
             'notnull' => true,
             'length' => '255',
             ));
        $this->hasColumn('htmlContent', 'clob', null, array(
             'type' => 'clob',
             ));
        $this->hasColumn('published', 'boolean', null, array(
             'type' => 'boolean',
             'default' => false,
             ));


        $this->index('urlId', array(
             'fields' => 
             array(
              0 => 'urlId',
             ),
             ));
        $this->option('type', 'INNODB');
        $this->option('charset', 'utf8');
    }

    public function setUp()
    {
        parent::setUp();
        $timestampable0 = new Doctrine_Template_Timestampable();
        $i18n0 = new Doctrine_Template_I18n(array(
             'fields' => 
             array(
              0 => 'title',
              1 => 'htmlContent',
              2 => 'published',
             ),
             'length' => 5,
             ));
        $timestampable1 = new Doctrine_Template_Timestampable();
        $i18n0->addChild($timestampable1);
        $this->actAs($timestampable0);
        $this->actAs($i18n0);
    }
}