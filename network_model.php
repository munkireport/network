<?php

use CFPropertyList\CFPropertyList;

class Network_model extends \Model
{
    public function __construct($serial_number = '')
    {
        parent::__construct('id', strtolower('network')); //primary key, tablename
        $this->rs['id'] = '';
        $this->rs['serial_number'] = $serial_number;
        $this->rs['service'] = ''; // Service name
        $this->rs['order'] = 0; // Service order
        $this->rs['status'] = 1; // Active = 1, Inactive = 0
        $this->rs['ethernet'] = ''; // Ethernet address
        $this->rs['clientid'] = ''; // Client id
        $this->rs['ipv4conf'] = ''; // IPv4 configuration (automatic, manual)
        $this->rs['ipv4ip'] = ''; // IPv4 address
        $this->rs['ipv4mask'] = ''; // IPv4 network mask as string
        $this->rs['ipv4router'] = '';  // IPv4 router address as string
        $this->rs['ipv6conf'] = ''; // IPv6 configuration (automatic, manual)
        $this->rs['ipv6ip'] = ''; // IPv6 address as string
        $this->rs['ipv6prefixlen'] = 0; // IPv6 prefix length as int
        $this->rs['ipv6router'] = '';  // IPv6 router address as string
        $this->rs['ipv4dns'] = '';  // IPv4 DNS address(es) as string
        $this->rs['externalip'] = '';  // External IP address as string
        $this->rs['vlans'] = '';  // VLANs on service as string
        $this->rs['activemtu'] = 0;  // Active MTU as int
        $this->rs['validmturange'] = '';  // Range of valid MTUs as string
        $this->rs['currentmedia'] = '';  // Current media as string
        $this->rs['activemedia'] = '';  // Active media as string
        $this->rs['searchdomain'] = '';  // Search domain(s) 
        $this->rs['ipv4switchmacaddress'] = '';
        $this->rs['ipv4destaddresses'] = '';
        $this->rs['vpnservername'] = '';
        $this->rs['vpnserveraddress'] = '';
        $this->rs['overrideprimary'] = ''; // True/False
        $this->rs['ipv6clientid'] = '';
        $this->rs['ipv6destaddresses'] = '';
        $this->rs['ipv6switchmacaddress'] = '';
        $this->rs['ipv6vpnservername'] = '';
        $this->rs['ipv6coverrideprimary'] = ''; // True/False
        $this->rs['ipv6vpnserveraddress'] = '';
        $this->rs['dhcp_domain_name'] = '';
        $this->rs['dhcp_domain_name_servers'] = '';
        $this->rs['dhcp_lease_duration'] = '';
        $this->rs['dhcp_routers'] = '';
        $this->rs['dhcp_server_identifier'] = '';
        $this->rs['dhcp_subnet_mask'] = '';
        $this->rs['bsd_interface'] = '';
        $this->rs['location'] = '';
        $this->rs['netbiosname'] = '';
        $this->rs['workgroup'] = '';
        $this->rs['airdrop_channel'] = '';
        $this->rs['airdrop_supported'] = ''; // True/False
        $this->rs['wow_supported'] = ''; // True/False
        $this->rs['supported_channels'] = '';
        $this->rs['supported_phymodes'] = '';
        $this->rs['wireless_card_type'] = '';
        $this->rs['country_code'] = '';
        $this->rs['firmware_version'] = '';
        $this->rs['wireless_locale'] = '';

        return $this;
    }

    /**
     * Process data sent by postflight
     *
     * @param string data
     * @author abn290, rewritten by tuxudo
     **/
    public function process($data)
    {
        // If data is empty, throw error
        if (! $data) {
            throw new Exception("Error Processing Time Machine Module Request: No data found", 1);
        } else if (substr( $data, 0, 30 ) != '<?xml version="1.0" encoding="' ) { // Else if old style text, process with old text based handler
            
            // Translate network strings to db fields
            $translate = array(
                'Ethernet Address: ' => 'ethernet',
                'Client ID: ' => 'clientid',
                'Wi-Fi ID: ' => 'ethernet',
                'IP address: ' => 'ipv4ip',
                'DNS: ' => 'ipv4dns',
                'External IP: ' => 'externalip',
                'Search Domain: ' => 'searchdomain',
                'Subnet mask: ' => 'ipv4mask',
                'Router: ' => 'ipv4router',
                'IPv6: ' => 'ipv6conf',
                'VLANs: ' => 'vlans',
                'Active MTU: ' => 'activemtu',
                'Valid MTU Range: ' => 'validmturange',
                'Current: ' => 'currentmedia',
                'Active: ' => 'activemedia',
                'IPv6 IP address: ' => 'ipv6ip',
                'IPv6 Prefix Length: ' => 'ipv6prefixlen',
                'IPv6 Router: ' => 'ipv6router');

            // ipv4 dhcp configuration strings
            // Unfortunately you cannot detect if IPv4 is off with
            // netwerksetup -getinfo
            $ipv4conf = array(
                'DHCP Configuration' => 'DHCP',
                'Manually Using DHCP Router Configuration' => 'Manual',
                'BOOTP Configuration' => 'BOOTP',
                'Manual Configuration' => 'Manual');

            $services = array();
            $order = 1; // Service order

            // Parse network data
            foreach (explode("\n", $data) as $line) {
                if (preg_match('/^Service: (.*)$/', $line, $result)) {
                    $service = $result[1];
                    $services[$service] = $this->rs; // Copy db fields
                    $services[$service]['order'] = $order++;
                    continue;
                }

                // Skip lines before service starts
                if (! isset($service)) {
                    continue;
                }

                // Translate standard entries
                foreach ($translate as $search => $field) {
                    if (strpos($line, $search) === 0) {
                        $services[$service][$field] = substr($line, strlen($search));
                        break;
                    }
                }

                if (strpos($line, 'disabled')) {
                    $services[$service]['status'] = 0;
                    echo "$service disabled";
                    continue;
                }

                // Look through the standard ipv4 config strings
                foreach ($ipv4conf as $search => $field) {
                    if (strpos($line, $search) === 0) {
                        $services[$service]['ipv4conf'] = $field;
                        break;
                    }
                }
            }

            // Delete previous entries
            $this->deleteWhere('serial_number=?', $this->serial_number);

            // Now only store entries with a valid ethernet address or have a valid IP address
            foreach ($services as $service => $data) {
                if (($data['ethernet'] && strlen($data['ethernet']) == 17) || strpos($data['ipv4ip'], '.') || strpos($data['ipv6ip'], ':') ) {
                    $this->merge($data);
                    $this->id = '';
                    $this->service = $service;
                    $this->save();
                }
            }
            
        } else { // Else process with new XML handler
            
            // Delete previous entries
            $this->deleteWhere('serial_number=?', $this->serial_number);
            
            // Process incoming network_info.plist
            $parser = new CFPropertyList();
            $parser->parse($data);
            $plist = $parser->toArray();
            
            // Process each service
            foreach ($plist as $service) {
                foreach (array('service', 'order', 'status', 'ethernet', 'clientid', 'ipv4conf', 'ipv4ip', 'ipv4mask', 'ipv4router', 'ipv6conf', 'ipv6ip', 'ipv6prefixlen', 'ipv6router', 'ipv4dns', 'externalip', 'vlans', 'activemtu', 'validmturange', 'currentmedia', 'activemedia', 'searchdomain', 'ipv4switchmacaddress', 'ipv4destaddresses', 'vpnservername', 'vpnserveraddress', 'overrideprimary', 'ipv6clientid', 'ipv6destaddresses', 'ipv6switchmacaddress', 'ipv6vpnservername', 'ipv6coverrideprimary', 'ipv6vpnserveraddress', 'dhcp_domain_name', 'dhcp_domain_name_servers', 'dhcp_lease_duration', 'dhcp_routers', 'dhcp_server_identifier', 'dhcp_subnet_mask', 'bsd_interface', 'netbiosname', 'workgroup', 'location', 'airdrop_channel', 'airdrop_supported', 'wow_supported', 'supported_channels', 'supported_phymodes', 'wireless_card_type', 'wireless_card_type', 'firmware_version', 'country_code', 'wireless_locale') as $item) {
                    // If key does not exist in $service, null it
                    if ( ! array_key_exists($item, $service) || $service[$item] == '' && $service[$item] != '0') {
                        $this->$item = null;

                    // IPv6 IP address can be long arrays
                    } else if ($item == 'ipv6ip' && strpos($service[$item], ',') !== false) {
                        $ipv6_array = explode(",", $service[$item]);
                        $this->$item = $ipv6_array[0];

                    // Set the db fields to be the same as those in service
                    } else {
                        $this->$item = $service[$item];
                    }
                }

                // Save the data. We have a mighty need for it
                $this->id = '';
                $this->save();
            }
        }
    }
}
