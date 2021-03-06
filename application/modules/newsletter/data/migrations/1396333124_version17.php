<?php
/**
 * This class has been auto-generated by the Doctrine ORM Framework
 */
class Version17 extends Doctrine_Migration_Base
{
    public function up()
    {
        $this->dropForeignKey('newsletter_page', 'newsletter_page_attachment_id_attachment_serwis10_attachment_id');
        $this->dropForeignKey('newsletter_page', 'newsletter_page_photo_root_id_media_photo_id');
        $this->createForeignKey('newsletter_message_subscriber', 'newsletter_message_subscriber_message_id_newsletter_message_id', array(
             'name' => 'newsletter_message_subscriber_message_id_newsletter_message_id',
             'local' => 'message_id',
             'foreign' => 'id',
             'foreignTable' => 'newsletter_message',
             ));
        $this->createForeignKey('newsletter_message_subscriber', 'nsni_1', array(
             'name' => 'nsni_1',
             'local' => 'subscriber_id',
             'foreign' => 'id',
             'foreignTable' => 'newsletter_subscriber',
             ));
        $this->addIndex('newsletter_message_subscriber', 'newsletter_message_subscriber_message_id', array(
             'fields' => 
             array(
              0 => 'message_id',
             ),
             ));
        $this->addIndex('newsletter_message_subscriber', 'newsletter_message_subscriber_subscriber_id', array(
             'fields' => 
             array(
              0 => 'subscriber_id',
             ),
             ));
    }

    public function down()
    {
        $this->createForeignKey('newsletter_page', 'newsletter_page_attachment_id_attachment_serwis10_attachment_id', array(
             'name' => 'newsletter_page_attachment_id_attachment_serwis10_attachment_id',
             'local' => 'attachment_id',
             'foreign' => 'id',
             'foreignTable' => 'attachment_serwis10_attachment',
             ));
        $this->createForeignKey('newsletter_page', 'newsletter_page_photo_root_id_media_photo_id', array(
             'name' => 'newsletter_page_photo_root_id_media_photo_id',
             'local' => 'photo_root_id',
             'foreign' => 'id',
             'foreignTable' => 'media_photo',
             ));
        $this->dropForeignKey('newsletter_message_subscriber', 'newsletter_message_subscriber_message_id_newsletter_message_id');
        $this->dropForeignKey('newsletter_message_subscriber', 'nsni_1');
        $this->removeIndex('newsletter_message_subscriber', 'newsletter_message_subscriber_message_id', array(
             'fields' => 
             array(
              0 => 'message_id',
             ),
             ));
        $this->removeIndex('newsletter_message_subscriber', 'newsletter_message_subscriber_subscriber_id', array(
             'fields' => 
             array(
              0 => 'subscriber_id',
             ),
             ));
    }
}