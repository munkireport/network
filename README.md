Network module
==============

Provides network interface information gathered by `networksetup -getinfo`


Remarks
---

* 'Wi-Fi ID' is stored as ethernet
* Entries without an ethernet address or valid IP address are not stored
* ipv4conf is undetermined when the ipv4 configuration is 'Off' in the user interface.

Configuration
-------------

###VLANS

Plot VLANS by providing an array with labels and
a partial IP address of the routers. Specify multiple partials in array
if you want to group them together.
The router IP adress part is queried with SQL LIKE

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
