<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Capsule\Manager as Capsule;

class NetworkRewrite extends Migration
{
    private $tableName = 'network';

    public function up()
    {
        $capsule = new Capsule();
        
        $capsule::schema()->table($this->tableName, function (Blueprint $table) {
            $table->string('ipv4switchmacaddress')->nullable();
            $table->string('ipv4destaddresses')->nullable();
            $table->string('vpnservername')->nullable();
            $table->string('vpnserveraddress')->nullable();
            $table->boolean('overrideprimary')->nullable();
            $table->string('ipv6clientid')->nullable();
            $table->string('ipv6switchmacaddress')->nullable();
            $table->string('ipv6destaddresses')->nullable();
            $table->string('ipv6vpnservername')->nullable();
            $table->boolean('ipv6coverrideprimary')->nullable();
            $table->string('ipv6vpnserveraddress')->nullable();
            $table->string('dhcp_domain_name')->nullable();
            $table->string('dhcp_domain_name_servers')->nullable();
            $table->string('dhcp_lease_duration')->nullable();
            $table->string('dhcp_routers')->nullable();
            $table->string('dhcp_server_identifier')->nullable();
            $table->string('dhcp_subnet_mask')->nullable();
            $table->string('bsd_interface')->nullable();
            $table->string('netbiosname')->nullable();
            $table->string('workgroup')->nullable();
            $table->string('location')->nullable();
            $table->integer('airdrop_channel')->nullable();
            $table->boolean('airdrop_supported')->nullable();
            $table->boolean('wow_supported')->nullable();
            $table->string('supported_channels')->nullable();
            $table->string('supported_phymodes')->nullable();
            $table->string('wireless_card_type')->nullable();
            $table->string('country_code')->nullable();
            $table->string('firmware_version')->nullable();
            $table->string('wireless_locale')->nullable();
        });

        // (Re)create indexes
        $capsule::schema()->table($this->tableName, function (Blueprint $table) {
            $table->index('ipv4switchmacaddress');
            $table->index('ipv4destaddresses');
            $table->index('vpnservername');
            $table->index('vpnserveraddress');
            $table->index('overrideprimary');
            $table->index('ipv6clientid');
            $table->index('ipv6destaddresses');
            $table->index('ipv6vpnservername');
            $table->index('ipv6coverrideprimary');
            $table->index('ipv6vpnserveraddress');
            $table->index('dhcp_domain_name');
            $table->index('dhcp_domain_name_servers');
            $table->index('dhcp_lease_duration');
            $table->index('dhcp_routers');
            $table->index('dhcp_server_identifier');
            $table->index('dhcp_subnet_mask');
            $table->index('bsd_interface');
            $table->index('netbiosname');
            $table->index('workgroup');
            $table->index('location');
            $table->index('airdrop_channel');
            $table->index('airdrop_supported');
            $table->index('wow_supported');
            $table->index('supported_channels');
            $table->index('supported_phymodes');
            $table->index('wireless_card_type');
            $table->index('country_code');
            $table->index('firmware_version');
            $table->index('wireless_locale');
        });
    }

    public function down()
    {
        $capsule = new Capsule();
        $capsule::schema()->table($this->tableName, function (Blueprint $table) {
            $table->dropColumn('ipv4switchmacaddress');
            $table->dropColumn('ipv4destaddresses');
            $table->dropColumn('vpnservername');
            $table->dropColumn('vpnserveraddress');
            $table->dropColumn('overrideprimary');
            $table->dropColumn('ipv6clientid');
            $table->dropColumn('ipv6destaddresses');
            $table->dropColumn('ipv6vpnservername');
            $table->dropColumn('ipv6coverrideprimary');
            $table->dropColumn('ipv6vpnserveraddress');
            $table->dropColumn('dhcp_domain_name');
            $table->dropColumn('dhcp_domain_name_servers');
            $table->dropColumn('dhcp_lease_duration');
            $table->dropColumn('dhcp_routers');
            $table->dropColumn('dhcp_server_identifier');
            $table->dropColumn('dhcp_subnet_mask');
            $table->dropColumn('bsd_interface');
            $table->dropColumn('netbiosname');
            $table->dropColumn('workgroup');
            $table->dropColumn('location');
            $table->dropColumn('airdrop_channel');
            $table->dropColumn('airdrop_supported');
            $table->dropColumn('wow_supported');
            $table->dropColumn('supported_channels');
            $table->dropColumn('supported_phymodes');
            $table->dropColumn('wireless_card_type');
            $table->dropColumn('country_code');
            $table->dropColumn('firmware_version');
            $table->dropColumn('wireless_locale');
        });
    }
}
