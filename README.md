Network module
==============

Provides network interface information gathered by `networksetup -getinfo` and `system_profiler`


Remarks
---

* 'Wi-Fi ID' is stored as Ethernet
* ipv4conf is undetermined when the ipv4 configuration is 'Off' in the user interface.
* Getting AirPort information can take longer than the default allowed timeout. This can be disabled by setting `airport_scanned` to 1 in the networkinfo.py client script. After changing it, a new client package is needed

Configuration
-------------

###VLANS

Plot VLANS by providing an array with labels and a partial IP address of the routers. Specify multiple partials in array
if you want to group them together.
The router IP address part is queried with SQL LIKE

### Configuration file:

The configuration has to be a YAML file and is loaded from: 

`local/module_configs/ipv4routers.yml`

You can override this file by specifying the following variable:

`NETWORK_ROUTER_CONFIG_PATH=/path/to/custom/config.yml`

Example:
```
Wired: 211.88.10.1
WiFi:
    - 211.88.12.1
    - 211.88.13.1
'Private range':
    - 10.%
    - 192.168.%
    - 172.16.%
    - 172.17.%
    - 172.18.%
    - 172.19.%
    - 172.2_.%
    - 172.30.%
    - 172.31.%
Link-local:
    - 169.254.%
```


Table Schema
-----

* service (string) Service name
* status (int) Active = 1, Inactive = 0
* ethernet (string) Ethernet address
* clientid (string) Client id
* ipv4conf (string) IPv4 configuration (automatic, manual)
* ipv4ip (string) IPv4 address
* ipv4mask (string) IPv4 network mask
* ipv4router (string) IPv4 router address
* ipv6conf (string) IPv6 configuration (automatic, manual)
* ipv6ip (string) IPv6 address
* ipv6prefixlen (int) IPv6 prefix length
* ipv6router (string) IPv6 router address
* ipv4dns (string) IPv4 DNS server
* vlans (string) Set VLANs
* activemtu (interger) MTU in use by interface
* validmturange (string) Range of supported MTUs
* currentmedia (string) Current network media
* activemedia (string) Active network media
* searchdomain (string) Search domain
* externalip (string) External IP address
* ipv4switchmacaddress (string) MAC address of switch's port
* ipv4destaddresses (string) Router's IP address
* vpnservername (string) VPN URI
* vpnserveraddress (boolean) Resolved VPN server's IP address
* overrideprimary (string) Send all traffic through VPN
* ipv6clientid (string) IPv6 DHCP client ID
* ipv6destaddresses (string) IPv6 Router's IP address
* ipv6switchmacaddress (string) IPv6 switch's MAC address
* ipv6vpnservername (string) IPv6 VPN URI
* ipv6coverrideprimary (boolean) IPv6 Send all traffic through VPN
* ipv6vpnserveraddress (string) IPv6 resolved VPN server's IP address
* dhcp_domain_name (string) Retrieved DHCP domain name
* dhcp_domain_name_servers (string) Retrieved DHCP DNS
* dhcp_lease_duration (string) DHCP lease duration
* dhcp_routers (string) DHCP retrieved router
* dhcp_server_identifier (string) DHCP server IP address
* dhcp_subnet_mask (string) Retrieved subnet mask
* bsd_interface (string) BSD network interface
* netbiosname (string) NetBIOS name
* workgroup (string) Workgroup
* location (string) Current network location
* airdrop_channel (string) AirDrop channel
* airdrop_supported (boolean) AirDrop is supported
* wow_supported (boolean) Wake-on-Wireless supported
* supported_channels (string) Supported wireless channels
* supported_phymodes (string) Supported PHY modes
* wireless_card_type (string) Wireless card type
* country_code (string) Wireless country code
* firmware_version (string) Wireless card firmware version
* wireless_locale (string) Wireless card locale