<?php
/**
 * This class has been auto-generated by the Doctrine ORM Framework
 */
class Version3 extends Doctrine_Migration_Base
{
    public function up()
    {
        $this->createForeignKey('slider_slider_serwis5', 'slider_slider_serwis5_slide_root_id_slider_slide_serwis5_id', array(
             'name' => 'slider_slider_serwis5_slide_root_id_slider_slide_serwis5_id',
             'local' => 'slide_root_id',
             'foreign' => 'id',
             'foreignTable' => 'slider_slide_serwis5',
             ));
        $this->createForeignKey('slider_slider_serwis6', 'slider_slider_serwis6_slide_root_id_slider_slide_serwis6_id', array(
             'name' => 'slider_slider_serwis6_slide_root_id_slider_slide_serwis6_id',
             'local' => 'slide_root_id',
             'foreign' => 'id',
             'foreignTable' => 'slider_slide_serwis6',
             ));
        $this->createForeignKey('slider_slider_serwis7', 'slider_slider_serwis7_slide_root_id_slider_slide_serwis7_id', array(
             'name' => 'slider_slider_serwis7_slide_root_id_slider_slide_serwis7_id',
             'local' => 'slide_root_id',
             'foreign' => 'id',
             'foreignTable' => 'slider_slide_serwis7',
             ));
        $this->createForeignKey('slider_slider_serwis8', 'slider_slider_serwis8_slide_root_id_slider_slide_serwis8_id', array(
             'name' => 'slider_slider_serwis8_slide_root_id_slider_slide_serwis8_id',
             'local' => 'slide_root_id',
             'foreign' => 'id',
             'foreignTable' => 'slider_slide_serwis8',
             ));
        $this->addIndex('slider_slider_serwis5', 'slider_slider_serwis5_slide_root_id', array(
             'fields' => 
             array(
              0 => 'slide_root_id',
             ),
             ));
        $this->addIndex('slider_slider_serwis6', 'slider_slider_serwis6_slide_root_id', array(
             'fields' => 
             array(
              0 => 'slide_root_id',
             ),
             ));
        $this->addIndex('slider_slider_serwis7', 'slider_slider_serwis7_slide_root_id', array(
             'fields' => 
             array(
              0 => 'slide_root_id',
             ),
             ));
        $this->addIndex('slider_slider_serwis8', 'slider_slider_serwis8_slide_root_id', array(
             'fields' => 
             array(
              0 => 'slide_root_id',
             ),
             ));
    }

    public function down()
    {
        $this->dropForeignKey('slider_slider_serwis5', 'slider_slider_serwis5_slide_root_id_slider_slide_serwis5_id');
        $this->dropForeignKey('slider_slider_serwis6', 'slider_slider_serwis6_slide_root_id_slider_slide_serwis6_id');
        $this->dropForeignKey('slider_slider_serwis7', 'slider_slider_serwis7_slide_root_id_slider_slide_serwis7_id');
        $this->dropForeignKey('slider_slider_serwis8', 'slider_slider_serwis8_slide_root_id_slider_slide_serwis8_id');
        $this->removeIndex('slider_slider_serwis5', 'slider_slider_serwis5_slide_root_id', array(
             'fields' => 
             array(
              0 => 'slide_root_id',
             ),
             ));
        $this->removeIndex('slider_slider_serwis6', 'slider_slider_serwis6_slide_root_id', array(
             'fields' => 
             array(
              0 => 'slide_root_id',
             ),
             ));
        $this->removeIndex('slider_slider_serwis7', 'slider_slider_serwis7_slide_root_id', array(
             'fields' => 
             array(
              0 => 'slide_root_id',
             ),
             ));
        $this->removeIndex('slider_slider_serwis8', 'slider_slider_serwis8_slide_root_id', array(
             'fields' => 
             array(
              0 => 'slide_root_id',
             ),
             ));
    }
}