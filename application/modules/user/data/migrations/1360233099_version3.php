<?php
/**
 * This class has been auto-generated by the Doctrine ORM Framework
 */
class Version3 extends Doctrine_Migration_Base
{
    public function up()
    {
        $this->addColumn('user_profile', 'address', 'string', '255', array(
             ));
        $this->addColumn('user_profile', 'phone', 'string', '255', array(
             ));
        $this->addColumn('user_profile', 'company_name', 'string', '255', array(
             ));
        $this->addColumn('user_profile', 'website', 'string', '255', array(
             ));
        $this->addColumn('user_profile', 'nip', 'string', '255', array(
             ));
        $this->addColumn('user_profile', 'proxy_name', 'string', '255', array(
             ));
    }

    public function down()
    {
        $this->removeColumn('user_profile', 'address');
        $this->removeColumn('user_profile', 'phone');
        $this->removeColumn('user_profile', 'company_name');
        $this->removeColumn('user_profile', 'website');
        $this->removeColumn('user_profile', 'nip');
        $this->removeColumn('user_profile', 'proxy_name');
    }
}